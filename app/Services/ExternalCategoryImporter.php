<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Import danh mục sản phẩm từ API bên ngoài vào product_categories.
 *
 * Cấu trúc API trả về (mảng):
 * [
 *   { "id": "01", "name": "Săm lốp xe tải", "groups": [{"id":"0101","name":"Lốp tải nhẹ Bias"}] },
 *   { "id": "02", "name": "Lốp xe máy" },
 *   ...
 * ]
 *
 * Mapping vào DB:
 *   product_categories.code  = item['id']   (e.g. "01", "0101")
 *   parent_id                = null cho cấp 1, id của cha cho cấp 2
 *   product_category_translations.name/slug
 */
class ExternalCategoryImporter
{
    /** @var array<string, int>  cache code → id trong DB */
    private array $codeToId = [];

    /**
     * Import toàn bộ danh mục trả về từ API.
     * Hỗ trợ đệ quy nhiều cấp (root → sub → subsub ...).
     *
     * API trả về key "category" cho tên, "sub" cho danh mục con.
     *
     * @param  array  $categories  Raw array từ ExternalApiService::getCategories()
     * @return array{created: int, updated: int}
     */
    public function import(array $categories): array
    {
        $stats = ['created' => 0, 'updated' => 0];
        Log::channel('import_external')->info('[Category] Bắt đầu import danh mục', [
            'total_from_api' => count($categories),
            'raw_ids'        => array_map(fn ($c) => $c['id'] ?? null, $categories),
        ]);
        $this->importLevel($categories, null, null, $stats);
        Log::channel('import_external')->info('[Category] Kết thúc import danh mục', [
            'created'   => $stats['created'],
            'updated'   => $stats['updated'],
            'code_to_id' => $this->codeToId,
        ]);
        return $stats;
    }

    /**
     * Đệ quy import từng cấp danh mục.
     * Danh mục con lưu code = parentCode_code (vd: 01_0101) để tránh trùng code khác cha.
     *
     * @param  array  $items      Mảng item từ API
     * @param  int|null  $parentId   ID danh mục cha trong DB
     * @param  string|null  $parentCode  Code đã lưu của cha (để prefix cho con), null = cấp gốc
     */
    private function importLevel(array $items, ?int $parentId, ?string $parentCode, array &$stats): void
    {
        foreach ($items as $item) {
            $rawCode = (string) ($item['id'] ?? '');
            // API dùng key "category" cho tên (không phải "name")
            $name = (string) ($item['category'] ?? $item['name'] ?? '');

            if ($rawCode === '' || $name === '') {
                Log::channel('import_external')->warning('[Category] Bỏ qua item thiếu code/name', [
                    'item_keys' => array_keys($item),
                    'id'        => $item['id'] ?? null,
                    'category'  => $item['category'] ?? null,
                    'name'      => $item['name'] ?? null,
                    'parent_id' => $parentId,
                ]);
                continue;
            }

            // Code lưu DB: cấp gốc = rawCode, cấp con = parentCode_rawCode (tránh trùng 0101 ở nhiều cha)
            $storedCode = $parentCode !== null && $parentCode !== ''
                ? $parentCode . '_' . $rawCode
                : $rawCode;

            $this->upsertCategory($storedCode, $name, $parentId, $stats);

            // Đệ quy vào sub-categories (truyền storedCode làm parentCode cho con)
            $subs = $item['sub'] ?? $item['groups'] ?? $item['children'] ?? [];
            if (is_array($subs) && count($subs) > 0) {
                $newParentId = $this->codeToId[$storedCode] ?? null;
                $this->importLevel($subs, $newParentId, $storedCode, $stats);
            }
        }
    }

    /**
     * Tạo hoặc cập nhật 1 category + translations.
     * Lưu mã gốc (01, 03, 04, 04_2101...). Chỉ đổi 01/03/04 sang slug khi đã import sản phẩm xong (trong command).
     */
    private function upsertCategory(string $code, string $name, ?int $parentId, array &$stats): void
    {
        $existing = DB::table('product_categories')->where('code', $code)->first();

        if ($existing) {
            DB::table('product_categories')->where('id', $existing->id)->update([
                'parent_id'  => $parentId,
                'updated_at' => now(),
            ]);
            $categoryId = $existing->id;
            $stats['updated']++;
            Log::channel('import_external')->debug('[Category] Cập nhật', ['code' => $code, 'name' => $name, 'id' => $categoryId]);
        } else {
            $categoryId = DB::table('product_categories')->insertGetId([
                'parent_id'   => $parentId,
                'code'        => $code,
                'is_active'   => true,
                'is_featured' => false,
                'sort_order'  => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $stats['created']++;
            Log::channel('import_external')->debug('[Category] Tạo mới', ['code' => $code, 'name' => $name, 'id' => $categoryId]);
        }

        $this->codeToId[$code] = $categoryId;

        // Upsert translation (vi + en)
        foreach (['vi', 'en'] as $lang) {
            $slug = $this->uniqueSlug(Str::slug($name) . ($lang === 'en' ? '' : ''), $categoryId, $lang);

            $exists = DB::table('product_category_translations')
                ->where('category_id', $categoryId)
                ->where('language', $lang)
                ->exists();

            if ($exists) {
                DB::table('product_category_translations')
                    ->where('category_id', $categoryId)
                    ->where('language', $lang)
                    ->update(['name' => $name, 'updated_at' => now()]);
            } else {
                DB::table('product_category_translations')->insert([
                    'category_id' => $categoryId,
                    'language'    => $lang,
                    'name'        => $name,
                    'slug'        => $slug,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }

    /**
     * Lấy cache code → id (dùng sau khi import để lấy id cho product importer).
     */
    public function getCodeToIdMap(): array
    {
        if (!Schema::hasTable('product_categories')) {
            return [];
        }
        if (empty($this->codeToId)) {
            $rows = DB::table('product_categories')->select('id', 'code')->get();
            foreach ($rows as $row) {
                $this->codeToId[$row->code] = $row->id;
            }

            // Khi categories đã được đổi tên (01→sam-lop-xe-tai, 03→sam-lop-xe-may, 04→lop-advenza-pcr),
            // thêm alias numeric để importer vẫn nhận ra mã gốc từ API (item_category_code = "04", "01", "03").
            $numericAliases = [
                '04' => 'lop-advenza-pcr',
                '01' => 'sam-lop-xe-tai',
                '03' => 'sam-lop-xe-may',
            ];
            foreach ($numericAliases as $numericCode => $slug) {
                if (!isset($this->codeToId[$numericCode]) && isset($this->codeToId[$slug])) {
                    $this->codeToId[$numericCode] = $this->codeToId[$slug];
                }
            }
        }
        return $this->codeToId;
    }

    /**
     * Tạo slug unique trong product_category_translations.
     */
    private function uniqueSlug(string $base, int $categoryId, string $lang): string
    {
        $slug    = $base ?: 'danh-muc';
        $counter = 1;

        while (
            DB::table('product_category_translations')
                ->where('slug', $slug)
                ->where('language', $lang)
                ->where('category_id', '!=', $categoryId)
                ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}

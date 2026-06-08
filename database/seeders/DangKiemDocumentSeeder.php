<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\PostCategory;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\UploadedFile;

/**tạo thư mục TRANG THÔNG TIN ĐĂNG KIỂM up lên root folder 
 * cấp bậc TRANG THÔNG TIN ĐĂNG KIỂM/thong-tin-dang-kiem-sam-lop-o-to-tai/22_11.00R20 18PR CA662A_0112/file.pdf
 * đặt tên trong giống slug của danh mục có dữ liệu ví dụ "thong-tin-dang-kiem-sam-lop-o-to-tai" có danh mục bài 
 */
class DangKiemDocumentSeeder extends Seeder
{
    /**
     * Map: tên thư mục trên disk → slug category trong DB.
     * Dùng Str::slug(tên folder hiện tại) làm key để phòng Unicode NFD/NFC.
     */
    private const FOLDER_TO_SLUG_MAP = [
        'thong-tin-dang-kiem-lop-avenza-pcr'        => 'thong-tin-dang-kiem-lop-avenza-pcr',
        'thong-tin-dang-kiem-sam-lop-xe-dien'   => 'thong-tin-dang-kiem-sam-lop-xe-dien',
        'thong-tin-dang-kiem-sam-lop-xe-may'        => 'thong-tin-dang-kiem-sam-lop-xe-may',
        'thong-tin-dang-kiem-sam-lop-xe-dap'   => 'thong-tin-dang-kiem-sam-lop-xe-dap',
        'thong-tin-dang-kiem-sam-lop-o-to-tai'      => 'thong-tin-dang-kiem-sam-lop-o-to-tai',
        'thong-tin-dang-kiem-sam-lop-chuyen-dung'   => 'thong-tin-dang-kiem-sam-lop-chuyen-dung',
    ];

    /** Slug các root category do seeder này quản lý — dùng khi xóa dữ liệu cũ (unique values từ map trên) */
    private const ROOT_SLUGS_TO_CLEAR = [
        'thong-tin-dang-kiem-lop-avenza-pcr',
        'thong-tin-dang-kiem-sam-lop-xe-dien',
        'thong-tin-dang-kiem-sam-lop-xe-may',
        'thong-tin-dang-kiem-sam-lop-xe-dap',
        'thong-tin-dang-kiem-sam-lop-o-to-tai',
        'thong-tin-dang-kiem-sam-lop-chuyen-dung',
    ];

    private string $sourceBase;
    private string $storagePath;
    private string $fullStoragePath;
    private int $childSortCounter = 1;

    public function run(): void
    {
        $this->clearOldDangKiemData();

        // Tìm folder nguồn bằng cách scan root — tránh lỗi NFC/NFD Unicode encoding
        // giữa macOS (NFD) và Linux (NFC) khi upload qua rsync/scp
        $this->sourceBase     = $this->findSourceFolder();
        $this->storagePath    = 'uploads/documents/posts';
        $this->fullStoragePath = storage_path('app/public/' . $this->storagePath);

        if (!is_dir($this->fullStoragePath)) {
            mkdir($this->fullStoragePath, 0755, true);
        }

        if (!$this->sourceBase || !is_dir($this->sourceBase)) {
            $this->command->error('Không tìm thấy thư mục nguồn "TRANG THÔNG TIN ĐĂNG KIỂM" ở: ' . base_path());
            $this->command->line('Các folder hiện có ở root:');
            foreach (array_diff(scandir(base_path()), ['.', '..']) as $item) {
                if (is_dir(base_path($item))) {
                    $this->command->line('  - ' . $item);
                }
            }
            return;
        }

        $this->command->info('📂 Quét thư mục: ' . $this->sourceBase);

        $rootFolders = $this->listDirs($this->sourceBase);
        $rootSort = 1;

        foreach ($rootFolders as $rootName) {
            $folderSlug = Str::slug($rootName);

            // Tra cứu slug đích từ map; bỏ qua folder không có trong map
            $targetSlug = self::FOLDER_TO_SLUG_MAP[$folderSlug] ?? null;
            if (!$targetSlug) {
                $this->command->warn("  ⏭  Bỏ qua folder không có trong map: {$rootName} (slug={$folderSlug})");
                continue;
            }

            $rootCat = PostCategory::whereHas('translations', function ($q) use ($targetSlug) {
                $q->where('slug', $targetSlug)->where('language', 'vi');
            })->first();

            if (!$rootCat) {
                $rootCat = PostCategory::create([
                    'parent_id'  => null,
                    'is_active'  => true,
                    'is_featured' => false,
                    'sort_order' => $rootSort,
                ]);
                $rootCat->handleTranslations([
                    'name_vi'             => $rootName,
                    'name_en'             => $rootName,
                    'slug_vi'             => $targetSlug,
                    'slug_en'             => $targetSlug,
                    'description_vi'      => $rootName,
                    'description_en'      => $rootName,
                    'meta_title_vi'       => $rootName,
                    'meta_title_en'       => $rootName,
                    'meta_description_vi' => $rootName,
                    'meta_description_en' => $rootName,
                ]);
                $this->command->info("  ✅ Root [{$rootSort}] tạo mới: {$rootName} → {$targetSlug}");
            } else {
                $this->command->info("  ✅ Root: {$rootName} → {$targetSlug} (id={$rootCat->id})");
            }

            $rootSort++;
            $this->childSortCounter = 1;
            $this->processFolder($this->sourceBase . '/' . $rootName, $rootCat);
        }

        $this->command->info('🎉 Hoàn tất seeding TRANG THÔNG TIN ĐĂNG KIỂM.');

        // Tự xóa folder nguồn sau khi seed xong
        $this->deleteSourceFolder();
    }

    /**
     * Xóa toàn bộ bài viết và danh mục do seeder này quản lý (root slugs + con) trước khi seed lại.
     */
    private function clearOldDangKiemData(): void
    {
        $rootIds = DB::table('postcategories as c')
            ->join('postcategory_translations as t', 'c.id', '=', 't.postcategory_id')
            ->where('t.language', 'vi')
            ->whereIn('t.slug', self::ROOT_SLUGS_TO_CLEAR)
            ->whereNull('c.parent_id')
            ->pluck('c.id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        if (empty($rootIds)) {
            $this->command->info('Không có dữ liệu đăng kiểm cũ để xóa.');
            return;
        }

        $categoryIds = $this->getCategoryIdsWithDescendants($rootIds);

        $postIds = DB::table('post_postcategory')
            ->whereIn('postcategory_id', $categoryIds)
            ->pluck('post_id')
            ->unique()
            ->values()
            ->toArray();

        if (!empty($postIds)) {
            DB::table('post_translations')->whereIn('post_id', $postIds)->delete();
            DB::table('post_postcategory')->whereIn('post_id', $postIds)->delete();

            $fileIds = DB::table('posts')->whereIn('id', $postIds)->pluck('document_file_id')->filter()->unique()->values()->toArray();
            DB::table('posts')->whereIn('id', $postIds)->delete();
            if (!empty($fileIds)) {
                DB::table('uploaded_files')->whereIn('id', $fileIds)->delete();
            }
            $this->command->info('Đã xóa ' . count($postIds) . ' bài viết và file đính kèm.');
        }

        DB::table('postcategory_translations')->whereIn('postcategory_id', $categoryIds)->delete();
        DB::table('post_postcategory')->whereIn('postcategory_id', $categoryIds)->delete();

        $this->deletePostCategoriesFromLeavesToRoots($categoryIds);
        $this->command->info('Đã xóa ' . count($categoryIds) . ' danh mục đăng kiểm.');
    }

    /** Lấy ID bản thân + mọi ID con (đệ quy) */
    private function getCategoryIdsWithDescendants(array $parentIds): array
    {
        $ids = array_map('intval', $parentIds);
        $childIds = DB::table('postcategories')->whereIn('parent_id', $parentIds)->pluck('id')->toArray();
        if (!empty($childIds)) {
            $ids = array_merge($ids, $this->getCategoryIdsWithDescendants($childIds));
        }
        return array_values(array_unique($ids));
    }

    /** Xóa postcategories theo thứ tự từ lá lên gốc (đảm bảo FK parent_id). */
    private function deletePostCategoriesFromLeavesToRoots(array $categoryIds): void
    {
        $ids = array_values(array_unique($categoryIds));
        while (!empty($ids)) {
            $idsWhoAreParents = DB::table('postcategories')->whereIn('parent_id', $ids)->pluck('parent_id')->unique()->toArray();
            $leaves = array_values(array_diff($ids, $idsWhoAreParents));
            if (empty($leaves)) {
                break;
            }
            DB::table('postcategories')->whereIn('id', $leaves)->delete();
            $ids = array_values(array_diff($ids, $leaves));
        }
    }

    private function deleteSourceFolder(): void
    {
        if (!is_dir($this->sourceBase)) {
            return;
        }

        $this->command->warn('🗑  Đang xóa folder nguồn: ' . $this->sourceBase);
        $this->deleteDirectory($this->sourceBase);

        if (!is_dir($this->sourceBase)) {
            $this->command->info('✅ Đã xóa folder nguồn thành công.');
        } else {
            $this->command->error('❌ Không thể xóa folder nguồn, hãy xóa thủ công: ' . $this->sourceBase);
        }
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                //$this->deleteDirectory($path);
            } else {
                //unlink($path);
            }
        }
        //rmdir($dir);
    }

    /**
     * Duyệt một thư mục:
     *  - Nếu chứa PDF trực tiếp → tạo child PostCategory + Post cho từng PDF
     *  - Nếu chỉ có sub-folder (thư mục trung gian) → đệ quy với cùng $parentCat
     */
    private function processFolder(string $dirPath, PostCategory $parentCat): void
    {
        $dirs = $this->listDirs($dirPath);
        $pdfs = $this->listPdfs($dirPath);

        if (count($dirs) === 0 && count($pdfs) === 0) {
            return;
        }

        foreach ($dirs as $subName) {
            $subPath = $dirPath . '/' . $subName;
            $subPdfs = $this->listPdfs($subPath);
            $subDirs = $this->listDirs($subPath);

            if (count($subPdfs) > 0) {
                // Thư mục con trực tiếp chứa PDF → tạo child category
                $this->createChildCategoryWithPdfs($subName, $subPath, $subPdfs, $parentCat);
            } elseif (count($subDirs) > 0) {
                // Thư mục trung gian (e.g. "GCN cung cap VF") → đệ quy, gắn vào cùng cha
                $this->command->line("    ↳ Thư mục trung gian: {$subName}");
                $this->processFolder($subPath, $parentCat);
            }
        }
    }

    private function createChildCategoryWithPdfs(
        string $folderName,
        string $folderPath,
        array $pdfFiles,
        PostCategory $parentCat
    ): void {
        $baseSlug = Str::slug($folderName);

        // Tìm theo slug gốc trước (để idempotent: chạy lại không tạo trùng)
        $childCat = PostCategory::where('parent_id', $parentCat->id)
            ->whereHas('translations', function ($q) use ($baseSlug) {
                $q->where('slug', $baseSlug)->where('language', 'vi');
            })->first();

        // Fallback: tìm theo tên
        if (!$childCat) {
            $childCat = PostCategory::where('parent_id', $parentCat->id)
                ->whereHas('translations', function ($q) use ($folderName) {
                    $q->where('name', $folderName)->where('language', 'vi');
                })->first();
        }

        $slug = $baseSlug;

        if (!$childCat) {
            // Chỉ uniquify slug khi thật sự cần (tránh trùng với category khác cha)
            $slug = $this->uniqueSlug($baseSlug);
            $childCat = PostCategory::create([
                'parent_id'  => $parentCat->id,
                'is_active'  => true,
                'is_featured' => false,
                'sort_order' => $this->childSortCounter,
            ]);
            $childCat->handleTranslations([
                'name_vi'             => $folderName,
                'name_en'             => $folderName,
                'slug_vi'             => $slug,
                'slug_en'             => $slug,
                'description_vi'      => $folderName,
                'description_en'      => $folderName,
                'meta_title_vi'       => $folderName,
                'meta_title_en'       => $folderName,
                'meta_description_vi' => $folderName,
                'meta_description_en' => $folderName,
            ]);
            $this->command->line("    └─ [{$this->childSortCounter}] {$folderName}");
            $this->childSortCounter++;
        } else {
            $this->command->warn("    ⚠️  Child đã tồn tại (chỉ thêm file): {$folderName}");
        }

        $docSort = 1;
        foreach ($pdfFiles as $pdfFile) {
            $srcFile = $folderPath . '/' . $pdfFile;
            if (!is_file($srcFile)) {
                continue;
            }

            $baseName    = pathinfo($pdfFile, PATHINFO_FILENAME);
            $storedName  = Str::slug($baseName) . '-' . uniqid() . '.pdf';
            $destFile    = $this->fullStoragePath . '/' . $storedName;

            if (!copy($srcFile, $destFile)) {
                $this->command->warn("       ⚠️  Không copy được: {$pdfFile}");
                continue;
            }

            $uploadedFile = UploadedFile::create([
                'original_name' => $pdfFile,
                'stored_name'   => $storedName,
                'path'          => $this->storagePath,
                'mime_type'     => 'application/pdf',
                'size'          => filesize($destFile),
                'sha256'        => hash_file('sha256', $destFile),
                'uploaded_by'   => null,
            ]);

            $titleVi  = $baseName;
            $postSlug = Str::slug($titleVi) . '-' . $slug . '-' . $docSort;

            $post = Post::create([
                'status'           => 'published',
                'is_active'        => true,
                'is_featured'      => false,
                'sort_order'       => $docSort,
                'published_at'     => now(),
                'document_file_id' => $uploadedFile->id,
            ]);

            foreach (['vi', 'en'] as $lang) {
                PostTranslation::updateOrCreate(
                    ['post_id' => $post->id, 'language' => $lang],
                    [
                        'title'   => $titleVi,
                        'slug'    => $postSlug . ($lang === 'en' ? '-en' : ''),
                        'excerpt' => $titleVi,
                        'content' => '<p>' . e($titleVi) . '</p>',
                    ]
                );
            }

            $post->postcategories()->attach($childCat->id, [
                'is_primary' => true,
                'sort_order' => $docSort,
            ]);

            $docSort++;
        }
    }

    private function listDirs(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }
        return array_values(array_filter(scandir($path), function ($item) use ($path) {
            return $item !== '.' && $item !== '..' && $item !== '.DS_Store'
                && is_dir($path . '/' . $item);
        }));
    }

    private function listPdfs(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }
        return array_values(array_filter(scandir($path), function ($item) use ($path) {
            return is_file($path . '/' . $item)
                && strtolower(pathinfo($item, PATHINFO_EXTENSION)) === 'pdf';
        }));
    }

    /**
     * Tìm folder nguồn ở root project bằng cách scan thực tế.
     * Tránh lỗi NFC/NFD: PHP string 'TRANG THÔNG TIN ĐĂNG KIỂM' có thể
     * khác encoding so với tên folder trên disk (macOS NFD vs Linux NFC).
     */
    private function findSourceFolder(): ?string
    {
        $root = base_path();

        // Thử trực tiếp trước (nếu encoding khớp)
        if (is_dir($root . '/TRANG THÔNG TIN ĐĂNG KIỂM')) {
            return $root . '/TRANG THÔNG TIN ĐĂNG KIỂM';
        }

        // Scan và tìm bằng slug (loại bỏ dấu) — tránh NFC/NFD mismatch
        foreach (array_diff(scandir($root), ['.', '..']) as $item) {
            if (!is_dir($root . '/' . $item)) {
                continue;
            }
            if (Str::slug($item) === 'trang-thong-tin-dang-kiem') {
                return $root . '/' . $item;
            }
            // Fallback: so sánh ASCII (bỏ dấu) chứa "TRANG" và "DANG"
            $ascii = strtoupper((string) iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $item));
            if (str_starts_with($ascii, 'TRANG') && str_contains($ascii, 'DANG')) {
                return $root . '/' . $item;
            }
        }

        return null;
    }

    /** Tạo slug duy nhất bằng cách thêm suffix nếu đã tồn tại */
    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $counter = 2;
        while (
            \App\Models\PostCategoryTranslation::where('slug', $slug)
            ->where('language', 'vi')
            ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }
        return $slug;
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PostCategory;
use App\Models\PostCategoryTranslation;

/**
 * Seeder tạo sẵn 6 danh mục root cho đăng kiểm TRƯỚC KHI chạy DangKiemDocumentSeeder.
 *
 * Thứ tự chạy trong DatabaseSeeder:
 *   $this->call([
 *       DangKiemCategorySeeder::class,   // ← chạy trước
 *       DangKiemDocumentSeeder::class,   // ← chạy sau
 *   ]);
 */
class DangKiemCategorySeeder extends Seeder
{
    private const CATEGORIES = [
        [
            'name'       => 'Săm Lốp Ô Tô Tải',
            'slug'       => 'thong-tin-dang-kiem-sam-lop-o-to-tai',
            'sort_order' => 1,
        ],
        [
            'name'       => 'Săm Lốp Xe Đạp',
            'slug'       => 'thong-tin-dang-kiem-sam-lop-xe-dap',
            'sort_order' => 2,
        ],
        [
            'name'       => 'Săm Lốp Xe Máy',
            'slug'       => 'thong-tin-dang-kiem-sam-lop-xe-may',
            'sort_order' => 3,
        ],
        [
            'name'       => 'Lốp Avenza PCR',
            'slug'       => 'thong-tin-dang-kiem-lop-avenza-pcr',
            'sort_order' => 4,
        ],
        [
            'name'       => 'Săm Lốp Xe Điện',
            'slug'       => 'thong-tin-dang-kiem-sam-lop-xe-dien',
            'sort_order' => 5,
        ],
        [
            'name'       => 'Săm Lốp Chuyên Dụng',
            'slug'       => 'thong-tin-dang-kiem-sam-lop-chuyen-dung',
            'sort_order' => 6,
        ],
    ];

    public function run(): void
    {
        $this->command->info('📁 Bắt đầu tạo danh mục đăng kiểm...');

        foreach (self::CATEGORIES as $cat) {
            // Kiểm tra đã tồn tại chưa (idempotent — chạy lại không bị trùng)
            $exists = DB::table('postcategories as c')
                ->join('postcategory_translations as t', 'c.id', '=', 't.postcategory_id')
                ->where('t.language', 'vi')
                ->where('t.slug', $cat['slug'])
                ->whereNull('c.parent_id')
                ->exists();

            if ($exists) {
                $this->command->warn("  ⏭  Đã tồn tại, bỏ qua: {$cat['name']} ({$cat['slug']})");
                continue;
            }
            $rootCategoryId = PostCategoryTranslation::where('slug', 'thong-tin-dang-kiem')->where('language', 'vi')->first()->postcategory_id;
            $category = PostCategory::create([
                'parent_id'   => $rootCategoryId,
                'is_active'   => true,
                'is_featured' => false,
                'sort_order'  => $cat['sort_order'],
            ]);

            $category->handleTranslations([
                'name_vi'             => $cat['name'],
                'name_en'             => $cat['name'],
                'slug_vi'             => $cat['slug'],
                'slug_en'             => $cat['slug'],
                'description_vi'      => $cat['name'],
                'description_en'      => $cat['name'],
                'meta_title_vi'       => $cat['name'],
                'meta_title_en'       => $cat['name'],
                'meta_description_vi' => $cat['name'],
                'meta_description_en' => $cat['name'],
            ]);

            $this->command->info("  ✅ [{$cat['sort_order']}] Tạo mới: {$cat['name']} → {$cat['slug']}");
        }

        $this->command->info('🎉 Hoàn tất tạo 6 danh mục đăng kiểm.');
    }
}

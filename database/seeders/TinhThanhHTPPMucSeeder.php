<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\PostCategory;

class TinhThanhHTPPMucSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tìm category "Việt Nam" trong "Quốc gia Hệ thống phân phối"
        $vietnamCategory = PostCategory::whereHas('translations', function($query) {
            $query->where('name', 'Việt Nam');
        })->first();

        if (!$vietnamCategory) {
            $this->command->error('❌ Không tìm thấy category Việt Nam. Vui lòng chạy QuocGiaHTPPMucSeeder trước!');
            return;
        }

        $this->command->info('📁 Category: ' . $vietnamCategory->translations->first()->name . ' (ID: ' . $vietnamCategory->id . ')');

        // ✅ XÓA HẾT POST THUỘC DANH MỤC TRƯỚC KHI SEED
        $deleted = $this->deletePostsByCategory($vietnamCategory->id);
        $this->command->info("🗑️  Đã xoá {$deleted} bài viết cũ");

        // 63 tỉnh thành Việt Nam (sắp xếp theo ABC)
        $provinces = [
            // Miền Bắc
            ['code' => 'HN',  'name_vi' => 'Thành phố Hà Nội',      'name_en' => 'Hanoi',           'type' => 'thanh-pho', 'sort_order' => 1],
            ['code' => 'HP',  'name_vi' => 'Thành phố Hải Phòng',   'name_en' => 'Hai Phong',       'type' => 'thanh-pho', 'sort_order' => 2],
            ['code' => 'CB',  'name_vi' => 'Cao Bằng',               'name_en' => 'Cao Bang',        'type' => 'tinh',      'sort_order' => 3],
            ['code' => 'TQ',  'name_vi' => 'Tuyên Quang',            'name_en' => 'Tuyen Quang',     'type' => 'tinh',      'sort_order' => 4],
            ['code' => 'DB',  'name_vi' => 'Điện Biên',              'name_en' => 'Dien Bien',       'type' => 'tinh',      'sort_order' => 5],
            ['code' => 'LC',  'name_vi' => 'Lai Châu',               'name_en' => 'Lai Chau',        'type' => 'tinh',      'sort_order' => 6],
            ['code' => 'SL',  'name_vi' => 'Sơn La',                 'name_en' => 'Son La',          'type' => 'tinh',      'sort_order' => 7],
            ['code' => 'LCa', 'name_vi' => 'Lào Cai',                'name_en' => 'Lao Cai',         'type' => 'tinh',      'sort_order' => 8],
            ['code' => 'TNg', 'name_vi' => 'Thái Nguyên',            'name_en' => 'Thai Nguyen',     'type' => 'tinh',      'sort_order' => 9],
            ['code' => 'LS',  'name_vi' => 'Lạng Sơn',               'name_en' => 'Lang Son',        'type' => 'tinh',      'sort_order' => 10],
            ['code' => 'QN',  'name_vi' => 'Quảng Ninh',             'name_en' => 'Quang Ninh',      'type' => 'tinh',      'sort_order' => 11],
            ['code' => 'BN',  'name_vi' => 'Bắc Ninh',               'name_en' => 'Bac Ninh',        'type' => 'tinh',      'sort_order' => 12],
            ['code' => 'PT',  'name_vi' => 'Phú Thọ',                'name_en' => 'Phu Tho',         'type' => 'tinh',      'sort_order' => 13],
            ['code' => 'HY',  'name_vi' => 'Hưng Yên',               'name_en' => 'Hung Yen',        'type' => 'tinh',      'sort_order' => 14],
            ['code' => 'NB',  'name_vi' => 'Ninh Bình',              'name_en' => 'Ninh Binh',       'type' => 'tinh',      'sort_order' => 15],

            // Miền Trung
            ['code' => 'TH',  'name_vi' => 'Thanh Hóa',              'name_en' => 'Thanh Hoa',       'type' => 'tinh',      'sort_order' => 20],
            ['code' => 'NA',  'name_vi' => 'Nghệ An',                'name_en' => 'Nghe An',         'type' => 'tinh',      'sort_order' => 21],
            ['code' => 'HT',  'name_vi' => 'Hà Tĩnh',                'name_en' => 'Ha Tinh',         'type' => 'tinh',      'sort_order' => 22],
            ['code' => 'QT',  'name_vi' => 'Quảng Trị',              'name_en' => 'Quang Tri',       'type' => 'tinh',      'sort_order' => 23],
            ['code' => 'TTH', 'name_vi' => 'Thành phố Huế',          'name_en' => 'Hue',             'type' => 'thanh-pho', 'sort_order' => 24],
            ['code' => 'DN',  'name_vi' => 'Thành phố Đà Nẵng',      'name_en' => 'Da Nang',         'type' => 'thanh-pho', 'sort_order' => 25],
            ['code' => 'QNg', 'name_vi' => 'Quảng Ngãi',             'name_en' => 'Quang Ngai',      'type' => 'tinh',      'sort_order' => 26],
            ['code' => 'GL',  'name_vi' => 'Gia Lai',                 'name_en' => 'Gia Lai',         'type' => 'tinh',      'sort_order' => 27],
            ['code' => 'KH',  'name_vi' => 'Khánh Hòa',              'name_en' => 'Khanh Hoa',       'type' => 'tinh',      'sort_order' => 28],
            ['code' => 'DLk', 'name_vi' => 'Đắk Lắk',               'name_en' => 'Dak Lak',         'type' => 'tinh',      'sort_order' => 29],
            ['code' => 'LD',  'name_vi' => 'Lâm Đồng',               'name_en' => 'Lam Dong',        'type' => 'tinh',      'sort_order' => 30],

            // Miền Nam
            ['code' => 'DNai','name_vi' => 'Đồng Nai',               'name_en' => 'Dong Nai',        'type' => 'tinh',      'sort_order' => 40],
            ['code' => 'HCM', 'name_vi' => 'Thành phố Hồ Chí Minh', 'name_en' => 'Ho Chi Minh City','type' => 'thanh-pho', 'sort_order' => 41],
            ['code' => 'TNi', 'name_vi' => 'Tây Ninh',               'name_en' => 'Tay Ninh',        'type' => 'tinh',      'sort_order' => 42],
            ['code' => 'DT',  'name_vi' => 'Đồng Tháp',              'name_en' => 'Dong Thap',       'type' => 'tinh',      'sort_order' => 43],
            ['code' => 'VL',  'name_vi' => 'Vĩnh Long',              'name_en' => 'Vinh Long',       'type' => 'tinh',      'sort_order' => 44],
            ['code' => 'AG',  'name_vi' => 'An Giang',               'name_en' => 'An Giang',        'type' => 'tinh',      'sort_order' => 45],
            ['code' => 'CT',  'name_vi' => 'Thành phố Cần Thơ',      'name_en' => 'Can Tho',         'type' => 'thanh-pho', 'sort_order' => 46],
            ['code' => 'CM',  'name_vi' => 'Cà Mau',                 'name_en' => 'Ca Mau',          'type' => 'tinh',      'sort_order' => 47],
        ];

        $this->command->info('🔄 Đang tạo ' . count($provinces) . ' bài viết tỉnh thành Việt Nam...');

        $totalProcessed = 0;
        $totalError = 0;

        foreach ($provinces as $index => $province) {
            try {
                // Tạo post
                $post = Post::create([
                    'status' => 'published',
                    'is_active' => true,
                    'is_featured' => false,
                    'sort_order' => $index + 1,
                    'published_at' => now(),
                ]);

                // Attach category với relationship many-to-many
                $post->postcategories()->attach($vietnamCategory->id, [
                    'is_primary' => true,
                    'sort_order' => 1
                ]);

                // Tạo content HTML
                $contentHtml = '<div class="province-info">';
                $contentHtml .= '<p>Hệ thống phân phối sản phẩm Casumina tại ' . e($province['name_vi']) . '</p>';
                $contentHtml .= '</div>';

                // Handle translations
                $post->handleTranslations([
                    'title_vi' => $province['name_vi'],
                    'title_en' => $province['name_en'],
                    'excerpt_vi' => $province['code'],
                    'excerpt_en' => $province['code'],
                    'content_vi' => $contentHtml,
                    'content_en' => $contentHtml,
                    'meta_title_vi' => 'Hệ thống phân phối tại ' . $province['name_vi'],
                    'meta_title_en' => 'Distribution System in ' . $province['name_en'],
                    'meta_description_vi' => 'Danh sách các đại lý phân phối sản phẩm Casumina tại ' . $province['name_vi'],
                    'meta_description_en' => 'List of Casumina product distributors in ' . $province['name_en'],
                ]);

                $totalProcessed++;


            } catch (\Exception $e) {
                $totalError++;
                $this->command->error("❌ Lỗi khi tạo bài viết: " . $e->getMessage());
                $this->command->error("   Tên: " . $province['name_vi'] . " / " . $province['name_en']  );
            }
        }

        $this->command->info("");
        $this->command->info("═══════════════════════════════════════════════════════");
        $this->command->info("🎉 HOÀN THÀNH!");
        $this->command->info("   📦 Tổng số bài viết đã tạo: {$totalProcessed}");
        $this->command->info("   ❌ Tổng số lỗi: {$totalError}");
        $this->command->info("═══════════════════════════════════════════════════════");
    }

    /**
     * Xóa tất cả posts thuộc category
     */
    private function deletePostsByCategory(int $categoryId): int
    {
        $query = Post::whereHas('postcategories', function ($q) use ($categoryId) {
            $q->where('postcategories.id', $categoryId);
        });

        $count = (clone $query)->count();

        $query->chunkById(200, function ($posts) {
            foreach ($posts as $post) {
                $post->delete();
            }
        });

        return $count;
    }
}
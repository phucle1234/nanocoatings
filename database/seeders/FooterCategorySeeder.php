<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostCategory;

class FooterCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo danh mục gốc
        $rootCategory = PostCategory::create([
            'is_active' => true,
            'is_featured' => false,
            'is_banner' => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rootCategory->handleTranslations([
            'name_vi' => 'Trang Footer',
            'name_en' => 'Footer Page',
            'slug_vi' => 'footer-main',
            'slug_en' => 'footer-main-en',
            'description_vi' => 'Casumina - Bạn đường tin cậy',
            'description_en' => 'Casumina - Your trusted companion',
            'meta_title_vi' => 'CÔNG TY CỔ PHẦN CÔNG NGHIỆP CAO SU MIỀN NAM - CASUMINA',
            'meta_title_en' => 'CÔNG TY CỔ PHẦN CÔNG NGHIỆP CAO SU MIỀN NAM - CASUMINA',
            'meta_description_vi' => 'Trụ sở chính: 180 Nguyễn Thị Minh Khai, phường Xuân Hòa, thành phố Hồ Chí Minh<br>
                 Văn Phòng giao dịch: 146 Nguyễn Biểu, phường Chợ Quán, thành phố Hồ Chí Minh',
            'meta_description_en' => 'Headquarters: 180 Nguyen Thi Minh Khai, Xuan Hoa Ward, Ho Chi Minh City<br>
                 Transaction Office: 146 Nguyen Bieu, Cho Quan Ward, Ho Chi Minh City',
            'image_urls_vi' => '/langding/imgs/bg-footer.png',
            'image_urls_en' => '/langding/imgs/bg-footer.png',
        ]);

        // Tạo danh mục con - ✅ Dùng slug khác nhau cho mỗi ngôn ngữ
        $subCategories = [
            [
                'name_vi' => 'Liên hệ',
                'name_en' => 'Contact',
                'slug_vi' => 'footer-lien-he', // ✅ Slug cho tiếng Việt
                'slug_en' => 'footer-contact', // ✅ Slug khác cho tiếng Anh để tránh duplicate
                'description_vi' => 'Thông tin liên hệ trong footer',
                'description_en' => 'Contact information in footer',
                'meta_description_vi' => 'Thông tin liên hệ trong footer',
                'meta_description_en' => 'Contact information in footer',
                'image_urls_vi' => '',
                'image_urls_en' => '',
            ],
            [
                'name_vi' => 'Về Casumina',
                'name_en' => 'About Casumina',
                'slug_vi' => 'footer-ve-casumina', // ✅ Slug cho tiếng Việt
                'slug_en' => 'footer-about-casumina', // ✅ Slug khác cho tiếng Anh để tránh duplicate
                'description_vi' => 'Thông tin về Casumina trong footer',
                'description_en' => 'About Casumina information in footer',
                'meta_description_vi' => 'Thông tin về Casumina trong footer',
                'meta_description_en' => 'About Casumina information in footer',
                'image_urls_vi' => '',
                'image_urls_en' => '',
            ],
            [
                'name_vi' => 'Kết nối với Casumina',
                'name_en' => 'Connect with Casumina',
                'slug_vi' => 'ket-noi-voi-casumina', // ✅ Slug cho tiếng Việt
                'slug_en' => 'connect-with-casumina', // ✅ Slug khác cho tiếng Anh
                'description_vi' => 'Kết nối với Casumina qua các mạng xã hội',
                'description_en' => 'Connect with Casumina through social networks',
                'meta_description_vi' => '',
                'meta_description_en' => '',
                'image_urls_vi' => '',
                'image_urls_en' => '',
            ]
        ];

        foreach ($subCategories as $index => $categoryData) {
            $category = PostCategory::create([
                'parent_id' => $rootCategory->id,
                'is_active' => true,
                'is_featured' => false,
                'is_banner' => true,
                'sort_order' => $index + 2,
            ]);

            // ✅ Thêm slug_vi và slug_en vào handleTranslations
            $category->handleTranslations([
                'name_vi' => $categoryData['name_vi'],
                'name_en' => $categoryData['name_en'],
                'slug_vi' => $categoryData['slug_vi'] ?? \Illuminate\Support\Str::slug($categoryData['name_vi']), // ✅ Slug được chỉ định
                'slug_en' => $categoryData['slug_en'] ?? \Illuminate\Support\Str::slug($categoryData['name_en']), // ✅ Slug được chỉ định
                'description_vi' => $categoryData['description_vi'],
                'description_en' => $categoryData['description_en'],
                'meta_title_vi' => $categoryData['name_vi'] . ' - Banner',
                'meta_title_en' => $categoryData['name_en'] . ' - Banner',
                'meta_description_vi' => $categoryData['meta_description_vi'] ?? '',
                'meta_description_en' => $categoryData['meta_description_en'] ?? '',
                'image_urls_vi' => $categoryData['image_urls_vi'] ?? '',
                'image_urls_en' => $categoryData['image_urls_en'] ?? '',
            ]);
        }

        $this->command->info('✅ Đã tạo ' . (count($subCategories) + 1) . ' danh mục của trang footer');
    }
}

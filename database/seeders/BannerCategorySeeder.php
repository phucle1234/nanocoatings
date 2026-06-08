<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostCategory;

class BannerCategorySeeder extends Seeder
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
            'name_vi' => 'Banner Trang chủ',
            'name_en' => 'Home  Banner',
            'description_vi' => 'Danh mục banner chính',
            'description_en' => 'Main banner category',
            'meta_title_vi' => 'Banner - Casumina',
            'meta_title_en' => 'Banner - Casumina',
            'meta_description_vi' => 'Quản lý các banner trên website',
            'meta_description_en' => 'Manage website banners',
        ]);

        // Tạo danh mục con - ✅ Dùng slug khác nhau cho mỗi ngôn ngữ
        $subCategories = [
            [
                'name_vi' => 'Banner Slider Trang chủ',
                'name_en' => 'Home Slider Banner',
                'slug_vi' => 'home-slider', // ✅ Slug cho tiếng Việt
                'slug_en' => 'home-slider-en', // ✅ Slug khác cho tiếng Anh để tránh duplicate
                'description_vi' => 'Banner slider hiển thị trên trang chủ',
                'description_en' => 'Slider banners on homepage',
                'meta_description_vi' => 'Banner slider hiển thị trên trang chủ',
                'meta_description_en' => 'Slider banners on homepage',
                'image_urls_vi' => '',
                'image_urls_en' => '',
                'meta_title_vi' => 'Slider Trang chủ',
                'meta_title_en' => 'Home Slider Banner',
            ],
            [
                'name_vi' => 'Banner Slider Trang chủ 2',
                'name_en' => 'Home Slider Banner 2',
                'slug_vi' => 'home-slider-2', // ✅ Slug cho tiếng Việt
                'slug_en' => 'home-slider-2-en', // ✅ Slug khác cho tiếng Anh để tránh duplicate
                'description_vi' => 'Banner slider hiển thị trên trang chủ 2',
                'description_en' => 'Slider banners on homepage 2',
                'meta_description_vi' => 'Banner slider hiển thị trên trang chủ 2',
                'meta_description_en' => 'Slider banners on homepage 2',
                'image_urls_vi' => '/langding/imgs/bg-slider.jpg',
                'image_urls_en' => '/langding/imgs/bg-slider.jpg',
                'meta_title_vi' => 'Slider Trang chủ 2',
                'meta_title_en' => 'Home Slider Banner 2',
            ],
            [
                'name_vi' => 'Banner Khuyến mãi',
                'name_en' => 'Promotion Banner',
                'slug_vi' => 'home-promotion', // ✅ Slug cho tiếng Việt
                'slug_en' => 'home-promotion-en', // ✅ Slug khác cho tiếng Anh
                'description_vi' => 'Thông tin ưu đãi',
                'description_en' => 'Promotion information',
                'meta_description_vi' => <<<HTML
                        Lorem ipsum dolor sit amet, nihil audiam nam<br>no, ei eos exerci nostro.
                    HTML,
                'meta_description_en' => <<<HTML
                        Lorem ipsum dolor sit amet, nihil audiam nam<br>no, ei eos exerci nostro.
                    HTML,
                'image_urls_vi' => '/langding/imgs/section-info-bg.png',
                'image_urls_en' => '/langding/imgs/section-info-bg.png',
                'meta_title_vi' => 'Khuyến mãi',
                'meta_title_en' => 'Promotion',
            ],
            [
                'name_vi' => 'Giới thiệu',
                'name_en' => 'Introduction',
                'slug_vi' => 'video-introduction', // ✅ Slug cho tiếng Việt
                'slug_en' => 'video-introduction-en', // ✅ Slug khác cho tiếng Anh
                'description_vi' => <<<HTML
                    <div class="row g-0">
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">45M+</div>
                                <div class="stat-label">LỐP XE ĐƯỢC BÁN</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">5M+</div>
                                <div class="stat-label">ĐẠI LÝ</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">2K+</div>
                                <div class="stat-label">CỬA HÀNG</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">30M+</div>
                                <div class="stat-label">KHÁCH HÀNG</div>
                            </div>
                        </div>
                    </div>
                    HTML,
                'description_en' => <<<HTML
                    <div class="row g-0">
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">45M+</div>
                                <div class="stat-label">LỐP XE ĐƯỢC BÁN</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">5M+</div>
                                <div class="stat-label">ĐẠI LÝ</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">2K+</div>
                                <div class="stat-label">CỬA HÀNG</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">30M+</div>
                                <div class="stat-label">KHÁCH HÀNG</div>
                            </div>
                        </div>
                    </div>
                    HTML,
                'meta_description_vi' => <<<HTML
                    Công ty cổ phần Công nghiệp Cao su Miền Nam được thành lập từ những năm đầu sau giải phóng đất nước (19/04/1976), hiện nay là nhà sản xuất săm lốp xe hàng đầu Việt Nam và là đơn vị dẫn đầu nghành công nghiệp cao su. Với ban lãnh đạo tâm huyết và ngày càng trẻ hóa, cùng đội ngũ nhân viên sáng tạo, trình độ chuyên môn vững vàng...
                    HTML,
                'meta_description_en' => <<<HTML
                    Công ty cổ phần Công nghiệp Cao su Miền Nam được thành lập từ những năm đầu sau giải phóng đất nước (19/04/1976), hiện nay là nhà sản xuất săm lốp xe hàng đầu Việt Nam và là đơn vị dẫn đầu nghành công nghiệp cao su. Với ban lãnh đạo tâm huyết và ngày càng trẻ hóa, cùng đội ngũ nhân viên sáng tạo, trình độ chuyên môn vững vàng...
                    HTML,
                'meta_title_vi' => 'CASUMINA',
                'meta_title_en' => 'CASUMINA',
            ],
            [
                'name_vi' => 'Banner Đối tác',
                'name_en' => 'Partner Banner',
                'slug_vi' => 'partner-banner', // ✅ Slug cho tiếng Việt
                'slug_en' => 'partner-banner-en', // ✅ Slug khác cho tiếng Anh
                'description_vi' => <<<HTML
                    Casumina đồng hành với<br>hơn 200 thương hiệu nổi bật
                    HTML,
                'description_en' => <<<HTML
                    Casumina đồng hành với<br>hơn 200 thương hiệu nổi bật
                    HTML,
                'meta_description_vi' => <<<HTML
                    Chúng tôi tự hào khi là nhà đồng hành và đối tác quan trọng cùng với những thương hiệu hàng đầu trên thị trường.
                    HTML,
                'meta_description_en' => <<<HTML
                    Chúng tôi tự hào khi là nhà đồng hành và đối tác quan trọng cùng với những thương hiệu hàng đầu trên thị trường.
                    HTML,
                'meta_title_vi' => 'CASUMINA',
                'meta_title_en' => 'CASUMINA',
                'image_urls_vi' => '/langding/imgs/bg-partner.png',
                'image_urls_en' => '/langding/imgs/bg-partner.png',
            ],
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
                'meta_title_vi' => $categoryData['meta_title_vi'],
                'meta_title_en' => $categoryData['meta_title_en'],
                'meta_description_vi' => $categoryData['meta_description_vi'] ?? '',
                'meta_description_en' => $categoryData['meta_description_en'] ?? '',
                'image_urls_vi' => $categoryData['image_urls_vi'] ?? '',
                'image_urls_en' => $categoryData['image_urls_en'] ?? '',
            ]);
        }

        $this->command->info('✅ Đã tạo ' . (count($subCategories) + 1) . ' danh mục banner');
    }
}

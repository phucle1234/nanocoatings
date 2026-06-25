<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Services\SectorService;
use Illuminate\Database\Seeder;

class SectorWoodSeeder extends Seeder
{
    public function run(): void
    {
        $sectorService = app(SectorService::class);
        $hub = $sectorService->getOrCreateHubCategory();

        $sector = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', 'vat-lieu-go'))
            ->first();

        if (!$sector) {
            $sector = PostCategory::withoutGlobalScopes()->create([
                'parent_id' => $hub->id,
                'is_active' => true,
                'is_featured' => true,
                'is_banner' => false,
                'is_sector' => true,
                'sort_order' => 1,
            ]);

            $sector->handleTranslations([
                'name_vi' => 'Vật liệu gỗ',
                'name_en' => 'Wood materials',
                'slug_vi' => 'vat-lieu-go',
                'slug_en' => 'wood-materials-en',
                'description_vi' => 'Giải pháp phủ nano bảo vệ bề mặt gỗ, chống ẩm, chống mối và tăng độ bền cho nội thất, sàn gỗ, cửa gỗ ngoài trời.',
                'description_en' => 'Nano coating solutions for wood surface protection, moisture resistance, and enhanced durability for furniture, flooring, and exterior woodwork.',
                'meta_title_vi' => 'Phủ nano cho vật liệu gỗ | Nanocoatings',
                'meta_title_en' => 'Nano coatings for wood materials | Nanocoatings',
                'meta_description_vi' => 'Bảo vệ và phủ nano cho gỗ nội thất, sàn gỗ, cửa gỗ và công trình gỗ ngoài trời.',
                'meta_description_en' => 'Protect and coat interior wood, flooring, doors, and outdoor timber structures.',
                'image_urls_vi' => "/langding_nano/imgs/Slection3.png\n/langding/imgs/bg-slider.jpg",
                'image_urls_en' => "/langding_nano/imgs/Slection3.png\n/langding/imgs/bg-slider.jpg",
            ]);
        }

        $sectorService->provisionSector($sector);

        $this->seedBannerCategoryMeta($sector);
        $this->seedHeroBanners($sector);

        $this->command?->info('✅ Đã tạo ngành Vật liệu gỗ tại /applications/vat-lieu-go');
    }

    protected function seedBannerCategoryMeta(PostCategory $sector): void
    {
        $prefix = 'sector-vat-lieu-go';
        $meta = [
            $prefix . '-home-slider' => [
                'meta_title_vi' => 'Vật liệu gỗ',
                'meta_title_en' => 'Wood materials',
                'meta_description_vi' => 'Giải pháp phủ nano chuyên dụng cho gỗ',
                'meta_description_en' => 'Specialized nano coating for wood',
            ],
            $prefix . '-home-slider-2' => [
                'meta_title_vi' => 'Ứng dụng',
                'meta_title_en' => 'Applications',
                'meta_description_vi' => 'Ứng dụng phủ nano trên các bề mặt gỗ',
                'meta_description_en' => 'Nano coating applications on wood surfaces',
                'image_urls_vi' => '/langding/imgs/bg-slider.jpg',
                'image_urls_en' => '/langding/imgs/bg-slider.jpg',
            ],
            $prefix . '-video-introduction' => [
                'meta_title_vi' => 'Giới thiệu ngành gỗ',
                'meta_title_en' => 'Wood industry introduction',
                'meta_description_vi' => 'Nanocoatings — đối tác bảo vệ bề mặt gỗ',
                'meta_description_en' => 'Nanocoatings — your wood surface protection partner',
            ],
            $prefix . '-home-promotion' => [
                'meta_title_vi' => 'Ưu đãi',
                'meta_title_en' => 'Offers',
                'meta_description_vi' => 'Chương trình ưu đãi cho ngành gỗ',
                'meta_description_en' => 'Promotions for the wood sector',
                'image_urls_vi' => '/langding/imgs/section-info-bg.png',
                'image_urls_en' => '/langding/imgs/section-info-bg.png',
            ],
            $prefix . '-partner-banner' => [
                'meta_title_vi' => 'Đối tác',
                'meta_title_en' => 'Partners',
                'meta_description_vi' => 'Đối tác tin cậy trong ngành gỗ',
                'meta_description_en' => 'Trusted partners in the wood industry',
            ],
        ];

        foreach ($meta as $slugVi => $data) {
            $category = PostCategory::withoutGlobalScopes()
                ->whereHas('translations', fn ($q) => $q->where('slug', $slugVi))
                ->first();

            if ($category) {
                $category->handleTranslations($data);
            }
        }
    }

    protected function seedHeroBanners(PostCategory $sector): void
    {
        $heroCategory = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', 'sector-vat-lieu-go-home-slider'))
            ->first();

        if (!$heroCategory || $heroCategory->posts()->exists()) {
            return;
        }

        $banner = Post::withoutGlobalScopes()->create([
            'post_type' => 'banner',
            'status' => 'published',
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 1,
            'published_at' => now(),
        ]);

        $banner->postcategories()->attach($heroCategory->id, [
            'is_primary' => true,
            'sort_order' => 1,
        ]);

        $banner->handleTranslations([
            'title_vi' => 'Phủ nano cho gỗ',
            'title_en' => 'Nano coating for wood',
            'content_vi' => 'BẢO VỆ<BR>BỀ MẶT GỖ',
            'content_en' => 'WOOD<BR>SURFACE PROTECTION',
            'image_urls_vi' => "/langding/imgs/bg-slider.jpg\n/langding_nano/imgs/Slection3.png",
            'image_urls_en' => "/langding/imgs/bg-slider.jpg\n/langding_nano/imgs/Slection3.png",
        ]);

        $slider2Category = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', 'sector-vat-lieu-go-home-slider-2'))
            ->first();

        if ($slider2Category && !$slider2Category->posts()->exists()) {
            $banner2 = Post::withoutGlobalScopes()->create([
                'post_type' => 'banner',
                'status' => 'published',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'published_at' => now(),
            ]);

            $banner2->postcategories()->attach($slider2Category->id, [
                'is_primary' => true,
                'sort_order' => 1,
            ]);

            $banner2->handleTranslations([
                'title_vi' => 'Ứng dụng trên gỗ',
                'title_en' => 'Wood applications',
                'image_urls_vi' => "/langding/imgs/product2.png\n/langding/imgs/product2.png",
                'image_urls_en' => "/langding/imgs/product2.png\n/langding/imgs/product2.png",
            ]);
        }
    }
}

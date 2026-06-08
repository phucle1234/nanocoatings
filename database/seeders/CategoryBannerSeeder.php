<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostCategoryTranslation;
use Illuminate\Support\Str;

class CategoryBannerSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo category cha "Lốp Avenza PCR"
        $parentCategory = $this->createOrUpdateCategory(
            'lop-avenza-pcr',
            'Lốp Avenza PCR',
            'Avenza PCR Tires',
            null,
            1
        );

        $this->command->info('✅ Đã tạo category cha: Lốp Avenza PCR');

        // 2. Tạo posts cho category cha (1 banner + 5 info posts)
        $this->createParentCategoryPosts($parentCategory, 'Lốp Avenza PCR');
        $this->command->info('  └─ ✅ Đã tạo 6 posts cho category cha');

        // 3. Tạo 4 danh mục con
        $childCategories = [
            [
                'code' => 'coverer',
                'name_vi' => 'Coverer',
                'name_en' => 'Coverer',
                'sort_order' => 1,
                'bg_images' => [
                    'design' => '/langding/imgs/demo-bg-venturer.png',
                    'technology' => '/langding/imgs/demo-bg-venturer-1.png',
                    'experience' => '/langding/imgs/demo-bg-venturer-2.png',
                ]
            ],
            [
                'code' => 'venturer',
                'name_vi' => 'Venturer',
                'name_en' => 'Venturer',
                'sort_order' => 2,
                'bg_images' => [
                    'design' => '/langding/imgs/demo-bg-venturer.png',
                    'technology' => '/langding/imgs/demo-bg-venturer-1.png',
                    'experience' => '/langding/imgs/demo-bg-venturer-2.png',
                ]
            ],
            [
                'code' => 'discoverer',
                'name_vi' => 'Discoverer',
                'name_en' => 'Discoverer',
                'sort_order' => 3,
                'bg_images' => [
                    'design' => '/langding/imgs/demo-bg-venturer.png',
                    'technology' => '/langding/imgs/demo-bg-venturer-1.png',
                    'experience' => '/langding/imgs/demo-bg-venturer-2.png',
                ]
            ],
            [
                'code' => 'traveller',
                'name_vi' => 'Traveller',
                'name_en' => 'Traveller',
                'sort_order' => 4,
                'bg_images' => [
                    'design' => '/langding/imgs/demo-bg-venturer.png',
                    'technology' => '/langding/imgs/demo-bg-venturer-1.png',
                    'experience' => '/langding/imgs/demo-bg-venturer-2.png',
                ]
            ],
        ];

        foreach ($childCategories as $childData) {
            $childCategory = $this->createOrUpdateCategory(
                $childData['code'],
                $childData['name_vi'],
                $childData['name_en'],
                $parentCategory->id,
                $childData['sort_order']
            );

            $this->command->info("✅ Đã tạo category con: {$childData['name_vi']}");

            // Tạo posts cho category con (1 banner + 9 sliders)
            $this->createChildCategoryPosts($childCategory, $childData['name_vi'], $childData['bg_images']);
            
            $this->command->info("  └─ ✅ Đã tạo 10 posts cho {$childData['name_vi']}");
        }

        $this->command->info('🎉 Hoàn thành seeder CategoryBanner!');
    }

    /**
     * Tạo posts cho category cha (1 banner + 5 info posts)
     */
    private function createParentCategoryPosts($category, $categoryName)
    {
        $posts = [
            // Banner đầu trang
            [
                'post_type' => 'banner',
                'title_vi' => 'Ảnh đại diện ' . $categoryName,
                'title_en' => 'Banner ' . $categoryName,
                'excerpt_vi' => 'Banner đại diện',
                'excerpt_en' => 'Representative banner',
                'content_vi' => '<p>Banner đại diện cho ' . $categoryName . '</p>',
                'content_en' => '<p>Representative banner for ' . $categoryName . '</p>',
                'icon' => 'quality-icon.svg',
                'sort_order' => 1,
            ],
            // 5 info posts
            [
                'post_type' => 'info',
                'title_vi' => 'VỀ ' . strtoupper($categoryName),
                'title_en' => 'ABOUT ' . strtoupper($categoryName),
                'excerpt_vi' => $categoryName . ' là bạn đường tin cậy',
                'excerpt_en' => $categoryName . ' is your trusted companion',
                'content_vi' => '<p>Với nhiều năm lắng nghe và thấu hiểu yêu cầu của người dùng...</p>',
                'content_en' => '<p>With many years of listening and understanding user requirements...</p>',
                'icon' => 'experience-icon.svg',
                'sort_order' => 2,
            ],
            [
                'post_type' => 'info',
                'title_vi' => 'CHẤT LƯỢNG',
                'title_en' => 'QUALITY',
                'excerpt_vi' => 'Với nhiều năm lắng nghe và thấu hiểu yêu cầu của người dùng...',
                'excerpt_en' => 'Modern and advanced processes...',
                'content_vi' => '<p>Với nhiều năm lắng nghe...</p>',
                'content_en' => '<p>Modern and advanced processes...</p>',
                'icon' => 'technology-icon.svg',
                'sort_order' => 3,
            ],
            [
                'post_type' => 'info',
                'title_vi' => 'KINH NGHIỆM',
                'title_en' => 'EXPERIENCE',
                'excerpt_vi' => 'Kiểu gai được thiết kế hiện đại...',
                'excerpt_en' => 'The tread pattern is designed modern...',
                'content_vi' => '<p>Với nhiều năm lắng nghe...</p>',
                'content_en' => '<p>The tread pattern is designed...</p>',
                'icon' => 'safety-icon.svg',
                'sort_order' => 4,
            ],
            [
                'post_type' => 'info',
                'title_vi' => 'CÔNG NGHỆ',
                'title_en' => 'TECHNOLOGY',
                'excerpt_vi' => 'Lốp ' . $categoryName . ' được thiết kế để có độ bền cao...',
                'excerpt_en' => $categoryName . ' tires are designed for high durability...',
                'content_vi' => '<p>Hiện đại và Quy trình tiên tiến...</p>',
                'content_en' => '<p>Modern and advanced processes...</p>',
                'icon' => 'durability-icon.svg',
                'sort_order' => 5,
            ],
            [
                'post_type' => 'info',
                'title_vi' => 'AN TOÀN',
                'title_en' => 'SAFETY',
                'excerpt_vi' => 'Công nghệ lốp ' . $categoryName . ' giúp giảm lực cản lăn...',
                'excerpt_en' => $categoryName . ' tire technology helps reduce rolling resistance...',
                'content_vi' => '<p>Kiểu gai được thiết kế hiện đại...</p>',
                'content_en' => '<p>' . $categoryName . ' tire technology...</p>',
                'icon' => 'fuel-icon.svg',
                'sort_order' => 6,
            ],
        ];

        $this->createPosts($category, $posts);
    }

    /**
     * Tạo posts cho category con (1 banner + 9 sliders)
     */
    private function createChildCategoryPosts($category, $categoryName, $bgImages)
    {
        $posts = [
            // Banner đầu trang
            [
                'post_type' => 'banner',
                'title_vi' => 'Ảnh đại diện ' . $categoryName,
                'title_en' => 'Banner ' . $categoryName,
                'excerpt_vi' => 'Banner đại diện',
                'excerpt_en' => 'Representative banner',
                'content_vi' => '<p>Banner đại diện cho ' . $categoryName . '</p>',
                'content_en' => '<p>Representative banner for ' . $categoryName . '</p>',
                'sort_order' => 1,
            ],
        ];

        // 9 slider posts (3 sections x 3 slides)
        $sliderPosts = [
            // Design section (3 slides)
            [
                'post_type' => 'slider',
                'section_type' => 'design',
                'title_vi' => 'THIẾT KẾ',
                'title_en' => 'DESIGN',
                'excerpt_vi' => 'Phanh tốt chống trượt dài',
                'excerpt_en' => 'Good braking and long slip resistance',
                'content_vi' => 'Hoa lốp được thiết kế theo dạng hướng dọc...',
                'content_en' => 'The tread pattern is designed in a longitudinal direction...',
                'image' => '/langding/imgs/demo-banner-design-venturer.png',
                'bg_image' => $bgImages['design'],
                'sort_order' => 2,
            ],
            [
                'post_type' => 'slider',
                'section_type' => 'design',
                'title_vi' => 'THIẾT KẾ',
                'title_en' => 'DESIGN',
                'excerpt_vi' => 'Độ bám đường tuyệt vời',
                'excerpt_en' => 'Excellent grip',
                'content_vi' => 'Thiết kế gai lốp tối ưu...',
                'content_en' => 'Optimized tread design...',
                'image' => '/langding/imgs/demo-banner-design-venturer.png',
                'bg_image' => $bgImages['design'],
                'sort_order' => 3,
            ],
            [
                'post_type' => 'slider',
                'section_type' => 'design',
                'title_vi' => 'THIẾT KẾ',
                'title_en' => 'DESIGN',
                'excerpt_vi' => 'Thiết kế thể thao năng động',
                'excerpt_en' => 'Dynamic sports design',
                'content_vi' => 'Đường nét thiết kế thể thao...',
                'content_en' => 'Sporty design lines...',
                'image' => '/langding/imgs/demo-banner-design-venturer.png',
                'bg_image' => $bgImages['design'],
                'sort_order' => 4,
            ],
            // Technology section (3 slides)
            [
                'post_type' => 'slider',
                'section_type' => 'technology',
                'title_vi' => 'CÔNG NGHỆ',
                'title_en' => 'TECHNOLOGY',
                'excerpt_vi' => 'Thách thức khí hậu và thời tiết',
                'excerpt_en' => 'Challenge climate and weather',
                'content_vi' => 'Công thức cao su mặt lốp...',
                'content_en' => 'The tire tread rubber formula...',
                'image' => '/langding/imgs/demo-banner-design-venturer.png',
                'bg_image' => $bgImages['technology'],
                'sort_order' => 5,
            ],
            [
                'post_type' => 'slider',
                'section_type' => 'technology',
                'title_vi' => 'CÔNG NGHỆ',
                'title_en' => 'TECHNOLOGY',
                'excerpt_vi' => 'Tiết kiệm nhiên liệu',
                'excerpt_en' => 'Fuel saving',
                'content_vi' => 'Công nghệ tiên tiến...',
                'content_en' => 'Advanced technology...',
                'image' => '/langding/imgs/demo-banner-design-venturer.png',
                'bg_image' => $bgImages['technology'],
                'sort_order' => 6,
            ],
            [
                'post_type' => 'slider',
                'section_type' => 'technology',
                'title_vi' => 'CÔNG NGHỆ',
                'title_en' => 'TECHNOLOGY',
                'excerpt_vi' => 'Độ bền vượt trội',
                'excerpt_en' => 'Superior durability',
                'content_vi' => 'Kết cấu lốp được tối ưu...',
                'content_en' => 'Tire structure is optimized...',
                'image' => '/langding/imgs/demo-banner-design-venturer.png',
                'bg_image' => $bgImages['technology'],
                'sort_order' => 7,
            ],
            // Experience section (3 slides)
            [
                'post_type' => 'slider',
                'section_type' => 'experience',
                'title_vi' => 'TRẢI NGHIỆM',
                'title_en' => 'EXPERIENCE',
                'excerpt_vi' => 'Tiêu tiếng ồn, tản nhiệt tốt',
                'excerpt_en' => 'Noise reduction, good heat dissipation',
                'content_vi' => 'Rãnh gai có nhiều rãnh nhỏ...',
                'content_en' => 'The tread grooves help...',
                'image' => '/langding/imgs/demo-banner-design-venturer.png',
                'bg_image' => $bgImages['experience'],
                'sort_order' => 8,
            ],
            [
                'post_type' => 'slider',
                'section_type' => 'experience',
                'title_vi' => 'TRẢI NGHIỆM',
                'title_en' => 'EXPERIENCE',
                'excerpt_vi' => 'Lái xe êm ái thoải mái',
                'excerpt_en' => 'Smooth and comfortable driving',
                'content_vi' => 'Thiết kế đặc biệt...',
                'content_en' => 'Special design...',
                'image' => '/langding/imgs/demo-banner-design-venturer.png',
                'bg_image' => $bgImages['experience'],
                'sort_order' => 9,
            ],
            [
                'post_type' => 'slider',
                'section_type' => 'experience',
                'title_vi' => 'TRẢI NGHIỆM',
                'title_en' => 'EXPERIENCE',
                'excerpt_vi' => 'Kiểm soát hoàn hảo',
                'excerpt_en' => 'Perfect control',
                'content_vi' => 'Khả năng kiểm soát vượt trội...',
                'content_en' => 'Superior control ability...',
                'image' => '/langding/imgs/demo-banner-design-venturer.png',
                'bg_image' => $bgImages['experience'],
                'sort_order' => 10,
            ],
        ];

        $posts = array_merge($posts, $sliderPosts);
        $this->createPosts($category, $posts);
    }

    /**
     * Helper: Tạo posts
     */
    private function createPosts($category, $posts)
    {
        foreach ($posts as $postData) {
            $slug = Str::slug($postData['post_type'] . '-' . ($postData['section_type'] ?? '') . '-' . $postData['sort_order'] . '-' . $category->id);
            
            $existingPost = Post::whereHas('translations', function ($query) use ($slug) {
                $query->where('slug', $slug)->where('language', 'vi');
            })->first();

            if ($existingPost) {
                $post = $existingPost;
                $post->update([
                    'status' => 'published',
                    'is_active' => true,
                    'post_type' => $postData['post_type'],
                    'section_type' => $postData['section_type'] ?? null,
                    'sort_order' => $postData['sort_order'],
                    'icon' => $postData['icon'] ?? null,
                    'published_at' => now(),
                ]);
            } else {
                $post = Post::create([
                    'status' => 'published',
                    'is_active' => true,
                    'post_type' => $postData['post_type'],
                    'section_type' => $postData['section_type'] ?? null,
                    'sort_order' => $postData['sort_order'],
                    'icon' => $postData['icon'] ?? null,
                    'published_at' => now(),
                ]);
            }

            $images = [];
            if (isset($postData['image'])) {
                $images[] = $postData['image'];
            }
            if (isset($postData['bg_image'])) {
                $images[] = $postData['bg_image'];
            }
            if (isset($postData['icon'])) {
                $images[] = '/langding/imgs/icons/' . $postData['icon'];
            }

            $post->handleTranslations([
                'title_vi' => $postData['title_vi'],
                'title_en' => $postData['title_en'],
                'slug_vi' => $slug,
                'slug_en' => Str::slug($postData['post_type'] . '-' . ($postData['section_type'] ?? '') . '-' . $postData['sort_order'] . '-' . $category->id . '-en'),
                'excerpt_vi' => $postData['excerpt_vi'],
                'excerpt_en' => $postData['excerpt_en'],
                'content_vi' => $postData['content_vi'],
                'content_en' => $postData['content_en'],
                'url_vi' => 'https://data.casumina.org/',
                'url_en' => 'https://data.casumina.org/',
                'image_urls_vi' => $images,
                'image_urls_en' => $images,
            ]);

            if (!$post->postcategories()->where('postcategory_id', $category->id)->exists()) {
                $post->postcategories()->attach($category->id);
            }
        }
    }

    private function createOrUpdateCategory($code, $nameVi, $nameEn, $parentId, $sortOrder)
    {
        // ...existing code... (giữ nguyên)
        $existingTranslation = PostCategoryTranslation::where('slug', $code)
            ->where('language', 'vi')
            ->first();

        if ($existingTranslation) {
            $category = PostCategory::find($existingTranslation->postcategory_id);
            if ($category) {
                $category->update([
                    'parent_id' => $parentId,
                    'is_active' => true,
                    'is_featured' => true,
                    'is_banner' => true,
                    'sort_order' => $sortOrder,
                ]);
            }
        } else {
            $category = PostCategory::create([
                'parent_id' => $parentId,
                'is_active' => true,
                'is_featured' => true,
                'is_banner' => true,
                'sort_order' => $sortOrder,
            ]);
        }

        $category->handleTranslations([
            'name_vi' => $nameVi,
            'name_en' => $nameEn,
            'slug_vi' => $code,
            'slug_en' => $code . '-en',
            'description_vi' => "Banner giới thiệu về {$nameVi}",
            'description_en' => "Banner introducing {$nameEn}",
            'meta_title_vi' => "{$nameVi} - Bạn đường tin cậy",
            'meta_title_en' => "{$nameEn} - Your Trusted Companion",
            'meta_description_vi' => "Thông tin chi tiết về dòng sản phẩm {$nameVi}",
            'meta_description_en' => "Detailed information about {$nameEn} product line",
            'image_urls_vi' => "/langding/imgs/bg-avenza.png\n/langding/imgs/category/bg-search.png",
            'image_urls_en' => "/langding/imgs/bg-avenza.png\n/langding/imgs/category/bg-search.png",
        ]);

        return $category;
    }
}
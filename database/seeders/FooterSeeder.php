<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\PostCategory;

class FooterSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy danh mục banner để gán cho các banner (query theo slug từ translations)
        $footer_lien_he = PostCategory::whereHas('translations', function ($query) {
            $query->where('slug', 'footer-lien-he')->where('language', 'vi');
        })->first();
        $footer_ve_casumina = PostCategory::whereHas('translations', function ($query) {
            $query->where('slug', 'footer-ve-casumina')->where('language', 'vi');
        })->first();
        $footer_ket_noi_voi_casumina = PostCategory::whereHas('translations', function ($query) {
            $query->where('slug', 'ket-noi-voi-casumina')->where('language', 'vi');
        })->first();

        $banners = [
            // Footer Contact Banners
            [
                'bannercategory_id' => $footer_lien_he?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Trụ sở chính: 180 Nguyễn Thị Minh Khai, Phường Xuân Hòa, TP Hồ Chí Minh',
                    'title_en' => 'Headquarters: 180 Nguyen Thi Minh Khai, Xuan Hoa Ward, Ho Chi Minh City',
                    'description_vi' => '',
                    'description_en' => '',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => '',
                    'image_urls' => [
                        "/langding/imgs/icon-location.svg",
                    ],
                ]
            ],

            [
                'bannercategory_id' => $footer_lien_he?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => '(084)2838 362 369 - (084)2838 362 373',
                    'title_en' => '(084)2838 362 369 - (084)2838 362 373',
                    'description_vi' => '',
                    'description_en' => '',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => '',
                    'image_urls' => [
                        "/langding/imgs/telephone-call.svg",
                    ],
                ]
            ],

            [
                'bannercategory_id' => $footer_lien_he?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'casumina@casumina.com.vn',
                    'title_en' => 'casumina@casumina.com.vn',
                    'description_vi' => '',
                    'description_en' => '',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => '',
                    'image_urls' => [
                        "/langding/imgs/icon-mail.svg",
                    ],
                ]
            ],

            // Footer About Casumina Banners
            [
                'bannercategory_id' => $footer_ve_casumina?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Trang Chủ',
                    'title_en' => 'Home',
                    'description_vi' => '',
                    'description_en' => '',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => 'https://data.casumina.org/',
                    'image_urls' => [],
                ]
            ],
            [
                'bannercategory_id' => $footer_ve_casumina?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Giới thiệu Casumina',
                    'title_en' => 'About Casumina',
                    'description_vi' => '',
                    'description_en' => '',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => 'https://data.casumina.org/',
                    'image_urls' => [],
                ]
            ],
            [
                'bannercategory_id' => $footer_ve_casumina?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Thông tin sản phẩm',
                    'title_en' => 'Product Information',
                    'description_vi' => '',
                    'description_en' => '',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => 'https://data.casumina.org/',
                    'image_urls' => [],
                ]
            ],
            [
                'bannercategory_id' => $footer_ve_casumina?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Hệ thống Phân phối',
                    'title_en' => 'Distribution System',
                    'description_vi' => '',
                    'description_en' => '',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => 'https://data.casumina.org/',
                    'image_urls' => [],
                ]
            ],
            [
                'bannercategory_id' => $footer_ve_casumina?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Truyền thông',
                    'title_en' => 'Media',
                    'description_vi' => '',
                    'description_en' => '',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => 'https://data.casumina.org/',
                    'image_urls' => [],
                ]
            ],
            [
                'bannercategory_id' => $footer_ve_casumina?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Khuyến mãi',
                    'title_en' => 'Promotion',
                    'description_vi' => '',
                    'description_en' => '',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => 'https://data.casumina.org/',
                    'image_urls' => [],
                ]
            ],

            // Promotion Banners - Slider Item 1
            [
                'bannercategory_id' => $footer_ket_noi_voi_casumina?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Fanpage Facebook',
                    'title_en' => 'Fanpage Facebook',
                    'description_vi' => '',
                    'description_en' => '',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => 'https://www.facebook.com/Casuminavn',
                    'image_urls' => [
                        "/langding/imgs/facebook.png",
                    ],
                ]
            ],
            [
                'bannercategory_id' => $footer_ket_noi_voi_casumina?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Fanpage zalo',
                    'title_en' => 'Fanpage zalo',
                    'description_vi' => '',
                    'description_en' => '',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => 'https://zalo.me/2187648418711741062',
                    'image_urls' => [
                        "/langding/imgs/zalo.png",
                    ],
                ]
            ],
            [
                'bannercategory_id' => $footer_ket_noi_voi_casumina?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Fanpage youtube',
                    'title_en' => 'Fanpage youtube',
                    'description_vi' => '',
                    'description_en' => '',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => 'https://www.youtube.com/@casuminaofficial6579',
                    'image_urls' => [
                        "/langding/imgs/youtube.png",
                    ],
                ]
            ],
            [
                'bannercategory_id' => $footer_ket_noi_voi_casumina?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Fanpage tiktok',
                    'title_en' => 'Fanpage tiktok',
                    'description_vi' => '',
                    'description_en' => '',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => 'https://www.tiktok.com/@casumina_official',
                    'image_urls' => [
                        "/langding/imgs/tiktok.png",
                    ],
                ]
            ],
            [
                'bannercategory_id' => $footer_ket_noi_voi_casumina?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Fanpage tiktok',
                    'title_en' => 'Fanpage tiktok',
                    'description_vi' => '',
                    'description_en' => '',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => 'https://www.instagram.com/casumina_official',
                    'image_urls' => [
                        "/langding/imgs/instagram.png",
                    ],
                ]
            ],
            
        ];

        foreach ($banners as $bannerData) {
            $translations = $bannerData['translations'];
            $categoryId = $bannerData['bannercategory_id'];

            unset($bannerData['translations'], $bannerData['bannercategory_id'], $bannerData['slug']);

            $banner = Post::create($bannerData);

            // Thêm danh mục cho banner
            if ($categoryId) {
                $banner->postcategories()->attach($categoryId, [
                    'is_primary' => true,
                    'sort_order' => 1
                ]);
            }

            $imageUrlsTextarea = is_array($translations['image_urls'])
                ? implode("\n", $translations['image_urls']) // URL1\nURL2\nURL3
                : $translations['image_urls'];

            $banner->handleTranslations([
                'title_vi' => $translations['title_vi'] ?? '',
                'title_en' => $translations['title_en'] ?? '',
                'description_vi' => $translations['description_vi'] ?? '',
                'description_en' => $translations['description_en'] ?? '',
                'excerpt_vi' => $translations['button_text_vi'] ?? '',
                'excerpt_en' => $translations['button_text_en'] ?? '',
                'canonical_url_vi' => $translations['link_url'] ?? '',
                'canonical_url_en' => $translations['link_url'] ?? '',
                'meta_title_vi' => $translations['title_vi'] ?? '',
                'meta_title_en' => $translations['title_en'] ?? '',
                'meta_description_vi' => $translations['description_vi'] ?? '',
                'meta_description_en' => $translations['description_en'] ?? '',
                'content_vi' => $translations['content_vi'] ?? '',
                'content_en' => $translations['content_en'] ?? '',
                'image_urls_vi' => $imageUrlsTextarea,
                'image_urls_en' => $imageUrlsTextarea,
            ]);
        }

        $this->command->info('✅ Đã tạo ' . count($banners) . ' thông tin footer mẫu');
    }
}

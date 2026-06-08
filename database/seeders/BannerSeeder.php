<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\PostCategory;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy danh mục banner để gán cho các banner (query theo slug từ translations)
        $categoryHomeSlider = PostCategory::whereHas('translations', function ($query) {
            $query->where('slug', 'home-slider')->where('language', 'vi');
        })->first();
        $categoryHomeSlider2 = PostCategory::whereHas('translations', function ($query) {
            $query->where('slug', 'home-slider-2')->where('language', 'vi');
        })->first();
        $categoryPromotion = PostCategory::whereHas('translations', function ($query) {
            $query->where('slug', 'home-promotion')->where('language', 'vi');
        })->first();
        $categoryPartner = PostCategory::whereHas('translations', function ($query) {
            $query->where('slug', 'partner-banner')->where('language', 'vi');
        })->first();
        $categoryVideoIntroduction = PostCategory::whereHas('translations', function ($query) {
            $query->where('slug', 'video-introduction')->where('language', 'vi');
        })->first();

        $banners = [
            // Home Slider Banners
            [
                'bannercategory_id' => $categoryHomeSlider?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Slide banner trang chủ',
                    'title_en' => 'Home slider banner',
                    'description_vi' => 'Banner slider trang chủ',
                    'description_en' => 'Home slider banner',
                    'content_vi' => <<<HTML
                        SĂM LỐP<BR>Ô TÔ
                    HTML,
                    'content_en' => <<<HTML
                        TIRE<BR>CAR
                    HTML,
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => '',
                    'image_urls' => [
                        "/langding/imgs/category/banner-2.jpg",
                        "/langding/imgs/category/banner-3.jpg"
                    ],
                ]
            ],

            // Home Slider Banners 2
            [
                'bannercategory_id' => $categoryHomeSlider2?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Slide banner trang chủ 2',
                    'title_en' => 'Home slider banner 2',
                    'description_vi' => 'Banner slider trang chủ 2',
                    'description_en' => 'Home slider banner 2',
                    'content_vi' => <<<HTML
                        SĂM LỐP<BR>Ô TÔ
                    HTML,
                    'content_en' => <<<HTML
                        TIRE<BR>CAR
                    HTML,
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'link_url' => '',
                    'image_urls' => [
                        '/langding/imgs/product2.png',
                        '/langding/imgs/product2.png',
                        '/langding/imgs/product2.png',
                        '/langding/imgs/product2.png',
                    ],
                ]
            ],

            // Promotion Banners - Slider Item 1
            [
                'bannercategory_id' => $categoryPromotion?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => '15% Off',
                    'title_en' => '15% Off',
                    'content_vi' => <<<HTML
                        ON AIRPORT PICKUPS<br>ALL OVER USA
                    HTML,
                    'content_en' => <<<HTML
                        ON AIRPORT PICKUPS<br>ALL OVER USA
                    HTML,
                    'description_vi' => 'Giảm giá 15% cho dịch vụ đón sân bay',
                    'description_en' => '15% off airport pickup service',
                    'button_text_vi' => 'Chi tiết',
                    'button_text_en' => 'View details',
                    'link_url' => '/promotions/airport-pickup',
                    'image_urls' => [
                        '/langding/imgs/new.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPromotion?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 2,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => '15% Off',
                    'title_en' => '15% Off',
                    'content_vi' => <<<HTML
                        ON AIRPORT PICKUPS<br>ALL OVER USA
                    HTML,
                    'content_en' => <<<HTML
                        ON AIRPORT PICKUPS<br>ALL OVER USA
                    HTML,
                    'description_vi' => 'Ưu đãi đặc biệt dịch vụ đón sân bay',
                    'description_en' => 'Special airport pickup offer',
                    'button_text_vi' => 'Chi tiết',
                    'button_text_en' => 'View details',
                    'link_url' => '/promotions/airport-special',
                    'image_urls' => [
                        '/langding/imgs/new-2.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPromotion?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 3,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => '15% Off',
                    'title_en' => '15% Off',
                    'content_vi' => <<<HTML
                        ON AIRPORT PICKUPS<br>ALL OVER USA
                    HTML,
                    'content_en' => <<<HTML
                        ON AIRPORT PICKUPS<br>ALL OVER USA
                    HTML,
                    'description_vi' => 'Khuyến mãi dịch vụ đón tiễn',
                    'description_en' => 'Pickup service promotion',
                    'button_text_vi' => 'Chi tiết',
                    'button_text_en' => 'View details',
                    'link_url' => '/promotions/pickup',
                    'image_urls' => [
                        '/langding/imgs/new.png',
                    ],
                ]
            ],

            // Promotion Banners - Slider Item 2
            [
                'bannercategory_id' => $categoryPromotion?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 4,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => '15% Off',
                    'title_en' => '15% Off',
                    'content_vi' => <<<HTML
                        ON AIRPORT PICKUPS<br>ALL OVER USA
                    HTML,
                    'content_en' => <<<HTML
                        ON AIRPORT PICKUPS<br>ALL OVER USA
                    HTML,
                    'description_vi' => 'Flash sale cuối tuần',
                    'description_en' => 'Weekend flash sale',
                    'button_text_vi' => 'Chi tiết',
                    'button_text_en' => 'View details',
                    'link_url' => '/promotions/weekend',
                    'image_urls' => [
                        '/langding/imgs/new-2.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPromotion?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 5,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => '15% Off',
                    'title_en' => '15% Off',
                    'content_vi' => <<<HTML
                        ON AIRPORT PICKUPS<br>ALL OVER USA
                    HTML,
                    'content_en' => <<<HTML
                        ON AIRPORT PICKUPS<br>ALL OVER USA
                    HTML,
                    'description_vi' => 'Giảm giá mùa lễ hội',
                    'description_en' => 'Holiday season discount',
                    'button_text_vi' => 'Chi tiết',
                    'button_text_en' => 'View details',
                    'link_url' => '/promotions/holiday',
                    'image_urls' => [
                        '/langding/imgs/new.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPromotion?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 6,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => '15% Off',
                    'title_en' => '15% Off',
                    'content_vi' => <<<HTML
                        ON AIRPORT PICKUPS<br>ALL OVER USA
                    HTML,
                    'content_en' => <<<HTML
                        ON AIRPORT PICKUPS<br>ALL OVER USA
                    HTML,
                    'description_vi' => 'Ưu đãi khách hàng thân thiết',
                    'description_en' => 'Loyalty customer offer',
                    'button_text_vi' => 'Chi tiết',
                    'button_text_en' => 'View details',
                    'link_url' => '/promotions/loyalty',
                    'image_urls' => [
                        '/langding/imgs/new.png',
                    ],
                ]
            ],

            // Video Introduction - Slider
            [
                'bannercategory_id' => $categoryVideoIntroduction?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'GIỚI THIỆU',
                    'title_en' => 'INTRODUCTION',
                    'content_vi' => <<<HTML
                        <h2 class="font-hanzel fs-48 fw-400 text-center mt-2 text-red">CASUMINA</h2>
                        <div class="video-intro fw-400 fs-16 text-center mt-2 mx-auto">
                            Công ty cổ phần Công nghiệp Cao su Miền Nam được thành lập từ những năm đầu sau giải phóng đất nước (19/04/1976), 
                            hiện nay là nhà sản xuất săm lốp xe hàng đầu Việt Nam và là đơn vị dẫn đầu nghành công nghiệp cao su. 
                            Với ban lãnh đạo tâm huyết và ngày càng trẻ hóa, cùng đội ngũ nhân viên sáng tạo, trình độ chuyên môn vững vàng...
                        </div>
                    HTML,
                    'content_en' => <<<HTML
                        <h2 class="font-hanzel fs-48 fw-400 text-center mt-2 text-red">CASUMINA</h2>
                        <div class="video-intro fw-400 fs-16 text-center mt-2 mx-auto">
                            Southern Rubber Industry Joint Stock Company was established in the early years after the liberation of the country (04/19/1976), 
                            currently is Vietnam's leading tire manufacturer and the leading unit in the rubber industry. 
                            With a dedicated and increasingly younger leadership team, along with creative staff and solid expertise...
                        </div>
                    HTML,
                    'description_vi' => 'Video giới thiệu công ty Casumina',
                    'description_en' => 'Casumina company introduction video',
                    'link_url' => 'https://www.youtube.com/watch?v=lyaOlS_IVx0',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/video.jpg',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryVideoIntroduction?->id,
                'is_active' => true,
                'is_featured' => true,

                'sort_order' => 2,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Quy trình sản xuất',
                    'title_en' => 'Production Process',
                    'content_vi' => <<<HTML
                        <div class="video-intro fw-400 fs-16 text-center mt-2 mx-auto">
                            Khám phá quy trình sản xuất hiện đại của Casumina với công nghệ tiên tiến và đội ngũ kỹ thuật chuyên nghiệp. 
                            Cam kết mang đến những sản phẩm lốp xe chất lượng cao nhất.
                        </div>
                    HTML,
                    'content_en' => <<<HTML
                        <div class="video-intro fw-400 fs-16 text-center mt-2 mx-auto">
                            Discover Casumina's modern production process with advanced technology and professional technical team. 
                            Committed to delivering the highest quality tire products.
                        </div>
                    HTML,
                    'description_vi' => 'Video quy trình sản xuất lốp xe',
                    'description_en' => 'Tire production process video',
                    'link_url' => 'https://www.youtube.com/watch?v=lyaOlS_IVx0',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/video.jpg',
                    ],
                ]
            ],

            // Partner Banners - Row 1 (11 items)
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Michelin',
                    'title_en' => 'Partner Michelin',
                    'description_vi' => 'Đối tác chiến lược - Michelin',
                    'description_en' => 'Strategic Partner - Michelin',
                    'link_url' => '/partners/michelin',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-1.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Bridgestone',
                    'title_en' => 'Partner Bridgestone',
                    'description_vi' => 'Đối tác chiến lược - Bridgestone',
                    'description_en' => 'Strategic Partner - Bridgestone',
                    'link_url' => '/partners/bridgestone',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-2.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Goodyear',
                    'title_en' => 'Partner Goodyear',
                    'description_vi' => 'Đối tác chiến lược - Goodyear',
                    'description_en' => 'Strategic Partner - Goodyear',
                    'link_url' => '/partners/goodyear',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-3.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Continental',
                    'title_en' => 'Partner Continental',
                    'description_vi' => 'Đối tác thương mại - Continental',
                    'description_en' => 'Commercial Partner - Continental',
                    'link_url' => '/partners/continental',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-1.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 5,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Pirelli',
                    'title_en' => 'Partner Pirelli',
                    'description_vi' => 'Đối tác phân phối - Pirelli',
                    'description_en' => 'Distribution Partner - Pirelli',
                    'link_url' => '/partners/pirelli',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-2.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 6,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Yokohama',
                    'title_en' => 'Partner Yokohama',
                    'description_vi' => 'Đối tác kỹ thuật - Yokohama',
                    'description_en' => 'Technical Partner - Yokohama',
                    'link_url' => '/partners/yokohama',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-3.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 7,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Dunlop',
                    'title_en' => 'Partner Dunlop',
                    'description_vi' => 'Đối tác chiến lược - Dunlop',
                    'description_en' => 'Strategic Partner - Dunlop',
                    'link_url' => '/partners/dunlop',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-1.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 8,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Hankook',
                    'title_en' => 'Partner Hankook',
                    'description_vi' => 'Đối tác thương mại - Hankook',
                    'description_en' => 'Commercial Partner - Hankook',
                    'link_url' => '/partners/hankook',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-2.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 9,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Kumho',
                    'title_en' => 'Partner Kumho',
                    'description_vi' => 'Đối tác phân phối - Kumho',
                    'description_en' => 'Distribution Partner - Kumho',
                    'link_url' => '/partners/kumho',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-3.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 10,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Toyo',
                    'title_en' => 'Partner Toyo',
                    'description_vi' => 'Đối tác kỹ thuật - Toyo',
                    'description_en' => 'Technical Partner - Toyo',
                    'link_url' => '/partners/toyo',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-1.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 11,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Nexen',
                    'title_en' => 'Partner Nexen',
                    'description_vi' => 'Đối tác chiến lược - Nexen',
                    'description_en' => 'Strategic Partner - Nexen',
                    'link_url' => '/partners/nexen',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-3.png',
                    ],
                ]
            ],

            // Partner Banners - Row 2 (11 items)
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 12,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Falken',
                    'title_en' => 'Partner Falken',
                    'description_vi' => 'Đối tác thương mại - Falken',
                    'description_en' => 'Commercial Partner - Falken',
                    'link_url' => '/partners/falken',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-1.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 13,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Nitto',
                    'title_en' => 'Partner Nitto',
                    'description_vi' => 'Đối tác phân phối - Nitto',
                    'description_en' => 'Distribution Partner - Nitto',
                    'link_url' => '/partners/nitto',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-2.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 14,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Cooper',
                    'title_en' => 'Partner Cooper',
                    'description_vi' => 'Đối tác kỹ thuật - Cooper',
                    'description_en' => 'Technical Partner - Cooper',
                    'link_url' => '/partners/cooper',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-3.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 15,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác BFGoodrich',
                    'title_en' => 'Partner BFGoodrich',
                    'description_vi' => 'Đối tác chiến lược - BFGoodrich',
                    'description_en' => 'Strategic Partner - BFGoodrich',
                    'link_url' => '/partners/bfgoodrich',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-1.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 16,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Firestone',
                    'title_en' => 'Partner Firestone',
                    'description_vi' => 'Đối tác thương mại - Firestone',
                    'description_en' => 'Commercial Partner - Firestone',
                    'link_url' => '/partners/firestone',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-2.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 17,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Maxxis',
                    'title_en' => 'Partner Maxxis',
                    'description_vi' => 'Đối tác phân phối - Maxxis',
                    'description_en' => 'Distribution Partner - Maxxis',
                    'link_url' => '/partners/maxxis',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-3.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 18,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác General',
                    'title_en' => 'Partner General',
                    'description_vi' => 'Đối tác kỹ thuật - General',
                    'description_en' => 'Technical Partner - General',
                    'link_url' => '/partners/general',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-1.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 19,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác GT Radial',
                    'title_en' => 'Partner GT Radial',
                    'description_vi' => 'Đối tác chiến lược - GT Radial',
                    'description_en' => 'Strategic Partner - GT Radial',
                    'link_url' => '/partners/gt-radial',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-2.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 20,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Achilles',
                    'title_en' => 'Partner Achilles',
                    'description_vi' => 'Đối tác thương mại - Achilles',
                    'description_en' => 'Commercial Partner - Achilles',
                    'link_url' => '/partners/achilles',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-3.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 21,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Sailun',
                    'title_en' => 'Partner Sailun',
                    'description_vi' => 'Đối tác phân phối - Sailun',
                    'description_en' => 'Distribution Partner - Sailun',
                    'link_url' => '/partners/sailun',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-1.png',
                    ],
                ]
            ],
            [
                'bannercategory_id' => $categoryPartner?->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 22,
                'published_at' => now(),
                'translations' => [
                    'title_vi' => 'Đối tác Triangle',
                    'title_en' => 'Partner Triangle',
                    'description_vi' => 'Đối tác kỹ thuật - Triangle',
                    'description_en' => 'Technical Partner - Triangle',
                    'link_url' => '/partners/triangle',
                    'content_vi' => '',
                    'content_en' => '',
                    'button_text_vi' => '',
                    'button_text_en' => '',
                    'image_urls' => [
                        '/langding/imgs/partner-3.png',
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

        $this->command->info('✅ Đã tạo ' . count($banners) . ' banner mẫu');
    }
}

<?php

return [
    'hub_slug' => 'nganh-ung-dung',
    'hub_slug_en' => 'application-sectors-en',
    'post_type' => 'sector_block',
    'banner_prefix' => 'sector',

    'banner_keys' => [
        'slider_hero' => 'home-slider',
        'video' => 'video-introduction',
        'category' => 'home-category',
        'application_slider' => 'home-slider-2',
        'bestseller' => 'home-bestseller',
        'promotion' => 'home-promotion',
        'partner' => 'partner-banner',
        'media' => 'home-media',
        'footer' => 'footer-main',
    ],

    'blocks' => [
        ['section_type' => 'slider_hero', 'title_vi' => 'Slider nền (Hero)', 'title_en' => 'Hero background slider', 'sort_order' => 1],
        ['section_type' => 'video', 'title_vi' => 'Video giới thiệu', 'title_en' => 'Introduction video', 'sort_order' => 2],
        ['section_type' => 'category', 'title_vi' => 'Danh mục sản phẩm', 'title_en' => 'Product categories', 'sort_order' => 3],
        ['section_type' => 'application_slider', 'title_vi' => 'Ứng dụng (Slider 2)', 'title_en' => 'Application potential slider', 'sort_order' => 4],
        ['section_type' => 'bestseller', 'title_vi' => 'Sản phẩm bán chạy', 'title_en' => 'Bestseller products', 'sort_order' => 5],
        ['section_type' => 'promotion', 'title_vi' => 'Khuyến mãi', 'title_en' => 'Promotions', 'sort_order' => 6],
        ['section_type' => 'partner', 'title_vi' => 'Đối tác', 'title_en' => 'Partners', 'sort_order' => 7],
        ['section_type' => 'media', 'title_vi' => 'Truyền thông / Tin tức', 'title_en' => 'Media / News', 'sort_order' => 8],
        ['section_type' => 'footer', 'title_vi' => 'Footer', 'title_en' => 'Footer', 'sort_order' => 9],
    ],
];

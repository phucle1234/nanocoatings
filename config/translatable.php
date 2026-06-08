<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Translatable Routes Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình các routes có hỗ trợ đa ngôn ngữ và slug translation
    | 
    | Format:
    | 'route_prefix' => [
    |     'table' => 'translation_table_name',
    |     'id_column' => 'foreign_key_column',
    | ]
    |
    */

    'routes' => [
        'product' => [
            'table' => 'product_translations',
            'id_column' => 'product_id',
        ],
        'post' => [
            'table' => 'post_translations',
            'id_column' => 'post_id',
        ],
        'category' => [
            'table' => 'product_category_translations',
            'id_column' => 'category_id',
        ],
        
        // ===================================
        // Thêm routes mới ở đây, không cần sửa Controller
        // ===================================
        // 'service' => [
        //     'table' => 'service_translations',
        //     'id_column' => 'service_id',
        // ],
        // 'portfolio' => [
        //     'table' => 'portfolio_translations',
        //     'id_column' => 'portfolio_id',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-detect Translation Tables
    |--------------------------------------------------------------------------
    |
    | Nếu bật, hệ thống sẽ tự động tìm translation table theo convention:
    | Route: /abc/{slug} → Table: abc_translations, ID: abc_id
    |
    */

    'auto_detect' => true,
];


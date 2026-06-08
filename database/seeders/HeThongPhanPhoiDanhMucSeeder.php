<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostCategory;

class HeThongPhanPhoiDanhMucSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Root category: Hệ thống phân phối
        $rootCategory = PostCategory::create([
            'is_active'   => true,
            'is_featured' => true,
            'sort_order'  => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $rootCategory->handleTranslations([
            'name_vi'             => 'Hệ thống phân phối',
            'name_en'             => 'Distribution System',
            'description_vi'      => 'Danh mục hệ thống phân phối',
            'description_en'      => 'Distribution system categories',
            'meta_title_vi'       => 'Hệ thống phân phối - Trang web',
            'meta_title_en'       => 'Distribution System - Website',
            'meta_description_vi' => 'Tổng hợp các danh mục hệ thống phân phối',
            'meta_description_en' => 'Overview of distribution system categories',
        ]);

        // Sub categories
        $subCategories = [
            [
                'name_vi'        => 'Hệ thống phân phối - Lốp xe tải',
                'name_en'        => 'Distribution System - Truck Tires',
                'description_vi' => 'Danh mục lốp xe tải',
                'description_en' => 'Truck tire category',
            ],
            [
                'name_vi'        => 'Hệ thống phân phối - Lốp xe Đạp - Xe Máy',
                'name_en'        => 'Distribution System - Bicycle & Motorcycle Tires',
                'description_vi' => 'Danh mục lốp xe đạp và xe máy',
                'description_en' => 'Bicycle and motorcycle tire category',
            ],
            [
                'name_vi'        => 'Hệ thống phân phối - Lốp PCR Advenza',
                'name_en'        => 'Distribution System - PCR Advenza Tires',
                'description_vi' => 'Danh mục lốp PCR Advenza',
                'description_en' => 'PCR Advenza tire category',
            ],
            [
                'name_vi'        => 'Hệ thống phân phối - Quốc Tế',
                'name_en'        => 'Distribution System - International',
                'description_vi' => 'Danh mục quốc tế',
                'description_en' => 'International category',
            ],
        ];

        foreach ($subCategories as $index => $categoryData) {
            $category = PostCategory::create([
                'parent_id'   => $rootCategory->id,
                'is_active'   => true,
                'is_featured' => false,
                'sort_order'  => $index + 2,
            ]);

            $category->handleTranslations([
                'name_vi'             => $categoryData['name_vi'],
                'name_en'             => $categoryData['name_en'],
                'description_vi'      => $categoryData['description_vi'],
                'description_en'      => $categoryData['description_en'],
                'meta_title_vi'       => $categoryData['name_vi'] . ' - Hệ thống phân phối',
                'meta_title_en'       => $categoryData['name_en'] . ' - Distribution System',
                'meta_description_vi' => $categoryData['description_vi'],
                'meta_description_en' => $categoryData['description_en'],
            ]);
        }

        $this->command->info('✅ Đã tạo ' . (count($subCategories) + 1) . ' danh mục hệ thống phân phối');
    }
}

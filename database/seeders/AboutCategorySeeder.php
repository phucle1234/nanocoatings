<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostCategory;

class AboutCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo danh mục "Giới thiệu Casumina"
        $category = PostCategory::create([
            'is_active' => true,
            'is_featured' => false,
            'is_banner' => false,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $category->handleTranslations([
            'name_vi' => 'Giới thiệu Casumina',
            'name_en' => 'About Casumina',
            'slug_vi' => 'gioi-thieu-cong-ty',
            'slug_en' => 'about-casumina',
            'description_vi' => 'Danh mục giới thiệu về công ty Casumina',
            'description_en' => 'Category about Casumina company',
            'meta_title_vi' => 'Giới thiệu - Casumina',
            'meta_title_en' => 'About - Casumina',
            'meta_description_vi' => 'Thông tin giới thiệu về công ty Casumina',
            'meta_description_en' => 'Information about Casumina company',
        ]);

        $this->command->info('✅ Đã tạo 1 danh mục giới thiệu');
    }
}
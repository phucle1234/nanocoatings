<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostCategory;
use Illuminate\Support\Str;

class PostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo danh mục gốc
        $rootCategory = PostCategory::create([
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rootCategory->handleTranslations([
            'name_vi' => 'Tin tức',
            'name_en' => 'News',
            'description_vi' => 'Danh mục tin tức chính',
            'description_en' => 'Main news category',
            'meta_title_vi' => 'Tin tức - Trang web',
            'meta_title_en' => 'News - Website',
            'meta_description_vi' => 'Cập nhật tin tức mới nhất',
            'meta_description_en' => 'Latest news updates',
        ]);

        // Tạo danh mục con
        $subCategories = [
            [
                'name_vi' => 'Công nghệ',
                'name_en' => 'Technology',
                'description_vi' => 'Tin tức về công nghệ',
                'description_en' => 'Technology news',
            ],
            [
                'name_vi' => 'Kinh tế',
                'name_en' => 'Economy',
                'description_vi' => 'Tin tức kinh tế',
                'description_en' => 'Economic news',
            ],
            [
                'name_vi' => 'Thể thao',
                'name_en' => 'Sports',
                'description_vi' => 'Tin tức thể thao',
                'description_en' => 'Sports news',
            ],
        ];

        foreach ($subCategories as $index => $categoryData) {
            $category = PostCategory::create([
                'parent_id' => $rootCategory->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => $index + 2,
            ]);

            $category->handleTranslations([
                'name_vi' => $categoryData['name_vi'],
                'name_en' => $categoryData['name_en'],
                'description_vi' => $categoryData['description_vi'],
                'description_en' => $categoryData['description_en'],
                'meta_title_vi' => $categoryData['name_vi'] . ' - Tin tức',
                'meta_title_en' => $categoryData['name_en'] . ' - News',
                'meta_description_vi' => $categoryData['description_vi'],
                'meta_description_en' => $categoryData['description_en'],
            ]);
        }

        $this->command->info('✅ Đã tạo ' . (count($subCategories) + 1) . ' danh mục tin tức');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostCategory;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class QuocGiaHTPPMucSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Bắt đầu tạo danh mục quốc gia Hệ thống phân phối...');

        // ✅ XÓA DANH MỤC CŨ NẾU TỒN TẠI
        $oldRootCategory = PostCategory::whereHas('translations', function($query) {
            $query->where('name', 'Quốc gia Hệ thống phân phối');
        })->first();

        if ($oldRootCategory) {
            $this->command->info('🗑️  Tìm thấy danh mục cũ, đang xóa...');
            
            // Lấy tất cả category con
            $allCategoryIds = PostCategory::where('parent_id', $oldRootCategory->id)
                ->pluck('id')
                ->push($oldRootCategory->id)
                ->toArray();

            // Xóa tất cả posts thuộc các categories này
            $deletedPosts = $this->deletePostsByCategories($allCategoryIds);
            $this->command->info("   ✅ Đã xóa {$deletedPosts} bài viết");

            // Xóa các category con
            PostCategory::where('parent_id', $oldRootCategory->id)->delete();
            
            // Xóa category gốc
            $oldRootCategory->delete();
            
            $this->command->info('   ✅ Đã xóa danh mục cũ');
        }

        // Root category: Quốc gia Hệ thống phân phối
        $rootCategory = PostCategory::create([
            'is_active'   => true,
            'is_featured' => true,
            'sort_order'  => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $rootCategory->handleTranslations([
            'name_vi'             => 'Quốc gia Hệ thống phân phối',
            'name_en'             => 'International Distribution System',
            'description_vi'      => 'Hệ thống phân phối quốc tế',
            'description_en'      => 'International distribution system',
            'meta_title_vi'       => 'Quốc gia Hệ thống phân phối',
            'meta_title_en'       => 'International Distribution System',
            'meta_description_vi' => 'Hệ thống phân phối sản phẩm trên toàn thế giới',
            'meta_description_en' => 'Product distribution system worldwide',
        ]);

        $this->command->info('✅ Đã tạo danh mục gốc: Quốc gia Hệ thống phân phối');

        // Danh sách các quốc gia (không phân theo châu lục)
        $countries = [
            // ĐÔNG NAM Á
            ['name_vi' => 'Việt Nam',      'name_en' => 'Vietnam',          'code' => 'VN', 'phone_code' => '+84',  'region' => 'dong-nam-a',  'sort_order' => 1],
            ['name_vi' => 'Lào',           'name_en' => 'Laos',             'code' => 'LA', 'phone_code' => '+856', 'region' => 'dong-nam-a',  'sort_order' => 2],
            ['name_vi' => 'Campuchia',     'name_en' => 'Cambodia',         'code' => 'KH', 'phone_code' => '+855', 'region' => 'dong-nam-a',  'sort_order' => 3],
            ['name_vi' => 'Malaysia',      'name_en' => 'Malaysia',         'code' => 'MY', 'phone_code' => '+60',  'region' => 'dong-nam-a',  'sort_order' => 4],
            ['name_vi' => 'Indonesia',     'name_en' => 'Indonesia',        'code' => 'ID', 'phone_code' => '+62',  'region' => 'dong-nam-a',  'sort_order' => 5],
            ['name_vi' => 'Brunei',        'name_en' => 'Brunei',           'code' => 'BN', 'phone_code' => '+673', 'region' => 'dong-nam-a',  'sort_order' => 6],
            ['name_vi' => 'Myanmar',       'name_en' => 'Myanmar',          'code' => 'MM', 'phone_code' => '+95',  'region' => 'dong-nam-a',  'sort_order' => 7],
            ['name_vi' => 'Philippines',   'name_en' => 'Philippines',      'code' => 'PH', 'phone_code' => '+63',  'region' => 'dong-nam-a',  'sort_order' => 8],

            // TRUNG ĐÔNG
            ['name_vi' => 'Yemen',         'name_en' => 'Yemen',            'code' => 'YE', 'phone_code' => '+967', 'region' => 'trung-dong',  'sort_order' => 10],
            ['name_vi' => 'UAE',           'name_en' => 'UAE',              'code' => 'AE', 'phone_code' => '+971', 'region' => 'trung-dong',  'sort_order' => 11],
            ['name_vi' => 'Saudi Arabia',  'name_en' => 'Saudi Arabia',     'code' => 'SA', 'phone_code' => '+966', 'region' => 'trung-dong',  'sort_order' => 12],
            ['name_vi' => 'Iraq',          'name_en' => 'Iraq',             'code' => 'IQ', 'phone_code' => '+964', 'region' => 'trung-dong',  'sort_order' => 13],
            ['name_vi' => 'Iran',          'name_en' => 'Iran',             'code' => 'IR', 'phone_code' => '+98',  'region' => 'trung-dong',  'sort_order' => 14],
            ['name_vi' => 'Thổ Nhĩ Kỳ',    'name_en' => 'Turkiye',          'code' => 'TR', 'phone_code' => '+90',  'region' => 'trung-dong',  'sort_order' => 15],
            ['name_vi' => 'Afghanistan',   'name_en' => 'Afghanistan',      'code' => 'AF', 'phone_code' => '+93',  'region' => 'trung-dong',  'sort_order' => 16],
            ['name_vi' => 'Pakistan',      'name_en' => 'Pakistan',         'code' => 'PK', 'phone_code' => '+92',  'region' => 'trung-dong',  'sort_order' => 17],

            // CHÂU PHI
            ['name_vi' => 'Togo',          'name_en' => 'Togo',             'code' => 'TG', 'phone_code' => '+228', 'region' => 'chau-phi',    'sort_order' => 20],
            ['name_vi' => 'Burkina Faso',  'name_en' => 'Burkina Faso',     'code' => 'BF', 'phone_code' => '+226', 'region' => 'chau-phi',    'sort_order' => 21],
            ['name_vi' => 'Ghana',         'name_en' => 'Ghana',            'code' => 'GH', 'phone_code' => '+233', 'region' => 'chau-phi',    'sort_order' => 22],

            // CHÂU MỸ
            ['name_vi' => 'Mỹ',            'name_en' => 'United States',    'code' => 'US', 'phone_code' => '+1',   'region' => 'chau-my',     'sort_order' => 30],
            ['name_vi' => 'Mexico',        'name_en' => 'Mexico',           'code' => 'MX', 'phone_code' => '+52',  'region' => 'chau-my',     'sort_order' => 31],
            ['name_vi' => 'Venezuela',     'name_en' => 'Venezuela',        'code' => 'VE', 'phone_code' => '+58',  'region' => 'chau-my',     'sort_order' => 32],
            ['name_vi' => 'Brazil',        'name_en' => 'Brazil',           'code' => 'BR', 'phone_code' => '+55',  'region' => 'chau-my',     'sort_order' => 33],
            ['name_vi' => 'Argentina',     'name_en' => 'Argentina',        'code' => 'AR', 'phone_code' => '+54',  'region' => 'chau-my',     'sort_order' => 34],
            ['name_vi' => 'Peru',          'name_en' => 'Peru',             'code' => 'PE', 'phone_code' => '+51',  'region' => 'chau-my',     'sort_order' => 35],
            ['name_vi' => 'Panama',        'name_en' => 'Panama',           'code' => 'PA', 'phone_code' => '+507', 'region' => 'chau-my',     'sort_order' => 36],
            ['name_vi' => 'Cuba',          'name_en' => 'Cuba',             'code' => 'CU', 'phone_code' => '+53',  'region' => 'chau-my',     'sort_order' => 37],

            // CHÂU ÂU
            ['name_vi' => 'Nga',           'name_en' => 'Russia',           'code' => 'RU', 'phone_code' => '+7',   'region' => 'chau-au',     'sort_order' => 40],
        ];

        $this->command->info('🔄 Đang tạo ' . count($countries) . ' quốc gia...');

        foreach ($countries as $index => $countryData) {
            $category = PostCategory::create([
                'parent_id'   => $rootCategory->id,
                'is_active'   => true,
                'is_featured' => false,
                'sort_order'  => $index + 2,
            ]);

            $category->handleTranslations([
                'name_vi'             => $countryData['name_vi'],
                'name_en'             => $countryData['name_en'],
                'description_vi'      => 'Hệ thống phân phối tại ' . $countryData['name_vi'],
                'description_en'      => 'Distribution system in ' . $countryData['name_en'],
                'meta_title_vi'       => $countryData['code'],
                'meta_title_en'       => $countryData['code'],
                'meta_description_vi' => 'Hệ thống phân phối sản phẩm tại ' . $countryData['name_vi'],
                'meta_description_en' => 'Product distribution system in ' . $countryData['name_en'],
            ]);
        }

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('🎉 HOÀN THÀNH!');
        $this->command->info('   📦 Đã tạo 1 danh mục gốc');
        $this->command->info('   🌍 Đã tạo ' . count($countries) . ' quốc gia');
        $this->command->info('═══════════════════════════════════════════════════════');
    }

    /**
     * ✅ Xóa tất cả posts thuộc các categories
     */
    private function deletePostsByCategories(array $categoryIds): int
    {
        if (empty($categoryIds)) {
            return 0;
        }

        // Lấy tất cả post IDs thuộc các categories này
        $postIds = DB::table('post_postcategory')
            ->whereIn('postcategory_id', $categoryIds)
            ->pluck('post_id')
            ->unique()
            ->toArray();

        if (empty($postIds)) {
            return 0;
        }

        // Xóa relationships
        DB::table('post_postcategory')->whereIn('post_id', $postIds)->delete();

        // Xóa translations
        DB::table('post_translations')->whereIn('post_id', $postIds)->delete();

        // Xóa posts
        $deleted = Post::whereIn('id', $postIds)->delete();

        return $deleted;
    }
}
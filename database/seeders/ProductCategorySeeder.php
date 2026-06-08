<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\ProductCategory;
use App\Models\ProductCategoryTranslation;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Bắt đầu tạo danh mục sản phẩm...');

        // Xóa tất cả dữ liệu liên quan đến sản phẩm
        $this->command->info('🧹 Đang xóa tất cả dữ liệu sản phẩm...');
        $this->deleteAllProductData();

        // Xóa dữ liệu cũ nếu cần
        $this->command->info('🧹 Đang xóa dữ liệu danh mục cũ...');
        $codesToDelete = ['sam-lop-xe-tai', 'sam-lop-xe-dap', 'sam-lop-xe-may', 'lop-advenza-pcr', 'sam-lop-chuyen-dung', 'sam-lop-xe-dien'];

        // Xóa tất cả child categories của các parent categories này
        $parentCategoriesToDelete = ProductCategory::whereIn('code', $codesToDelete)->pluck('id');
        if ($parentCategoriesToDelete->isNotEmpty()) {
            // Xóa child categories
            $childCategoriesToDelete = ProductCategory::whereIn('parent_id', $parentCategoriesToDelete)->pluck('id');
            if ($childCategoriesToDelete->isNotEmpty()) {
                ProductCategoryTranslation::whereIn('category_id', $childCategoriesToDelete)->delete();
                ProductCategory::whereIn('id', $childCategoriesToDelete)->delete();
            }
            // Xóa parent categories
            ProductCategoryTranslation::whereIn('category_id', $parentCategoriesToDelete)->delete();
            ProductCategory::whereIn('id', $parentCategoriesToDelete)->delete();
            $this->command->info("✅ Đã xóa dữ liệu cũ");
        }

        // Cấu trúc danh mục mới
        $categories = [
            [
                'name_vi' => 'Săm Lốp Xe Tải',
                'name_en' => 'Truck Tires',
                'code' => 'sam-lop-xe-tai',
                'children' => [
                    ['name_vi' => 'Lốp tải nhẹ Bias( Nylon)', 'name_en' => 'Light Truck Tires (Nylon)'],
                    ['name_vi' => 'Lốp tải nặng Bias ( Nylon)', 'name_en' => 'Heavy Truck Tires (Nylon)'],
                    [
                        'name_vi' => 'Lốp ôtô Radial(TBR)',
                        'name_en' => 'Radial Car Tires(TBR)',
                        'code' => 'lop-oto-radial-bo-thep',
                        'children' => [
                            ['name_vi' => 'Greenstone', 'name_en' => 'Greenstone'],
                            ['name_vi' => 'Bluestone', 'name_en' => 'Bluestone'],
                            ['name_vi' => 'Redstone', 'name_en' => 'Redstone'],
                            ['name_vi' => 'Silverstone', 'name_en' => 'Silverstone'],
                        ],
                    ],
                    ['name_vi' => 'Lốp tải nhẹ( PCR)', 'name_en' => 'Light Truck Tires (PCR)'],
                    ['name_vi' => 'Săm yếm ôtô', 'name_en' => 'Car Inner Tubes'],
                ],
            ],
            [
                'name_vi' => 'Săm Lốp Xe Đạp',
                'name_en' => 'Bicycle Tires',
                'code' => 'sam-lop-xe-dap',
                'children' => [
                    ['name_vi' => 'Lốp thể thao', 'name_en' => 'Sport Tires'],
                    ['name_vi' => 'Lốp truyền thống', 'name_en' => 'Traditional Tires'],
                    ['name_vi' => 'Săm xe đạp', 'name_en' => 'Bicycle Inner Tubes'],
                ],
            ],
            [
                'name_vi' => 'Săm Lốp Xe Máy',
                'name_en' => 'Motorcycle Tires',
                'code' => 'sam-lop-xe-may',
                'children' => [
                    ['name_vi' => 'Lốp Tube Type (Casumina)', 'name_en' => 'Tube Type (Casumina)'],
                    [
                        'name_vi' => 'Lốp Tubeless (Euromina)',
                        'name_en' => 'Tubeless (Euromina)',
                        'code' => 'sam-lop-xe-may-lop-tubeless-euromina',
                        'children' => [
                            ['name_vi' => 'E Series', 'name_en' => 'E-SERIES'],
                            ['name_vi' => 'Advance', 'name_en' => 'ADVANCE'],
                            ['name_vi' => 'M75', 'name_en' => 'M75'],
                            ['name_vi' => 'Lốp đặc trưng Casumina', 'name_en' => 'Signature Tires'],
                        ],
                    ],
                    ['name_vi' => 'Săm xe máy', 'name_en' => 'Motorcycle Inner Tubes'],
                ],
            ],
            [
                'name_vi' => 'Lốp PCR Advenza',
                'name_en' => 'Advenza PCR Tires',
                'code' => 'lop-advenza-pcr',
                'children' => [
                    ['name_vi' => 'Venturer', 'name_en' => 'Venturer'],
                    ['name_vi' => 'Coverer', 'name_en' => 'Coverer'],
                    ['name_vi' => 'Discoverer', 'name_en' => 'Discoverer'],
                    ['name_vi' => 'Traveller', 'name_en' => 'Traveller'],
                ],
            ],
            [
                'name_vi' => 'Săm Lốp Chuyên Dụng',
                'name_en' => 'Specialty Tires',
                'code' => 'sam-lop-chuyen-dung',
                'children' => [
                    ['name_vi' => 'Lốp nông nghiệp', 'name_en' => 'Agricultural Tires'],
                    ['name_vi' => 'Lốp công nghiệp', 'name_en' => 'Industrial Tires'],
                    ['name_vi' => 'Lốp công trình (OTR)', 'name_en' => 'Off-The-Road (OTR) Tires'],
                    ['name_vi' => 'Săm yếm chuyên dụng', 'name_en' => 'Specialty Inner Tubes'],
                ],
            ],
            [
                'name_vi' => 'Săm Lốp Xe Điện',
                'name_en' => 'Electric Vehicle Tires',
                'code' => 'sam-lop-xe-dien',
                'children' => [
                    ['name_vi' => 'Lốp xe đạp điện', 'name_en' => 'Electric Bicycle Tires'],
                    ['name_vi' => 'Lốp xe máy điện', 'name_en' => 'Electric Motorcycle Tires'],
                    ['name_vi' => 'Lốp ôtô điện', 'name_en' => 'Electric Car Tires'],
                    ['name_vi' => 'Săm xe điện', 'name_en' => 'Electric Vehicle Inner Tubes'],
                ],
            ],
        ];

        $sortOrder = 1;

        foreach ($categories as $parentData) {
            // Tạo danh mục cha
            $parentCategory = $this->createOrUpdateCategory(
                code: $parentData['code'],
                nameVi: $parentData['name_vi'],
                nameEn: $parentData['name_en'],
                parentId: null,
                sortOrder: $sortOrder++
            );

            $this->command->info("✅ Đã tạo danh mục cha: {$parentData['name_vi']}");

            $childSortOrder = 1;
            foreach ($parentData['children'] as $childData) {
                // ✅ Kiểm tra nếu child có code riêng (như 'lop-oto-radial-bo-thep')
                $childCode = $childData['code'] ?? ($parentData['code'] . '-' . Str::slug($childData['name_vi']));

                // Tạo child category
                $childCategory = $this->createOrUpdateCategory(
                    code: $childCode,
                    nameVi: $childData['name_vi'],
                    nameEn: $childData['name_en'],
                    parentId: $parentCategory->id,
                    sortOrder: $childSortOrder++
                );

                $this->command->info("  └─ ✅ Đã tạo danh mục con: {$childData['name_vi']}");

                // ✅ THÊM: Kiểm tra nếu child có children (grandchildren), tạo chúng
                if (isset($childData['children']) && is_array($childData['children']) && !empty($childData['children'])) {
                    $grandchildSortOrder = 1;
                    foreach ($childData['children'] as $grandchildData) {
                        $grandchildCode = $childCode . '-' . Str::slug($grandchildData['name_vi']);

                        $this->createOrUpdateCategory(
                            code: $grandchildCode,
                            nameVi: $grandchildData['name_vi'],
                            nameEn: $grandchildData['name_en'],
                            parentId: $childCategory->id,
                            sortOrder: $grandchildSortOrder++
                        );

                        $this->command->info("      └─ ✅ Đã tạo danh mục cháu: {$grandchildData['name_vi']}");
                    }
                }
            }
        }

        $this->command->info('🎉 Hoàn thành tạo danh mục sản phẩm!');
    }

    /**
     * Tạo hoặc cập nhật danh mục
     */
    private function createOrUpdateCategory(
        string $code,
        string $nameVi,
        string $nameEn,
        ?int $parentId = null,
        int $sortOrder = 1
    ): ProductCategory {
        // Tạo hoặc cập nhật category
        $category = ProductCategory::updateOrCreate(
            ['code' => $code],
            [
                'parent_id' => $parentId,
                'is_active' => true,
                'is_featured' => $parentId === null, // Chỉ đánh dấu featured cho danh mục cha
                'sort_order' => $sortOrder,
            ]
        );

        // Tạo hoặc cập nhật translation tiếng Việt
        $existingVi = ProductCategoryTranslation::where('category_id', $category->id)
            ->where('language', 'vi')
            ->first();

        if ($existingVi) {
            // Nếu đã tồn tại, giữ nguyên slug và chỉ update các field khác
            $existingVi->update([
                'name' => $nameVi,
                'description' => $parentId ? "Danh mục {$nameVi}" : "Thương hiệu {$nameVi}",
                'meta_title' => $nameVi,
                'meta_description' => $parentId ? "Danh mục {$nameVi}" : "Thương hiệu {$nameVi}",
            ]);
        } else {
            // Nếu chưa tồn tại, generate slug mới
            try {
                $slugVi = $this->generateUniqueSlug($nameVi, 'vi', $category->id, $code, $parentId);
                ProductCategoryTranslation::create([
                    'category_id' => $category->id,
                    'language' => 'vi',
                    'name' => $nameVi,
                    'description' => $parentId ? "Danh mục {$nameVi}" : "Thương hiệu {$nameVi}",
                    'slug' => $slugVi,
                    'meta_title' => $nameVi,
                    'meta_description' => $parentId ? "Danh mục {$nameVi}" : "Thương hiệu {$nameVi}",
                    'image_urls' => [
                        "/storage/images/danh-muc-san-pham.png"
                    ],
                ]);
                $this->command->info("    ✅ Created VI translation for {$nameVi}");
            } catch (\Exception $e) {
                $this->command->error("    ❌ Error creating VI translation for {$nameVi}: " . $e->getMessage());
                throw $e;
            }
        }

        // Tạo hoặc cập nhật translation tiếng Anh
        $existingEn = ProductCategoryTranslation::where('category_id', $category->id)
            ->where('language', 'en')
            ->first();

        if ($existingEn) {
            // Nếu đã tồn tại, giữ nguyên slug và chỉ update các field khác
            $existingEn->update([
                'name' => $nameEn,
                'description' => $parentId ? "{$nameEn} category" : "{$nameEn} brand",
                'meta_title' => $nameEn,
                'meta_description' => $parentId ? "{$nameEn} category" : "{$nameEn} brand",
            ]);
        } else {
            // Nếu chưa tồn tại, generate slug mới
            $slugEn = $this->generateUniqueSlug($nameEn, 'en', $category->id, $code, $parentId);

            // Double check: Nếu slug đã tồn tại (kể cả từ translation khác của cùng category), tạo slug mới
            if (ProductCategoryTranslation::where('slug', $slugEn)
                ->where('language', $category->id)
                ->exists()
            ) {
                $slugEn = $this->generateUniqueSlug($nameEn . '-en', 'en', $category->id, $code, $parentId);
            }

            try {
                ProductCategoryTranslation::create([
                    'category_id' => $category->id,
                    'language' => 'en',
                    'name' => $nameEn,
                    'description' => $parentId ? "{$nameEn} category" : "{$nameEn} brand",
                    'slug' => $slugEn,
                    'meta_title' => $nameEn,
                    'image_urls' => [
                        "/storage/images/danh-muc-san-pham.png"
                    ],
                    'meta_description' => $parentId ? "{$nameEn} category" : "{$nameEn} brand",
                ]);
                $this->command->info("    ✅ Created EN translation for {$nameEn}");
            } catch (\Exception $e) {
                $this->command->error("    ❌ Error creating EN translation for {$nameEn}: " . $e->getMessage());
                throw $e;
            }
        }

        return $category;
    }

    /**
     * Tạo slug duy nhất
     * Slug phải unique theo composite key (slug + language)
     */
    private function generateUniqueSlug(string $name, string $language, int $categoryId, ?string $categoryCode = null, ?int $parentId = null): string
    {
        // Nếu là child category (có parentId), thêm parent code vào slug để đảm bảo unique
        if ($parentId !== null && $categoryCode) {
            // Tách parent code từ category code (ví dụ: "sam-lop-xe-tai-lop-tai-nhe" -> "sam-lop-xe-tai")
            $parts = explode('-', $categoryCode);
            // Tìm parent code (phần trước dấu gạch nối cuối cùng của tên danh mục con)
            $baseSlug = Str::slug($name);
        } else {
            // Nếu là parent category, chỉ dùng name
            $baseSlug = Str::slug($name);
        }

        $slug = $baseSlug;
        $counter = 1;

        // Check slug unique theo composite key (slug + language)
        // Loại trừ chính category hiện tại nếu đang update
        while (ProductCategoryTranslation::where('slug', $slug)
            ->where('language', $language)
            ->where('category_id', '!=', $categoryId)
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;

            // Tránh vòng lặp vô hạn
            if ($counter > 1000) {
                $slug = $baseSlug . '-' . $categoryId . '-' . time();
                break;
            }
        }

        return $slug;
    }

    /**
     * Xóa tất cả dữ liệu liên quan đến sản phẩm
     */
    private function deleteAllProductData(): void
    {
        // Tắt foreign key checks để tránh lỗi constraint
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = [
            // Pivot tables first (phải xóa trước)
            'product_attribute_product',
            'product_product_category',

            // Translation tables
            'product_translations',
            'product_attribute_value_translations',
            'product_attribute_translations',

            // Related data tables
            'product_inventory',
            'inventory_movements',
            'product_analytics',
            'product_reviews',
            'wishlists',
            'cart_items',
            'order_items', // Cẩn thận: xóa cả order items

            // Attribute values (phải xóa trước attributes)
            'product_attribute_values',

            // Main tables
            'products',
            'product_attributes', // Có thể giữ lại hoặc xóa, tùy yêu cầu
        ];

        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($tables as $table) {
            try {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $count = DB::table($table)->count();
                    DB::table($table)->truncate();
                    $this->command->info("  ✅ Đã xóa {$table} ({$count} records)");
                    $deletedCount++;
                } else {
                    $this->command->warn("  ⚠️  Bảng {$table} không tồn tại, bỏ qua...");
                    $skippedCount++;
                }
            } catch (\Exception $e) {
                $this->command->error("  ❌ Lỗi xóa {$table}: " . $e->getMessage());
            }
        }

        // Bật lại foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Reset auto increment
        $this->command->info('🔄 Đang reset auto increment...');
        $autoIncrementTables = [
            'products',
            'product_attributes',
            'product_attribute_values',
            'product_inventory',
            'product_analytics',
            'product_reviews',
            'wishlists',
            'cart_items',
            'order_items',
        ];

        foreach ($autoIncrementTables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                try {
                    DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
                    $this->command->info("  ✅ Reset auto increment cho {$table}");
                } catch (\Exception $e) {
                    $this->command->warn("  ⚠️  Không thể reset auto increment cho {$table}: " . $e->getMessage());
                }
            }
        }

        $this->command->info("✅ Hoàn thành xóa dữ liệu sản phẩm!");
        $this->command->info("   - Đã xóa: {$deletedCount} bảng");
        $this->command->info("   - Bỏ qua: {$skippedCount} bảng");
        $this->command->info("");
    }
}

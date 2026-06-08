<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            [
                'code' => 'finger',
                'type' => 'text',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'manufacturer',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 2,
                'options' => json_encode(['']),
            ],
            [
                'code' => 'model',
                'type' => 'text',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'code' => 'production_year',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 4,
                'options' => json_encode(range(2000, 2026)),
            ],
            [
                'code' => 'size',
                'type' => 'text',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'code' => 'wide',
                'type' => 'number',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'code' => 'rate',
                'type' => 'number',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'code' => 'diameter',
                'type' => 'number',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'code' => 'unit',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => false,
                'is_comparable' => false,
                'is_active' => true,
                'sort_order' => 9,
                'options' => json_encode(['inch', 'mm']),
            ],
            [
                'code' => 'production_type',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 10,
                'options' => json_encode(['Original', 'Replacement', 'Performance', 'Winter', 'Summer', 'All-Season']),
            ],
            [
                'code' => 'weight',
                'type' => 'number',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'code' => 'speed_rating',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 12,
                'options' => json_encode(['Q', 'R', 'S', 'T', 'U', 'H', 'V', 'W', 'Y', 'Z']),
            ],
            [
                'code' => 'path_type',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 13,
                'options' => json_encode(['Highway', 'City', 'Off-road', 'Mixed']),
            ],
            [
                'code' => 'heat_resistance',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 14,
                'options' => json_encode(['A', 'B', 'C']),
            ],
            [
                'code' => 'warranty',
                'type' => 'text',
                'is_required' => false,
                'is_filterable' => false,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 15,
            ],
            [
                'code' => 'characteristic',
                'type' => 'textarea',
                'is_required' => false,
                'is_filterable' => false,
                'is_comparable' => false,
                'is_active' => true,
                'sort_order' => 16,
            ],
            [
                'code' => 'item_code',
                'type' => 'text',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 17,
            ],
            [
                'code' => 'load_index',
                'type' => 'number',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 19,
            ],
            [
                'code' => 'max_speed',
                'type' => 'number',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'code' => 'length',
                'type' => 'number',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 21,
            ],

            [
                'code' => 'height',
                'type' => 'number',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 23,
            ],
            [
                'code' => 'road_grip',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 24,
                // Theo JSON: A, B, C (giống heat_resistance)
                'options' => json_encode(['A', 'B', 'C']),
            ],
            [
                'code' => 'speed_index',
                'type' => 'text',
                'is_required' => false,
                'is_filterable' => false,
                'is_comparable' => false,
                'is_active' => true,
                'sort_order' => 12,
                'options' => json_encode(['Q', 'R', 'S', 'T', 'U', 'H', 'V', 'W', 'Y', 'Z']),
            ],

            [
                'code' => 'ply_rating',
                'type' => 'text',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 26,
            ],
            [
                'code' => 'ply_rating_1',
                'type' => 'text',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 26,
            ],
            [
                'code' => 'tire_type',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 27,
                'options' => json_encode(['TT', 'TL', 'Tube Type', 'Tubeless']),
            ],
            [
                'code' => 'load_index_number',
                'type' => 'number',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 28,
            ],
            [
                'code' => 'tread_depth',
                'type' => 'number',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 29,
            ],
            [
                'code' => 'tire_line',
                'type' => 'text',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 30,
            ],
        ];

        foreach ($attributes as $attribute) {
            // Kiểm tra xem thuộc tính đã tồn tại chưa
            $existingAttribute = DB::table('product_attributes')->where('code', $attribute['code'])->first();

            if ($existingAttribute) {
                $attributeId = $existingAttribute->id;
                // Cập nhật translations nếu chưa có
                $existingTranslation = DB::table('product_attribute_translations')
                    ->where('attribute_id', $attributeId)
                    ->where('language', 'vi')
                    ->first();

                if (!$existingTranslation) {
                    // Tạo translations nếu chưa có
                    $translations = [
                        [
                            'attribute_id' => $attributeId,
                            'language' => 'en',
                            'name' => $this->getAttributeName($attribute['code'], 'en'),
                            'description' => $this->getAttributeDescription($attribute['code'], 'en'),
                        ],
                        [
                            'attribute_id' => $attributeId,
                            'language' => 'vi',
                            'name' => $this->getAttributeName($attribute['code'], 'vi'),
                            'description' => $this->getAttributeDescription($attribute['code'], 'vi'),
                        ],
                    ];

                    foreach ($translations as $translation) {
                        DB::table('product_attribute_translations')->insert($translation);
                    }
                }
            } else {
                // Tạo mới nếu chưa tồn tại
                $attributeId = DB::table('product_attributes')->insertGetId($attribute);

                // Tạo translations cho attribute
                $translations = [
                    [
                        'attribute_id' => $attributeId,
                        'language' => 'en',
                        'name' => $this->getAttributeName($attribute['code'], 'en'),
                        'description' => $this->getAttributeDescription($attribute['code'], 'en'),
                    ],
                    [
                        'attribute_id' => $attributeId,
                        'language' => 'vi',
                        'name' => $this->getAttributeName($attribute['code'], 'vi'),
                        'description' => $this->getAttributeDescription($attribute['code'], 'vi'),
                    ],
                ];

                foreach ($translations as $translation) {
                    DB::table('product_attribute_translations')->insert($translation);
                }
            }
        }
    }

    private function getAttributeName($code, $language)
    {
        $names = [
            'finger' => [
                'en' => 'Finger',
                'vi' => 'Mã gai',
            ],
            'manufacturer' => [
                'en' => 'Manufacturer',
                'vi' => 'Hãng xe',
            ],
            'model' => [
                'en' => 'Model',
                'vi' => 'Mẫu',
            ],
            'production_year' => [
                'en' => 'Production Year',
                'vi' => 'Năm sản xuất',
            ],
            'size' => [
                'en' => 'Size',
                'vi' => 'Kích thước',
            ],
            'wide' => [
                'en' => 'Width',
                'vi' => 'Chiều rộng',
            ],
            'rate' => [
                'en' => 'Rate',
                'vi' => 'Tỷ lệ',
            ],
            'diameter' => [
                'en' => 'Diameter',
                'vi' => 'Đường kính',
            ],
            'unit' => [
                'en' => 'Unit',
                'vi' => 'Đơn vị',
            ],
            'production_type' => [
                'en' => 'Production Type',
                'vi' => 'Chủng loại lốp',
            ],
            'ply_rating' => [
                'en' => 'Ply Rating',
                'vi' => 'Số lớp bố',
            ],
            'ply_rating_1' => [
                'en' => 'Ply Rating 1 (e.g., 18PR)',
                'vi' => 'Ply Rating 1 (ví dụ: 18PR)',
            ],
            'tire_type' => [
                'en' => 'Tire Type',
                'vi' => 'Loại lốp',
            ],
            'load_index_number' => [
                'en' => 'Load Index Number',
                'vi' => 'Chỉ số tải',
            ],
            'tread_depth' => [
                'en' => 'Tread Depth',
                'vi' => 'Chiều sâu gai (mm)',
            ],
            'tire_line' => [
                'en' => 'Tire Line',
                'vi' => 'Dòng lốp',
            ],
            'speed_rating' => [
                'en' => 'Speed Rating',
                'vi' => 'Tốc độ',
            ],
            'speed_index' => [
                'en' => 'Speed Index',
                'vi' => 'Chỉ số tốc độ',
            ],
            'path_type' => [
                'en' => 'Path Type',
                'vi' => 'Loại đường',
            ],
            'heat_resistance' => [
                'en' => 'Heat Resistance',
                'vi' => 'Chịu nhiệt',
            ],
            'warranty' => [
                'en' => 'Warranty',
                'vi' => 'Bảo hành',
            ],
            'characteristic' => [
                'en' => 'Characteristic',
                'vi' => 'Đặc điểm',
            ],
            'item_code' => [
                'en' => 'Item Code',
                'vi' => 'Mã sản phẩm',
            ],
            'load_index' => [
                'en' => 'Load Index',
                'vi' => 'Tải trọng',
            ],
            'max_speed' => [
                'en' => 'Maximum Speed',
                'vi' => 'Tốc độ tối đa',
            ],
            'length' => [
                'en' => 'Length',
                'vi' => 'Chiều dài',
            ],
            'height' => [
                'en' => 'Height',
                'vi' => 'Chiều cao',
            ],
            'road_grip' => [
                'en' => 'Road Grip',
                'vi' => 'Bám đường',
            ],
        ];

        return $names[$code][$language] ?? $code;
    }

    private function getAttributeDescription($code, $language)
    {
        $descriptions = [

            'finger' => [
                'en' => 'Finger pattern code',
                'vi' => 'Mã Gai',
            ],
            'manufacturer' => [
                'en' => 'Vehicle manufacturer brand',
                'vi' => 'Hãng xe',
            ],
            'model' => [
                'en' => 'Tire model name',
                'vi' => 'Tên mẫu lốp',
            ],
            'production_year' => [
                'en' => 'Year of production',
                'vi' => 'Năm sản xuất',
            ],
            'size' => [
                'en' => 'Tire size specification',
                'vi' => 'Thông số kích thước lốp',
            ],
            'ply_rating' => [
                'en' => 'Ply rating (e.g., 18PR, 16PR HD)',
                'vi' => 'Ply rating (ví dụ: 18PR, 16PR HD)',
            ],
            'tire_type' => [
                'en' => 'Type of tire (TT = Tube Type, TL = Tubeless)',
                'vi' => 'Loại lốp (TT = Có săm, TL = Không săm)',
            ],
            'load_index_number' => [
                'en' => 'Load index number (e.g., 43, 40)',
                'vi' => 'Chỉ số tải (ví dụ: 43, 40)',
            ],
            'tread_depth' => [
                'en' => 'Tread depth in millimeters',
                'vi' => 'Chiều sâu gai (mm)',
            ],
            'tire_line' => [
                'en' => 'Tire product line (e.g., E Force, Lightning)',
                'vi' => 'Dòng sản phẩm lốp (ví dụ: E Force, Lightning)',
            ],
            'wide' => [
                'en' => 'Tire width in millimeters',
                'vi' => 'Chiều rộng lốp tính bằng mm',
            ],
            'rate' => [
                'en' => 'Aspect ratio of the tire',
                'vi' => 'Tỷ lệ khía cạnh của lốp',
            ],
            'diameter' => [
                'en' => 'Rim diameter in inches',
                'vi' => 'Đường kính vành tính bằng inch',
            ],
            'unit' => [
                'en' => 'Measurement unit',
                'vi' => 'Đơn vị đo lường',
            ],
            'production_type' => [
                'en' => 'Type of tire production',
                'vi' => 'Chủng loại lốp',
            ],
            'speed_rating' => [
                'en' => 'Maximum speed rating',
                'vi' => 'Tốc độ',
            ],
            'speed_index' => [
                'en' => 'Speed index',
                'vi' => 'Chỉ số tốc độ',
            ],
            'path_type' => [
                'en' => 'Recommended path type',
                'vi' => 'Loại đường được khuyến nghị',
            ],
            'heat_resistance' => [
                'en' => 'Heat resistance rating',
                'vi' => 'Đánh giá Chịu nhiệt',
            ],
            'warranty' => [
                'en' => 'Warranty period',
                'vi' => 'Thời gian bảo hành',
            ],
            'characteristic' => [
                'en' => 'Special characteristics',
                'vi' => 'Đặc điểm đặc biệt',
            ],
            'item_code' => [
                'en' => 'Product item code',
                'vi' => 'Mã sản phẩm',
            ],
            'load_index' => [
                'en' => 'Load index (e.g., 79 (437 kg))',
                'vi' => 'Tải trọng (ví dụ: 79 (437 kg))',
            ],
            'weight' => [
                'en' => 'Load index value',
                'vi' => 'Giá trị tải trọng',
            ],
            'max_speed' => [
                'en' => 'Maximum speed in km/h',
                'vi' => 'Tốc độ tối đa tính bằng km/h',
            ],
            'length' => [
                'en' => 'Product length in cm',
                'vi' => 'Chiều dài sản phẩm tính bằng cm',
            ],

            'height' => [
                'en' => 'Product height in cm',
                'vi' => 'Chiều cao sản phẩm tính bằng cm',
            ],
            'road_grip' => [
                'en' => 'Road grip and adhesion capability',
                'vi' => 'Khả năng bám đường và độ dính',
            ],
        ];

        return $descriptions[$code][$language] ?? '';
    }
}

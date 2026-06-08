<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductFeatureAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Tạo các giá trị cho thuộc tính "Đặc điểm" (product_features)
     */
    public function run(): void
    {
        // Lấy attribute "product_features"
        $characteristicAttr = DB::table('product_attributes')
            ->where('code', 'product_features')
            ->first();

        if (!$characteristicAttr) {
            $this->command->error('Attribute "product_features" not found! Please run ProductAttributeSeeder first.');
            return;
        }

        $this->command->info("Found attribute 'product_features' (ID: {$characteristicAttr->id})");

        // Danh sách các giá trị đặc điểm
        $characteristicValues = [
            [
                'value' => 'stable_safe_operation',
                'vi' => 'Vận hành ổn định, an toàn',
                'en' => 'Stable and safe operation',
                'sort_order' => 1,
            ],
            [
                'value' => 'fast_heat_dissipation',
                'vi' => 'Tản nhiệt nhanh',
                'en' => 'Fast heat dissipation',
                'sort_order' => 2,
            ],
            [
                'value' => 'good_load_capacity',
                'vi' => 'Chịu tải tốt',
                'en' => 'Good load capacity',
                'sort_order' => 3,
            ],
            [
                'value' => 'fuel_efficient',
                'vi' => 'Tiết kiệm nhiên liệu',
                'en' => 'Fuel efficient',
                'sort_order' => 4,
            ],
            [
                'value' => 'grip_anti_slip',
                'vi' => 'Bám đường, chống trơn trượt',
                'en' => 'Good grip and anti-slip',
                'sort_order' => 5,
            ],
            [
                'value' => 'winding_slope_road',
                'vi' => 'Đường quanh co, đèo dốc',
                'en' => 'Winding and slope roads',
                'sort_order' => 6,
            ],
            [
                'value' => 'construction_mining_road',
                'vi' => 'Đường công trình, hầm mỏ, đất đá',
                'en' => 'Construction, mining, rocky roads',
                'sort_order' => 7,
            ],
            [
                'value' => 'cut_resistant_durable',
                'vi' => 'Chống cắt chém tốt, độ bền cao',
                'en' => 'Cut resistant and highly durable',
                'sort_order' => 8,
            ],
            [
                'value' => 'low_speed_suitable',
                'vi' => 'Phù hợp chạy tốc độ thấp',
                'en' => 'Suitable for low speed',
                'sort_order' => 9,
            ],
            // Các đặc tính bổ sung

            [
                'value' => 'low_wear',
                'vi' => 'Độ mòn thấp',
                'en' => 'Low wear',
                'sort_order' => 10,
            ],
            [
                'value' => 'smooth_operation',
                'vi' => 'Vận hành êm ái',
                'en' => 'Smooth operation',
                'sort_order' => 11,
            ],
            [
                'value' => 'quiet_operation',
                'vi' => 'Vận hành yên tĩnh',
                'en' => 'Quiet operation',
                'sort_order' => 12,
            ],
            [
                'value' => 'offroad_rocky_road',
                'vi' => 'Đi đường đất đá',
                'en' => 'Suitable for rocky roads',
                'sort_order' => 13,
            ],
            [
                'value' => 'heavy_load',
                'vi' => 'Tải trọng lớn',
                'en' => 'Heavy load capacity',
                'sort_order' => 14,
            ],
            [
                'value' => 'passenger_16_seats',
                'vi' => 'Dành cho xe 16 chỗ vận chuyển hành khách',
                'en' => 'For 16-seat passenger vehicles',
                'sort_order' => 15,
            ],
            [
                'value' => 'wet_performance',
                'vi' => 'Hiệu suất đường ướt',
                'en' => 'Wet road performance',
                'sort_order' => 16,
            ],
            [
                'value' => 'dry_performance',
                'vi' => 'Hiệu suất đường khô',
                'en' => 'Dry road performance',
                'sort_order' => 17,
            ],
        ];

        foreach ($characteristicValues as $charValue) {
            // Kiểm tra xem giá trị đã tồn tại chưa
            $existingValue = DB::table('product_attribute_values')
                ->where('attribute_id', $characteristicAttr->id)
                ->where('value', $charValue['value'])
                ->first();

            if ($existingValue) {
                $this->command->warn("Value '{$charValue['value']}' already exists. Skipping...");
                continue;
            }

            // Insert giá trị
            $attributeValueId = DB::table('product_attribute_values')->insertGetId([
                'attribute_id' => $characteristicAttr->id,
                'value' => $charValue['value'],
                'color_code' => null,
                'image_url' => null,
                'is_active' => true,
                'sort_order' => $charValue['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert translations
            $translations = [
                [
                    'attribute_value_id' => $attributeValueId,
                    'language' => 'vi',
                    'value' => $charValue['vi'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'attribute_value_id' => $attributeValueId,
                    'language' => 'en',
                    'value' => $charValue['en'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($translations as $translation) {
                DB::table('product_attribute_value_translations')->insert($translation);
            }

            $this->command->info("✓ Created value: {$charValue['vi']}");
        }

        $this->command->info('product_features values seeded successfully!');
    }
}

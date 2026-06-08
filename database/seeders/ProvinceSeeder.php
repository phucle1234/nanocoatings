<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    /**
     * Seed provinces from TinhThanhHTPPMucSeeder source data.
     */
    public function run(): void
    {
        $vietnamId = DB::table('npp_countries')->where('code', 'VN')->value('id');

        if (!$vietnamId) {
            $this->command->error('ProvinceSeeder: country VN not found. Please run CountrySeeder first.');
            return;
        }

        $provinces = [
            ['code' => 'HN', 'name_vi' => 'Thành phố Hà Nội', 'name_en' => 'Hanoi', 'type' => 'thanh-pho', 'sort_order' => 1],
            ['code' => 'HP', 'name_vi' => 'Thành phố Hải Phòng', 'name_en' => 'Hai Phong', 'type' => 'thanh-pho', 'sort_order' => 2],
            ['code' => 'CB', 'name_vi' => 'Cao Bằng', 'name_en' => 'Cao Bang', 'type' => 'tinh', 'sort_order' => 3],
            ['code' => 'TQ', 'name_vi' => 'Tuyên Quang', 'name_en' => 'Tuyen Quang', 'type' => 'tinh', 'sort_order' => 4],
            ['code' => 'DB', 'name_vi' => 'Điện Biên', 'name_en' => 'Dien Bien', 'type' => 'tinh', 'sort_order' => 5],
            ['code' => 'LC', 'name_vi' => 'Lai Châu', 'name_en' => 'Lai Chau', 'type' => 'tinh', 'sort_order' => 6],
            ['code' => 'SL', 'name_vi' => 'Sơn La', 'name_en' => 'Son La', 'type' => 'tinh', 'sort_order' => 7],
            ['code' => 'LCa', 'name_vi' => 'Lào Cai', 'name_en' => 'Lao Cai', 'type' => 'tinh', 'sort_order' => 8],
            ['code' => 'TNg', 'name_vi' => 'Thái Nguyên', 'name_en' => 'Thai Nguyen', 'type' => 'tinh', 'sort_order' => 9],
            ['code' => 'LS', 'name_vi' => 'Lạng Sơn', 'name_en' => 'Lang Son', 'type' => 'tinh', 'sort_order' => 10],
            ['code' => 'QN', 'name_vi' => 'Quảng Ninh', 'name_en' => 'Quang Ninh', 'type' => 'tinh', 'sort_order' => 11],
            ['code' => 'BN', 'name_vi' => 'Bắc Ninh', 'name_en' => 'Bac Ninh', 'type' => 'tinh', 'sort_order' => 12],
            ['code' => 'PT', 'name_vi' => 'Phú Thọ', 'name_en' => 'Phu Tho', 'type' => 'tinh', 'sort_order' => 13],
            ['code' => 'HY', 'name_vi' => 'Hưng Yên', 'name_en' => 'Hung Yen', 'type' => 'tinh', 'sort_order' => 14],
            ['code' => 'NB', 'name_vi' => 'Ninh Bình', 'name_en' => 'Ninh Binh', 'type' => 'tinh', 'sort_order' => 15],
            ['code' => 'TH', 'name_vi' => 'Thanh Hóa', 'name_en' => 'Thanh Hoa', 'type' => 'tinh', 'sort_order' => 20],
            ['code' => 'NA', 'name_vi' => 'Nghệ An', 'name_en' => 'Nghe An', 'type' => 'tinh', 'sort_order' => 21],
            ['code' => 'HT', 'name_vi' => 'Hà Tĩnh', 'name_en' => 'Ha Tinh', 'type' => 'tinh', 'sort_order' => 22],
            ['code' => 'QT', 'name_vi' => 'Quảng Trị', 'name_en' => 'Quang Tri', 'type' => 'tinh', 'sort_order' => 23],
            ['code' => 'TTH', 'name_vi' => 'Thành phố Huế', 'name_en' => 'Hue', 'type' => 'thanh-pho', 'sort_order' => 24],
            ['code' => 'DN', 'name_vi' => 'Thành phố Đà Nẵng', 'name_en' => 'Da Nang', 'type' => 'thanh-pho', 'sort_order' => 25],
            ['code' => 'QNg', 'name_vi' => 'Quảng Ngãi', 'name_en' => 'Quang Ngai', 'type' => 'tinh', 'sort_order' => 26],
            ['code' => 'GL', 'name_vi' => 'Gia Lai', 'name_en' => 'Gia Lai', 'type' => 'tinh', 'sort_order' => 27],
            ['code' => 'KH', 'name_vi' => 'Khánh Hòa', 'name_en' => 'Khanh Hoa', 'type' => 'tinh', 'sort_order' => 28],
            ['code' => 'DLk', 'name_vi' => 'Đắk Lắk', 'name_en' => 'Dak Lak', 'type' => 'tinh', 'sort_order' => 29],
            ['code' => 'LD', 'name_vi' => 'Lâm Đồng', 'name_en' => 'Lam Dong', 'type' => 'tinh', 'sort_order' => 30],
            ['code' => 'DNai', 'name_vi' => 'Đồng Nai', 'name_en' => 'Dong Nai', 'type' => 'tinh', 'sort_order' => 40],
            ['code' => 'HCM', 'name_vi' => 'Thành phố Hồ Chí Minh', 'name_en' => 'Ho Chi Minh City', 'type' => 'thanh-pho', 'sort_order' => 41],
            ['code' => 'TNi', 'name_vi' => 'Tây Ninh', 'name_en' => 'Tay Ninh', 'type' => 'tinh', 'sort_order' => 42],
            ['code' => 'DT', 'name_vi' => 'Đồng Tháp', 'name_en' => 'Dong Thap', 'type' => 'tinh', 'sort_order' => 43],
            ['code' => 'VL', 'name_vi' => 'Vĩnh Long', 'name_en' => 'Vinh Long', 'type' => 'tinh', 'sort_order' => 44],
            ['code' => 'AG', 'name_vi' => 'An Giang', 'name_en' => 'An Giang', 'type' => 'tinh', 'sort_order' => 45],
            ['code' => 'CT', 'name_vi' => 'Thành phố Cần Thơ', 'name_en' => 'Can Tho', 'type' => 'thanh-pho', 'sort_order' => 46],
            ['code' => 'CM', 'name_vi' => 'Cà Mau', 'name_en' => 'Ca Mau', 'type' => 'tinh', 'sort_order' => 47],
        ];

        $now = now();

        $payload = array_map(function (array $province) use ($vietnamId, $now) {
            return [
                'country_id' => $vietnamId,
                'name_vi' => $province['name_vi'],
                'name_en' => $province['name_en'],
                'code' => $province['code'],
                'type' => $province['type'],
                'sort_order' => $province['sort_order'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $provinces);

        DB::table('npp_provinces')->upsert(
            $payload,
            ['country_id', 'code'],
            ['name_vi', 'name_en', 'type', 'sort_order', 'is_active', 'updated_at']
        );

        $this->command->info('ProvinceSeeder: seeded ' . count($provinces) . ' provinces for VN.');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Seed countries from QuocGiaHTPPMucSeeder source data.
     */
    public function run(): void
    {
        $countries = [
            ['name_vi' => 'Việt Nam', 'name_en' => 'Vietnam', 'code' => 'VN', 'phone_code' => '+84', 'region' => 'dong-nam-a', 'sort_order' => 1],
            ['name_vi' => 'Lào', 'name_en' => 'Laos', 'code' => 'LA', 'phone_code' => '+856', 'region' => 'dong-nam-a', 'sort_order' => 2],
            ['name_vi' => 'Campuchia', 'name_en' => 'Cambodia', 'code' => 'KH', 'phone_code' => '+855', 'region' => 'dong-nam-a', 'sort_order' => 3],
            ['name_vi' => 'Malaysia', 'name_en' => 'Malaysia', 'code' => 'MY', 'phone_code' => '+60', 'region' => 'dong-nam-a', 'sort_order' => 4],
            ['name_vi' => 'Indonesia', 'name_en' => 'Indonesia', 'code' => 'ID', 'phone_code' => '+62', 'region' => 'dong-nam-a', 'sort_order' => 5],
            ['name_vi' => 'Brunei', 'name_en' => 'Brunei', 'code' => 'BN', 'phone_code' => '+673', 'region' => 'dong-nam-a', 'sort_order' => 6],
            ['name_vi' => 'Myanmar', 'name_en' => 'Myanmar', 'code' => 'MM', 'phone_code' => '+95', 'region' => 'dong-nam-a', 'sort_order' => 7],
            ['name_vi' => 'Philippines', 'name_en' => 'Philippines', 'code' => 'PH', 'phone_code' => '+63', 'region' => 'dong-nam-a', 'sort_order' => 8],
            ['name_vi' => 'Yemen', 'name_en' => 'Yemen', 'code' => 'YE', 'phone_code' => '+967', 'region' => 'trung-dong', 'sort_order' => 10],
            ['name_vi' => 'UAE', 'name_en' => 'UAE', 'code' => 'AE', 'phone_code' => '+971', 'region' => 'trung-dong', 'sort_order' => 11],
            ['name_vi' => 'Saudi Arabia', 'name_en' => 'Saudi Arabia', 'code' => 'SA', 'phone_code' => '+966', 'region' => 'trung-dong', 'sort_order' => 12],
            ['name_vi' => 'Iraq', 'name_en' => 'Iraq', 'code' => 'IQ', 'phone_code' => '+964', 'region' => 'trung-dong', 'sort_order' => 13],
            ['name_vi' => 'Iran', 'name_en' => 'Iran', 'code' => 'IR', 'phone_code' => '+98', 'region' => 'trung-dong', 'sort_order' => 14],
            ['name_vi' => 'Thổ Nhĩ Kỳ', 'name_en' => 'Turkiye', 'code' => 'TR', 'phone_code' => '+90', 'region' => 'trung-dong', 'sort_order' => 15],
            ['name_vi' => 'Afghanistan', 'name_en' => 'Afghanistan', 'code' => 'AF', 'phone_code' => '+93', 'region' => 'trung-dong', 'sort_order' => 16],
            ['name_vi' => 'Pakistan', 'name_en' => 'Pakistan', 'code' => 'PK', 'phone_code' => '+92', 'region' => 'trung-dong', 'sort_order' => 17],
            ['name_vi' => 'Togo', 'name_en' => 'Togo', 'code' => 'TG', 'phone_code' => '+228', 'region' => 'chau-phi', 'sort_order' => 20],
            ['name_vi' => 'Burkina Faso', 'name_en' => 'Burkina Faso', 'code' => 'BF', 'phone_code' => '+226', 'region' => 'chau-phi', 'sort_order' => 21],
            ['name_vi' => 'Ghana', 'name_en' => 'Ghana', 'code' => 'GH', 'phone_code' => '+233', 'region' => 'chau-phi', 'sort_order' => 22],
            ['name_vi' => 'Mỹ', 'name_en' => 'United States', 'code' => 'US', 'phone_code' => '+1', 'region' => 'chau-my', 'sort_order' => 30],
            ['name_vi' => 'Mexico', 'name_en' => 'Mexico', 'code' => 'MX', 'phone_code' => '+52', 'region' => 'chau-my', 'sort_order' => 31],
            ['name_vi' => 'Venezuela', 'name_en' => 'Venezuela', 'code' => 'VE', 'phone_code' => '+58', 'region' => 'chau-my', 'sort_order' => 32],
            ['name_vi' => 'Brazil', 'name_en' => 'Brazil', 'code' => 'BR', 'phone_code' => '+55', 'region' => 'chau-my', 'sort_order' => 33],
            ['name_vi' => 'Argentina', 'name_en' => 'Argentina', 'code' => 'AR', 'phone_code' => '+54', 'region' => 'chau-my', 'sort_order' => 34],
            ['name_vi' => 'Peru', 'name_en' => 'Peru', 'code' => 'PE', 'phone_code' => '+51', 'region' => 'chau-my', 'sort_order' => 35],
            ['name_vi' => 'Panama', 'name_en' => 'Panama', 'code' => 'PA', 'phone_code' => '+507', 'region' => 'chau-my', 'sort_order' => 36],
            ['name_vi' => 'Cuba', 'name_en' => 'Cuba', 'code' => 'CU', 'phone_code' => '+53', 'region' => 'chau-my', 'sort_order' => 37],
            ['name_vi' => 'Nga', 'name_en' => 'Russia', 'code' => 'RU', 'phone_code' => '+7', 'region' => 'chau-au', 'sort_order' => 40],
        ];

        $now = now();

        $payload = array_map(function (array $country) use ($now) {
            return [
                'name_vi' => $country['name_vi'],
                'name_en' => $country['name_en'],
                'code' => $country['code'],
                'phone_code' => $country['phone_code'],
                'region' => $country['region'],
                'sort_order' => $country['sort_order'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $countries);

        DB::table('npp_countries')->upsert(
            $payload,
            ['code'],
            ['name_vi', 'name_en', 'phone_code', 'region', 'sort_order', 'is_active', 'updated_at']
        );

        $this->command->info('CountrySeeder: seeded ' . count($countries) . ' countries.');
    }
}

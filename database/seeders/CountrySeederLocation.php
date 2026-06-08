<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeederLocation extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/countries.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("File not found: {$jsonPath}");
            return;
        }

        $allCountries = json_decode(file_get_contents($jsonPath), true);

        if (!is_array($allCountries)) {
            $this->command->error('Invalid JSON format.');
            return;
        }

        // Index theo country code để tra nhanh
        $coordsMap = collect($allCountries)->keyBy('country');

        $codes = DB::table('npp_countries')->pluck('code');

        $updated = 0;

        foreach ($codes as $code) {
            $entry = $coordsMap->get($code);
            if (!$entry) {
                $this->command->warn("No coords found for: {$code}");
                continue;
            }

            DB::table('npp_countries')
                ->where('code', $code)
                ->update([
                    'latitude' => $entry['latitude'],
                    'longitude' => $entry['longitude'],
                ]);

            $updated++;
        }

        $this->command->info("CountrySeederLocation: updated {$updated} countries.");
    }
}

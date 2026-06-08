<?php

namespace Database\Seeders;

use App\Services\HomepageLayoutService;
use Illuminate\Database\Seeder;

class HomepageLayoutSeeder extends Seeder
{
    public function run(): void
    {
        app(HomepageLayoutService::class)->ensureDefaultBlocks();

        $this->command->info('Homepage layout blocks seeded (category + 9 posts).');
    }
}

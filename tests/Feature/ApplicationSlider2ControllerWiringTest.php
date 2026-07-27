<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationSlider2ControllerWiringTest extends TestCase
{
    public function test_home_controller_fetches_and_shares_home_slider_3(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/langding/HomeController.php'));

        $this->assertStringContainsString("getBannersBySlug('home-slider-3')", $source);
        $this->assertStringContainsString("'homeSliderBanners3',", $source);
    }

    public function test_sector_controller_maps_home_slider_3_banner_key(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/langding/SectorController.php'));

        $this->assertStringContainsString("'home-slider-3' => \$data['homeSliderBanners3'] = \$banners,", $source);
    }
}

<?php

namespace Tests\Unit;

use Tests\TestCase;

class ApplicationSlider2LayoutConfigTest extends TestCase
{
    public function test_homepage_layout_registers_application_slider_2_block(): void
    {
        $blocks = config('homepage_layout.blocks');
        $sectionTypes = array_column($blocks, 'section_type');
        $sortOrders = array_column($blocks, 'sort_order');

        $this->assertContains('application_slider_2', $sectionTypes);
        $this->assertSame(count($sortOrders), count(array_unique($sortOrders)), 'sort_order values must be unique');
        $this->assertSame('home-slider-3', config('homepage_layout.banner_keys.application_slider_2'));
    }

    public function test_sector_layout_registers_application_slider_2_block(): void
    {
        $blocks = config('sector_layout.blocks');
        $sectionTypes = array_column($blocks, 'section_type');
        $sortOrders = array_column($blocks, 'sort_order');

        $this->assertContains('application_slider_2', $sectionTypes);
        $this->assertSame(count($sortOrders), count(array_unique($sortOrders)), 'sort_order values must be unique');
        $this->assertSame('home-slider-3', config('sector_layout.banner_keys.application_slider_2'));
    }

    public function test_application_slider_2_view_exists(): void
    {
        $this->assertTrue(view()->exists('langding.home.blocks.application_slider_2'));
    }
}

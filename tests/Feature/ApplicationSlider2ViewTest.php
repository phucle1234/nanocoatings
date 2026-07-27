<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationSlider2ViewTest extends TestCase
{
    public function test_renders_banner_images_as_slides(): void
    {
        $banner = (object) [
            'all_images' => ['/storage/images/second-slider-1.webp', '/storage/images/second-slider-2.webp'],
            'url' => 'https://example.com/product',
            'title' => 'Second application slider banner',
        ];

        $view = $this->view('langding.home.blocks.application_slider_2', [
            'homeSliderBanners3' => ['banners' => collect([$banner]), 'category_bg_image' => null, 'meta_description' => null],
            'isSectorPage' => false,
        ]);

        $view->assertSee('/storage/images/second-slider-1.webp', false);
        $view->assertSee('/storage/images/second-slider-2.webp', false);
        $view->assertSee('https://example.com/product', false);
    }
}

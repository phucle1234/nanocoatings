<?php

namespace Tests\Unit;

use Tests\TestCase;

class MainSliderItemsAutoplayTest extends TestCase
{
    public function test_main_slider_items_autoplays_every_3_seconds(): void
    {
        $js = file_get_contents(public_path('langding/js/main.js'));

        $initStart = strpos($js, '$(".main-slider-items").slick({');
        $initEnd = strpos($js, '});', $initStart);
        $initBlock = substr($js, $initStart, $initEnd - $initStart);

        $this->assertStringContainsString('autoplay: true', $initBlock);
        $this->assertStringContainsString('autoplaySpeed: 3000', $initBlock);
    }
}

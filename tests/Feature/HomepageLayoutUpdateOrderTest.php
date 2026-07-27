<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HomepageLayoutUpdateOrderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_homepage_layout_update_order_accepts_current_block_count(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => 1]), 'backpack');

        $blockCount = count(config('homepage_layout.blocks'));
        $blocks = [];
        for ($i = 1; $i <= $blockCount; $i++) {
            $blocks[] = ['id' => $i, 'is_active' => true];
        }

        $response = $this->post(route('admin.homepage-layout.update'), ['blocks' => $blocks]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.homepage-layout.index'));
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ProductCategory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiTest extends TestCase
{
    /**
     * Test Category API - Get all categories
     */
    public function test_get_all_categories(): void
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'slug',
                        'name',
                        'description',
                        'icon',
                        'image',
                        'parent_id',
                        'is_featured',
                        'sort_order',
                        'products_count',
                    ]
                ],
                'locale'
            ])
            ->assertJson(['success' => true]);
    }

    /**
     * Test Category API - Get featured categories
     */
    public function test_get_featured_categories(): void
    {
        $response = $this->getJson('/api/categories/featured');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'locale'
            ])
            ->assertJson(['success' => true]);
    }

    /**
     * Test Category API - Get root categories
     */
    public function test_get_root_categories(): void
    {
        $response = $this->getJson('/api/categories/root');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'locale'
            ])
            ->assertJson(['success' => true]);
    }

    /**
     * Test Category API - Get category by ID
     */
    public function test_get_category_by_id(): void
    {
        $category = ProductCategory::where('is_active', true)->first();

        if ($category) {
            $response = $this->getJson("/api/categories/{$category->id}");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'slug',
                        'name',
                        'description',
                        'icon',
                        'image',
                        'parent_id',
                        'children',
                        'path',
                        'is_featured',
                        'sort_order',
                        'products_count',
                    ],
                    'locale'
                ])
                ->assertJson(['success' => true]);
        } else {
            $this->markTestSkipped('No active categories found');
        }
    }

    /**
     * Test Category API - Get category children
     */
    public function test_get_category_children(): void
    {
        $category = ProductCategory::where('is_active', true)->first();

        if ($category) {
            $response = $this->getJson("/api/categories/{$category->id}/children");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'parent',
                        'children'
                    ],
                    'locale'
                ])
                ->assertJson(['success' => true]);
        } else {
            $this->markTestSkipped('No active categories found');
        }
    }

    /**
     * Test Category API - Get category products
     */
    public function test_get_category_products(): void
    {
        $category = ProductCategory::where('is_active', true)->first();

        if ($category) {
            $response = $this->getJson("/api/categories/{$category->id}/products");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'category',
                        'products',
                        'pagination' => [
                            'current_page',
                            'per_page',
                            'total',
                            'last_page',
                            'from',
                            'to',
                        ]
                    ],
                    'locale'
                ])
                ->assertJson(['success' => true]);
        } else {
            $this->markTestSkipped('No active categories found');
        }
    }

    /**
     * Test Product API - Get products by category (query param)
     */
    public function test_get_products_by_category_query(): void
    {
        $category = ProductCategory::where('is_active', true)->first();

        if ($category) {
            $response = $this->getJson("/api/products/by-category?category_id={$category->id}");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'category',
                        'products',
                        'pagination'
                    ],
                    'locale'
                ])
                ->assertJson(['success' => true]);
        } else {
            $this->markTestSkipped('No active categories found');
        }
    }

    /**
     * Test Product API - Get products by category (URL param)
     */
    public function test_get_products_by_category_url(): void
    {
        $category = ProductCategory::where('is_active', true)->first();

        if ($category) {
            $response = $this->getJson("/api/products/category/{$category->id}");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'category',
                        'products',
                        'pagination'
                    ],
                    'locale'
                ])
                ->assertJson(['success' => true]);
        } else {
            $this->markTestSkipped('No active categories found');
        }
    }

    /**
     * Test Search API
     */
    public function test_search_api(): void
    {
        $response = $this->postJson('/api/search', [
            'query' => 'test',
            'type' => 'text'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'query',
                'results',
                'total',
                'search_type'
            ]);
    }

    /**
     * Test Category API - 404 for non-existent category
     */
    public function test_get_category_not_found(): void
    {
        $response = $this->getJson('/api/categories/99999');

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    /**
     * Test Category API - Filter by parent_id
     */
    public function test_get_categories_filter_by_parent(): void
    {
        $response = $this->getJson('/api/categories?parent_id=null');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test Category API - Include children
     */
    public function test_get_categories_include_children(): void
    {
        $response = $this->getJson('/api/categories?include_children=1');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}

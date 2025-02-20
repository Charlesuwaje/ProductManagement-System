<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    // use RefreshDatabase;

    /**
     * Test getting all products with filters.
     */
    public function test_get_all_products_with_filters()
    {
        Product::factory()->create(['name' => 'Laptop', 'category_id' => 1, 'price' => 1000]);
        Product::factory()->create(['name' => 'Phone', 'category_id' => 2, 'price' => 500]);

        $response = $this->getJson('/api/products?name=Lap&category_id=1&price_min=900&price_max=1100');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Laptop'])
            ->assertJsonMissing(['name' => 'Phone']);
    }

    /**
     * Test creating a product with valid data.
     */
    public function test_create_product_with_valid_data()
    {
        $data = [
            'name' => 'Tablet',
            'category_id' => 3,
            'price' => 300,
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Tablet']);

        $this->assertDatabaseHas('products', $data);
    }

    /**
     * Test creating a product with invalid data.
     */
    public function test_create_product_with_invalid_data()
    {
        $data = [
            'category_id' => 3,
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(422);
    }
}
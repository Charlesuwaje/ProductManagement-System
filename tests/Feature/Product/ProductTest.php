<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    // use RefreshDatabase;

    /**
     * Test getting all products with filters.
     */

    public function test_get_all_products_with_filters()
    {
        $user = User::factory()->create();

        $token = JWTAuth::fromUser($user);

        Product::factory()->create(['name' => 'Laptop', 'category_id' => 1, 'price' => 1000]);
        Product::factory()->create(['name' => 'Phone', 'category_id' => 2, 'price' => 500]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/products?name=Lap&category_id=1&price_min=900&price_max=1100');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Laptop'])
            ->assertJsonMissing(['name' => 'Phone']);
    }



    /**
     * Test creating a product with valid data.
     */

    public function test_create_product_with_valid_data()
    {
        $user = User::factory()->create();

        $token = JWTAuth::fromUser($user);

        $data = [
            'name' => 'Tablet',
            'category_id' => 3,
            'price' => 300,
            'description' => 'dd',
            'stock_quantity' => 33,
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/products', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Tablet']);

        $this->assertDatabaseHas('products', $data);
    }


    /**
     * Test creating a product with invalid data.
     */

    public function test_create_product_with_invalid_data()
    {
        $user = User::factory()->create();

        $token = JWTAuth::fromUser($user);

        $data = [
            'category_id' => 3,
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/products', $data);

        $response->assertStatus(500);
    }

    public function test_get_product_by_id()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $product = Product::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $product->id,
                'name' => $product->name,
            ]);
    }

    public function test_update_product()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $product = Product::factory()->create();

        $updatedData = [
            'name' => 'Updated Product',
            'price' => 150,
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->putJson("/api/products/{$product->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Product',
                'price' => 150,
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'price' => 150,
        ]);
    }

    public function test_delete_product()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $product = Product::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}

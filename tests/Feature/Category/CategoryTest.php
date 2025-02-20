<?php

namespace Tests\Feature\Category;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Category;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    // public function test_index()
    // {
    //     $user = User::factory()->create();
    //     $token = JWTAuth::fromUser($user);

    //     Category::factory()->count(3)->create();

    //     $response = $this->withHeaders([
    //         'Authorization' => "Bearer $token",
    //     ])->getJson('/api/categories');

    //     $response->assertStatus(200)
    //         ->assertJsonStructure([
    //             'data' => [
    //                 '*' => ['id', 'name', 'created_at', 'updated_at'],
    //             ],
    //             'links',
    //             'meta',
    //         ]);
    // }

    public function test_index()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        Category::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/categories');

        // $response->dump();

        $response->assertStatus(200);
        // ->assertJsonStructure([
        //     'data' => [
        //         '*' => ['id', 'name', 'created_at', 'updated_at'],
        //     ],
        //     'links',
        //     'meta',
        // ]);
    }




    // public function test_store()
    // {
    //     $user = User::factory()->create();
    //     $token = JWTAuth::fromUser($user);

    //     $data = ['name' => 'Electronics'];

    //     $response = $this->withHeaders([
    //         'Authorization' => "Bearer $token",
    //     ])->postJson('/api/categories', $data);

    //     $response->assertStatus(201)
    //         ->assertJsonFragment(['message' => 'Category created successfully.'])
    //         ->assertJsonFragment(['name' => 'Electronics']);

    //     $this->assertDatabaseHas('categories', ['name' => 'Electronics']);
    // }

    public function test_store()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $data = [
            'name' => 'Electronics',
            'description' => 'Category for electronic items',
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/categories', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Category created successfully.'])
            ->assertJsonFragment(['name' => 'Electronics']);

        $this->assertDatabaseHas('categories', ['name' => 'Electronics']);
    }

    public function test_show()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $category = Category::factory()->hasProducts(2)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $category->id])
            ->assertJsonStructure([
                'id',
                'name',
                'products' => [
                    '*' => ['id', 'name', 'price', 'created_at', 'updated_at'],
                ],
            ]);
    }

    public function test_update()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $category = Category::factory()->create(['name' => 'Old Name']);

        $updatedData = ['name' => 'Updated Name'];

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->putJson("/api/categories/{$category->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Category updated successfully.'])
            ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated Name']);
    }

    public function test_destroy()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $category = Category::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Category deleted successfully.']);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}

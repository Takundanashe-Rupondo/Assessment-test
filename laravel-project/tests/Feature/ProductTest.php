<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_get_all_products()
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                ->assertJsonCount(5);
    }

    public function test_empty_products_list_returns_empty_array()
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                ->assertJson([]);
    }

    public function test_anyone_can_get_product_by_id()
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 29.99
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $product->id,
                    'name' => 'Test Product',
                    'price' => 29.99
                ]);
    }

    public function test_getting_nonexistent_product_returns_404()
    {
        $response = $this->getJson('/api/products/99999');

        $response->assertStatus(404);
    }

    public function test_admin_can_create_product()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('auth_token')->plainTextToken;

        $productData = [
            'name' => 'New Product',
            'description' => 'Product description',
            'price' => 19.99,
            'stock' => 100
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/products', $productData);

        $response->assertStatus(201)
                ->assertJson([
                    'name' => 'New Product',
                    'description' => 'Product description',
                    'price' => 19.99,
                    'stock' => 100
                ]);

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'price' => 19.99
        ]);
    }

    public function test_regular_user_cannot_create_product()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $productData = [
            'name' => 'New Product',
            'price' => 19.99
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/products', $productData);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_create_product()
    {
        $productData = [
            'name' => 'New Product',
            'price' => 19.99
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(401);
    }

    public function test_cannot_create_product_with_invalid_data()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/products', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'price']);
    }

    public function test_cannot_create_product_with_negative_price()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('auth_token')->plainTextToken;

        $productData = [
            'name' => 'Invalid Product',
            'price' => -10.99
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/products', $productData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['price']);
    }

    public function test_admin_can_update_product()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();
        $token = $admin->createToken('auth_token')->plainTextToken;

        $updateData = [
            'name' => 'Updated Product',
            'price' => 39.99
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $product->id,
                    'name' => 'Updated Product',
                    'price' => 39.99
                ]);
    }

    public function test_regular_user_cannot_update_product()
    {
        $user = User::factory()->create(['role' => 'user']);
        $product = Product::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $updateData = [
            'name' => 'Updated Product',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_product()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();
        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Product deleted successfully']);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id
        ]);
    }

    public function test_regular_user_cannot_delete_product()
    {
        $user = User::factory()->create(['role' => 'user']);
        $product = Product::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(403);
    }

    public function test_cannot_update_nonexistent_product()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('auth_token')->plainTextToken;

        $updateData = [
            'name' => 'Updated Product',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson('/api/products/99999', $updateData);

        $response->assertStatus(404);
    }

}

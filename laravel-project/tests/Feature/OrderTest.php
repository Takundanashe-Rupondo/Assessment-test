<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;


    

    public function test_user_gets_empty_orders_list()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/orders');

        $response->assertStatus(200)
                ->assertJson([]);
    }

    public function test_user_can_get_own_order_by_id()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $order->id,
                    'user_id' => $user->id
                ]);
    }

    public function test_user_cannot_get_other_users_order()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user2->id]);
        $token = $user1->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson("/api/orders/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_create_order_with_valid_data()
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(2)->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $orderData = [
            'items' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => 2,
                    'price' => $products[0]->price
                ],
                [
                    'product_id' => $products[1]->id,
                    'quantity' => 1,
                    'price' => $products[1]->price
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'id',
                    'user_id',
                    'total_amount',
                    'status',
                    'items' => [
                        '*' => [
                            'id',
                            'product_id',
                            'quantity',
                            'price'
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $products[0]->id,
            'quantity' => 2
        ]);
    }

    public function test_user_cannot_create_order_without_items()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $orderData = [
            'items' => []
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items']);
    }

    public function test_user_cannot_create_order_with_nonexistent_product()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $orderData = [
            'items' => [
                [
                    'product_id' => 99999,
                    'quantity' => 1,
                    'price' => 10.99
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items.0.product_id']);
    }

    public function test_user_cannot_create_order_with_invalid_quantity()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 0,
                    'price' => $product->price
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items.0.quantity']);
    }

    public function test_admin_can_update_order_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create(['status' => 'pending']);
        $token = $admin->createToken('auth_token')->plainTextToken;

        $updateData = [
            'status' => 'completed'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $order->id,
                    'status' => 'completed'
                ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed'
        ]);
    }

    public function test_regular_user_cannot_update_order_status()
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = Order::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $updateData = [
            'status' => 'completed'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_admin_can_get_order_statistics()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Order::factory()->count(5)->create(['status' => 'completed']);
        Order::factory()->count(3)->create(['status' => 'pending']);
        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/orders/stats');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'total_orders',
                    'completed_orders',
                    'pending_orders',
                    'total_revenue'
                ]);
    }

    public function test_regular_user_cannot_get_order_statistics()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/orders/stats');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_orders()
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    }

    public function test_order_total_calculation()
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['price' => 10.00]);
        $product2 = Product::factory()->create(['price' => 20.00]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $orderData = [
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 2,
                    'price' => 10.00
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 1,
                    'price' => 20.00
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
                ->assertJson([
                    'total_amount' => 40.00 // (2 * 10) + (1 * 20)
                ]);
    }
}

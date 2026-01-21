<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_created()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total_amount' => 99.99
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(99.99, $order->total_amount);
        $this->assertEquals($user->id, $order->user_id);
    }

    public function test_order_belongs_to_user()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    public function test_order_has_many_items()
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        $this->assertCount(3, $order->items);
    }

    public function test_order_fillable_attributes()
    {
        $user = User::factory()->create();
        $orderData = [
            'user_id' => $user->id,
            'status' => 'completed',
            'total_amount' => 150.00
        ];

        $order = Order::create($orderData);

        $this->assertEquals($orderData['user_id'], $order->user_id);
        $this->assertEquals($orderData['status'], $order->status);
        $this->assertEquals($orderData['total_amount'], $order->total_amount);
    }

    public function test_order_casts()
    {
        $order = Order::factory()->create([
            'total_amount' => 99.99,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->assertIsFloat($order->total_amount);
        $this->assertInstanceOf(\Carbon\Carbon::class, $order->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $order->updated_at);
    }

    public function test_order_default_status_is_pending()
    {
        $order = Order::factory()->create();

        $this->assertEquals('pending', $order->status);
    }

    public function test_order_status_can_be_completed()
    {
        $order = Order::factory()->create(['status' => 'completed']);

        $this->assertEquals('completed', $order->status);
    }

    public function test_order_status_can_be_cancelled()
    {
        $order = Order::factory()->create(['status' => 'cancelled']);

        $this->assertEquals('cancelled', $order->status);
    }

    public function test_order_total_amount_can_be_zero()
    {
        $order = Order::factory()->create(['total_amount' => 0]);

        $this->assertEquals(0, $order->total_amount);
    }

    public function test_order_total_amount_can_be_decimal()
    {
        $order = Order::factory()->create(['total_amount' => 99.99]);

        $this->assertEquals(99.99, $order->total_amount);
        $this->assertIsFloat($order->total_amount);
    }

    public function test_order_can_have_items()
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create();
        
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 29.99
        ]);

        $this->assertTrue($order->items->contains($orderItem));
        $this->assertEquals(2, $orderItem->quantity);
        $this->assertEquals(29.99, $orderItem->price);
    }

    public function test_order_item_belongs_to_order()
    {
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);

        $this->assertInstanceOf(Order::class, $orderItem->order);
        $this->assertEquals($order->id, $orderItem->order->id);
    }

    public function test_order_item_belongs_to_product()
    {
        $product = Product::factory()->create();
        $orderItem = OrderItem::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $orderItem->product);
        $this->assertEquals($product->id, $orderItem->product->id);
    }

    public function test_order_can_be_updated()
    {
        $order = Order::factory()->create([
            'status' => 'pending',
            'total_amount' => 50.00
        ]);

        $order->update([
            'status' => 'completed',
            'total_amount' => 75.00
        ]);

        $this->assertEquals('completed', $order->fresh()->status);
        $this->assertEquals(75.00, $order->fresh()->total_amount);
    }

    public function test_order_can_be_deleted()
    {
        $order = Order::factory()->create();
        $orderId = $order->id;

        $order->delete();

        $this->assertDatabaseMissing('orders', ['id' => $orderId]);
    }

    public function test_order_with_items_can_be_deleted()
    {
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);
        $itemId = $orderItem->id;

        $order->delete();

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('order_items', ['id' => $itemId]);
    }

    public function test_order_factory_creates_valid_order()
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->user_id);
        $this->assertNotNull($order->status);
        $this->assertNotNull($order->total_amount);
        $this->assertNotEmpty($order->status);
        $this->assertGreaterThanOrEqual(0, $order->total_amount);
    }

    public function test_order_items_total_calculation()
    {
        $order = Order::factory()->create(['total_amount' => 0]);
        $product1 = Product::factory()->create(['price' => 10.00]);
        $product2 = Product::factory()->create(['price' => 20.00]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => 10.00
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => 20.00
        ]);

        // Total should be (2 * 10) + (1 * 20) = 40
        $this->assertCount(2, $order->items);
        $this->assertEquals(40.00, $order->total_amount);
    }

    public function test_multiple_orders_belong_to_same_user()
    {
        $user = User::factory()->create();
        $order1 = Order::factory()->create(['user_id' => $user->id]);
        $order2 = Order::factory()->create(['user_id' => $user->id]);
        $order3 = Order::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $order1->user_id);
        $this->assertEquals($user->id, $order2->user_id);
        $this->assertEquals($user->id, $order3->user_id);
    }
}


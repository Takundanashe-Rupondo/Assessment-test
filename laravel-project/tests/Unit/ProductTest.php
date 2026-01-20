<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_created()
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 29.99,
            'stock' => 100
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals('Test Description', $product->description);
        $this->assertEquals(29.99, $product->price);
        $this->assertEquals(100, $product->stock);
    }

    public function test_product_fillable_attributes()
    {
        $productData = [
            'name' => 'New Product',
            'description' => 'Product Description',
            'price' => 19.99,
            'stock' => 50
        ];

        $product = Product::create($productData);

        $this->assertEquals($productData['name'], $product->name);
        $this->assertEquals($productData['description'], $product->description);
        $this->assertEquals($productData['price'], $product->price);
        $this->assertEquals($productData['stock'], $product->stock);
    }

    public function test_product_casts()
    {
        $product = Product::factory()->create([
            'price' => 29.99,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->assertIsFloat($product->price);
        $this->assertInstanceOf(\Carbon\Carbon::class, $product->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $product->updated_at);
    }

    public function test_product_factory_creates_valid_product()
    {
        $product = Product::factory()->create();

        $this->assertNotNull($product->name);
        $this->assertNotNull($product->price);
        $this->assertNotNull($product->stock);
        $this->assertNotEmpty($product->name);
        $this->assertGreaterThanOrEqual(0, $product->price);
        $this->assertGreaterThanOrEqual(0, $product->stock);
    }

    public function test_product_price_can_be_decimal()
    {
        $product = Product::factory()->create([
            'price' => 19.99
        ]);

        $this->assertEquals(19.99, $product->price);
        $this->assertIsFloat($product->price);
    }

    public function test_product_stock_can_be_zero()
    {
        $product = Product::factory()->create([
            'stock' => 0
        ]);

        $this->assertEquals(0, $product->stock);
    }

    public function test_product_description_is_optional()
    {
        $product = Product::factory()->create([
            'description' => null
        ]);

        $this->assertNull($product->description);
    }

    public function test_product_name_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        Product::factory()->create(['name' => null]);
    }

    public function test_product_price_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        Product::factory()->create(['price' => null]);
    }

    public function test_product_stock_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        Product::factory()->create(['stock' => null]);
    }

    public function test_product_can_be_updated()
    {
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price' => 10.99
        ]);

        $product->update([
            'name' => 'Updated Name',
            'price' => 15.99
        ]);

        $this->assertEquals('Updated Name', $product->fresh()->name);
        $this->assertEquals(15.99, $product->fresh()->price);
    }

    public function test_product_can_be_deleted()
    {
        $product = Product::factory()->create();
        $productId = $product->id;

        $product->delete();

        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }

    public function test_product_stock_can_be_negative()
    {
        $product = Product::factory()->create([
            'stock' => -5
        ]);

        $this->assertEquals(-5, $product->stock);
    }

    public function test_product_price_can_be_zero()
    {
        $product = Product::factory()->create([
            'price' => 0
        ]);

        $this->assertEquals(0, $product->price);
    }

    public function test_product_name_max_length()
    {
        $longName = str_repeat('a', 256);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        Product::factory()->create(['name' => $longName]);
    }

    public function test_product_factory_with_different_price_ranges()
    {
        $product1 = Product::factory()->create(['price' => 1.99]);
        $product2 = Product::factory()->create(['price' => 999.99]);

        $this->assertEquals(1.99, $product1->price);
        $this->assertEquals(999.99, $product2->price);
    }
}

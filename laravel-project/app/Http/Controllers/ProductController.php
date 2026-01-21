<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::all();
        
        // Ensure price is returned as float/decimal
        $products->transform(function ($product) {
            $product->price = (float) $product->price;
            return $product;
        });

        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        
        // Ensure price is returned as float/decimal
        $product->price = (float) $product->price;

        return response()->json($product);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'description' => 'string',
            'stock' => 'required|integer'
        ]);

        $product = Product::create($request->all());
        $product->price = (float) $product->price;

        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());

        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}


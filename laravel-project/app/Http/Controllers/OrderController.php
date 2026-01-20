<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('user', 'items')->get();
        
        // Ensure amounts are returned as float/decimal
        $orders->transform(function ($order) {
            $order->total_amount = (float) $order->total_amount;
            
            // Also ensure order item prices are float
            if ($order->items) {
                $order->items->transform(function ($item) {
                    $item->price = (float) $item->price;
                    return $item;
                });
            }
            
            return $order;
        });

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function show($id)
    {
        $order = Order::with('user', 'items')->findOrFail($id);
        
        // Ensure amounts are returned as float/decimal
        $order->total_amount = (float) $order->total_amount;
        
        // Also ensure order item prices are float
        if ($order->items) {
            $order->items->transform(function ($item) {
                $item->price = (float) $item->price;
                return $item;
            });
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|integer',
        ]);

        $totalAmount = 0;
        $orderItems = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);

            $itemTotal = $product->price * $item['quantity'];
            $totalAmount += $itemTotal;

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $item['price'] ?? $product->price,
            ];
        }

        $order = Order::create([
            'user_id' => $request->user()->id,
            'status' => 'pending',
            'total_amount' => $totalAmount,
        ]);

        $order->items()->createMany($orderItems);

        return response()->json($order, 201);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->status = $request->input('status');
        $order->save();

        return response()->json($order);
    }

    public function getOrderStats(Request $request)
    {
        $status = $request->input('status');

        $orders = DB::select("SELECT * FROM orders WHERE status = '$status'");

        return response()->json($orders);
    }
}


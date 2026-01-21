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
        $orders = Order::with('user', 'items')
            ->where('user_id', $request->user()->id)
            ->get();
        
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

        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::with('user', 'items')->findOrFail($id);
        
        // Check if user owns the order or is admin
        if ($order->user_id !== request()->user()->id && request()->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        // Ensure amounts are returned as float/decimal
        $order->total_amount = (float) $order->total_amount;
        
        // Also ensure order item prices are float
        if ($order->items) {
            $order->items->transform(function ($item) {
                $item->price = (float) $item->price;
                return $item;
            });
        }

        return response()->json($order);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
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
                'price' => $product->price,
            ];
        }

        $order = Order::create([
            'user_id' => $request->user()->id,
            'status' => 'pending',
            'total_amount' => $totalAmount,
        ]);

        $order->items()->createMany($orderItems);
        $order->load('user', 'items');
        $order->total_amount = (float) $order->total_amount;

        return response()->json($order, 201);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        // Only admin can update order status
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        $order->status = $request->input('status');
        $order->save();

        return response()->json($order);
    }

    public function getOrderStats(Request $request)
    {
        $totalOrders = Order::count();
        $completedOrders = Order::where('status', 'completed')->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');

        return response()->json([
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'pending_orders' => $pendingOrders,
            'cancelled_orders' => $cancelledOrders,
            'total_revenue' => (float) $totalRevenue,
        ]);
    }
}


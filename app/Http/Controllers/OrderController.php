<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;

class OrderController extends Controller 
{
    public function index()
    {
        // Pastikan Order memiliki relasi ke Product dan Customer
        $orders = Order::with(['product', 'customer'])->get();
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:customers,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $order = Order::create($validated);
        return response()->json($order, 201);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'customer_id' => 'sometimes|exists:customers,id',
            'quantity' => 'sometimes|integer|min:1',
        ]);

        $order->update($validated);
        return response()->json($order);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
}

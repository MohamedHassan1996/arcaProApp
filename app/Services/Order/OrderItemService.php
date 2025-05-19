<?php
namespace App\Services\Order;

use App\Models\Order\OrderItem;
use App\Models\Product\Product;

class OrderItemService
{
    public function all()
    {
        $orderItems = OrderItem::get();
        return $orderItems;
    }

    public function edit($id)
    {
        $orderItem = OrderItem::with(['order', 'product'])->find($id);
        return $orderItem;
    }

    public function store(array $data)
    {
        $productPrice = Product::where('id', $data['productId'])->pluck('price')->first();
        $orderItem = OrderItem::create([
            'order_id' => $data['orderId'],
            'product_id' => $data['productId'],
            'price' => $productPrice,
            'qty' => $data['qty'],
        ]);
        return $orderItem;
    }
    public function update(){}
    public function delete(){}
}

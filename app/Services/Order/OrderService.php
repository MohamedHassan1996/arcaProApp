<?php
namespace App\Services\Order;

use App\Enums\Order\DiscountType;
use App\Enums\Order\OrderStatus;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Models\Order\Order;
use Illuminate\Support\Facades\Http;

class OrderService
{
    protected $orderItemService;
    public function __construct( OrderItemService $orderItemService)
    {
        $this->orderItemService = $orderItemService;
    }


    public function all(){
         $orders = Order::get();
            return $orders;
    }
    public function editOrder($id){
        return Order::with(['orderItems'])->find($id);
    }

    public function createOrder(array $data){

        $totalPrice = 0;
        $totalPriceAfterDiscount = 0;

        $order = Order::create([
            'discount' => $data['discount']??null,
            'discount_type' => DiscountType::from($data['discountType'])->value,
            'price' => $totalPrice,
            'price_after_discount' => $totalPriceAfterDiscount,
            'client_phone_id' => $data['clientPhoneId'],
            'client_email_id' => $data['clientEmailId'],
            'client_address_id' => $data['clientAddressId'],
            'client_id' => $data['clientId'],
            'status' => OrderStatus::from($data['status'])->value,
        ]);

        foreach ($data['orderItems'] as $itemData) {
            $item= $this->orderItemService->store([
                    'orderId' => $order->id,
                    ...$itemData
                ]);
            $totalPrice += $item->price * $item->qty;
        }

        if ($order->discount_type == DiscountType::PERCENTAGE) {
            $totalPriceAfterDiscount = $totalPrice - ($totalPrice * ($data['discount'] / 100));
        } elseif ($order->discount_type == DiscountType::FIXCED) {
            $totalPriceAfterDiscount = $totalPrice - $data['discount'];
        }elseif($order->discount_type == DiscountType::NO_DISCOUNT){
            $totalPriceAfterDiscount = $totalPrice;
        }
        $order->update([
            'price_after_discount' => $totalPriceAfterDiscount,
            'price' => $totalPrice
        ]);

        return $order;

    }
    public function updateOrder(int $id,array $data){
        $order = Order::find($id);
        $order->update([
            'number' => $data['number'],
            'discount' => $data['discount'],
            'discount_type' => $data['discount_type'],
            'price_after_discount' => $data['price_after_discount'],
            'client_phone_id' => $data['client_phone_id'],
            'client_email_id' => $data['client_email_id'],
            'client_address_id' => $data['client_address_id'],
            'client_id' => $data['client_id'],
            'status' => $data['status'],
        ]);
        $order->save();
        return $order;

    }
    public function deleteOrder($id){
            $order = Order::find($id);
            $order->delete();
    }

}

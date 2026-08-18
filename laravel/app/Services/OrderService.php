<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ClothesVariant;
use App\Models\ShoesVariant;
use App\Jobs\SendOrderNotificationJob;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{

    protected CartService $cartService;
    public function __construct(CartService $cartService){
        $this->cartService = $cartService;
    }

    public function createOrder(array $data){
        $user = Auth::user();
        $cart= $this->cartService->getCart();

        if($cart->items->isEmpty()){
            throw new Exception('Giỏ hàng trống');
        }

        $orderCode = 'ORD-' . date('YMD') . '-' . strtoupper(Str::random(5));
        DB::beginTransaction();
        try{

            $order = Order::create([
                'user_id'=>$user ? $user->id : null,
                'order_code'=> $orderCode,
                'customer_name'=>$data['customer_name'],
                'customer_phone'=>$data['customer_phone'],
                'shipping_address'=>$data['shipping_address'],
                'note' => $data['note'] ?? null,
                'total_amount' => $cart->total,
                'shipping_fee' => $data['shipping_fee'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'payment_method' => $data['payment_method'] ?? 'cod',
                'status' => 'pending',
            ]);

            foreach($cart->items as $item){
                $variant =  $item->variant_type == 'shoe' 
                    ?ShoesVariant::find($item->variant_id) 
                    : ClothesVariant::find($item->variant_id);

                     OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'variant_type' => $item->variant_type,
                    'variant_id' => $item->variant_id,
                    'size' => $item->size,
                    'color' => $item->color,
                    'stud_type' => $item->stud_type,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->price * $item->quantity,
                ]);
            }

            $this->cartService->clearCart();
            DB::commit();

            // SendOrderNotificationJob::dispatch($order);
            return $order->load('items');

        }catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function getOrdersByUser($userId){
        return Order::where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    public function getOrderDetail($orderId){
        return Order::with('items.product')
                ->where('user_id', Auth::id())
                ->findOrFail($orderId);
    }

    //admin method

    public function getAllOrders(){
        return Order::with('user', 'items.product')
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    public function getOrderById($orderId){
        return Order::with('user', 'items.product')
                ->findOrFail($orderId);
    }

    public function updateOrderStatus( $orderId, $status){
        $order = Order::findOrFail($orderId);

        if($status === 'processing' && $order->status ==='pending'){
            $this->deductStock($order);
        }

        $order->status =  $status;
        $order->save();

        return $order;
    }

    protected function deductStock(Order $order){
        foreach($order->items as $item){
            if($item->variant_type ==='shoe'){
                $variant =ShoesVariant::find($item->variant_id);
                if($variant){
                    $variant->quantity -= $item->quantity;
                    if($variant->quantity < 0){
                        throw new Exception("không đủ tồn kho cho sản phẩm: " . $variant->product->name);
                    }
                    $variant->save();
                }
            }else{
                $variant = ClothesVariant::find($item->variant_id);
                if($variant){
                    $variant->quantity -= $item->quantity;
                    if($variant->quantity < 0){
                        throw new Exception("Không đủ tồn kho cho sản phẩm: " . $variant->product->name);
                    }
                    $variant->save();
                }
            }
        }
    }

}
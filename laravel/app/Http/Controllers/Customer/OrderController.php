<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    //

    protected CartService $cartService;
    protected OrderService $orderService;

    public function __construct(OrderService $orderService, CartService $cartService)
    {
        $this->orderService = $orderService;
        $this->cartService = $cartService;
    }

    public function checkout(){
        if(!Auth::check()){
            return redirect()->route('/')->with('error', 'Vui Lòng đăng nhập để đặt hàng.');
        }

        $cart = $this->cartService->getCart();
        if($cart->items->isEmpty()){
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống');
        }

        $cartTotal = $this->cartService->getCartTotal();
        return view('customer.checkout.index', compact('cart', 'cartTotal'));
    }

    public function placeOrder(Request $request){
        if(!Auth::check()){
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để đặt hàng.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'customer_name'=> 'required|string|max:255',
            'customer_phone'=>'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'nullable|in:cod,banking,momo,vnpay',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try{
            $order =  $this->orderService->createOrder($request->all());
            return response()->json([
                    'success' => true,
                    'message' => 'Đặt hàng thành công!',
                    'order_code' => $order->order_code,
                    'order_id' => $order->id,
                ]);

        }catch( Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $orders = $this->orderService->getOrdersByUser(Auth::id());
        return view('customer.orders.index', compact('orders'));
    }

        /**
     * Chi tiết đơn hàng
     */
    public function show($id)
    {
        $order = $this->orderService->getOrderDetail($id);
        return view('customer.orders.show', compact('order'));
    }
}

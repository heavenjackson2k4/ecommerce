<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\ProductService;
use App\Services\OrderService;
use DomainException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    //

    protected CartService $cartService;
    protected OrderService $orderService;
    protected ProductService $productService;

    public function __construct(OrderService $orderService, CartService $cartService, ProductService $productService)
    {
        $this->orderService = $orderService;
        $this->cartService = $cartService;
        $this->productService = $productService;
    }

    public function checkout(){
        $cart = $this->cartService->getCart();
        if($cart->items->isEmpty()){
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống');
        }

        $cartTotal = $this->cartService->getCartTotal();
        $checkoutData = session('checkout.form', []);
        return view('customer.checkout.index', compact('cart', 'cartTotal', 'checkoutData'));
    }

    public function placeOrder(Request $request){
        $validator = Validator::make($request->all(), [
            'customer_name'=> 'required|string|max:255',
            'customer_email'=> 'required|email|max:255',
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

        $checkoutData = $validator->validated();

        if(!Auth::check()){
            $request->session()->put('checkout.form', $checkoutData);
            $request->session()->put('checkout.auth_pending', true);

            return response()->json([
                'success' => false,
                'requires_auth' => true,
                'message' => 'Vui lòng đăng ký tài khoản để hoàn tất đặt hàng.',
                'redirect_url' => route('register'),
            ], 401);
        }

        abort_unless(Auth::user()->isCustomer(), 403, 'Chỉ khách hàng mới được đặt hàng.');

        try{
            $order =  $this->orderService->createOrder($checkoutData);
            $request->session()->forget(['checkout.form', 'checkout.auth_pending']);
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
        $itemImages = $this->productService->getItemImages($order->items);
        return view('customer.orders.show', compact('order', 'itemImages'));
    }

    public function cancel(Request $request, int $id)
    {
        try {
            $order = $this->orderService->cancelOrderByCustomer($id, (int) Auth::id());

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Đã hủy đơn hàng thành công.',
                    'data' => $order->statusData(),
                ]);
            }

            return redirect()->back()->with('success', 'Đã hủy đơn hàng thành công.');
        } catch (DomainException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 409);
            }

            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    public function statuses(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1|max:25',
            'ids.*' => 'required|integer|min:1|distinct',
        ]);

        return response()->json([
            'data' => $this->orderService->getOrderStatuses($validated['ids'], (int) Auth::id()),
        ])->header('Cache-Control', 'no-store, private');
    }
}

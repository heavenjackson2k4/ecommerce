<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected ProductService $productService;

    public function __construct(OrderService $orderService, ProductService $productService)
    {
        $this->orderService = $orderService;
        $this->productService = $productService;
    }

    /**
     * Danh sách đơn hàng cho admin
     */
    public function index()
    {
        $orders = $this->orderService->getAllOrders();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Tìm kiếm đơn hàng theo số điện thoại và ngày đặt
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'orderDates' => 'nullable|date_format:d-m-Y',
        ]);

        $orders = $this->orderService->searchOrdersByPhoneOrDate(
            trim((string) ($validated['phone'] ?? '')),
            (string) ($validated['orderDates'] ?? '')
        );

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Chi tiết đơn hàng cho admin
     */
    public function show($id)
    {
        $order = $this->orderService->getOrderById($id);
        $itemImages = $this->productService->getItemImages($order->items);
        return view('admin.orders.show', compact('order', 'itemImages'));
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        try {
            $order = $this->orderService->updateOrderStatus($id, $request->status);
            
            return redirect()->back()->with('success', 'Cập nhật trạng thái đơn hàng thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * API cập nhật trạng thái (cho AJAX)
     */
    public function updateStatusApi(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        try {
            $order = $this->orderService->updateOrderStatus($id, $request->status);
            
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công!',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
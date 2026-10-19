<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ClothesVariant;
use App\Models\ShoesVariant;
use App\Jobs\SendOrderNotificationJob;
use App\Events\OrderStatusUpdated;
use DomainException;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{

    private const PER_PAGE = 15;
    private const MAX_INVENTORY_QUANTITY = 1000000;

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
                    ->orderBy('id', 'desc')
                    ->paginate(self::PER_PAGE);
    }

    public function getOrderDetail($orderId){
        return Order::with('items.product.shoe.images', 'items.product.cloth.images')
                ->where('user_id', Auth::id())
                ->findOrFail($orderId);
    }

    //admin method

    public function getAllOrders(){
        return Order::query()
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->paginate(self::PER_PAGE);
    }

    public function searchOrdersByPhoneOrDate(string $phone, string $orderDates){
        $query = Order::query()
                    ->select([
                        'id',
                        'order_code',
                        'customer_name',
                        'customer_phone',
                        'total_amount',
                        'status',
                        'created_at',
                    ]);

        if ($phone !== '') {
            $query->where('customer_phone', $phone);
        }

        if ($orderDates !== '') {
            $date = Carbon::createFromFormat('d-m-Y', $orderDates)->toDateString();
            $query->whereDate('created_at', $date);
        }

        return $query->orderBy('created_at', 'desc')
                     ->orderBy('id', 'desc')
                     ->paginate(self::PER_PAGE)
                     ->withQueryString();
    }

    public function getOrderById($orderId){
        return Order::with('user', 'items.product.shoe.images', 'items.product.cloth.images')
                ->findOrFail($orderId);
    }

    public function getOrderStatuses(array $ids, ?int $customerId = null): array
    {
        return Order::query()
            ->select(['id', 'status'])
            ->whereIn('id', $ids)
            ->when($customerId !== null, fn ($query) => $query->where('user_id', $customerId))
            ->get()
            ->map(fn (Order $order) => $order->statusData())
            ->all();
    }

    public function updateOrderStatus(int $orderId, string $status, string $currentStatus): Order
    {
        return $this->transitionOrder($orderId, $status, $currentStatus);
    }

    public function cancelOrderByCustomer(int $orderId, int $customerId): Order
    {
        return $this->transitionOrder($orderId, 'cancelled', 'pending', $customerId);
    }

    private function transitionOrder(int $orderId, string $status, string $currentStatus, ?int $customerId = null): Order
    {
        return DB::transaction(function () use ($orderId, $status, $currentStatus, $customerId) {
            $order = Order::query()
                ->when($customerId !== null, fn ($query) => $query->where('user_id', $customerId))
                ->lockForUpdate()
                ->findOrFail($orderId);

            if ($order->status !== $currentStatus) {
                throw new DomainException('Trạng thái đơn đã thay đổi. Vui lòng kiểm tra lại trước khi thao tác.');
            }

            if (!in_array($status, $order->allowedNextStatuses(), true)) {
                throw new DomainException('Không thể chuyển đơn sang trạng thái đã chọn.');
            }

            if ($status === 'processing') {
                $this->adjustOrderStock($order, false);
            } elseif ($status === 'cancelled' && $order->status === 'processing') {
                $this->adjustOrderStock($order, true);
            }

            $order->status = $status;
            $order->save();

            DB::afterCommit(function () use ($order) {
                try {
                    event(new OrderStatusUpdated($order));
                } catch (\Throwable $exception) {
                    // Đơn đã lưu thành công; lỗi gửi thông báo không được báo thành lỗi cập nhật đơn.
                    report($exception);
                }
            });

            return $order;
        }, 3);
    }

    private function adjustOrderStock(Order $order, bool $restore): void
    {
        $items = $order->items()->get();

        if ($items->isEmpty() || $items->contains(fn ($item) => !in_array($item->variant_type, ['shoe', 'cloth'], true) || $item->quantity <= 0)) {
            throw new DomainException('Chi tiết đơn hàng không hợp lệ. Vui lòng kiểm tra trước khi xử lý.');
        }

        foreach (['shoe' => ShoesVariant::class, 'cloth' => ClothesVariant::class] as $type => $model) {
            $groups = $items->where('variant_type', $type)->groupBy('variant_id');

            if ($groups->isEmpty()) {
                continue;
            }

            $variants = $model::query()
                ->whereIn('id', $groups->keys())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($groups as $variantId => $group) {
                $variant = $variants->get($variantId);

                if (!$variant || $group->contains(fn ($item) => (int) $item->product_id !== (int) $variant->product_id)) {
                    throw new DomainException('Có biến thể của đơn không còn tồn tại hoặc không khớp sản phẩm.');
                }

                $quantity = (int) $group->sum('quantity');
                $newQuantity = $restore ? $variant->quantity + $quantity : $variant->quantity - $quantity;

                if ($newQuantity < 0) {
                    throw new DomainException('Không đủ tồn kho cho biến thể: ' . $variant->size . ' / ' . $variant->color . '.');
                }

                if ($newQuantity > self::MAX_INVENTORY_QUANTITY) {
                    throw new DomainException('Tồn kho sau xử lý vượt giới hạn. Vui lòng kiểm tra lại số lượng của biến thể.');
                }

                $variant->quantity = $newQuantity;
                $variant->save();
            }
        }
    }

}
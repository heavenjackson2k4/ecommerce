@extends('layouts.admin')

@section('title', 'Chi tiết doanh thu')
@section('page-title', 'Chi tiết doanh thu')

@section('content')
<div class="relative mx-auto max-w-7xl space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Danh sách đơn hàng</h2>
            <p class="mt-1 text-sm text-gray-500">Ngày đặt: {{ $report['date_range'] }}</p>
        </div>
        <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-900 hover:text-black">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m15 18-6-6 6-6" />
            </svg>
            Quay lại doanh thu
        </a>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white px-6 py-5 shadow-sm">
        <div>
            <p class="text-sm text-gray-500">Số đơn đã nhận hàng</p>
            <p class="mt-1 text-xl font-semibold text-gray-900">{{ number_format($report['order_count'], 0, ',', '.') }}</p>
        </div>
        <div class="sm:text-right">
            <p class="text-sm text-gray-500">Tổng doanh thu trong khoảng chọn</p>
            <p class="mt-1 text-xl font-semibold text-gray-900">{{ number_format($report['revenue'], 0, ',', '.') }} đ</p>
        </div>
    </div>

    <p class="text-sm leading-6 text-gray-500">Chỉ hiển thị đơn đã nhận hàng (delivered). Đơn giá và thành tiền lấy từ đơn hàng lúc mua, không lấy giá sản phẩm hiện tại.</p>

    @forelse($report['orders'] as $order)
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="revenue-order-{{ $order->id }}">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 px-6 py-4">
                <div>
                    <h3 id="revenue-order-{{ $order->id }}" class="font-semibold text-gray-900">#{{ $order->order_code }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $order->customer_name }} · {{ $order->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-4">
                    <p class="font-semibold text-gray-900">{{ number_format($order->total_amount, 0, ',', '.') }} đ</p>
                    <a href="{{ route('admin.orders.show', $order->id) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-900 hover:text-black">
                        Mở đơn hàng<span class="sr-only"> trong tab mới</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 3h7v7m0-7L10 14M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-5" />
                        </svg>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] table-fixed text-sm">
                    <caption class="sr-only">Sản phẩm trong đơn {{ $order->order_code }}</caption>
                    <colgroup>
                        <col class="w-[30%]">
                        <col class="w-[22%]">
                        <col class="w-[12%]">
                        <col class="w-[18%]">
                        <col class="w-[18%]">
                    </colgroup>
                    <thead class="border-b border-gray-200 bg-gray-50 text-gray-500">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left font-medium">Sản phẩm</th>
                            <th scope="col" class="px-6 py-3 text-left font-medium">Biến thể</th>
                            <th scope="col" class="px-6 py-3 text-center font-medium">Số lượng</th>
                            <th scope="col" class="px-6 py-3 text-right font-medium">Đơn giá</th>
                            <th scope="col" class="px-6 py-3 text-right font-medium">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($order->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="break-words px-6 py-4 align-middle font-medium text-gray-900">{{ $item->product?->name ?? 'Sản phẩm không còn tồn tại' }}</td>
                                <td class="break-words px-6 py-4 align-middle text-gray-600">
                                    <p>Size: {{ $item->size }}</p>
                                    <p class="mt-1">Màu: {{ $item->color }}</p>
                                    @if($item->stud_type)
                                        <p class="mt-1">Loại đinh: {{ $item->stud_type }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center align-middle tabular-nums text-gray-700">{{ $item->quantity }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right align-middle tabular-nums text-gray-700">{{ number_format($item->price, 0, ',', '.') }} đ</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right align-middle font-semibold tabular-nums text-gray-900">{{ number_format($item->total, 0, ',', '.') }} đ</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-gray-500">Đơn hàng chưa có thông tin sản phẩm.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @empty
        <div class="rounded-xl border border-gray-200 bg-white px-6 py-12 text-center text-gray-500">
            {{ $report['order_count'] ? 'Không có đơn hàng trên trang này.' : 'Không có đơn đã nhận hàng trong khoảng thời gian đã chọn.' }}
        </div>
    @endforelse

    @if($report['orders']->hasPages())
        <div>{{ $report['orders']->links() }}</div>
    @endif
</div>
@endsection

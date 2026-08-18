@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Quản lý đơn hàng</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left text-sm font-medium text-gray-500 px-4 py-3">Mã đơn hàng</th>
                    <th class="text-left text-sm font-medium text-gray-500 px-4 py-3">Khách hàng</th>
                    <th class="text-left text-sm font-medium text-gray-500 px-4 py-3">Ngày đặt</th>
                    <th class="text-left text-sm font-medium text-gray-500 px-4 py-3">Tổng tiền</th>
                    <th class="text-left text-sm font-medium text-gray-500 px-4 py-3">Trạng thái</th>
                    <th class="text-center text-sm font-medium text-gray-500 px-4 py-3">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $order->order_code }}</td>
                        <td class="px-4 py-3">
                            <div class="text-sm">
                                <div class="font-medium text-gray-900">{{ $order->customer_name }}</div>
                                <div class="text-gray-500">{{ $order->customer_phone }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-1 text-xs rounded-full bg-{{ $order->status_color }}-100 text-{{ $order->status_color }}-700">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="text-sm text-black hover:underline">
                                Xem chi tiết
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            Chưa có đơn hàng nào.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
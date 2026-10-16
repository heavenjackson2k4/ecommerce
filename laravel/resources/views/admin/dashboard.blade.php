@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Tổng quan')
@section('content')
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-sm font-medium text-gray-500">Sản phẩm</h3>
            <p class="mt-1 text-2xl font-bold text-gray-800">{{ $statistics['products'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-sm font-medium text-gray-500">Đơn hàng</h3>
            <p class="mt-1 text-2xl font-bold text-gray-800">{{ $statistics['orders'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-sm font-medium text-gray-500">Danh mục</h3>
            <p class="mt-1 text-2xl font-bold text-gray-800">{{ $statistics['categories'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-sm font-medium text-gray-500">Đơn hàng hôm nay</h3>
            <p class="mt-1 text-2xl font-bold text-gray-800">{{ $statistics['orders_today'] }}</p>
        </div>
        <div class="border border-amber-200 bg-amber-50 p-6 rounded-lg shadow-sm">
            <h3 class="text-sm font-medium text-amber-700">Chờ xác nhận</h3>
            <p class="mt-1 text-2xl font-bold text-amber-700">{{ $statistics['pending_orders'] }}</p>
        </div>
    </div>
@endsection
@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Tổng quan')
@section('content')
    <div class="grid grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-sm font-medium text-gray-500">Sản phẩm</h3>
            <p class="text-2xl font-bold text-gray-800">0</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-sm font-medium text-gray-500">Đơn hàng</h3>
            <p class="text-2xl font-bold text-gray-800">0</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-sm font-medium text-gray-500">Danh mục</h3>
            <p class="text-2xl font-bold text-gray-800">{{ \App\Models\Category::count() }}</p>
        </div>
    </div>
@endsection
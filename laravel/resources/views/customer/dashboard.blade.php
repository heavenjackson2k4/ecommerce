@extends('layouts.customer')

@section('title', 'Dashboard')

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-slate-900">
            <h1 class="text-2xl font-bold">🛒 Chào mừng {{ Auth::user()->name }}!</h1>
            <p class="mt-2">Đây là trang dành cho khách hàng. Bạn có thể xem sản phẩm, giỏ hàng, danh sách yêu thích, ...</p>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div class="text-sm text-slate-600">Sản phẩm yêu thích</div>
                    <div class="text-2xl font-bold">0</div>
                </div>
                <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div class="text-sm text-slate-600">Đơn hàng</div>
                    <div class="text-2xl font-bold">0</div>
                </div>
                <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div class="text-sm text-slate-600">Giỏ hàng</div>
                    <div class="text-2xl font-bold">0</div>
                </div>
            </div>
        </div>
    </div>
@endsection
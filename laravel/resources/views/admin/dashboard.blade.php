@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-slate-900">
            <h1 class="text-2xl font-bold">👋 Chào mừng Admin!</h1>
            <p class="mt-2">Đây là trang quản trị. Bạn có quyền quản lý sản phẩm, đơn hàng, báo cáo, ...</p>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div class="text-sm text-slate-600">Tổng sản phẩm</div>
                    <div class="text-2xl font-bold">0</div>
                </div>
                <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div class="text-sm text-slate-600">Đơn hàng mới</div>
                    <div class="text-2xl font-bold">0</div>
                </div>
                <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div class="text-sm text-slate-600">Khách hàng</div>
                    <div class="text-2xl font-bold">{{ number_format(\App\Models\User::count()) }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
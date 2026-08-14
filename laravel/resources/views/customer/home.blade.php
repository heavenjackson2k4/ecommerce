@extends('layouts.customer')

@section('title', 'Trang chủ')

@section('content')
    <!-- Banner -->
    <section class="mb-8 sm:mb-12">
        <div class="relative bg-gray-100 rounded-2xl overflow-hidden h-56 sm:h-72 md:h-96 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="relative z-10 text-center text-white px-4">
                <h1 class="text-2xl sm:text-4xl md:text-5xl font-bold">Thời trang nam đẳng cấp</h1>
                <p class="mt-2 text-sm sm:text-lg md:text-xl">Phong cách hiện đại – Chất lượng vượt trội</p>
                <a href="{{ route('products.index') }}" class="mt-4 inline-block bg-white text-black px-6 sm:px-8 py-2 sm:py-3 rounded-lg font-medium hover:bg-gray-100 transition text-sm sm:text-base">
                    Mua ngay
                </a>
            </div>
        </div>
    </section>

    <!-- New Products -->
    <section class="mb-10 sm:mb-12">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Sản phẩm mới</h2>
            <a href="{{ route('products.index') }}" class="text-xs sm:text-sm text-gray-500 hover:text-black">Xem tất cả →</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
            @forelse($newProducts as $product)
                @include('customer.partials.product-card', ['product' => $product])
            @empty
                <p class="col-span-full text-gray-500 text-center py-8 text-sm">Chưa có sản phẩm mới.</p>
            @endforelse
        </div>
    </section>

    <!-- Popular Products -->
    <section class="mb-10 sm:mb-12">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Sản phẩm bán chạy</h2>
            <a href="{{ route('products.index') }}" class="text-xs sm:text-sm text-gray-500 hover:text-black">Xem tất cả →</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
            @forelse($popularProducts as $product)
                @include('customer.partials.product-card', ['product' => $product])
            @empty
                <p class="col-span-full text-gray-500 text-center py-8 text-sm">Chưa có sản phẩm bán chạy.</p>
            @endforelse
        </div>
    </section>

    <!-- Categories -->
    <section>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">Danh mục nổi bật</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4">
            @foreach($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="group relative bg-gray-100 rounded-lg overflow-hidden aspect-square flex items-center justify-center hover:shadow-md transition">
                    <div class="text-center p-2">
                        <svg class="mx-auto h-8 w-8 sm:h-10 sm:w-10 text-gray-400 group-hover:text-black transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <p class="mt-1 text-xs sm:text-sm font-medium text-gray-800">{{ $category->name }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endsection
@props(['product'])

<div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden group">
    {{-- <a href="{{ route('products.show', $product->slug) }}" class="block">
        <div class="aspect-square bg-gray-100 flex items-center justify-center relative overflow-hidden">
            <!-- Placeholder ảnh sản phẩm -->
            <svg class="w-12 h-12 sm:w-16 sm:h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
    </a> --}}
    @php
        $primaryImage = $product->product_type === 'SHOE' 
            ? ($product->shoe?->primary_image ?? null)
            : ($product->cloth?->primary_image ?? null);
        $imageUrl = $primaryImage ?: asset('images/placeholder.jpg');
    @endphp

    <a href="{{ route('products.show', $product->slug) }}" class="block">
        <div class="aspect-square bg-gray-100 flex items-center justify-center relative overflow-hidden">
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
        </div>
    </a>
    
    <div class="p-3 sm:p-4">
        <a href="{{ route('products.show', $product->slug) }}" class="block">
            <h3 class="text-xs sm:text-sm font-medium text-gray-900 hover:text-black line-clamp-2 min-h-[2.5rem]">{{ $product->name }}</h3>
        </a>
        <div class="mt-2 flex items-center justify-between flex-wrap gap-1">
            <span class="text-sm sm:text-lg font-bold text-gray-900">{{ number_format($product->base_price, 0, ',', '.') }} ₫</span>
            <div class="flex items-center space-x-1">
                <span class="text-yellow-400 text-xs sm:text-sm">★</span>
                <span class="text-xs sm:text-sm text-gray-500">4.5</span>
            </div>
        </div>
        <div class="mt-3 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
            <button class="w-full sm:flex-1 bg-black text-white text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-lg hover:bg-gray-800 transition add-to-cart" data-product="{{ $product->id }}">
                Thêm vào giỏ
            </button>
            <button class="w-full sm:w-auto p-1.5 sm:p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition wishlist-toggle flex items-center justify-center" data-product="{{ $product->id }}">
                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>
        </div>
    </div>
</div>
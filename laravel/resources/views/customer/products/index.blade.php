@extends('layouts.customer')

@section('title', 'Sản phẩm')

@section('breadcrumb')
    @include('customer.partials.breadcrumb', ['items' => [['label' => 'Sản phẩm']]])
@endsection

@section('content')
    <div class="flex flex-col md:flex-row gap-4 sm:gap-6">
        <!-- Sidebar (ẩn trên mobile, hiện khi bấm nút) -->
        <div class="md:w-64 flex-shrink-0">
            <button id="filter-toggle" class="w-full md:hidden bg-black text-white py-2 rounded-lg text-sm mb-3">
                Bộ lọc ▼
            </button>
            <div id="filter-sidebar" class="hidden md:block">
                @include('customer.partials.sidebar', ['categories' => $categories, 'selectedCategory' => $selectedCategory])
            </div>
        </div>

        <!-- Products -->
        <div class="flex-1">
            <!-- Header -->
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <p class="text-xs sm:text-sm text-gray-500">
                    Hiển thị {{ $products->firstItem() ?? 0 }} – {{ $products->lastItem() ?? 0 }} / {{ $products->total() }} sản phẩm
                </p>
                <div class="flex items-center space-x-2 text-xs sm:text-sm">
                    <label class="text-gray-600">Sắp xếp:</label>
                    <select id="sort" class="border border-gray-300 rounded-lg px-2 sm:px-3 py-1.5 outline-none focus:ring-1 focus:ring-black text-xs sm:text-sm">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá thấp → cao</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá cao → thấp</option>
                    </select>
                </div>
            </div>

            @if($products->count())
                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    @foreach($products as $product)
                        @include('customer.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $products->links('customer.partials.pagination') }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 sm:h-16 sm:w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="mt-4 text-gray-500 text-sm">Không tìm thấy sản phẩm nào.</p>
                    <a href="{{ route('products.index') }}" class="mt-2 inline-block text-black hover:underline text-sm">Xóa bộ lọc</a>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile filter toggle
        const filterToggle = document.getElementById('filter-toggle');
        const filterSidebar = document.getElementById('filter-sidebar');
        if (filterToggle) {
            filterToggle.addEventListener('click', function() {
                filterSidebar.classList.toggle('hidden');
                this.textContent = filterSidebar.classList.contains('hidden') ? 'Bộ lọc ▼' : 'Bộ lọc ▲';
            });
        }

        // Sort
        document.getElementById('sort')?.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', this.value);
            window.location.href = url.toString();
        });

        // Apply filters
        document.getElementById('apply-filters')?.addEventListener('click', function() {
            const url = new URL(window.location.href);
            const category = document.querySelector('input[name="category"]:checked')?.value;
            const priceRange = document.querySelector('input[name="price_range"]:checked')?.value;
            const sizes = Array.from(document.querySelectorAll('input[name="sizes[]"]:checked')).map(el => el.value);
            const studTypes = Array.from(document.querySelectorAll('input[name="stud_types[]"]:checked')).map(el => el.value);

            if (category) url.searchParams.set('category', category);
            else url.searchParams.delete('category');
            if (priceRange) url.searchParams.set('price', priceRange);
            else url.searchParams.delete('price');
            if (sizes.length) url.searchParams.set('sizes', sizes.join(','));
            else url.searchParams.delete('sizes');
            if (studTypes.length) url.searchParams.set('stud_types', studTypes.join(','));
            else url.searchParams.delete('stud_types');

            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        });
    });
</script>
@endpush
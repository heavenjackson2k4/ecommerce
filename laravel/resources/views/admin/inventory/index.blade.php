@extends('layouts.admin')

@section('title', $type === 'shoe' ? 'Xem tồn kho giày' : 'Xem tồn kho quần áo')
@section('page-title', 'Dashboard')

@section('content')
<div
    class="mx-auto max-w-7xl"
    x-data="{ selectedProduct: null }"
    @keydown.escape.window="selectedProduct = null"
>
    <h2 class="mb-5 text-2xl font-bold text-gray-900">
        {{ $type === 'shoe' ? 'Danh sách giày' : 'Danh sách quần áo' }}
    </h2>

    <form method="GET" class="mb-6 flex justify-start">
        <label class="relative block w-full max-w-sm">
            <span class="sr-only">Tìm kiếm sản phẩm</span>
            <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
            </svg>
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Tìm kiếm sản phẩm..."
                class="w-full rounded-lg border border-gray-300 bg-white py-3 pl-12 pr-4 text-gray-900 outline-none transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900"
            >
        </label>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="border-b border-gray-200 bg-gray-50 text-sm text-gray-700">
                    <tr>
                        <th class="w-20 px-6 py-4 font-semibold">STT</th>
                        <th class="px-6 py-4 font-semibold">Tên sản phẩm</th>
                        <th class="w-1/3 px-6 py-4 font-semibold">Danh mục</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($products as $index => $product)
                        <tr
                            role="button"
                            tabindex="0"
                            class="cursor-pointer transition hover:bg-gray-50 focus:bg-gray-50 focus:outline-none"
                            @click="selectedProduct = {{ Js::from($product) }}"
                            @keydown.enter="selectedProduct = {{ Js::from($product) }}"
                        >
                            <td class="px-6 py-4 text-gray-500">{{ $products->firstItem() + $index }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $product['name'] }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $product['category'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                {{ request('search') ? 'Không tìm thấy sản phẩm phù hợp.' : 'Chưa có sản phẩm trong kho.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($products->hasPages())
        <div class="mt-5">{{ $products->links() }}</div>
    @endif

    <template x-teleport="body">
        <div
            x-show="selectedProduct"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @click.self="selectedProduct = null"
            role="dialog"
            aria-modal="true"
        >
            <div class="relative max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white shadow-xl" x-show="selectedProduct" x-transition>
                <button
                    type="button"
                    @click="selectedProduct = null"
                    class="absolute right-5 top-5 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-700 shadow hover:bg-gray-100"
                    aria-label="Đóng"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div class="h-72 bg-gray-100 sm:h-80">
                    <template x-if="selectedProduct?.image">
                        <img :src="selectedProduct.image" :alt="selectedProduct.name" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!selectedProduct?.image">
                        <div class="flex h-full items-center justify-center text-gray-400">
                            <svg class="h-14 w-14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m3 16 5-5 4 4 2-2 7 7M3 5h18v14H3V5Zm13 4h.01"/>
                            </svg>
                        </div>
                    </template>
                </div>

                <div class="p-7 sm:p-8">
                    <p class="text-sm uppercase tracking-wide text-gray-500" x-text="selectedProduct?.category"></p>
                    <h2 class="mt-1 text-2xl font-bold text-gray-900" x-text="selectedProduct?.name"></h2>
                    <h3 class="mb-4 mt-7 text-lg font-semibold text-gray-900">Chi tiết sản phẩm</h3>

                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="w-full text-left">
                            <thead class="border-b border-gray-200 bg-gray-50 text-sm text-gray-700">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Kích thước</th>
                                    <th class="px-5 py-3 font-semibold">Màu sắc</th>
                                    @if($type === 'shoe')
                                        <th class="px-5 py-3 font-semibold">Loại đế</th>
                                    @endif
                                    <th class="px-5 py-3 font-semibold">Tồn kho</th>
                                    <th class="px-5 py-3 font-semibold">Giá bán</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <template x-for="(variant, index) in selectedProduct?.variants || []" :key="index">
                                    <tr>
                                        <td class="px-5 py-3" x-text="variant.size"></td>
                                        <td class="px-5 py-3" x-text="variant.color"></td>
                                        @if($type === 'shoe')
                                            <td class="px-5 py-3" x-text="variant.stud_type || '—'"></td>
                                        @endif
                                        <td class="px-5 py-3">
                                            <span
                                                class="inline-flex min-w-10 justify-center rounded-full px-3 py-1 text-sm font-semibold"
                                                :class="variant.quantity > 30 ? 'bg-emerald-100 text-emerald-700' : (variant.quantity > 0 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700')"
                                                x-text="variant.quantity"
                                            ></span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3 font-semibold" x-text="new Intl.NumberFormat('vi-VN').format(variant.price) + ' VNĐ'"></td>
                                    </tr>
                                </template>
                                <template x-if="!selectedProduct?.variants?.length">
                                    <tr>
                                        <td colspan="5" class="px-5 py-8 text-center text-gray-500">Sản phẩm chưa có biến thể.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection

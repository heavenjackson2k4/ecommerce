@extends('layouts.admin')

@section('title', $type === 'shoe' ? 'Xem tồn kho giày' : 'Xem tồn kho quần áo')
@section('page-title', 'Dashboard')

@section('content')
<div
    class="mx-auto max-w-7xl"
    x-data="inventoryEditor(@js([
        'products' => $products->items(),
        'updateUrl' => $type === 'shoe'
            ? route('admin.inventory.shoes.update', ['id' => '__VARIANT__'])
            : route('admin.inventory.clothes.update', ['id' => '__VARIANT__']),
        'bulkUpdateUrl' => $type === 'shoe'
            ? route('admin.inventory.shoes.bulk-update', ['productId' => '__PRODUCT__'])
            : route('admin.inventory.clothes.bulk-update', ['productId' => '__PRODUCT__']),
    ]))"
    @keydown.escape.window="closeProduct()"
>
    <div
        x-cloak
        x-show="notification"
        x-transition
        class="fixed right-4 top-4 z-[60] flex min-w-72 items-center justify-between gap-4 rounded-lg px-4 py-3 text-white shadow-lg"
        :class="notification?.type === 'error' ? 'bg-red-500' : 'bg-green-500'"
        role="status"
        aria-live="polite"
    >
        <span class="text-sm font-medium" x-text="notification?.message"></span>
        <button type="button" @click="notification = null" class="text-white hover:text-gray-200" aria-label="Đóng thông báo">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

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
                            @click="openProduct({{ $product['id'] }})"
                            @keydown.enter="openProduct({{ $product['id'] }})"
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
            @click.self="closeProduct()"
            role="dialog"
            aria-modal="true"
        >
            <div class="relative max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-2xl bg-white shadow-xl" x-show="selectedProduct" x-transition>
                <button
                    type="button"
                    @click="closeProduct()"
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

                    <div class="mb-4 flex flex-col gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-3 text-sm text-gray-600">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.25 11.25 12 10.5m0 0 .75.75M12 10.5v4.5m9-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <p>Bạn có thể chỉnh sửa số lượng tồn kho và giá bán của từng biến thể.</p>
                        </div>
                        <button
                            x-show="selectedProduct?.variants?.length && !bulkEditing"
                            type="button"
                            @click="startBulkEditing()"
                            :disabled="saving"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-black disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 6.75h15m-15 5.25h15m-15 5.25h15M8.25 4.5v4.5m7.5 5.25v4.5" />
                            </svg>
                            Cập nhật nhiều biến thể
                        </button>
                    </div>

                    @include('admin.inventory.partials.bulk-editor')

                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="w-full text-left">
                            <thead class="border-b border-gray-200 bg-gray-50 text-sm text-gray-700">
                                <tr>
                                    <th x-show="bulkEditing" x-cloak class="w-14 px-5 py-3 font-semibold">
                                        <input
                                            type="checkbox"
                                            :checked="allBulkSelected()"
                                            @change="selectAllBulk($event.target.checked)"
                                            class="h-4 w-4 rounded border-gray-300 accent-black"
                                            aria-label="Chọn tất cả biến thể"
                                        >
                                    </th>
                                    <th class="px-5 py-3 font-semibold">Kích thước</th>
                                    <th class="px-5 py-3 font-semibold">Màu sắc</th>
                                    @if($type === 'shoe')
                                        <th class="px-5 py-3 font-semibold">Loại đế</th>
                                    @endif
                                    <th class="px-5 py-3 font-semibold">Tồn kho</th>
                                    <th class="px-5 py-3 font-semibold">Giá bán</th>
                                    <th x-show="!bulkEditing" class="px-5 py-3 text-center font-semibold">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <template x-for="variant in selectedProduct?.variants || []" :key="variant.id">
                                    <tr :class="bulkEditing && bulkItems[variant.id]?.selected ? 'bg-gray-50' : (editingVariantId === variant.id ? 'bg-gray-50' : '')">
                                        <td x-show="bulkEditing" x-cloak class="px-5 py-3">
                                            <input
                                                type="checkbox"
                                                x-model="bulkItems[variant.id].selected"
                                                class="h-4 w-4 rounded border-gray-300 accent-black"
                                                :aria-label="'Chọn biến thể ' + variant.size + ' ' + variant.color"
                                            >
                                        </td>
                                        <td class="px-5 py-3" x-text="variant.size"></td>
                                        <td class="px-5 py-3" x-text="variant.color"></td>
                                        @if($type === 'shoe')
                                            <td class="px-5 py-3" x-text="variant.stud_type || '—'"></td>
                                        @endif
                                        <td class="px-5 py-3">
                                            <template x-if="!bulkEditing && editingVariantId !== variant.id">
                                                <span
                                                    class="inline-flex min-w-10 justify-center rounded-full px-3 py-1 text-sm font-semibold"
                                                    :class="variant.quantity > 30 ? 'bg-emerald-100 text-emerald-700' : (variant.quantity > 0 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700')"
                                                    x-text="variant.quantity"
                                                ></span>
                                            </template>
                                            <template x-if="!bulkEditing && editingVariantId === variant.id">
                                                <input
                                                    type="number"
                                                    min="0"
                                                    step="1"
                                                    x-model.number="editForm.quantity"
                                                    class="w-24 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900"
                                                    aria-label="Số lượng tồn kho"
                                                >
                                            </template>
                                            <template x-if="bulkEditing">
                                                <div class="min-w-40">
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        step="1"
                                                        x-model.number="bulkItems[variant.id].quantity"
                                                        :disabled="!bulkItems[variant.id].selected || saving"
                                                        class="w-24 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400"
                                                        :aria-label="'Số lượng của biến thể ' + variant.size + ' ' + variant.color"
                                                    >
                                                    <p x-show="bulkItems[variant.id].selected" class="mt-1 text-xs text-gray-500">
                                                        <span x-text="variant.quantity"></span>
                                                        <span class="px-1">→</span>
                                                        <span class="font-semibold text-gray-900" x-text="bulkPreview(variant)"></span>
                                                    </p>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="px-5 py-3">
                                            <template x-if="bulkEditing || editingVariantId !== variant.id">
                                                <div class="whitespace-nowrap">
                                                    <p class="font-semibold" x-text="formatPrice(variant.price) + ' VNĐ'"></p>
                                                    <p x-show="bulkEditing && bulkItems[variant.id]?.selected && updatePrice" class="mt-1 text-xs text-gray-500">
                                                        <span>→</span>
                                                        <span class="font-semibold text-gray-900" x-text="bulkPricePreview(variant) === null ? '—' : formatPrice(bulkPricePreview(variant)) + ' VNĐ'"></span>
                                                    </p>
                                                </div>
                                            </template>
                                            <template x-if="!bulkEditing && editingVariantId === variant.id">
                                                <div class="min-w-44 space-y-2">
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        step="1000"
                                                        x-model.number="editForm.price"
                                                        :disabled="editForm.useBasePrice"
                                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500"
                                                        aria-label="Giá bán"
                                                    >
                                                    <label class="flex cursor-pointer items-center gap-2 text-xs text-gray-600">
                                                        <input type="checkbox" x-model="editForm.useBasePrice" class="h-4 w-4 accent-black">
                                                        <span>Dùng giá cơ bản</span>
                                                    </label>
                                                </div>
                                            </template>
                                        </td>
                                        <td x-show="!bulkEditing" class="px-5 py-3 text-center">
                                            <template x-if="!bulkEditing && editingVariantId !== variant.id">
                                                <button
                                                    type="button"
                                                    @click="startEditing(variant)"
                                                    :disabled="saving"
                                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-900 hover:text-black disabled:cursor-not-allowed disabled:opacity-50"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.862 3.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 15.07a4.5 4.5 0 0 1-1.897 1.13L6 17l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L18.55 1.8M16.862 3.487 19.5 6.125" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 13.5V19.125A1.875 1.875 0 0 1 17.625 21H4.875A1.875 1.875 0 0 1 3 19.125V6.375A1.875 1.875 0 0 1 4.875 4.5H10.5" />
                                                    </svg>
                                                    Chỉnh sửa
                                                </button>
                                            </template>
                                            <template x-if="!bulkEditing && editingVariantId === variant.id">
                                                <div class="flex items-center justify-center gap-2">
                                                    <button
                                                        type="button"
                                                        @click="saveVariant(variant)"
                                                        :disabled="saving"
                                                        class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-black disabled:cursor-not-allowed disabled:opacity-60"
                                                    >
                                                        <svg class="h-4 w-4" :class="saving ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path x-show="!saving" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m5 12 4 4L19 6" />
                                                            <path x-show="saving" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3a9 9 0 1 1-9 9" />
                                                        </svg>
                                                        <span x-text="saving ? 'Đang lưu' : 'Lưu'"></span>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="cancelEditing()"
                                                        :disabled="saving"
                                                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-600 transition hover:border-gray-900 hover:text-black disabled:cursor-not-allowed disabled:opacity-50"
                                                    >
                                                        Hủy
                                                    </button>
                                                </div>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="!selectedProduct?.variants?.length">
                                    <tr>
                                        <td colspan="{{ $type === 'shoe' ? 6 : 5 }}" class="px-5 py-8 text-center text-gray-500">Sản phẩm chưa có biến thể.</td>
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

@push('scripts')
    @include('admin.inventory.partials.inventory-script')
@endpush

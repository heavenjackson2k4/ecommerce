<div x-show="bulkEditing" x-cloak class="mb-4 overflow-hidden rounded-xl border border-gray-300 bg-gray-50">
    <div class="border-b border-gray-200 bg-white px-5 py-4">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-900 text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 6.75h15m-15 5.25h15m-15 5.25h15M8.25 4.5v4.5m7.5 5.25v4.5" />
                </svg>
            </span>
            <div>
                <h4 class="font-semibold text-gray-900">Cập nhật hàng loạt</h4>
                <p class="mt-0.5 text-xs text-gray-600">Chọn biến thể trong bảng và xem trước thay đổi trước khi lưu.</p>
            </div>
        </div>
    </div>

    <div class="grid gap-4 p-4 lg:grid-cols-2">
        <section class="rounded-xl border border-gray-300 bg-white p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5 12 3 3.75 7.5M20.25 7.5 12 12m8.25-4.5V16.5L12 21m0-9L3.75 7.5M12 12v9m-8.25-13.5V16.5L12 21" />
                    </svg>
                </span>
                <div>
                    <h5 class="text-sm font-semibold text-gray-900">Tồn kho</h5>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 rounded-lg border border-gray-300 bg-gray-100 p-1">
                <button
                    type="button"
                    @click="changeBulkMode('increment')"
                    class="rounded-md px-3 py-2 text-sm font-medium transition"
                    :class="bulkMode === 'increment' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                >
                    Nhập thêm
                </button>
                <button
                    type="button"
                    @click="changeBulkMode('set')"
                    class="rounded-md px-3 py-2 text-sm font-medium transition"
                    :class="bulkMode === 'set' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                >
                    Đặt tồn kho
                </button>
            </div>
            <p class="mt-2 min-h-8 text-xs leading-5 text-gray-600" x-text="bulkMode === 'increment' ? 'Cộng số lượng nhập vào tồn kho hiện tại.' : 'Thay thế tồn kho hiện tại bằng số lượng mới.'"></p>

            <div class="mt-3">
                <label for="bulk-quantity" class="mb-1 block text-xs font-medium text-gray-600">Số lượng chung</label>
                <div class="flex items-stretch gap-2">
                    <input
                        id="bulk-quantity"
                        type="number"
                        min="0"
                        step="1"
                        x-model.number="commonQuantity"
                        class="min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900"
                    >
                    <button
                        type="button"
                        @click="applyCommonQuantity()"
                        :disabled="selectedBulkCount() === 0"
                        class="shrink-0 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-900 hover:text-black disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Áp dụng
                    </button>
                </div>
            </div>
        </section>

        <section class="rounded-xl border bg-white p-4 transition" :class="updatePrice ? 'border-gray-400' : 'border-gray-300'">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m3-9.75H10.5a2.25 2.25 0 0 0 0 4.5h3a2.25 2.25 0 0 1 0 4.5H9M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                    <div>
                        <h5 class="text-sm font-semibold text-gray-900">Giá bán</h5>
                    </div>
                </div>
                <button
                    type="button"
                    role="switch"
                    :aria-checked="updatePrice"
                    @click="updatePrice = !updatePrice"
                    class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition"
                    :class="updatePrice ? 'bg-gray-900' : 'bg-gray-300'"
                    aria-label="Bật hoặc tắt điều chỉnh giá"
                >
                    <span class="mt-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition" :class="updatePrice ? 'translate-x-5' : 'translate-x-0.5'"></span>
                </button>
            </div>

            <div x-show="updatePrice" x-cloak class="mt-4 space-y-3">
                <div>
                    <label for="bulk-price-mode" class="mb-1 block text-xs font-medium text-gray-600">Cách điều chỉnh</label>
                    <select
                        id="bulk-price-mode"
                        x-model="priceMode"
                        @change="priceValue = ''"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900"
                    >
                        <option value="set">Đặt giá mới</option>
                        <option value="increase_amount">Tăng theo số tiền</option>
                        <option value="decrease_amount">Giảm theo số tiền</option>
                        <option value="increase_percent">Tăng theo phần trăm</option>
                        <option value="decrease_percent">Giảm theo phần trăm</option>
                        <option value="base">Dùng giá cơ bản</option>
                    </select>
                </div>
                <div x-show="priceMode !== 'base'">
                    <label for="bulk-price-value" class="mb-1 block text-xs font-medium text-gray-600" x-text="priceMode.includes('percent') ? 'Mức điều chỉnh (%)' : 'Số tiền (VNĐ)'"></label>
                    <input
                        id="bulk-price-value"
                        type="number"
                        min="0"
                        :step="priceMode.includes('percent') ? '0.1' : '1000'"
                        x-model.number="priceValue"
                        :placeholder="priceMode.includes('percent') ? 'Ví dụ: 10' : 'Ví dụ: 50000'"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900"
                    >
                </div>
                <p class="text-xs leading-5 text-gray-600" x-text="priceMode === 'base' ? 'Xóa giá riêng và sử dụng giá cơ bản của sản phẩm.' : 'Giá mới được lưu riêng cho từng biến thể đã chọn.'"></p>
            </div>

            <div x-show="!updatePrice" class="mt-4 flex min-h-32 flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gray-100 px-4 text-center text-xs leading-5 text-gray-600">
                <svg class="h-9 w-10 text-gray-500" fill="currentColor" viewBox="0 0 576 512" aria-hidden="true">
                    <path d="M401.2 39.1 549.4 189.4c27.7 28.1 27.7 73.1 0 101.2L393 448.9c-9.3 9.4-24.5 9.5-33.9.2s-9.5-24.5-.2-33.9L515.3 256.8c9.2-9.3 9.2-24.4 0-33.7L367 72.9c-9.3-9.4-9.2-24.6.2-33.9s24.6-9.2 33.9.2ZM32.1 229.5V96c0-35.3 28.7-64 64-64h133.5c17 0 33.3 6.7 45.3 18.7l144 144c25 25 25 65.5 0 90.5L285.4 418.7c-25 25-65.5 25-90.5 0l-144-144c-12-12-18.7-28.3-18.7-45.3ZM176.1 144a32 32 0 1 0-64 0 32 32 0 1 0 64 0Z" />
                </svg>
                <span>Bật để điều chỉnh giá bán cho các biến thể đã chọn.</span>
            </div>
        </section>
    </div>

    <div class="flex flex-col gap-3 border-t border-gray-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="flex items-center gap-1 text-sm font-semibold text-gray-700">
            <span x-text="selectedBulkCount()"></span>
            <span>/</span>
            <span x-text="selectedProduct?.variants?.length || 0"></span>
            <span>biến thể đã chọn</span>
        </p>
        <div class="flex items-center justify-end gap-2">
            <button
                type="button"
                @click="cancelBulkEditing()"
                :disabled="saving"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 transition hover:border-gray-900 hover:text-black disabled:cursor-not-allowed disabled:opacity-50"
            >
                Hủy
            </button>
            <button
                type="button"
                @click="saveBulkInventory()"
                :disabled="saving || selectedBulkCount() === 0"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-black disabled:cursor-not-allowed disabled:opacity-60"
            >
                <svg class="h-4 w-4" :class="saving ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path x-show="!saving" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m5 12 4 4L19 6" />
                    <path x-show="saving" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3a9 9 0 1 1-9 9" />
                </svg>
                <span x-text="saving ? 'Đang lưu' : 'Lưu tất cả'"></span>
            </button>
        </div>
    </div>
</div>

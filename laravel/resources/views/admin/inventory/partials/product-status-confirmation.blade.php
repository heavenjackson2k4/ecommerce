<template x-teleport="body">
    <div
        x-cloak
        x-show="pendingProductStatusChange"
        x-transition.opacity
        x-effect="if (pendingProductStatusChange) $nextTick(() => $refs.productStatusConfirmAction.focus())"
        @click.self="cancelProductStatusChange()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="inventory-product-status-confirmation-title"
        aria-describedby="inventory-product-status-confirmation-message"
    >
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h2 id="inventory-product-status-confirmation-title" class="text-lg font-semibold text-gray-900">Chuyển sản phẩm sang archived?</h2>
            <p id="inventory-product-status-confirmation-message" class="mt-2 text-sm leading-6 text-gray-600">
                Sản phẩm <span class="font-medium text-gray-900" x-text="products[pendingProductStatusChange?.productId]?.name || ''"></span>
                sẽ được ẩn khỏi trang bán hàng. Ảnh, biến thể và số lượng tồn kho vẫn được giữ nguyên. Bạn có thể chuyển lại sang active sau.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    @click="cancelProductStatusChange()"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    Hủy
                </button>
                <button
                    type="button"
                    x-ref="productStatusConfirmAction"
                    @click="confirmProductStatusChange()"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-black"
                >
                    Xác nhận
                </button>
            </div>
        </div>
    </div>
</template>

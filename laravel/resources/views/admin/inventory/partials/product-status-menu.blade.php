<template x-teleport="body">
    <div
        id="inventory-product-status-menu"
        x-ref="productStatusMenu"
        x-cloak
        x-show="productStatusMenuProductId !== null"
        x-transition.opacity
        :style="{ top: productStatusMenuPosition.top + 'px', left: productStatusMenuPosition.left + 'px', width: productStatusMenuPosition.width + 'px' }"
        @click.stop
        @click.outside="closeProductStatusMenu(false)"
        @keydown.arrow-down.prevent="focusProductStatusOption(1)"
        @keydown.arrow-up.prevent="focusProductStatusOption(-1)"
        @keydown.tab="closeProductStatusMenu()"
        class="fixed z-[55] space-y-1 rounded-xl border border-gray-400 bg-white p-1.5 shadow-lg"
        role="listbox"
        :aria-labelledby="productStatusMenuProductId !== null ? 'product-status-' + productStatusMenuProductId : null"
    >
        @foreach($productStatuses as $status => $label)
            <button
                type="button"
                role="option"
                data-status="{{ $status }}"
                :aria-selected="products[productStatusMenuProductId]?.status === '{{ $status }}'"
                @click="chooseProductStatus('{{ $status }}')"
                :class="products[productStatusMenuProductId]?.status === '{{ $status }}' ? 'border-gray-400 bg-gray-100' : 'border-transparent'"
                @class([
                    'flex w-full items-center justify-between gap-3 rounded-lg border px-3 py-2 text-left text-sm font-semibold outline-none transition hover:border-gray-400 hover:bg-gray-50 focus:border-gray-900 focus:bg-gray-100',
                    'text-gray-800' => $status === 'draft',
                    'text-emerald-800' => $status === 'active',
                    'text-amber-800' => $status === 'archived',
                ])
            >
                <span>{{ $label }}</span>
                <svg class="h-4 w-4 shrink-0" :class="products[productStatusMenuProductId]?.status === '{{ $status }}' ? '' : 'invisible'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6" />
                </svg>
            </button>
        @endforeach
    </div>
</template>

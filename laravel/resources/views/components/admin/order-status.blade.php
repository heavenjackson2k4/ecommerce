@props([
    'order',
    'editable' => false,
    'size' => 'sm',
])

@php
    $statuses = [
        'pending' => [
            'label' => 'Chờ xác nhận',
            'classes' => 'border-orange-200 bg-orange-50 text-orange-700',
        ],
        'processing' => [
            'label' => 'Đang xử lý',
            'classes' => 'border-blue-200 bg-blue-50 text-blue-700',
        ],
        'shipped' => [
            'label' => 'Đã giao hàng',
            'classes' => 'border-gray-300 bg-gray-100 text-gray-700',
        ],
        'delivered' => [
            'label' => 'Đã nhận hàng',
            'classes' => 'border-green-200 bg-green-50 text-green-700',
        ],
        'cancelled' => [
            'label' => 'Đã hủy',
            'classes' => 'border-red-200 bg-red-50 text-red-700',
        ],
    ];

    $currentStatus = $statuses[$order->status] ?? [
        'label' => $order->status_label,
        'classes' => 'border-gray-200 bg-gray-50 text-gray-700',
    ];

    $sizeClasses = $size === 'md' ? 'px-3 py-1.5 text-sm' : 'px-2.5 py-1 text-xs';
@endphp

@if($editable)
    <div
        x-data="{ open: false }"
        @click.outside="open = false"
        @keydown.escape.window="open = false"
        class="relative inline-block"
    >
        <form x-ref="statusForm" action="{{ route('admin.orders.update-status', $order->id) }}" method="POST">
            @csrf
            <input x-ref="statusInput" type="hidden" name="status" value="{{ $order->status }}">
            <button
                type="button"
                @click="open = !open"
                :aria-expanded="open.toString()"
                aria-haspopup="listbox"
                aria-controls="order-status-options-{{ $order->id }}"
                class="inline-flex min-w-40 items-center justify-between gap-3 rounded-lg border px-3 py-1.5 text-sm font-medium outline-none transition focus:ring-2 focus:ring-gray-900 focus:ring-offset-1 {{ $currentStatus['classes'] }}"
            >
                <span>{{ $currentStatus['label'] }}</span>
                <svg class="h-4 w-4 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m6 9 6 6 6-6" />
                </svg>
            </button>

            <div
                x-cloak
                x-show="open"
                x-transition.origin.top
                id="order-status-options-{{ $order->id }}"
                role="listbox"
                class="absolute right-0 z-20 mt-2 w-48 space-y-1 rounded-xl border border-gray-200 bg-white p-2 shadow-lg"
            >
                @foreach($statuses as $value => $status)
                    <button
                        type="button"
                        role="option"
                        aria-selected="{{ $order->status === $value ? 'true' : 'false' }}"
                        @click="$refs.statusInput.value = '{{ $value }}'; $refs.statusForm.submit()"
                        class="flex w-full items-center justify-between rounded-lg border px-3 py-2 text-left text-sm font-medium transition hover:brightness-95 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-gray-900 {{ $status['classes'] }}"
                    >
                        <span>{{ $status['label'] }}</span>
                        @if($order->status === $value)
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6" />
                            </svg>
                        @endif
                    </button>
                @endforeach
            </div>
        </form>
    </div>
@else
    <span {{ $attributes->class("inline-flex items-center rounded-full border font-medium {$currentStatus['classes']} {$sizeClasses}") }}>
        {{ $order->status_label }}
    </span>
@endif

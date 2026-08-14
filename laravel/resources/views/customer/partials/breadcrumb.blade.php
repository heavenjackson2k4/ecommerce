@props(['items' => []])

<nav class="bg-gray-50 border-b border-gray-200 py-2 sm:py-3">
    <div class="container mx-auto px-4 sm:px-6">
        <ol class="flex flex-wrap items-center space-x-2 text-xs sm:text-sm text-gray-600">
            <li>
                <a href="/" class="hover:text-black transition">Trang chủ</a>
            </li>
            @foreach($items as $item)
                <li>
                    <svg class="h-3 w-3 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                <li>
                    @if($loop->last)
                        <span class="text-gray-800 font-medium">{{ $item['label'] }}</span>
                    @else
                        <a href="{{ $item['url'] }}" class="hover:text-black transition">{{ $item['label'] }}</a>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</nav>
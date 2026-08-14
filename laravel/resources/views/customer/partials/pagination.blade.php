@if ($paginator->hasPages())
    <nav class="flex flex-wrap items-center justify-center gap-1 sm:gap-2 mt-6">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-2 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed text-sm">‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm">‹</a>
        @endif

        {{-- Elements --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-3 py-2 text-gray-400 text-sm">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-2 bg-black text-white rounded-lg text-sm">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm">›</a>
        @else
            <span class="px-3 py-2 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed text-sm">›</span>
        @endif
    </nav>
@endif
<header class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
    <div>
        <h1 class="text-xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
    </div>
    <div class="flex items-center space-x-4">
        <span class="text-sm text-gray-600">{{ Auth::user()->name }}</span>
        <form action="{{ route('logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="text-sm text-gray-600 hover:text-gray-800">Đăng xuất</button>
        </form>
    </div>
</header>
<aside class="w-64 bg-white border-r border-gray-200 flex-shrink-0 overflow-y-auto">
    <div class="p-4 border-b border-gray-200">
        <a href="/" class="text-xl font-bold text-gray-800">LOGO</a>
    </div>
    <nav class="p-4 space-y-2" x-data="{
        openProduct: {{ request()->routeIs('admin.nhap-*') ? 'true' : 'false' }},
        openInventory: {{ request()->routeIs('admin.inventory.*') ? 'true' : 'false' }}
    }">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100' : '' }}">
            <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Trang chủ</span>
        </a>

        <!-- Nhập sản phẩm (có sub) -->
        <div>
            <button type="button" @click="openProduct = !openProduct" class="flex items-center justify-between w-full px-4 py-2 rounded-lg hover:bg-gray-100">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Nhập sản phẩm</span>
                </div>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="openProduct ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div x-show="openProduct" x-cloak class="ml-6 mt-1 space-y-1">
                <a href="{{ route('admin.nhap-quan-ao') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.nhap-quan-ao') ? 'bg-gray-100' : '' }}">
                    Nhập quần áo
                </a>
                <a href="{{ route('admin.nhap-giay') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.nhap-giay') ? 'bg-gray-100' : '' }}">
                    Nhập giày
                </a>
            </div>
        </div>

        <div>
            <button type="button" @click="openInventory = !openInventory" class="flex items-center justify-between w-full px-4 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.inventory.*') ? 'bg-gray-100' : '' }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14l-1 12H6L5 8Zm-1-4h16v4H4V4Zm6 8h4"/>
                    </svg>
                    <span>Xem tồn kho</span>
                </div>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="openInventory ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div x-show="openInventory" x-cloak class="ml-6 mt-1 space-y-1">
                <a href="{{ route('admin.inventory.clothes') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.inventory.clothes') ? 'bg-gray-100 font-semibold' : '' }}">
                    Xem quần áo
                </a>
                <a href="{{ route('admin.inventory.shoes') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.inventory.shoes') ? 'bg-gray-100 font-semibold' : '' }}">
                    Xem giày
                </a>
            </div>
        </div>

        <a href="{{ route('admin.orders.index') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.orders.*') ? 'bg-gray-100 font-semibold' : '' }}">
            <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <span>Đơn hàng</span>
        </a>

        <!-- Add other menu items later -->
    </nav>
</aside>

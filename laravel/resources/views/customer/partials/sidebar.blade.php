@props(['categories' => [], 'selectedCategory' => null])

<aside class="bg-white rounded-lg shadow-sm p-4 space-y-6">
    <!-- Categories -->
    <div>
        <h3 class="font-semibold text-gray-900 mb-3 text-sm sm:text-base">Danh mục</h3>
        <ul class="space-y-2 text-sm">
            <li>
                <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.index') && !request('category') ? 'text-black font-medium' : 'text-gray-600 hover:text-black' }}">
                    Tất cả sản phẩm
                </a>
            </li>
            @foreach($categories as $category)
                <li>
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
                       class="{{ request('category') == $category->slug ? 'text-black font-medium' : 'text-gray-600 hover:text-black' }}">
                        {{ $category->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Price Range -->
    <div>
        <h3 class="font-semibold text-gray-900 mb-3 text-sm sm:text-base">Khoảng giá</h3>
        <div class="space-y-2 text-sm">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="radio" name="price_range" value="" class="text-black" checked>
                <span class="text-gray-600">Tất cả</span>
            </label>
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="radio" name="price_range" value="0-500000" class="text-black">
                <span class="text-gray-600">Dưới 500.000 ₫</span>
            </label>
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="radio" name="price_range" value="500000-1000000" class="text-black">
                <span class="text-gray-600">500.000 - 1.000.000 ₫</span>
            </label>
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="radio" name="price_range" value="1000000-2000000" class="text-black">
                <span class="text-gray-600">1.000.000 - 2.000.000 ₫</span>
            </label>
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="radio" name="price_range" value="2000000+" class="text-black">
                <span class="text-gray-600">Trên 2.000.000 ₫</span>
            </label>
        </div>
    </div>

    <!-- Size -->
    <div>
        <h3 class="font-semibold text-gray-900 mb-3 text-sm sm:text-base">Size</h3>
        <div class="grid grid-cols-4 gap-2 text-sm">
            @foreach(['39','40','41','42','43','44','45'] as $size)
                <label class="flex items-center justify-center border border-gray-300 rounded-lg py-1.5 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="sizes[]" value="{{ $size }}" class="hidden peer">
                    <span class="peer-checked:text-black text-gray-600">{{ $size }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <!-- Stud Type -->
    <div>
        <h3 class="font-semibold text-gray-900 mb-3 text-sm sm:text-base">Loại đinh</h3>
        <div class="grid grid-cols-3 gap-2 text-sm">
            @foreach(['TF','FG','AG','IC','SG'] as $type)
                <label class="flex items-center justify-center border border-gray-300 rounded-lg py-1.5 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="stud_types[]" value="{{ $type }}" class="hidden peer">
                    <span class="peer-checked:text-black text-gray-600">{{ $type }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <button id="apply-filters" class="w-full bg-black text-white py-2 rounded-lg hover:bg-gray-800 transition text-sm">
        Áp dụng bộ lọc
    </button>
</aside>
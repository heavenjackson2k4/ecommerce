@extends('layouts.admin')

@section('title', 'Nhập quần áo')
@section('page-title', 'Nhập quần áo mới')
@section('content')
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <form id="form-cloth" method="POST" action="{{ route('admin.clothes.store') }}" class="space-y-4">
            @csrf
            <!-- Các trường thông tin chung -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Danh mục</label>
                    <select id="category_id" name="category_id" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200">
                        @foreach(\App\Models\Category::all() as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Tên sản phẩm</label>
                    <input type="text" id="name" name="name" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200" required>
                </div>
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" id="slug" name="slug" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200" required>
                </div>
                <div>
                    <label for="base_price" class="block text-sm font-medium text-gray-700">Giá cơ bản (VNĐ)</label>
                    <input type="number" id="base_price" name="base_price" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200" required>
                </div>
                <div>
                    <label for="sleeve_type" class="block text-sm font-medium text-gray-700">Loại tay</label>
                    <select id="sleeve_type" name="sleeve_type" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200">
                        <option value="SHORT">Tay ngắn</option>
                        <option value="LONG">Tay dài</option>
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Trạng thái</label>
                    <select id="status" name="status" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200">
                        <option value="active">Hoạt động</option>
                        <option value="draft">Nháp</option>
                        <option value="archived">Lưu trữ</option>
                    </select>
                </div>
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Mô tả</label>
                <textarea id="description" name="description" rows="4" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200"></textarea>
            </div>

            <hr class="my-6">

            <!-- Variants -->
            <div>
                <h3 class="text-lg font-medium text-gray-800">Biến thể (size, màu, tồn kho)</h3>
                <div id="variants-container">
                    <div class="variant-item grid grid-cols-5 gap-4 items-end mt-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">SKU</label>
                            <input type="text" name="variants[0][sku]" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Màu</label>
                            <input type="text" name="variants[0][color]" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Size</label>
                            <input type="text" name="variants[0][size]" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Tồn kho</label>
                            <input type="number" name="variants[0][stock_quantity]" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200" value="0">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Giá riêng</label>
                            <input type="number" name="variants[0][price_override]" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200">
                        </div>
                    </div>
                </div>
                <button type="button" id="add-variant" class="mt-2 inline-flex items-center px-3 py-1 border border-gray-300 text-sm rounded-lg hover:bg-gray-50">+ Thêm biến thể</button>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900">Lưu sản phẩm</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // JS để thêm dòng variant
        let variantIndex = 1;
        document.getElementById('add-variant').addEventListener('click', function() {
            const container = document.getElementById('variants-container');
            const newRow = document.createElement('div');
            newRow.className = 'variant-item grid grid-cols-5 gap-4 items-end mt-4';
            newRow.innerHTML = `
                <div><label class="block text-xs font-medium text-gray-500">SKU</label><input type="text" name="variants[${variantIndex}][sku]" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200" required></div>
                <div><label class="block text-xs font-medium text-gray-500">Màu</label><input type="text" name="variants[${variantIndex}][color]" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200" required></div>
                <div><label class="block text-xs font-medium text-gray-500">Size</label><input type="text" name="variants[${variantIndex}][size]" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200" required></div>
                <div><label class="block text-xs font-medium text-gray-500">Tồn kho</label><input type="number" name="variants[${variantIndex}][stock_quantity]" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200" value="0"></div>
                <div><label class="block text-xs font-medium text-gray-500">Giá riêng</label><input type="number" name="variants[${variantIndex}][price_override]" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-gray-200"></div>
            `;
            container.appendChild(newRow);
            variantIndex++;
        });
    </script>
    @endpush
@endsection
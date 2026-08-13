@extends('layouts.admin')

@section('title', 'Nhập giày')

@section('content')
<div class="max-w-5xl mx-auto py-6">
    <h2 class="text-2xl font-bold mb-6">Nhập giày mới</h2>

    {{-- Hiển thị lỗi chung --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.shoes.store') }}" id="shoe-form" class="bg-white p-6 rounded shadow">
        @csrf

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Danh mục <span class="text-red-500">*</span></label>
                <select name="category_id" class="w-full border rounded px-3 py-2 @error('category_id') border-red-500 @enderror" required>
                    <option value="">-- Chọn danh mục --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tên sản phẩm <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Slug <span class="text-red-500">*</span></label>
                <input type="text" name="slug" value="{{ old('slug') }}" class="w-full border rounded px-3 py-2 @error('slug') border-red-500 @enderror" required>
                @error('slug')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Giá cơ bản (VNĐ) <span class="text-red-500">*</span></label>
                <input type="number" step="1000" name="base_price" value="{{ old('base_price') }}" class="w-full border rounded px-3 py-2 @error('base_price') border-red-500 @enderror" required>
                @error('base_price')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Silo (dòng giày)</label>
                <input type="text" name="silo" value="{{ old('silo') }}" class="w-full border rounded px-3 py-2" placeholder="VD: Mercurial, Predator">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Trạng thái</label>
                <select name="status" class="w-full border rounded px-3 py-2">
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
        </div>

        {{-- Phần tạo biến thể tự động --}}
        <div class="border-t pt-4 mt-4">
            <h3 class="text-lg font-semibold mb-3">Tạo biến thể tự động</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Sizes (cách nhau dấu phẩy)</label>
                    <input type="text" id="sizes-input" class="w-full border rounded px-3 py-2" placeholder="31,32,33">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Màu sắc (cách nhau dấu phẩy)</label>
                    <input type="text" id="colors-input" class="w-full border rounded px-3 py-2" placeholder="Đỏ,Đen,Xanh">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Loại đinh (cách nhau dấu phẩy)</label>
                    <input type="text" id="stud-types-input" class="w-full border rounded px-3 py-2" placeholder="TF,IC,AG">
                </div>
            </div>
            <button type="button" id="generate-variants-btn" class="mt-3 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Tạo biến thể
            </button>
            <p class="text-sm text-slate-500 mt-2">* Nhập các thuộc tính và bấm "Tạo biến thể" để tạo danh sách tổ hợp. Sau đó nhập số lượng và giá bán cho từng dòng.</p>
        </div>

        {{-- Bảng biến thể thủ công --}}
        <div class="border-t pt-4 mt-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-semibold">Danh sách biến thể</h3>
                <button type="button" id="add-variant-btn" class="bg-black text-white px-4 py-1 rounded text-sm">+ Thêm dòng</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border text-sm" id="variant-table">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="border p-2">Size</th>
                            <th class="border p-2">Màu</th>
                            <th class="border p-2">Loại đinh</th>
                            <th class="border p-2">Số lượng</th>
                            <th class="border p-2">Giá bán (VNĐ)</th>
                            <th class="border p-2">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="variant-body">
                        {{-- Các dòng sẽ được thêm bằng JS --}}
                    </tbody>
                </table>
            </div>
            @error('variants')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:bg-slate-800">Lưu giày</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowCount = 0;
    const tbody = document.getElementById('variant-body');
    const addBtn = document.getElementById('add-variant-btn');
    const generateBtn = document.getElementById('generate-variants-btn');
    const sizesInput = document.getElementById('sizes-input');
    const colorsInput = document.getElementById('colors-input');
    const studTypesInput = document.getElementById('stud-types-input');

    // Hàm thêm một dòng mới
    function addRow(size = '', color = '', studType = '', quantity = '', price = '') {
        const row = document.createElement('tr');
        row.className = 'variant-row';
        row.innerHTML = `
            <td class="border p-1">
                <input type="text" name="variants[${rowCount}][size]" value="${size}" class="w-full border-none px-1" placeholder="Size">
            </td>
            <td class="border p-1">
                <input type="text" name="variants[${rowCount}][color]" value="${color}" class="w-full border-none px-1" placeholder="Màu">
            </td>
            <td class="border p-1">
                <select name="variants[${rowCount}][stud_type]" class="w-full border-none px-1">
                    <option value="TF" ${studType === 'TF' ? 'selected' : ''}>TF</option>
                    <option value="FG" ${studType === 'FG' ? 'selected' : ''}>FG</option>
                    <option value="AG" ${studType === 'AG' ? 'selected' : ''}>AG</option>
                    <option value="IC" ${studType === 'IC' ? 'selected' : ''}>IC</option>
                    <option value="SG" ${studType === 'SG' ? 'selected' : ''}>SG</option>
                </select>
            </td>
            <td class="border p-1">
                <input type="number" name="variants[${rowCount}][quantity]" value="${quantity}" class="w-full border-none px-1" placeholder="0" min="0">
            </td>
            <td class="border p-1">
                <input type="number" step="1000" name="variants[${rowCount}][price_override]" value="${price}" class="w-full border-none px-1" placeholder="Giá bán">
            </td>
            <td class="border p-1 text-center">
                <button type="button" class="remove-variant text-red-500 hover:text-red-700">✕</button>
            </td>
        `;
        row.querySelector('.remove-variant').addEventListener('click', function() {
            row.remove();
        });
        tbody.appendChild(row);
        rowCount++;
    }

    // Thêm một dòng trống mặc định
    addRow();

    // Sự kiện thêm dòng
    addBtn.addEventListener('click', function() {
        addRow();
    });

    // Sự kiện tạo biến thể
    generateBtn.addEventListener('click', function() {
        const sizes = sizesInput.value.split(',').map(s => s.trim()).filter(Boolean);
        const colors = colorsInput.value.split(',').map(c => c.trim()).filter(Boolean);
        const studTypes = studTypesInput.value.split(',').map(st => st.trim()).filter(Boolean);

        if (sizes.length === 0 || colors.length === 0 || studTypes.length === 0) {
            alert('Vui lòng nhập đầy đủ Size, Màu và Loại đinh (cách nhau dấu phẩy).');
            return;
        }

        // Xóa tất cả dòng hiện tại
        tbody.innerHTML = '';

        // Tạo tổ hợp Descartes
        sizes.forEach(size => {
            colors.forEach(color => {
                studTypes.forEach(studType => {
                    addRow(size, color, studType, '', '');
                });
            });
        });
    });
});
</script>
@endsection
{{-- Toast sẽ hiển thị ở đây --}}
@include('admin.partials.toast')
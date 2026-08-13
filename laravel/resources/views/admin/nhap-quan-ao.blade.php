@extends('layouts.admin')

@section('title', 'Nhập quần áo mới')

@section('content')
<div class="max-w-6xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Nhập quần áo mới</h2>

    <form method="POST" action="{{ route('admin.clothes.store') }}" id="cloth-form" class="bg-white p-6 rounded-lg shadow">
        @csrf

        <!-- Thông tin chung -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium mb-1">Danh mục <span class="text-red-500">*</span></label>
                <select name="category_id" class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-black" required>
                    <option value="">-- Chọn danh mục --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tên sản phẩm <span class="text-red-500">*</span></label>
                <input type="text" name="name" class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-black" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Slug <span class="text-red-500">*</span></label>
                <input type="text" name="slug" class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-black" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Giá cơ bản (VNĐ) <span class="text-red-500">*</span></label>
                <input type="number" step="1000" name="base_price" class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-black" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Kiểu tay <span class="text-red-500">*</span></label>
                <select name="sleeve_type" class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-black" required>
                    <option value="SHORT">Tay ngắn</option>
                    <option value="LONG">Tay dài</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Trạng thái</label>
                <select name="status" class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-black">
                    <option value="active">Active</option>
                    <option value="draft">Draft</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
        </div>

        <!-- Tạo biến thể tự động -->
        <div class="border-t border-slate-200 pt-4 mt-4">
            <h3 class="text-lg font-semibold mb-3">Tạo biến thể tự động</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Sizes <span class="text-red-500">*</span></label>
                    <input type="text" id="cloth-sizes" class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-black" placeholder="VD: S,M,L,XL">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Màu sắc <span class="text-red-500">*</span></label>
                    <input type="text" id="cloth-colors" class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-black" placeholder="VD: Trắng,Xanh,Đen">
                </div>
            </div>
            <div class="mt-3 flex gap-3">
                <button type="button" id="generate-variants-btn" class="bg-black text-white px-4 py-2 rounded hover:bg-slate-800 transition">Tạo biến thể</button>
                <button type="button" id="clear-variants-btn" class="border border-slate-300 text-slate-700 px-4 py-2 rounded hover:bg-slate-100 transition">Xóa tất cả</button>
            </div>
            <p class="text-sm text-slate-500 mt-2">* Nhập các giá trị cách nhau bằng dấu phẩy, sau đó bấm "Tạo biến thể" để tạo tất cả tổ hợp.</p>
        </div>

        <!-- Bảng biến thể -->
        <div class="border-t border-slate-200 pt-4 mt-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-semibold">Danh sách biến thể</h3>
                <button type="button" id="add-row-btn" class="bg-black text-white px-4 py-1 rounded text-sm hover:bg-slate-800 transition">+ Thêm dòng thủ công</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border text-sm">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="border p-2 text-left">Size</th>
                            <th class="border p-2 text-left">Màu</th>
                            <th class="border p-2 text-left">Số lượng</th>
                            <th class="border p-2 text-left">Giá bán (VNĐ)</th>
                            <th class="border p-2 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="variant-table">
                        <!-- Các dòng sẽ được thêm vào đây -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:bg-slate-800 transition">Lưu quần áo</button>
            <a href="{{ route('admin.dashboard') }}" class="bg-slate-200 text-slate-700 px-6 py-2 rounded hover:bg-slate-300 transition">Hủy</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowCount = 0;
    const tableBody = document.getElementById('variant-table');
    const generateBtn = document.getElementById('generate-variants-btn');
    const clearBtn = document.getElementById('clear-variants-btn');
    const addRowBtn = document.getElementById('add-row-btn');

    // Thêm một dòng mới vào bảng
    function addRow(size = '', color = '', quantity = '', price = '') {
        const row = document.createElement('tr');
        row.dataset.index = rowCount;
        row.innerHTML = `
            <td class="border p-1">
                <input type="text" name="variants[${rowCount}][size]" value="${size}" class="w-full border-none focus:ring-0" placeholder="M">
            </td>
            <td class="border p-1">
                <input type="text" name="variants[${rowCount}][color]" value="${color}" class="w-full border-none focus:ring-0" placeholder="Trắng">
            </td>
            <td class="border p-1">
                <input type="number" name="variants[${rowCount}][quantity]" value="${quantity}" class="w-full border-none focus:ring-0" placeholder="0" min="0">
            </td>
            <td class="border p-1">
                <input type="number" step="1000" name="variants[${rowCount}][price_override]" value="${price}" class="w-full border-none focus:ring-0" placeholder="Giá bán">
            </td>
            <td class="border p-1 text-center">
                <button type="button" class="remove-row text-red-500 hover:text-red-700 transition" title="Xóa dòng">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </td>
        `;
        row.querySelector('.remove-row').addEventListener('click', function() {
            row.remove();
            reindexRows();
        });
        tableBody.appendChild(row);
        rowCount++;
        reindexRows();
    }

    // Cập nhật lại chỉ số index cho các dòng
    function reindexRows() {
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach((row, index) => {
            row.dataset.index = index;
            const inputs = row.querySelectorAll('input, select');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/\[.*?\]/, `[${index}]`);
                    input.setAttribute('name', newName);
                }
            });
        });
        rowCount = rows.length;
    }

    // Tạo biến thể từ các mảng
    function generateVariants() {
        const sizesInput = document.getElementById('cloth-sizes');
        const colorsInput = document.getElementById('cloth-colors');

        const sizes = sizesInput.value.split(',').map(s => s.trim()).filter(s => s);
        const colors = colorsInput.value.split(',').map(c => c.trim()).filter(c => c);

        if (!sizes.length || !colors.length) {
            alert('Vui lòng nhập đầy đủ các trường Size và Màu sắc (cách nhau bằng dấu phẩy).');
            return;
        }

        // Xóa các dòng hiện tại
        tableBody.innerHTML = '';
        rowCount = 0;

        // Tạo tổ hợp Descartes
        for (const size of sizes) {
            for (const color of colors) {
                addRow(size, color, '', '');
            }
        }
    }

    // Xóa toàn bộ bảng
    function clearTable() {
        if (tableBody.children.length === 0) return;
        if (confirm('Bạn có chắc muốn xóa tất cả các dòng biến thể?')) {
            tableBody.innerHTML = '';
            rowCount = 0;
        }
    }

    // Sự kiện
    generateBtn.addEventListener('click', generateVariants);
    clearBtn.addEventListener('click', clearTable);

    addRowBtn.addEventListener('click', function() {
        addRow('', '', '', '');
    });

    // Thêm một dòng mặc định khi load trang
    addRow('', '', '', '');
});
</script>
@endsection

@include('admin.partials.toast')

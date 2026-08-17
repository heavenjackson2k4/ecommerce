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
                    <tbody id="variant-body">
                        <!-- Các dòng sẽ được thêm vào đây -->
                    </tbody>
                </table>
            </div>
        </div>
        <!-- ===== THÊM PHẦN QUẢN LÝ ẢNH (SAU BẢNG BIẾN THỂ, TRƯỚC NÚT SUBMIT) ===== -->
        <div class="border-t pt-4 mt-4">
            <h3 class="text-lg font-semibold mb-3">Quản lý ảnh quần áo</h3>
            <p class="text-sm text-gray-500 mb-3">Tối đa 3 ảnh cho mỗi màu. Kéo thả để sắp xếp.</p>
            
            <div id="image-management">
                <div id="color-image-sections" class="space-y-4">
                    <!-- Các section màu sẽ được tạo động -->
                </div>
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
    const tableBody = document.getElementById('variant-body');
    const generateBtn = document.getElementById('generate-variants-btn');
    const clearBtn = document.getElementById('clear-variants-btn');
    const addRowBtn = document.getElementById('add-row-btn');

    // Thêm một dòng mới vào bảng
    function addRow(size = '', color = '', quantity = '', price = '') {
        const row = document.createElement('tr');
        row.className = 'variant-row';
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

// ===== PHẦN QUẢN LÝ ẢNH =====
    const MAX_IMAGES_PER_COLOR = 3;
    let globalImageIndex = 0;

    function getColorsFromVariants() {
        const rows = document.querySelectorAll('#variant-body .variant-row');
        console.log("rows table: ", rows);
        const colors = new Set();
        rows.forEach(row => {
            const colorInput = row.querySelector('input[name*="[color]"]');
            if (colorInput && colorInput.value.trim()) {
                colors.add(colorInput.value.trim());
            }
        });
        return Array.from(colors);
    }

    function renderImageSections() {
        console.log("da di vao ham renderImageSection");
        const container = document.getElementById('color-image-sections');
        const colors = getColorsFromVariants();
        console.log("color: ", colors);
        const existingData = {};
        
        // Lưu dữ liệu ảnh cũ từ các section
        container.querySelectorAll('.color-section').forEach(section => {
            const color = section.dataset.color;
            const images = [];
            section.querySelectorAll('.image-item').forEach(item => {
                images.push({
                    image_url: item.dataset.url,
                    public_id: item.dataset.publicId,
                    is_primary: item.dataset.isPrimary === 'true',
                    display_order: parseInt(item.dataset.order) || 0,
                    color: color,
                });
            });
            if (images.length) existingData[color] = images;
        });

        container.innerHTML = '';
        colors.forEach(color => {
            const section = document.createElement('div');
            section.className = 'color-section border border-gray-200 rounded-lg p-4';
            section.dataset.color = color;
            
            const images = existingData[color] || [];
            let imagesHtml = images.map((img) => {
                const idx = globalImageIndex++;
                return `
                    <div class="image-item relative inline-block m-1 group" 
                         data-url="${img.image_url}" 
                         data-public-id="${img.public_id}" 
                         data-is-primary="${img.is_primary}" 
                         data-order="${img.display_order}"
                         data-color="${color}">
                        <img src="${img.image_url}" class="w-20 h-20 object-cover rounded border" alt="Image">
                        <button type="button" class="remove-image absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">×</button>
                        ${img.is_primary ? '<span class="absolute bottom-0 left-0 bg-black text-white text-[10px] px-1 rounded">Chính</span>' : ''}
                        <div class="absolute bottom-0 left-0 right-0 bg-black/70 text-white text-[10px] p-1 flex items-center justify-between opacity-0 group-hover:opacity-100 transition">
                            <button type="button" class="set-primary hover:text-yellow-300 ${img.is_primary ? 'text-yellow-300' : ''}">★</button>
                        </div>
                        <input type="hidden" name="images[${idx}][image_url]" value="${img.image_url}">
                        <input type="hidden" name="images[${idx}][public_id]" value="${img.public_id}">
                        <input type="hidden" name="images[${idx}][color]" value="${color}">
                        <input type="hidden" name="images[${idx}][is_primary]" value="${img.is_primary}">
                        <input type="hidden" name="images[${idx}][display_order]" value="${img.display_order}">
                    </div>
                `;
            }).join('');

            section.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-gray-800">Màu: <span class="font-bold">${color}</span></h4>
                    <span class="text-xs text-gray-500">${images.length}/${MAX_IMAGES_PER_COLOR} ảnh</span>
                </div>
                <div class="image-drop-zone border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-black transition cursor-pointer upload-area" data-color="${color}">
                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="text-sm text-gray-500 mt-1">Kéo thả ảnh vào đây hoặc nhấp để chọn</p>
                    <input type="file" accept="image/*" class="hidden file-input" data-color="${color}">
                </div>
                <div class="image-list mt-2 flex flex-wrap">
                    ${imagesHtml}
                </div>
            `;

            container.appendChild(section);
            setupImageEvents(section);
        });
    }

    async function uploadImage(file, color) {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('color', color);
        formData.append('product_type', 'clothes');

        try {
            const response = await fetch('/admin/upload-image', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData,
            });
            const result = await response.json();
            if (result.success) return result.data;
            alert('Upload ảnh thất bại: ' + (result.error || 'Lỗi không xác định'));
            return null;
        } catch (error) {
            alert('Có lỗi xảy ra khi upload ảnh');
            console.error(error);
            return null;
        }
    }

    function addImageToSection(section, data, color) {
        const list = section.querySelector('.image-list');
        const idx = globalImageIndex++;
        const item = document.createElement('div');
        item.className = 'image-item relative inline-block m-1 group';
        item.dataset.url = data.image_url;
        item.dataset.publicId = data.public_id;
        item.dataset.isPrimary = 'false';
        item.dataset.order = idx;
        item.dataset.color = color;
        
        item.innerHTML = `
            <img src="${data.image_url}" class="w-20 h-20 object-cover rounded border" alt="Image">
            <button type="button" class="remove-image absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">×</button>
            <div class="absolute bottom-0 left-0 right-0 bg-black/70 text-white text-[10px] p-1 flex items-center justify-between opacity-0 group-hover:opacity-100 transition">
                <button type="button" class="set-primary hover:text-yellow-300">★</button>
            </div>
            <input type="hidden" name="images[${idx}][image_url]" value="${data.image_url}">
            <input type="hidden" name="images[${idx}][public_id]" value="${data.public_id}">
            <input type="hidden" name="images[${idx}][color]" value="${color}">
            <input type="hidden" name="images[${idx}][is_primary]" value="false">
            <input type="hidden" name="images[${idx}][display_order]" value="${idx}">
        `;
        list.appendChild(item);
        updateImageCount(section);
        setupImageEventsForItem(item);
    }

    function updateImageCount(section) {
        const count = section.querySelectorAll('.image-item').length;
        const label = section.querySelector('.flex span.text-xs');
        if (label) label.textContent = `${count}/${MAX_IMAGES_PER_COLOR} ảnh`;
    }

    function setupImageEventsForItem(item) {
        const removeBtn = item.querySelector('.remove-image');
        removeBtn.addEventListener('click', function() {
            const section = item.closest('.color-section');
            item.remove();
            updateImageCount(section);
            reorderImages(section);
        });

        const primaryBtn = item.querySelector('.set-primary');
        if (primaryBtn) {
            primaryBtn.addEventListener('click', function() {
                const section = item.closest('.color-section');
                section.querySelectorAll('.image-item').forEach(i => {
                    i.dataset.isPrimary = 'false';
                    const input = i.querySelector('input[name*="[is_primary]"]');
                    if (input) input.value = 'false';
                    const btn = i.querySelector('.set-primary');
                    if (btn) btn.classList.remove('text-yellow-300');
                });
                item.dataset.isPrimary = 'true';
                const input = item.querySelector('input[name*="[is_primary]"]');
                if (input) input.value = 'true';
                primaryBtn.classList.add('text-yellow-300');
            });
        }
    }

    function setupImageEvents(section) {
        section.querySelectorAll('.image-item').forEach(item => setupImageEventsForItem(item));
    }

    function reorderImages(section) {
        const items = section.querySelectorAll('.image-item');
        items.forEach((item, idx) => {
            item.dataset.order = idx;
            const orderInput = item.querySelector('input[name*="[display_order]"]');
            if (orderInput) orderInput.value = idx;
        });
    }

    function setupDragDrop() {
        document.addEventListener('dragover', function(e) {
            const dropZone = e.target.closest('.image-drop-zone');
            if (dropZone) {
                e.preventDefault();
                dropZone.classList.add('border-black', 'bg-gray-50');
            }
        });
        document.addEventListener('dragleave', function(e) {
            const dropZone = e.target.closest('.image-drop-zone');
            if (dropZone) {
                dropZone.classList.remove('border-black', 'bg-gray-50');
            }
        });
        document.addEventListener('drop', async function(e) {
            e.preventDefault();
            const dropZone = e.target.closest('.image-drop-zone');
            if (!dropZone) return;
            dropZone.classList.remove('border-black', 'bg-gray-50');
            
            const files = e.dataTransfer.files;
            const color = dropZone.dataset.color;
            const section = dropZone.closest('.color-section');
            
            const currentImages = section.querySelectorAll('.image-item').length;
            if (currentImages + files.length > MAX_IMAGES_PER_COLOR) {
                alert(`Mỗi màu chỉ được tối đa ${MAX_IMAGES_PER_COLOR} ảnh!`);
                return;
            }
            
            for (const file of files) {
                if (!file.type.startsWith('image/')) continue;
                const data = await uploadImage(file, color);
                if (data) addImageToSection(section, data, color);
            }
        });
    }

    document.addEventListener('click', function(e) {
        const dropZone = e.target.closest('.upload-area');
        if (dropZone) {
            const input = dropZone.querySelector('.file-input');
            if (input) input.click();
        }
    });

    document.addEventListener('change', async function(e) {
        const input = e.target.closest('.file-input');
        if (!input) return;
        const color = input.dataset.color;
        const section = input.closest('.color-section');
        const files = input.files;
        
        if (!files.length) return;
        
        const currentImages = section.querySelectorAll('.image-item').length;
        if (currentImages + files.length > MAX_IMAGES_PER_COLOR) {
            alert(`Mỗi màu chỉ được tối đa ${MAX_IMAGES_PER_COLOR} ảnh!`);
            input.value = '';
            return;
        }

        for (const file of files) {
            const data = await uploadImage(file, color);
            if (data) addImageToSection(section, data, color);
        }
        input.value = '';
    });

    setupDragDrop();
    renderImageSections();
    window.renderImageSections = renderImageSections;
    
    // Lắng nghe thay đổi variant để cập nhật danh sách màu
    const observer = new MutationObserver(function() {
        renderImageSections();
    });
    const variantBody = document.getElementById('variant-body');
    if (variantBody) {
        observer.observe(variantBody, { childList: true, subtree: true });
    }

});
</script>
@endsection

@include('admin.partials.toast')

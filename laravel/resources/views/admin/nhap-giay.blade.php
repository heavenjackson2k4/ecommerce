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
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                        <input type="checkbox" id="use-base-price" class="h-4 w-4 accent-black">
                        <span>Sử dụng giá cơ bản</span>
                    </label>
                    <button type="button" id="add-variant-btn" class="bg-black text-white px-4 py-1 rounded text-sm">+ Thêm dòng</button>
                </div>
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

        <!-- ===== PHẦN QUẢN LÝ ẢNH (THÊM MỚI) ===== -->
        <div class="border-t pt-4 mt-4">
            <h3 class="text-lg font-semibold mb-3">Quản lý ảnh giày</h3>
            <p class="text-sm text-gray-500 mb-3">Tối đa 3 ảnh cho mỗi màu. Kéo thả để sắp xếp.</p>
            
            <div id="image-management">
                <!-- Các khu vực upload màu sẽ được tạo tự động từ danh sách màu trong variants -->
                <div id="color-image-sections" class="space-y-4">
                    <!-- Mỗi màu sẽ có một section riêng -->
                </div>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:bg-slate-800">Lưu giày</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productNameInput = document.querySelector('input[name="name"]');
    const slugInput = document.querySelector('input[name="slug"]');

    function createSlug(value) {
        return value
            .trim()
            .toLowerCase()
            .replace(/đ/g, 'd')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    productNameInput.addEventListener('blur', function() {
        slugInput.value = createSlug(productNameInput.value);
    });

    let rowCount = 0;
    const tbody = document.getElementById('variant-body');
    const addBtn = document.getElementById('add-variant-btn');
    const generateBtn = document.getElementById('generate-variants-btn');
    const sizesInput = document.getElementById('sizes-input');
    const colorsInput = document.getElementById('colors-input');
    const studTypesInput = document.getElementById('stud-types-input');
    const basePriceInput = document.querySelector('input[name="base_price"]');
    const useBasePriceCheckbox = document.getElementById('use-base-price');

    function syncVariantPricesWithBasePrice() {
        const priceInputs = tbody.querySelectorAll('input[name*="[price_override]"]');

        priceInputs.forEach(input => {
            if (useBasePriceCheckbox.checked) {
                input.value = basePriceInput.value;
            }

            input.readOnly = useBasePriceCheckbox.checked;
            input.classList.toggle('bg-slate-100', useBasePriceCheckbox.checked);
            input.classList.toggle('text-slate-500', useBasePriceCheckbox.checked);
        });
    }

    useBasePriceCheckbox.addEventListener('change', syncVariantPricesWithBasePrice);
    basePriceInput.addEventListener('input', function() {
        if (useBasePriceCheckbox.checked) {
            syncVariantPricesWithBasePrice();
        }
    });

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
        syncVariantPricesWithBasePrice();
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


    const MAX_IMAGES_PER_COLOR = 3;
    let globalImageIndex = 0;

    function getColorsFromVariants() {
        const rows = document.querySelectorAll('#variant-body .variant-row');
        console.log("row table nhap giay: ", rows);
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
        const container = document.getElementById('color-image-sections');
        const colors = getColorsFromVariants();
        console.log("color giay: ", colors);
        const existingData = {};
        
        // Lưu dữ liệu ảnh cũ từ các section
        container.querySelectorAll('.color-section').forEach(section => {
            const color = section.dataset.color;
            const images = [];
            section.querySelectorAll('.image-item').forEach(item => {
                images.push({
                    image_url: item.dataset.url,
                    public_id: item.dataset.publicId,
                    stud_type: item.dataset.studType || null,
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
                         data-stud-type="${img.stud_type || ''}" 
                         data-is-primary="${img.is_primary}" 
                         data-order="${img.display_order}"
                         data-color="${color}">
                        <img src="${img.image_url}" class="w-20 h-20 object-cover rounded border" alt="Image">
                        <button type="button" class="remove-image absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">×</button>
                        ${img.is_primary ? '<span class="absolute bottom-0 left-0 bg-black text-white text-[10px] px-1 rounded">Chính</span>' : ''}
                        <div class="absolute bottom-0 left-0 right-0 bg-black/70 text-white text-[10px] p-1 flex items-center justify-between opacity-0 group-hover:opacity-100 transition">
                            <button type="button" class="set-primary hover:text-yellow-300 ${img.is_primary ? 'text-yellow-300' : ''}">★</button>
                            ${img.stud_type ? `<span class="text-[8px]">${img.stud_type}</span>` : ''}
                        </div>
                        <input type="hidden" name="images[${idx}][image_url]" value="${img.image_url}">
                        <input type="hidden" name="images[${idx}][public_id]" value="${img.public_id}">
                        <input type="hidden" name="images[${idx}][color]" value="${color}">
                        <input type="hidden" name="images[${idx}][stud_type]" value="${img.stud_type || ''}">
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

    async function uploadImage(file, color, studType = null) {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('color', color);
        formData.append('product_type', 'shoes');
        if (studType) formData.append('stud_type', studType);

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
        item.dataset.studType = data.stud_type || '';
        item.dataset.isPrimary = 'false';
        item.dataset.order = idx;
        item.dataset.color = color;
        
        const hasStudType = data.stud_type && data.stud_type.trim() !== '';
        const studTypeText = hasStudType ? `<span class="text-[8px]">${data.stud_type}</span>` : '';
        
        item.innerHTML = `
            <img src="${data.image_url}" class="w-20 h-20 object-cover rounded border" alt="Image">
            <button type="button" class="remove-image absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">×</button>
            <div class="absolute bottom-0 left-0 right-0 bg-black/70 text-white text-[10px] p-1 flex items-center justify-between opacity-0 group-hover:opacity-100 transition">
                <button type="button" class="set-primary hover:text-yellow-300">★</button>
                ${studTypeText}
            </div>
            <input type="hidden" name="images[${idx}][image_url]" value="${data.image_url}">
            <input type="hidden" name="images[${idx}][public_id]" value="${data.public_id}">
            <input type="hidden" name="images[${idx}][color]" value="${color}">
            <input type="hidden" name="images[${idx}][stud_type]" value="${data.stud_type || ''}">
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
{{-- Toast sẽ hiển thị ở đây --}}
@include('admin.partials.toast')

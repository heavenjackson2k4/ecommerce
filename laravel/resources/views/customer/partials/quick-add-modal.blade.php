<div id="quick-add-modal" class="fixed inset-0 z-[65] hidden items-center justify-center bg-black/40 px-4 py-6" role="dialog" aria-modal="true" aria-labelledby="quick-add-title">
    <div class="max-h-full w-full max-w-[520px] overflow-y-auto rounded-2xl bg-white p-5 shadow-xl sm:p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex min-w-0 items-center gap-3">
                <img id="quick-add-image" src="" alt="" class="h-20 w-20 flex-shrink-0 rounded-xl border border-gray-200 object-cover">
                <div class="min-w-0">
                    <h2 id="quick-add-title" class="line-clamp-2 text-lg font-semibold text-gray-900"></h2>
                    <p id="quick-add-price" class="mt-1 text-lg font-bold text-gray-900"></p>
                    <p id="quick-add-selection" class="mt-1 hidden text-sm text-gray-500"></p>
                </div>
            </div>
            <button type="button" id="close-quick-add-modal" class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700" aria-label="Đóng">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="mt-5 space-y-4">
            <div>
                <p class="text-sm font-medium text-gray-700">Màu sắc</p>
                <div id="quick-add-colors" class="mt-2 flex flex-wrap gap-2"></div>
            </div>
            <div>
                <p id="quick-add-size-label" class="text-sm font-medium text-gray-700">Kích thước</p>
                <div id="quick-add-sizes" class="mt-2 flex flex-wrap gap-2"></div>
            </div>
            <div id="quick-add-stud-group" class="hidden">
                <p class="text-sm font-medium text-gray-700">Loại đinh</p>
                <div id="quick-add-studs" class="mt-2 flex flex-wrap gap-2"></div>
            </div>
            <p id="quick-add-stock" class="text-sm text-gray-500">Chọn đầy đủ tùy chọn để thêm vào giỏ.</p>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-2">
            <button type="button" id="cancel-quick-add-modal" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                Hủy
            </button>
            <button type="button" id="confirm-quick-add" class="rounded-lg bg-black px-4 py-2.5 text-sm font-medium text-white transition hover:bg-gray-800 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500" disabled>
                Thêm vào giỏ
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('quick-add-modal');
    const closeButton = document.getElementById('close-quick-add-modal');
    const cancelButton = document.getElementById('cancel-quick-add-modal');
    const confirmButton = document.getElementById('confirm-quick-add');
    const productImage = document.getElementById('quick-add-image');
    const productTitle = document.getElementById('quick-add-title');
    const productPrice = document.getElementById('quick-add-price');
    const selectionSummary = document.getElementById('quick-add-selection');
    const colorContainer = document.getElementById('quick-add-colors');
    const sizeLabel = document.getElementById('quick-add-size-label');
    const sizeContainer = document.getElementById('quick-add-sizes');
    const studGroup = document.getElementById('quick-add-stud-group');
    const studContainer = document.getElementById('quick-add-studs');
    const stockMessage = document.getElementById('quick-add-stock');
    const addUrl = @json(route('api.cart.add'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let product = null;
    let selectedColor = null;
    let selectedSize = null;
    let selectedStud = null;
    let previousActiveElement = null;
    let saving = false;

    if (!modal) return;

    function formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(Number(price) || 0) + ' ₫';
    }

    function uniqueValues(variants, field) {
        return [...new Set(variants.map(variant => String(variant[field])))];
    }

    function sortedSizes(values) {
        if (product.type === 'shoe') {
            return values.sort((first, second) => Number(first) - Number(second));
        }

        const order = ['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'];
        return values.sort((first, second) => {
            const firstIndex = order.indexOf(first.toUpperCase());
            const secondIndex = order.indexOf(second.toUpperCase());
            const normalizedFirst = firstIndex === -1 ? order.length : firstIndex;
            const normalizedSecond = secondIndex === -1 ? order.length : secondIndex;

            return normalizedFirst - normalizedSecond || first.localeCompare(second);
        });
    }

    function variantsForSelection(includeStud = true) {
        return product.variants.filter(variant => {
            if (selectedColor && variant.color !== selectedColor) return false;
            if (selectedSize && String(variant.size) !== selectedSize) return false;
            if (includeStud && selectedStud && variant.stud_type !== selectedStud) return false;
            return true;
        });
    }

    function selectSingleOptions() {
        const colors = uniqueValues(product.variants, 'color');
        if (!selectedColor && colors.length === 1) {
            selectedColor = colors[0];
        }

        if (selectedColor && !selectedSize) {
            const sizes = uniqueValues(
                product.variants.filter(variant => variant.color === selectedColor),
                'size'
            );
            if (sizes.length === 1) {
                selectedSize = sizes[0];
            }
        }

        if (product.type === 'shoe' && selectedColor && selectedSize && !selectedStud) {
            const studs = uniqueValues(variantsForSelection(false), 'stud_type');
            if (studs.length === 1) {
                selectedStud = studs[0];
            }
        }
    }

    function selectedVariant() {
        if (!selectedColor || !selectedSize || (product.type === 'shoe' && !selectedStud)) {
            return null;
        }

        return product.variants.find(variant =>
            variant.color === selectedColor
            && String(variant.size) === selectedSize
            && (product.type !== 'shoe' || variant.stud_type === selectedStud)
        ) || null;
    }

    function renderOptions(container, values, selectedValue, field, isDisabled = false) {
        container.replaceChildren();

        values.forEach(value => {
            const button = document.createElement('button');
            const active = value === selectedValue;
            const disabled = typeof isDisabled === 'function' ? isDisabled(value) : isDisabled;
            button.type = 'button';
            button.dataset.option = field;
            button.dataset.value = value;
            button.textContent = value;
            button.disabled = disabled;
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
            button.className = active
                ? 'rounded-lg border border-black bg-gray-200 px-3 py-2 text-sm font-medium text-gray-900 transition'
                : 'rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition hover:border-black disabled:cursor-not-allowed disabled:hover:border-gray-300';
            container.appendChild(button);
        });
    }

    function render() {
        selectSingleOptions();

        const colors = uniqueValues(product.variants, 'color');
        const sizes = sortedSizes(uniqueValues(product.variants, 'size'));
        const studs = product.type === 'shoe' ? uniqueValues(product.variants, 'stud_type') : [];

        renderOptions(colorContainer, colors, selectedColor, 'color');
        renderOptions(sizeContainer, sizes, selectedSize, 'size', value =>
            !selectedColor || !product.variants.some(variant =>
                variant.color === selectedColor && String(variant.size) === value
            )
        );
        sizeLabel.textContent = product.type === 'shoe' ? 'Size giày' : 'Kích thước';
        studGroup.classList.toggle('hidden', product.type !== 'shoe');
        if (product.type === 'shoe') {
            renderOptions(studContainer, studs, selectedStud, 'stud', value =>
                !selectedColor || !selectedSize || !product.variants.some(variant =>
                    variant.color === selectedColor
                    && String(variant.size) === selectedSize
                    && variant.stud_type === value
                )
            );
        }

        const variant = selectedVariant();
        const selectedImage = selectedColor ? product.images_by_color?.[selectedColor] : null;
        const selectionParts = [];
        if (selectedColor) selectionParts.push(`Màu: ${selectedColor}`);
        if (selectedSize) selectionParts.push(product.type === 'shoe' ? `Size: ${selectedSize}` : `Kích thước: ${selectedSize}`);
        if (product.type === 'shoe' && selectedStud) selectionParts.push(`Loại đinh: ${selectedStud}`);

        productImage.src = selectedImage || product.image;
        productImage.alt = selectedColor ? `${product.name} - ${selectedColor}` : product.name;
        productPrice.textContent = formatPrice(variant?.price ?? product.base_price);
        selectionSummary.textContent = selectionParts.join(' · ');
        selectionSummary.classList.toggle('hidden', selectionParts.length === 0);
        stockMessage.textContent = variant ? '' : 'Chọn đầy đủ tùy chọn để thêm vào giỏ.';
        stockMessage.classList.toggle('hidden', Boolean(variant));
        confirmButton.disabled = !variant || saving;
    }

    function openModal(selectedProduct) {
        product = selectedProduct;
        selectedColor = null;
        selectedSize = null;
        selectedStud = null;
        previousActiveElement = document.activeElement;
        productImage.src = product.image;
        productImage.alt = product.name;
        productTitle.textContent = product.name;
        productPrice.textContent = formatPrice(product.base_price);
        render();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        closeButton.focus();
    }

    function hideModal() {
        if (saving) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        previousActiveElement?.focus();
        product = null;
    }

    async function addVariant(selectedProduct, variant, button, closeAfterSuccess = false) {
        if (saving) return;

        saving = true;
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Đang thêm...';

        try {
            const response = await fetch(addUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    product_id: selectedProduct.id,
                    variant_id: variant.id,
                    variant_type: selectedProduct.type,
                    quantity: 1,
                }),
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Không thể thêm sản phẩm vào giỏ hàng.');
            }

            window.updateCartCount?.(data.cart_count);
            window.loadCartMini?.();
            window.showToast(data.message || 'Đã thêm vào giỏ hàng.');

            if (closeAfterSuccess) {
                saving = false;
                hideModal();
                return;
            }

            button.textContent = 'Đã thêm';
            button.classList.remove('bg-black', 'hover:bg-gray-800');
            button.classList.add('bg-green-600', 'hover:bg-green-700');
            setTimeout(() => {
                button.textContent = originalText;
                button.classList.remove('bg-green-600', 'hover:bg-green-700');
                button.classList.add('bg-black', 'hover:bg-gray-800');
                button.disabled = false;
            }, 2000);
        } catch (error) {
            window.showToast(error.message || 'Không thể thêm sản phẩm vào giỏ hàng.', 'error');
            button.textContent = originalText;
            button.disabled = false;
        } finally {
            saving = false;
            if (product) render();
        }
    }

    document.addEventListener('click', function(event) {
        const trigger = event.target.closest('.quick-add-trigger');
        if (!trigger) return;

        event.preventDefault();
        let selectedProduct;
        try {
            selectedProduct = JSON.parse(trigger.dataset.quickAdd);
        } catch (error) {
            window.showToast('Không thể tải thông tin biến thể sản phẩm.', 'error');
            return;
        }

        if (selectedProduct.variants.length === 1) {
            addVariant(selectedProduct, selectedProduct.variants[0], trigger);
            return;
        }

        openModal(selectedProduct);
    });

    modal.addEventListener('click', function(event) {
        const option = event.target.closest('[data-option]');
        if (option) {
            const value = option.dataset.value;
            if (option.dataset.option === 'color') {
                selectedColor = value;
                selectedSize = null;
                selectedStud = null;
            } else if (option.dataset.option === 'size') {
                selectedSize = value;
                selectedStud = null;
            } else {
                selectedStud = value;
            }
            render();
            return;
        }

        if (event.target === modal) {
            hideModal();
        }
    });

    confirmButton.addEventListener('click', function() {
        const variant = selectedVariant();
        if (variant) {
            addVariant(product, variant, confirmButton, true);
        }
    });
    cancelButton.addEventListener('click', hideModal);
    closeButton.addEventListener('click', hideModal);
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.classList.contains('flex')) {
            hideModal();
        }
    });
});
</script>

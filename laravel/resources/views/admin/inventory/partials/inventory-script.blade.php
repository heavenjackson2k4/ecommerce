<script>
    window.inventoryEditor = function(config) {
        return {
            products: Object.fromEntries(
                (config.products || []).map(product => [Number(product.id), product])
            ),
            productStatuses: config.productStatuses,
            productStatusMenuProductId: null,
            productStatusMenuTrigger: null,
            productStatusMenuPosition: { top: 0, left: 0, width: 0 },
            selectedProduct: null,
            editingVariantId: null,
            editForm: {
                quantity: 0,
                price: 0,
                useBasePrice: false,
            },
            bulkEditing: false,
            bulkMode: 'increment',
            bulkItems: {},
            commonQuantity: 0,
            updatePrice: false,
            priceMode: 'set',
            priceValue: '',
            saving: false,
            productStatusSavingId: null,
            pendingProductStatusChange: null,
            notification: null,
            notificationTimer: null,

            openProduct(productId) {
                if (this.saving || this.productStatusSavingId !== null || this.pendingProductStatusChange) return;

                this.closeProductStatusMenu(false);
                this.selectedProduct = this.products[Number(productId)] || null;
                if (!this.selectedProduct) return;

                this.editingVariantId = null;
                this.resetBulkEditing();
            },

            closeProduct() {
                if (this.saving) return;

                this.selectedProduct = null;
                this.editingVariantId = null;
                this.resetBulkEditing();
            },

            startEditing(variant) {
                if (this.saving || this.bulkEditing) return;

                this.editingVariantId = variant.id;
                this.editForm.quantity = variant.quantity;
                this.editForm.price = variant.price;
                this.editForm.useBasePrice = variant.price_override === null;
            },

            cancelEditing() {
                if (this.saving) return;

                this.editingVariantId = null;
            },

            startBulkEditing() {
                if (this.saving || !this.selectedProduct?.variants?.length) return;

                this.editingVariantId = null;
                this.bulkEditing = true;
                this.bulkMode = 'increment';
                this.commonQuantity = 0;
                this.updatePrice = false;
                this.priceMode = 'set';
                this.priceValue = '';
                this.bulkItems = Object.fromEntries(
                    this.selectedProduct.variants.map(variant => [variant.id, {
                        selected: false,
                        quantity: 0,
                    }])
                );
            },

            resetBulkEditing() {
                this.bulkEditing = false;
                this.bulkMode = 'increment';
                this.commonQuantity = 0;
                this.updatePrice = false;
                this.priceMode = 'set';
                this.priceValue = '';
                this.bulkItems = Object.fromEntries(
                    (this.selectedProduct?.variants || []).map(variant => [variant.id, {
                        selected: false,
                        quantity: 0,
                    }])
                );
            },

            cancelBulkEditing() {
                if (this.saving) return;

                this.resetBulkEditing();
            },

            changeBulkMode(mode) {
                if (this.saving || this.bulkMode === mode) return;

                this.bulkMode = mode;
                this.commonQuantity = 0;
                this.selectedProduct.variants.forEach(variant => {
                    this.bulkItems[variant.id].quantity = mode === 'increment'
                        ? 0
                        : Number(variant.quantity);
                });
            },

            selectedBulkCount() {
                return Object.values(this.bulkItems).filter(item => item.selected).length;
            },

            allBulkSelected() {
                const items = Object.values(this.bulkItems);
                return items.length > 0 && items.every(item => item.selected);
            },

            selectAllBulk(selected) {
                Object.values(this.bulkItems).forEach(item => {
                    item.selected = selected;
                });
            },

            applyCommonQuantity() {
                const quantity = Number(this.commonQuantity);
                if (!Number.isInteger(quantity) || quantity < 0) {
                    this.showNotification('Số lượng áp dụng phải là số nguyên không âm.', 'error');
                    return;
                }

                Object.values(this.bulkItems).forEach(item => {
                    if (item.selected) item.quantity = quantity;
                });
            },

            bulkPreview(variant) {
                const quantity = Number(this.bulkItems[variant.id]?.quantity);
                if (!Number.isInteger(quantity) || quantity < 0) return '—';

                return this.bulkMode === 'increment'
                    ? Number(variant.quantity) + quantity
                    : quantity;
            },

            bulkPricePreview(variant) {
                if (this.priceMode === 'base') return Number(this.selectedProduct.base_price);

                if (this.priceValue === '' || this.priceValue === null) return null;

                const value = Number(this.priceValue);
                const currentPrice = Number(variant.price);
                if (!Number.isFinite(value) || value < 0) return null;

                switch (this.priceMode) {
                    case 'set':
                        return value;
                    case 'increase_amount':
                        return currentPrice + value;
                    case 'decrease_amount':
                        return Math.max(0, currentPrice - value);
                    case 'increase_percent':
                        return currentPrice * (1 + value / 100);
                    case 'decrease_percent':
                        return Math.max(0, currentPrice * (1 - value / 100));
                    default:
                        return null;
                }
            },

            formatPrice(price) {
                return new Intl.NumberFormat('vi-VN').format(Number(price) || 0);
            },

            showNotification(message, type = 'success') {
                clearTimeout(this.notificationTimer);
                this.notification = { message, type };
                this.notificationTimer = setTimeout(() => {
                    this.notification = null;
                }, 5000);
            },

            toggleProductStatusMenu(productId, trigger) {
                if (this.saving || this.productStatusSavingId !== null || this.pendingProductStatusChange) return;
                if (this.productStatusMenuProductId === Number(productId)) {
                    this.closeProductStatusMenu();
                    return;
                }

                const rect = trigger.getBoundingClientRect();
                this.productStatusMenuProductId = Number(productId);
                this.productStatusMenuTrigger = trigger;
                this.productStatusMenuPosition = { top: rect.bottom + 6, left: rect.left, width: rect.width };
                this.$nextTick(() => {
                    if (this.productStatusMenuProductId !== Number(productId)) return;

                    const menu = this.$refs.productStatusMenu;
                    this.productStatusMenuPosition.top = rect.bottom + menu.offsetHeight + 6 > window.innerHeight - 8
                        ? Math.max(8, rect.top - menu.offsetHeight - 6)
                        : rect.bottom + 6;
                    this.productStatusMenuPosition.left = Math.max(8, Math.min(rect.left, window.innerWidth - rect.width - 8));
                    menu.querySelector('[data-status="' + this.products[Number(productId)].status + '"]')?.focus({ preventScroll: true });
                });
            },

            closeProductStatusMenu(restoreFocus = true) {
                if (this.productStatusMenuProductId === null) return;

                this.productStatusMenuProductId = null;
                if (restoreFocus) this.productStatusMenuTrigger?.focus({ preventScroll: true });
                this.productStatusMenuTrigger = null;
            },

            focusProductStatusOption(direction) {
                const options = Array.from(this.$refs.productStatusMenu.querySelectorAll('[data-status]'));
                const index = options.indexOf(document.activeElement);
                options[(index + direction + options.length) % options.length]?.focus({ preventScroll: true });
            },

            chooseProductStatus(status) {
                const productId = this.productStatusMenuProductId;
                this.closeProductStatusMenu();
                if (productId === null) return;

                return this.requestProductStatusChange(productId, status);
            },

            requestProductStatusChange(productId, status) {
                const product = this.products[Number(productId)];
                if (!product || this.saving || this.productStatusSavingId !== null || this.pendingProductStatusChange) return;
                if (!Object.prototype.hasOwnProperty.call(config.productStatuses, status) || status === product.status) return;

                if (status === 'archived') {
                    this.pendingProductStatusChange = { productId: Number(productId), status, currentStatus: product.status };
                    return;
                }

                return this.saveInventoryProductStatus(Number(productId), status, product.status);
            },

            cancelProductStatusChange() {
                this.pendingProductStatusChange = null;
            },

            confirmProductStatusChange() {
                const change = this.pendingProductStatusChange;
                if (!change) return;

                this.pendingProductStatusChange = null;
                return this.saveInventoryProductStatus(change.productId, change.status, change.currentStatus);
            },

            async saveInventoryProductStatus(productId, status, currentStatus) {
                if (this.saving || this.productStatusSavingId !== null) return;

                this.productStatusSavingId = productId;
                const updateUrl = config.inventoryProductStatusUpdateUrl.replace('__PRODUCT__', productId);
                try {
                    const response = await window.axios.patch(updateUrl, {
                        status,
                        current_status: currentStatus,
                    }, {
                        timeout: 15000,
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                    });

                    this.products[productId].status = response.data.data.status;
                    this.showNotification(response.data.message || 'Cập nhật trạng thái sản phẩm thành công.');
                } catch (error) {
                    if (error.code === 'ECONNABORTED' || error.code === 'ETIMEDOUT') {
                        this.showNotification('Chưa nhận được phản hồi từ máy chủ. Vui lòng tải lại trang để kiểm tra trạng thái sản phẩm trước khi thử lại.', 'error');
                        return;
                    }

                    const errors = error.response?.data?.errors;
                    const message = errors
                        ? Object.values(errors).flat()[0]
                        : (error.response?.data?.message || 'Không thể cập nhật trạng thái sản phẩm. Vui lòng thử lại.');
                    this.showNotification(message, 'error');
                } finally {
                    this.productStatusSavingId = null;
                }
            },

            async saveVariant(variant) {
                const quantity = Number(this.editForm.quantity);
                if (!Number.isInteger(quantity) || quantity < 0) {
                    this.showNotification('Số lượng phải là số nguyên không âm.', 'error');
                    return;
                }

                let priceOverride = null;
                if (!this.editForm.useBasePrice) {
                    priceOverride = Number(this.editForm.price);
                    if (!Number.isFinite(priceOverride) || priceOverride < 0) {
                        this.showNotification('Giá bán phải là số không âm.', 'error');
                        return;
                    }
                }

                const updates = {};
                const currentPriceOverride = variant.price_override === null
                    ? null
                    : Number(variant.price_override);

                if (quantity !== Number(variant.quantity)) {
                    updates.quantity = quantity;
                }

                if (priceOverride !== currentPriceOverride) {
                    updates.price_override = priceOverride;
                }

                if (Object.keys(updates).length === 0) {
                    this.editingVariantId = null;
                    return;
                }

                const basePrice = Number(this.selectedProduct.base_price);
                const updateUrl = config.updateUrl.replace('__VARIANT__', variant.id);
                this.saving = true;

                try {
                    const response = await window.axios.patch(updateUrl, updates, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                    });

                    const updatedVariant = response.data.data;
                    variant.quantity = Number(updatedVariant.quantity);
                    variant.price_override = updatedVariant.price_override === null
                        ? null
                        : Number(updatedVariant.price_override);
                    variant.price = variant.price_override ?? basePrice;
                    this.editingVariantId = null;
                    this.showNotification(response.data.message || 'Cập nhật tồn kho thành công.');
                } catch (error) {
                    const errors = error.response?.data?.errors;
                    const message = errors
                        ? Object.values(errors).flat()[0]
                        : (error.response?.data?.message || 'Không thể cập nhật tồn kho. Vui lòng thử lại.');
                    this.showNotification(message, 'error');
                } finally {
                    this.saving = false;
                }
            },

            async saveBulkInventory() {
                const variants = this.selectedProduct.variants
                    .filter(variant => this.bulkItems[variant.id]?.selected)
                    .map(variant => ({
                        id: variant.id,
                        quantity: Number(this.bulkItems[variant.id].quantity),
                    }));

                if (!variants.length) {
                    this.showNotification('Vui lòng chọn ít nhất một biến thể.', 'error');
                    return;
                }

                if (variants.some(variant => !Number.isInteger(variant.quantity) || variant.quantity < 0)) {
                    this.showNotification('Số lượng của các biến thể phải là số nguyên không âm.', 'error');
                    return;
                }

                let priceAdjustment = null;
                if (this.updatePrice) {
                    if (this.priceMode !== 'base'
                        && (this.priceValue === '' || this.priceValue === null
                            || !Number.isFinite(Number(this.priceValue)) || Number(this.priceValue) < 0)) {
                        this.showNotification('Giá trị điều chỉnh giá phải là số không âm.', 'error');
                        return;
                    }

                    priceAdjustment = {
                        mode: this.priceMode,
                        value: this.priceMode === 'base' ? null : Number(this.priceValue),
                    };
                }

                const updateUrl = config.bulkUpdateUrl.replace('__PRODUCT__', this.selectedProduct.id);
                this.saving = true;

                try {
                    const response = await window.axios.patch(updateUrl, {
                        mode: this.bulkMode,
                        variants,
                        price_adjustment: priceAdjustment,
                    }, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                    });

                    const updatedVariants = new Map(
                        response.data.data.map(variant => [Number(variant.id), variant])
                    );
                    this.selectedProduct.variants.forEach(variant => {
                        const updatedVariant = updatedVariants.get(Number(variant.id));
                        if (updatedVariant) {
                            variant.quantity = Number(updatedVariant.quantity);
                            variant.price_override = updatedVariant.price_override === null
                                ? null
                                : Number(updatedVariant.price_override);
                            variant.price = variant.price_override ?? Number(this.selectedProduct.base_price);
                        }
                    });

                    this.resetBulkEditing();
                    this.showNotification(response.data.message || 'Cập nhật hàng loạt thành công.');
                } catch (error) {
                    const errors = error.response?.data?.errors;
                    const message = errors
                        ? Object.values(errors).flat()[0]
                        : (error.response?.data?.message || 'Không thể cập nhật hàng loạt. Vui lòng thử lại.');
                    this.showNotification(message, 'error');
                } finally {
                    this.saving = false;
                }
            },
        };
    };
</script>

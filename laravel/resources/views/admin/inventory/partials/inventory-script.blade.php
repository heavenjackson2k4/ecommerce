<script>
    window.inventoryEditor = function(config) {
        return {
            products: Object.fromEntries(
                (config.products || []).map(product => [Number(product.id), product])
            ),
            selectedProduct: null,
            editingVariantId: null,
            editForm: {
                quantity: 0,
                price: 0,
                useBasePrice: false,
            },
            saving: false,
            notification: null,
            notificationTimer: null,

            openProduct(productId) {
                this.selectedProduct = this.products[Number(productId)] || null;
                if (!this.selectedProduct) return;

                this.cancelEditing();
            },

            closeProduct() {
                this.selectedProduct = null;
                this.cancelEditing();
            },

            startEditing(variant) {
                if (this.saving) return;

                this.editingVariantId = variant.id;
                this.editForm.quantity = variant.quantity;
                this.editForm.price = variant.price;
                this.editForm.useBasePrice = variant.price_override === null;
            },

            cancelEditing() {
                if (this.saving) return;

                this.editingVariantId = null;
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
        };
    };
</script>

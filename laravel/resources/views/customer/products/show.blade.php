@extends('layouts.customer')

@section('title', $product->name)

@section('breadcrumb')
    @include('customer.partials.breadcrumb', ['items' => [
        ['label' => 'Sản phẩm', 'url' => route('products.index')],
        ['label' => $product->name]
    ]])
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
        <!-- Cột trái: Ảnh chính + Gallery -->
        <div class="space-y-4">
            <!-- Ảnh chính -->
            <div class="relative mx-auto flex aspect-square w-full max-w-[520px] items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                <div id="main-image-container" class="flex h-full w-full items-center justify-center">
                    @if($defaultImage)
                        <img
                            src="{{ $defaultImage->image_url }}"
                            id="main-product-image"
                            class="h-full w-full object-cover transition-opacity duration-200"
                            alt="{{ $product->name }} - {{ $defaultColor }}"
                        >
                    @else
                        <svg class="w-16 h-16 sm:w-24 sm:h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    @endif
                </div>
                <button
                    type="button"
                    id="previous-product-image"
                    class="absolute left-3 top-1/2 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-gray-200 bg-white/90 text-gray-700 shadow-sm transition hover:bg-white hover:text-black focus:outline-none focus:ring-2 focus:ring-slate-800"
                    aria-label="Xem ảnh trước"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button
                    type="button"
                    id="next-product-image"
                    class="absolute right-3 top-1/2 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-gray-200 bg-white/90 text-gray-700 shadow-sm transition hover:bg-white hover:text-black focus:outline-none focus:ring-2 focus:ring-slate-800"
                    aria-label="Xem ảnh tiếp theo"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Gallery ảnh (nằm dưới ảnh chính) -->
            @if($imagesByColor->isNotEmpty())
            <div id="product-gallery" class="mx-auto w-full max-w-[520px]">
                @foreach($imagesByColor as $color => $colorImages)
                    <div
                        class="gallery-color-group {{ $color === $defaultColor ? '' : 'hidden' }}"
                        data-gallery-color="{{ $color }}"
                    >
                        <div class="flex justify-center gap-2 overflow-x-auto pb-1">
                            @foreach($colorImages as $image)
                                <button
                                    type="button"
                                    class="thumbnail h-16 w-16 flex-shrink-0 overflow-hidden rounded-lg border-2 bg-white p-1 transition {{ $loop->first && $color === $defaultColor ? 'border-black' : 'border-gray-200 hover:border-gray-400' }}"
                                    data-full="{{ $image->image_url }}"
                                    data-color="{{ $color }}"
                                    aria-label="Xem ảnh {{ $loop->iteration }} màu {{ $color }}"
                                >
                                    <img
                                        src="{{ $image->image_url }}"
                                        class="h-full w-full object-contain"
                                        alt="{{ $product->name }} - {{ $color }} - ảnh {{ $loop->iteration }}"
                                    >
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Cột phải: Thông tin sản phẩm -->
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
            <p class="text-2xl sm:text-3xl font-bold text-gray-900 mt-2">{{ number_format($product->base_price, 0, ',', '.') }} ₫</p>

            <div class="mt-5 sm:mt-6 space-y-4">
                <!-- Size -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Size</label>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @forelse($sizes as $size)
                            <button class="size-option border border-gray-300 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-sm hover:border-black transition" data-size="{{ $size }}">
                                {{ $size }}
                            </button>
                        @empty
                            <span class="text-gray-500 text-sm">Không có size</span>
                        @endforelse
                    </div>
                </div>

                <!-- Color -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Màu sắc</label>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @php $colors = $product->variants->pluck('color')->unique()->values(); @endphp
                        @forelse($colors as $color)
                            <button class="color-option border border-gray-300 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-sm hover:border-black transition" data-color="{{ $color }}">
                                {{ $color }}
                            </button>
                        @empty
                            <span class="text-gray-500 text-sm">Không có màu</span>
                        @endforelse
                    </div>
                </div>

                <!-- Stud Type (if shoe) -->
                @if($product->product_type === 'SHOE')
                <div>
                    <label class="block text-sm font-medium text-gray-700">Loại đinh</label>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @php $studTypes = $product->variants->pluck('stud_type')->unique()->values(); @endphp
                        @forelse($studTypes as $type)
                            <button class="stud-option border border-gray-300 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-sm hover:border-black transition" data-stud="{{ $type }}">
                                {{ $type }}
                            </button>
                        @empty
                            <span class="text-gray-500 text-sm">Không có loại đinh</span>
                        @endforelse
                    </div>
                </div>
                @endif

                <!-- Quantity -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Số lượng</label>
                    <div class="flex items-center space-x-2 mt-1">
                        <button id="decrease-qty" class="border border-gray-300 rounded-lg px-3 py-1.5 hover:bg-gray-50 text-sm">−</button>
                        <input type="number" id="quantity" value="1" min="1" class="w-14 sm:w-16 text-center border border-gray-300 rounded-lg py-1.5 text-sm">
                        <button id="increase-qty" class="border border-gray-300 rounded-lg px-3 py-1.5 hover:bg-gray-50 text-sm">+</button>
                    </div>
                </div>

            <!-- Buttons -->
            <div class="mt-5 sm:mt-6 flex flex-col sm:flex-row gap-3">
                <button id="add-to-cart" class="flex-1 bg-black text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg hover:bg-gray-800 transition font-medium text-sm sm:text-base">
                    Thêm vào giỏ hàng
                </button>
                <button id="buy-now" class="flex-1 bg-green-600 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg hover:bg-green-700 transition font-medium text-sm sm:text-base">
                    Mua ngay
                </button>
                @include('customer.partials.wishlist-button', [
                    'product' => $product,
                    'variant' => 'detail',
                ])
            </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="mt-10 sm:mt-12">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3">Mô tả sản phẩm</h3>
        <div class="prose prose-sm max-w-none text-gray-600">
            {!! nl2br(e($product->description ?? 'Chưa có mô tả cho sản phẩm này.')) !!}
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count())
        <div class="mt-10 sm:mt-12">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Sản phẩm liên quan</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4">
                @foreach($relatedProducts as $related)
                    @include('customer.partials.product-card', ['product' => $related])
                @endforeach
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Quantity
        const qtyInput = document.getElementById('quantity');
        document.getElementById('decrease-qty').addEventListener('click', function() {
            let val = parseInt(qtyInput.value) || 1;
            if (val > 1) qtyInput.value = val - 1;
        });
        document.getElementById('increase-qty').addEventListener('click', function() {
            let val = parseInt(qtyInput.value) || 1;
            qtyInput.value = val + 1;
        });

        const mainProductImage = document.getElementById('main-product-image');
        const galleryGroups = Array.from(document.querySelectorAll('.gallery-color-group'));
        const thumbnails = Array.from(document.querySelectorAll('.thumbnail'));
        const previousImageButton = document.getElementById('previous-product-image');
        const nextImageButton = document.getElementById('next-product-image');
        const defaultGalleryColor = @json($defaultColor);
        const productName = @json($product->name);

        function getVisibleThumbnails() {
            const visibleGroup = galleryGroups.find(group => !group.classList.contains('hidden'));

            return visibleGroup ? Array.from(visibleGroup.querySelectorAll('.thumbnail')) : [];
        }

        function updateGalleryNavigation() {
            const hasMultipleImages = getVisibleThumbnails().length > 1;

            [previousImageButton, nextImageButton].forEach(button => {
                if (!button) return;

                button.classList.toggle('hidden', !hasMultipleImages);
                button.classList.toggle('flex', hasMultipleImages);
            });
        }

        function changeMainImage(thumbnail) {
            if (!mainProductImage || !thumbnail) return;

            mainProductImage.src = thumbnail.dataset.full;
            mainProductImage.alt = `${productName} - ${thumbnail.dataset.color}`;

            thumbnails.forEach(item => {
                item.classList.remove('border-black');
                item.classList.add('border-gray-200', 'hover:border-gray-400');
            });
            thumbnail.classList.remove('border-gray-200', 'hover:border-gray-400');
            thumbnail.classList.add('border-black');
            updateGalleryNavigation();
        }

        function navigateGallery(direction) {
            const visibleThumbnails = getVisibleThumbnails();
            if (visibleThumbnails.length < 2) return;

            const currentIndex = visibleThumbnails.findIndex(thumbnail => thumbnail.classList.contains('border-black'));
            const nextIndex = (currentIndex + direction + visibleThumbnails.length) % visibleThumbnails.length;
            changeMainImage(visibleThumbnails[nextIndex]);
        }

        function showGalleryForColor(color) {
            const selectedGroup = galleryGroups.find(group => group.dataset.galleryColor === color)
                ?? galleryGroups.find(group => group.dataset.galleryColor === defaultGalleryColor);

            if (!selectedGroup) return;

            galleryGroups.forEach(group => {
                group.classList.toggle('hidden', group !== selectedGroup);
            });

            changeMainImage(selectedGroup.querySelector('.thumbnail'));
        }

        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                changeMainImage(this);
            });
        });

        previousImageButton?.addEventListener('click', function() {
            navigateGallery(-1);
        });

        nextImageButton?.addEventListener('click', function() {
            navigateGallery(1);
        });

        updateGalleryNavigation();

        // Variant selection visual feedback
        document.querySelectorAll('.size-option, .color-option, .stud-option').forEach(btn => {
            btn.addEventListener('click', function() {
                const parent = this.parentElement;
                parent.querySelectorAll('button').forEach(b => {
                    b.classList.remove('border-black', 'bg-gray-200');
                });
                this.classList.add('border-black', 'bg-gray-200');
            });
        });

        document.querySelectorAll('.color-option').forEach(btn => {
            btn.addEventListener('click', function() {
                showGalleryForColor(this.dataset.color);
            });
        });

        // Add to cart
        document.getElementById('add-to-cart')?.addEventListener('click', function() {
            const sizeBtn = document.querySelector('.size-option.border-black');
            const colorBtn = document.querySelector('.color-option.border-black');
            const studBtn = document.querySelector('.stud-option.border-black');
            const quantity = parseInt(qtyInput.value) || 1;
            const isShoe = @json($product->product_type === 'SHOE');

            if (!sizeBtn) {
                window.showToast('Vui lòng chọn size.', 'error');
                return;
            }

            if (!colorBtn) {
                window.showToast('Vui lòng chọn màu sắc.', 'error');
                return;
            }

            if (isShoe && !studBtn) {
                window.showToast('Vui lòng chọn loại đinh.', 'error');
                return;
            }

            const size = sizeBtn.dataset.size;
            const color = colorBtn.dataset.color;
            const studType = studBtn ? studBtn.dataset.stud : null;
            const variants = @json($product->variants);
            const variant = variants.find(item =>
                String(item.size) === size
                && item.color === color
                && (!isShoe || item.stud_type === studType)
            );

            if (!variant) {
                window.showToast('Biến thể sản phẩm đã chọn không tồn tại.', 'error');
                return;
            }

            fetch('{{ route("api.cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    product_id: {{ $product->id }},
                    variant_id: variant.id,
                    variant_type: isShoe ? 'shoe' : 'cloth',
                    size: size,
                    color: color,
                    stud_type: studType,
                    quantity: quantity,
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (window.updateCartCount) {
                        window.updateCartCount(data.cart_count);
                    }
                    if (window.loadCartMini) {
                        window.loadCartMini();
                    }
                    window.showToast(data.message || 'Đã thêm vào giỏ hàng.');
                } else {
                    window.showToast(data.message || 'Có lỗi xảy ra. Vui lòng thử lại.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
            });
        });

            // Mua ngay
document.getElementById('buy-now')?.addEventListener('click', function() {
    const sizeBtn = document.querySelector('.size-option.border-black');
    const colorBtn = document.querySelector('.color-option.border-black');
    const studBtn = document.querySelector('.stud-option.border-black');
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    
    if (!sizeBtn || !colorBtn) {
        window.showToast('Vui lòng chọn size và màu sắc.', 'error');
        return;
    }
    
    const size = sizeBtn.dataset.size;
    const color = colorBtn.dataset.color;
    const studType = studBtn ? studBtn.dataset.stud : null;
    
    // Thêm vào giỏ hàng trước khi chuyển đến checkout
    const productId = {{ $product->id }};
    
    fetch('{{ route("api.cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            product_id: productId,
            size: size,
            color: color,
            stud_type: studType,
            quantity: quantity,
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.updateCartCount) {
                window.updateCartCount(data.cart_count);
            }
            window.location.href = '{{ route("checkout") }}';
        } else {
            window.showToast(data.message || 'Có lỗi xảy ra. Vui lòng thử lại.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
    });
});


    });
</script>
@endpush

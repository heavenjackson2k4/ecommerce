@extends('layouts.customer')

@section('title', 'Thanh toán')

@section('breadcrumb')
    @include('customer.partials.breadcrumb', ['items' => [
        ['label' => 'Giỏ hàng', 'url' => route('cart.index')],
        ['label' => 'Thanh toán']
    ]])
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Thanh toán</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Form thông tin -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Thông tin giao hàng</h2>
                
                <form id="checkout-form">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên <span class="text-red-500">*</span></label>
                            <input type="text" name="customer_name" id="customer_name" value="{{ Auth::user()->name }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:border-transparent" required>
                            <p class="text-red-500 text-sm mt-1 hidden error-message" id="customer_name_error"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                            <input type="text" name="customer_phone" id="customer_phone" value="{{ Auth::user()->phone ?? '' }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:border-transparent" required>
                            <p class="text-red-500 text-sm mt-1 hidden error-message" id="customer_phone_error"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ giao hàng <span class="text-red-500">*</span></label>
                            <textarea name="shipping_address" id="shipping_address" rows="3" 
                                      class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:border-transparent" required></textarea>
                            <p class="text-red-500 text-sm mt-1 hidden error-message" id="shipping_address_error"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú (tùy chọn)</label>
                            <textarea name="note" id="note" rows="2" 
                                      class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:border-transparent"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phương thức thanh toán</label>
                            <select name="payment_method" id="payment_method" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:border-transparent">
                                <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                                <option value="banking" disabled>Chuyển khoản ngân hàng (Sắp có)</option>
                                <option value="momo" disabled>MoMo (Sắp có)</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tóm tắt đơn hàng -->
        <div>
            <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Tóm tắt đơn hàng</h2>
                
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @foreach($cart->items as $item)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">
                                {{ $item->product->name }} 
                                <span class="text-xs text-gray-400">x{{ $item->quantity }}</span>
                            </span>
                            <span class="font-medium text-gray-900">{{ number_format($item->price * $item->quantity, 0, ',', '.') }} ₫</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-200 mt-4 pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tạm tính</span>
                        <span class="font-medium text-gray-900">{{ number_format($cartTotal, 0, ',', '.') }} ₫</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Phí vận chuyển</span>
                        <span class="font-medium text-gray-900">0 ₫</span>
                    </div>
                    <div class="flex justify-between text-base font-bold pt-2 border-t border-gray-200">
                        <span>Tổng cộng</span>
                        <span id="order-total">{{ number_format($cartTotal, 0, ',', '.') }} ₫</span>
                    </div>
                </div>

                <button type="submit" form="checkout-form" id="place-order-btn" class="w-full mt-4 bg-black text-white px-6 py-3 rounded-lg hover:bg-gray-800 transition font-medium">
                    Đặt hàng
                </button>

                <div id="order-message" class="mt-3 hidden"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('place-order-btn');
    const messageDiv = document.getElementById('order-message');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang xử lý...';

        // Clear old errors
        document.querySelectorAll('.error-message').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        fetch('{{ route("checkout.place") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(data),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.className = 'mt-3 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg';
                messageDiv.innerHTML = `
                    ✅ ${data.message}<br>
                    Mã đơn hàng: <strong>${data.order_code}</strong>
                `;
                messageDiv.classList.remove('hidden');
                submitBtn.textContent = 'Đặt hàng thành công!';
                submitBtn.disabled = true;
                
                // Chuyển hướng sau 3 giây
                setTimeout(() => {
                    window.location.href = '/customer/orders';
                }, 3000);
            } else {
                if (data.errors) {
                    // Hiển thị lỗi validation
                    Object.keys(data.errors).forEach(key => {
                        const errorEl = document.getElementById(key + '_error');
                        const inputEl = document.getElementById(key);
                        if (errorEl) {
                            errorEl.textContent = data.errors[key][0];
                            errorEl.classList.remove('hidden');
                        }
                        if (inputEl) {
                            inputEl.classList.add('border-red-500');
                        }
                    });
                } else {
                    messageDiv.className = 'mt-3 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg';
                    messageDiv.textContent = '❌ ' + (data.message || 'Có lỗi xảy ra. Vui lòng thử lại.');
                    messageDiv.classList.remove('hidden');
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'Đặt hàng';
            }
        })
        .catch(error => {
            messageDiv.className = 'mt-3 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg';
            messageDiv.textContent = '❌ Có lỗi xảy ra. Vui lòng thử lại.';
            messageDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Đặt hàng';
            console.error('Error:', error);
        });
    });
});
</script>
@endsection
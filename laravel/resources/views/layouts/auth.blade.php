<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Đăng nhập') - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen flex items-center justify-center bg-slate-50">
        <div class="w-full max-w-6xl bg-white rounded-2xl shadow-xl overflow-hidden grid grid-cols-1 md:grid-cols-2">
            <!-- Cột trái - Banner hình ảnh -->
            <div class="hidden md:flex items-center justify-center p-8 bg-black/5 relative">
                <div class="absolute inset-0 bg-gradient-to-br from-slate-900/70 to-black/40 z-10"></div>
                <img src="{{ asset('images/auth-banner.jpg') }}" alt="Thời trang nam" class="absolute inset-0 w-full h-full object-cover">
                <div class="relative z-20 text-white text-center p-6">
                    <h2 class="text-4xl font-bold tracking-tight">Nâng tầm phong cách</h2>
                    <p class="mt-2 text-lg font-light">Thời trang nam hiện đại – Tinh tế và đẳng cấp</p>
                </div>
            </div>

            <!-- Cột phải - Form -->
            <div class="p-8 md:p-12 flex flex-col justify-center">
                <div class="mb-8">
                    <a href="/" class="text-2xl font-bold tracking-tight text-slate-900">LOGO</a>
                </div>
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
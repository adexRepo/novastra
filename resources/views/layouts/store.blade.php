<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bahan Pangan & Food Supply') · {{ $companySettings['company_name'] }} {{ $companySettings['brand_suffix'] }}</title>
    <meta name="description" content="{{ $companySettings['business_summary'] }}">
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="min-h-screen antialiased"
    data-products-url="{{ route('products.index') }}"
    data-checkout-url="{{ route('checkout') }}"
    data-cart-url="{{ route('cart') }}"
    data-analytics-url="{{ route('analytics.view') }}"
>
    @php
        $storeNavigation = [
            ['label' => 'Produk', 'route' => 'products.index', 'pattern' => 'products.*'],
            ['label' => 'Kategori', 'route' => 'categories.index', 'pattern' => 'categories.*'],
            ['label' => 'Tentang', 'route' => 'about', 'pattern' => 'about'],
            ['label' => 'FAQ', 'route' => 'faq', 'pattern' => 'faq'],
            ['label' => 'Kontak', 'route' => 'contact', 'pattern' => 'contact'],
        ];
    @endphp

    <div class="bg-ink px-4 py-2 text-center text-xs text-white">Gratis pengiriman untuk pesanan di atas Rp250.000</div>
    <header class="sticky top-0 z-40 border-b border-ink/10 bg-paper/95 backdrop-blur">
        <div class="page-shell flex h-16 items-center justify-between gap-2 sm:gap-5">
            <a href="{{ route('home') }}" aria-label="{{ $companySettings['company_name'] }} {{ $companySettings['brand_suffix'] }} — Beranda">
                <x-brand-logo />
            </a>

            <nav aria-label="Navigasi utama" class="hidden items-center gap-7 md:flex">
                @foreach ($storeNavigation as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="text-sm hover:text-brand-deep {{ request()->routeIs($item['pattern']) ? 'font-semibold text-brand-deep' : '' }}"
                        @if (request()->routeIs($item['pattern'])) aria-current="page" @endif
                    >{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-1 sm:gap-2">
                <details class="relative md:hidden">
                    <summary class="flex size-10 cursor-pointer list-none items-center justify-center rounded-full hover:bg-sand" aria-label="Buka menu utama">☰</summary>
                    <nav aria-label="Navigasi utama mobile" class="absolute right-0 top-12 grid w-56 gap-1 rounded-2xl border border-ink/10 bg-white p-3 shadow-xl">
                        @foreach ($storeNavigation as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                class="rounded-xl px-4 py-3 text-sm hover:bg-sand {{ request()->routeIs($item['pattern']) ? 'bg-sand font-semibold text-brand-deep' : '' }}"
                                @if (request()->routeIs($item['pattern'])) aria-current="page" @endif
                            >{{ $item['label'] }}</a>
                        @endforeach
                    </nav>
                </details>

                @auth
                    <a class="btn-outline min-h-9 px-3 sm:px-4" href="{{ auth()->user()->role === 'ADMIN' ? route('admin.dashboard') : route('orders.index') }}">{{ auth()->user()->role === 'ADMIN' ? 'Admin' : 'Pesanan' }}</a>
                @else
                    <a class="btn-outline min-h-9 px-3 sm:px-4" href="{{ route('login') }}">Masuk</a>
                @endauth
                <a class="relative rounded-full p-2.5 hover:bg-sand" href="{{ route('cart') }}" aria-label="Keranjang">🛒<span data-cart-count class="ml-1 text-xs font-bold">0</span></a>
            </div>
        </div>
    </header>

    @if (session('success'))
        <div class="page-shell pt-5"><p class="rounded-xl border border-brand/30 bg-sand p-3 text-sm text-ink" role="status">{{ session('success') }}</p></div>
    @endif
    @if ($errors->any())
        <div class="page-shell pt-5"><p class="rounded-xl bg-red-50 p-3 text-sm text-red-700" role="alert">{{ $errors->first() }}</p></div>
    @endif

    @yield('content')

    <footer class="mt-20 bg-ink py-14 text-white">
        <div class="page-shell grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div><x-brand-logo class="text-white" /><p class="mt-4 text-sm leading-6 text-white/60">{{ $companySettings['tagline'] }}</p></div>
            <div><p class="eyebrow text-white/40">Belanja</p><div class="mt-4 space-y-2 text-sm text-white/70"><a class="block" href="{{ route('products.index') }}">Semua produk</a><a class="block" href="{{ route('categories.index') }}">Kategori</a></div></div>
            <div><p class="eyebrow text-white/40">Perusahaan</p><div class="mt-4 space-y-2 text-sm text-white/70"><a class="block" href="{{ route('about') }}">Tentang kami</a><a class="block" href="{{ route('faq') }}">FAQ</a><a class="block" href="{{ route('contact') }}">Kontak & tender</a></div></div>
            <div><p class="eyebrow text-white/40">Legal & kontak</p><p class="mt-4 text-sm leading-6 text-white/70">NIB {{ $companySettings['nib'] }}<br>{{ $companySettings['location'] }}<br><a class="break-all" href="mailto:{{ $companySettings['email'] }}">{{ $companySettings['email'] }}</a></p></div>
        </div>
    </footer>
</body>
</html>

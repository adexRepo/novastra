<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bahan Masak Segar') · Novastra</title>
    <meta name="description" content="Bahan masak segar, bersih, dan praktis untuk keluarga.">
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen antialiased">
    <div class="bg-ink px-4 py-2 text-center text-xs text-white">Gratis pengiriman untuk pesanan di atas Rp250.000</div>
    <header class="sticky top-0 z-40 border-b border-ink/10 bg-paper/95 backdrop-blur">
        <div class="page-shell flex h-16 items-center justify-between gap-5">
            <a href="{{ route('home') }}" aria-label="Novastra Global Supply — Beranda">
                <x-brand-logo />
            </a>
            <nav aria-label="Navigasi utama" class="hidden items-center gap-7 md:flex">
                <a href="{{ route('products.index') }}" class="text-sm hover:text-brand-deep">Produk</a><a href="{{ route('categories.index') }}" class="text-sm hover:text-brand-deep">Kategori</a><a href="{{ route('about') }}" class="text-sm hover:text-brand-deep">Tentang</a><a href="{{ route('contact') }}" class="text-sm hover:text-brand-deep">Kontak</a>
            </nav>
            <div class="flex items-center gap-2">
                @auth
                    <a class="btn-outline min-h-9 px-4" href="{{ auth()->user()->role === 'ADMIN' ? route('admin.dashboard') : route('orders.index') }}">Akun</a>
                @else
                    <a class="btn-outline min-h-9 px-4" href="{{ route('login') }}">Masuk</a>
                @endauth
                <a class="relative rounded-full p-2.5 hover:bg-sand" href="{{ route('cart') }}" aria-label="Keranjang">🛒<span data-cart-count class="ml-1 text-xs font-bold">0</span></a>
            </div>
        </div>
    </header>
    @if(session('success'))<div class="page-shell pt-5"><p class="rounded-xl border border-brand/30 bg-sand p-3 text-sm text-ink">{{ session('success') }}</p></div>@endif
    @if($errors->any())<div class="page-shell pt-5"><p class="rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</p></div>@endif
    @yield('content')
    <footer class="mt-20 bg-ink py-14 text-white"><div class="page-shell grid gap-10 sm:grid-cols-2 lg:grid-cols-4"><div><x-brand-logo class="text-white" /><p class="mt-4 text-sm leading-6 text-white/60">Bahan segar untuk masak sehari-hari.</p></div><div><p class="eyebrow text-white/40">Belanja</p><div class="mt-4 space-y-2 text-sm text-white/70"><a class="block" href="{{ route('products.index') }}">Semua produk</a><a class="block" href="{{ route('categories.index') }}">Kategori</a></div></div><div><p class="eyebrow text-white/40">Perusahaan</p><div class="mt-4 space-y-2 text-sm text-white/70"><a class="block" href="{{ route('about') }}">Tentang kami</a><a class="block" href="{{ route('contact') }}">Kontak</a></div></div><div><p class="eyebrow text-white/40">Hubungi</p><p class="mt-4 text-sm text-white/70">halo@novastra.id<br>Senin–Sabtu, 09.00–17.00</p></div></div></footer>
</body></html>

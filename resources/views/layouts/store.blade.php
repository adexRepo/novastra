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

    @if (filled($companySettings['announcement_text']))
        <div data-announcement-banner class="bg-ink px-4 py-2 text-center text-xs text-white">{{ $companySettings['announcement_text'] }}</div>
    @endif
    <header class="sticky top-0 z-40 border-b border-ink/10 bg-paper/95 backdrop-blur">
        <div class="page-shell flex h-16 items-center justify-between gap-2 sm:gap-5">
            <a href="{{ route('home') }}" aria-label="{{ $companySettings['company_name'] }} {{ $companySettings['brand_suffix'] }} — Beranda">
                <x-brand-logo class="[&>span]:hidden min-[420px]:[&>span]:flex" />
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
                    @php
                        $account = auth()->user();
                        $isAdmin = $account->role === 'ADMIN';
                    @endphp
                    <details class="group relative" data-auth-state="{{ $isAdmin ? 'admin' : 'customer' }}">
                        <summary
                            class="flex min-h-11 cursor-pointer list-none items-center gap-2 rounded-full border border-ink/15 bg-white py-1.5 pl-1.5 pr-3 text-left transition-[background-color,border-color,transform] hover:border-brand hover:bg-sand/50 active:scale-[.96]"
                            aria-label="Akun {{ $isAdmin ? 'admin' : 'customer' }}: {{ $account->name }}"
                        >
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-ink text-white" aria-hidden="true">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21a8 8 0 0 0-16 0" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </span>
                            <span class="min-w-0 leading-none">
                                <span class="block text-[10px] font-semibold uppercase tracking-[.12em] text-brand-deep">{{ $isAdmin ? 'Admin' : 'Customer' }}</span>
                                <span class="mt-1 hidden max-w-24 truncate text-xs font-semibold text-ink sm:block">{{ $account->name }}</span>
                            </span>
                            <svg class="size-3.5 text-ink/45 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </summary>

                        <div class="absolute right-[-3.25rem] top-[calc(100%+.65rem)] w-[min(18rem,calc(100vw-1.5rem))] rounded-2xl border border-ink/10 bg-white p-3 shadow-[0_18px_50px_rgba(3,23,48,.16)] sm:right-0">
                            <div class="rounded-xl bg-sand/65 px-4 py-3">
                                <p class="text-[10px] font-semibold uppercase tracking-[.14em] text-brand-deep">Login sebagai {{ $isAdmin ? 'admin' : 'customer' }}</p>
                                <p class="mt-2 truncate text-sm font-semibold text-ink">{{ $account->name }}</p>
                                <p class="mt-1 truncate text-xs text-ink/55">{{ $account->email }}</p>
                            </div>
                            <div class="mt-2 grid gap-1">
                                <a class="flex min-h-11 items-center rounded-xl px-4 text-sm font-semibold transition-colors hover:bg-sand" href="{{ $isAdmin ? route('admin.dashboard') : route('orders.index') }}">
                                    {{ $isAdmin ? 'Buka dashboard admin' : 'Lihat pesanan saya' }}
                                </a>
                                <form method="post" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="flex min-h-11 w-full items-center rounded-xl px-4 text-left text-sm font-semibold text-red-700 transition-colors hover:bg-red-50" type="submit">Keluar dari akun</button>
                                </form>
                            </div>
                        </div>
                    </details>
                @else
                    <a data-auth-state="guest" class="btn-outline min-h-11 px-4 active:scale-[.96] transition-transform" href="{{ route('login') }}">Masuk</a>
                @endauth
                <a class="relative rounded-full p-2.5 hover:bg-sand" href="{{ route('cart') }}" aria-label="Keranjang">🛒<span data-cart-count class="ml-1 text-xs font-bold">0</span></a>
            </div>
        </div>
    </header>

    @if (session('success'))
        <div class="page-shell pt-5"><p class="rounded-xl border border-brand/30 bg-sand p-3 text-sm text-ink" role="status">{{ session('success') }}</p></div>
    @endif
    @if ($errors->any())
        <div class="page-shell pt-5">
            <div class="rounded-xl bg-red-50 p-4 text-sm text-red-700" role="alert">
                <p class="font-semibold">Periksa kembali informasi berikut:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @yield('content')

    <footer class="mt-20 bg-ink py-14 text-white">
        <div class="page-shell grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div><x-brand-logo class="text-white" /><p class="mt-4 text-sm leading-6 text-white/60">{{ $companySettings['tagline'] }}</p></div>
            <div><p class="eyebrow text-white/40">Belanja</p><div class="mt-4 space-y-2 text-sm text-white/70"><a class="block" href="{{ route('products.index') }}">Semua produk</a><a class="block" href="{{ route('categories.index') }}">Kategori</a></div></div>
            <div><p class="eyebrow text-white/40">Perusahaan</p><div class="mt-4 space-y-2 text-sm text-white/70"><a class="block" href="{{ route('about') }}">Tentang kami</a><a class="block" href="{{ route('faq') }}">FAQ</a><a class="block" href="{{ route('contact') }}">Kontak & tender</a></div></div>
            <div><p class="eyebrow text-white/40">Legal & kontak</p><p class="mt-4 text-sm leading-6 text-white/70">NIB {{ $companySettings['nib'] }}<br>{{ $companySettings['location'] }}<br><a class="break-all" href="mailto:{{ $companySettings['email'] }}">{{ $companySettings['email'] }}</a></p></div>
        </div>
        <div class="page-shell mt-12 flex flex-col gap-2 border-t border-white/10 pt-6 text-xs text-white/45 sm:flex-row sm:items-center sm:justify-between">
            <p>© 2026 {{ $companySettings['legal_name'] }}. Hak cipta dilindungi.</p>
            <p>{{ $companySettings['company_name'] }} {{ $companySettings['brand_suffix'] }}</p>
        </div>
    </footer>
</body>
</html>

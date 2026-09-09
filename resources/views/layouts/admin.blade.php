<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') · {{ $companySettings['company_name'] }}</title>
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-shell bg-paper">
    @php
        $adminNavigation = [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard'],
            ['label' => 'Produk', 'route' => 'admin.products.index', 'pattern' => 'admin.products.*'],
            ['label' => 'Kategori', 'route' => 'admin.categories.index', 'pattern' => 'admin.categories.*'],
            ['label' => 'Pesanan', 'route' => 'admin.orders.index', 'pattern' => 'admin.orders.*'],
            ['label' => 'Pembayaran', 'route' => 'admin.payments.index', 'pattern' => 'admin.payments.*'],
            ['label' => 'Feedback', 'route' => 'admin.feedback.index', 'pattern' => 'admin.feedback.*'],
            ['label' => 'Testimoni', 'route' => 'admin.testimonials.index', 'pattern' => 'admin.testimonials.*'],
            ['label' => 'FAQ', 'route' => 'admin.faqs.index', 'pattern' => 'admin.faqs.*'],
            ['label' => 'Laporan', 'route' => 'admin.reports.index', 'pattern' => 'admin.reports.*'],
            ['label' => 'Informasi Situs', 'route' => 'admin.settings.index', 'pattern' => 'admin.settings.*'],
        ];
    @endphp

    <div class="min-h-screen lg:grid lg:grid-cols-[240px_1fr]">
        <aside class="bg-ink p-5 text-white lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto">
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" aria-label="{{ $companySettings['company_name'] }} Admin — Dashboard">
                    <x-brand-logo class="text-white" />
                </a>
                <a class="text-xs text-white/70 lg:hidden" href="#content">Lewati ke konten</a>
            </div>

            <nav aria-label="Menu admin" class="mt-8 grid grid-cols-2 gap-1 sm:grid-cols-4 lg:grid-cols-1">
                @foreach ($adminNavigation as $item)
                    <a
                        class="admin-link"
                        href="{{ route($item['route']) }}"
                        @if (request()->routeIs($item['pattern'])) aria-current="page" @endif
                    >{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <form class="mt-8 border-t border-white/10 pt-5" method="post" action="{{ route('logout') }}">
                @csrf
                <button class="admin-link w-full text-left">Keluar</button>
            </form>
        </aside>

        <main id="content" class="min-w-0 p-4 sm:p-7 lg:p-10">
            @if (session('success'))
                <p class="mb-5 rounded-xl border border-brand/30 bg-sand p-3 text-sm text-ink">{{ session('success') }}</p>
            @endif
            @if ($errors->any())
                <p class="mb-5 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</p>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>

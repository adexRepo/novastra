@extends('layouts.store')

@section('content')
    <section class="page-shell py-8 sm:py-12">
        <div class="relative min-h-[560px] overflow-hidden rounded-3xl bg-ink">
            <img src="/uploads/products/novastra-fresh-collection.webp" alt="Koleksi bahan masak segar {{ $companySettings['company_name'] }}" class="absolute inset-0 h-full w-full object-cover opacity-70">
            <div class="absolute inset-0 bg-gradient-to-r from-ink via-ink/70 to-transparent"></div>
            <div class="relative flex min-h-[560px] max-w-2xl flex-col justify-end p-7 text-white sm:p-12 lg:p-16">
                <p class="eyebrow text-white/60">{{ $companySettings['hero_eyebrow'] }}</p>
                <h1 class="mt-4 font-display text-[2.5rem] font-semibold leading-[1.02] tracking-[-.045em] sm:text-[4rem]">{{ $companySettings['hero_title'] }}</h1>
                <p class="mt-6 max-w-lg leading-7 text-white/75">{{ $companySettings['hero_description'] }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a class="btn bg-white text-ink hover:bg-sand" href="{{ route('products.index') }}">Lihat semua produk</a>
                    <a class="btn-outline border-white/30 bg-transparent text-white hover:border-brand hover:text-brand" href="{{ route('contact') }}">Kebutuhan bisnis</a>
                </div>
            </div>
        </div>
    </section>

    <section class="page-shell py-14">
        <div class="flex items-end justify-between">
            <div><p class="eyebrow">Pilihan hari ini</p><h2 class="section-title mt-3">Bahan segar favorit.</h2></div>
            <a class="hidden text-sm font-semibold sm:block" href="{{ route('products.index') }}">Lihat semuanya →</a>
        </div>
        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($featured as $product)
                <x-product-card :product="$product" />
            @empty
                <p>Produk unggulan segera hadir.</p>
            @endforelse
        </div>
    </section>

    <section class="bg-sand/60 py-16">
        <div class="page-shell">
            <p class="eyebrow">Belanja per kategori</p>
            <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($categories as $category)
                    <a href="{{ route('categories.show', $category) }}" class="card p-6 transition hover:-translate-y-1 hover:border-brand">
                        <span class="text-3xl">{{ ['ayam-daging'=>'🍗','ikan-seafood'=>'🐟','sayur-buah'=>'🥬','bumbu-rempah'=>'🌶️'][$category->slug] ?? '🥕' }}</span>
                        <h3 class="mt-5 font-display text-2xl">{{ $category->name }}</h3>
                        <p class="mt-2 text-sm text-ink/55">{{ $category->products_count }} produk</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="page-shell py-16 sm:py-20">
        <div class="overflow-hidden rounded-3xl bg-ink text-white">
            <div class="grid lg:grid-cols-[1.1fr_.9fr]">
                <div class="p-7 sm:p-10 lg:p-14">
                    <p class="eyebrow text-brand">Legal & corporate readiness</p>
                    <h2 class="mt-4 max-w-xl font-display text-[2rem] font-semibold leading-[1.08] tracking-[-.035em] sm:text-[2.75rem]">Mitra pengadaan yang dibangun untuk dipercaya.</h2>
                    <p class="mt-5 max-w-xl leading-7 text-white/65">{{ $companySettings['legal_name'] }} melayani pengadaan dan distribusi bahan pangan untuk dapur, katering, restoran, industri makanan, dan institusi.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a class="btn" href="{{ route('about') }}">Tentang perusahaan</a>
                        <a class="btn-outline border-white/25 bg-transparent text-white hover:border-brand hover:text-brand" href="{{ route('contact') }}">Diskusikan kebutuhan</a>
                    </div>
                </div>
                <dl class="grid border-t border-white/10 sm:grid-cols-2 lg:border-l lg:border-t-0">
                    <div class="border-b border-white/10 p-7 sm:border-r lg:p-9"><dt class="text-xs uppercase tracking-[.18em] text-white/45">Legalitas</dt><dd class="mt-3 text-lg font-semibold">NIB {{ $companySettings['nib'] }}</dd><dd class="mt-1 text-sm text-white/55">Terdaftar melalui OSS</dd></div>
                    <div class="border-b border-white/10 p-7 lg:p-9"><dt class="text-xs uppercase tracking-[.18em] text-white/45">Domisili</dt><dd class="mt-3 text-lg font-semibold">{{ $companySettings['location'] }}</dd><dd class="mt-1 text-sm text-white/55">Indonesia</dd></div>
                    <div class="border-b border-white/10 p-7 sm:border-b-0 sm:border-r lg:p-9"><dt class="text-xs uppercase tracking-[.18em] text-white/45">Bidang usaha</dt><dd class="mt-3 text-lg font-semibold">Food supply</dd><dd class="mt-1 text-sm text-white/55">Procurement & distribution</dd></div>
                    <div class="p-7 lg:p-9"><dt class="text-xs uppercase tracking-[.18em] text-white/45">Komitmen</dt><dd class="mt-3 text-lg font-semibold">Kualitas & K3L</dd><dd class="mt-1 text-sm text-white/55">Proses yang bertanggung jawab</dd></div>
                </dl>
            </div>
        </div>
    </section>

    @if($testimonials->isNotEmpty())
        <section class="bg-sand/60 py-16 sm:py-20" data-carousel>
            <div class="page-shell">
                <div class="flex items-end justify-between gap-5">
                    <div><p class="eyebrow">Dipercaya pelanggan</p><h2 class="section-title mt-3">Cerita dari dapur mereka.</h2></div>
                    @if($testimonials->count() > 1)
                        <div class="flex gap-2"><button class="btn-outline size-11 px-0" type="button" data-carousel-prev aria-label="Testimoni sebelumnya">←</button><button class="btn-outline size-11 px-0" type="button" data-carousel-next aria-label="Testimoni berikutnya">→</button></div>
                    @endif
                </div>
                <div class="mt-9 flex snap-x snap-mandatory gap-4 overflow-x-auto pb-3 [scrollbar-width:none]" data-carousel-track>
                    @foreach($testimonials as $testimonial)
                        <article class="card flex-none basis-[88%] snap-start p-6 sm:basis-[calc(50%-0.5rem)] sm:p-8 lg:basis-[calc(33.333%-0.7rem)]">
                            @if($testimonial->rating)<p class="text-amber-500" aria-label="{{ $testimonial->rating }} dari 5 bintang">{{ str_repeat('★', $testimonial->rating) }}</p>@endif
                            <blockquote class="mt-5 font-serif text-lg leading-8 text-ink/70">“{{ $testimonial->content }}”</blockquote>
                            <footer class="mt-7 border-t pt-5"><strong>{{ $testimonial->display_name }}</strong>@if($testimonial->customer_type)<span class="mt-1 block text-sm text-ink/45">{{ $testimonial->customer_type }}</span>@endif</footer>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($homeFaqs->isNotEmpty())
        <section class="page-shell py-16 sm:py-20">
            <div class="grid gap-10 lg:grid-cols-[.7fr_1.3fr]">
                <div><p class="eyebrow">Pertanyaan umum</p><h2 class="section-title mt-3">Yang sering ditanyakan.</h2><a class="btn-outline mt-7" href="{{ route('faq') }}">Lihat semua FAQ</a></div>
                <div class="divide-y border-y border-ink/10">
                    @foreach($homeFaqs as $faq)
                        <details class="group py-5"><summary class="flex cursor-pointer list-none items-center justify-between gap-5 font-semibold"><span>{{ $faq->question }}</span><span class="text-xl text-brand-deep transition group-open:rotate-45">+</span></summary><p class="mt-4 max-w-2xl whitespace-pre-line leading-7 text-ink/60">{{ $faq->answer }}</p></details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

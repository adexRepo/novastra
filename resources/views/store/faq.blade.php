@extends('layouts.store')

@section('title', 'FAQ')

@section('content')
    <section class="page-shell py-12 sm:py-16">
        <p class="eyebrow">Bantuan pelanggan</p>
        <h1 class="section-title mt-3">Pertanyaan yang sering diajukan.</h1>
        <p class="mt-5 max-w-2xl leading-7 text-ink/60">Temukan jawaban mengenai produk, pemesanan, pengiriman, dan kerja sama pengadaan.</p>
        <div class="mt-10 max-w-4xl divide-y border-y border-ink/10">
            @forelse($faqs as $faq)
                <details class="group py-6"><summary class="flex cursor-pointer list-none items-center justify-between gap-6 text-lg font-semibold"><span>{{ $faq->question }}</span><span class="text-2xl text-brand-deep transition group-open:rotate-45">+</span></summary><p class="mt-4 max-w-3xl whitespace-pre-line leading-7 text-ink/60">{{ $faq->answer }}</p></details>
            @empty
                <p class="py-10 text-ink/50">FAQ belum tersedia. Silakan hubungi kami jika Anda memiliki pertanyaan.</p>
            @endforelse
        </div>
        <div class="mt-7">{{ $faqs->links() }}</div>
    </section>
@endsection

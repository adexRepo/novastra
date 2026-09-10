@extends('layouts.store')

@section('title', 'Kontak')

@section('content')
    <section class="page-shell py-16">
        <div class="grid gap-10 lg:grid-cols-2">
            <div>
                <p class="eyebrow">Hubungi kami</p>
                <h1 class="section-title mt-3">Belanja, pengadaan rutin, atau kebutuhan tender.</h1>
                <p class="mt-5 max-w-lg leading-7 text-ink/60">Kami siap membantu pertanyaan produk dan pesanan, sekaligus mendiskusikan volume, spesifikasi, jadwal, kemasan, serta pola pengiriman untuk kebutuhan bisnis.</p>
                <div class="mt-8 space-y-4 text-sm">
                    <p><span class="block text-ink/45">Email perusahaan</span><a class="mt-1 inline-block break-all font-semibold text-brand-deep" href="mailto:{{ $companySettings['email'] }}">{{ $companySettings['email'] }}</a></p>
                    <p><span class="block text-ink/45">Nomor telepon</span><a class="mt-1 inline-block font-semibold" href="tel:{{ preg_replace('/[^0-9+]/', '', $companySettings['phone']) }}">{{ $companySettings['phone'] }}</a></p>
                    <p><span class="block text-ink/45">Domisili</span><span class="mt-1 inline-block font-semibold">{{ $companySettings['location'] }}</span></p>
                    <p><span class="block text-ink/45">Jam layanan</span><span class="mt-1 inline-block font-semibold">{{ $companySettings['operating_hours'] }}</span></p>
                </div>
                <a class="btn mt-8" href="https://wa.me/{{ $companySettings['whatsapp'] }}">WhatsApp {{ $companySettings['company_name'] }}</a>
            </div>
            <form class="card p-6 sm:p-8" method="post" action="{{ route('contact.store') }}">
                @csrf
                <label>Nama<input class="field" name="name" value="{{ old('name') }}" required></label>
                <label class="mt-4 block">Email<input class="field" type="email" name="email" value="{{ old('email') }}" required></label>
                <label class="mt-4 block">Pesan<textarea class="field min-h-32 py-3" name="message" required>{{ old('message') }}</textarea></label>
                <button class="btn mt-5">Kirim pesan</button>
            </form>
        </div>
    </section>
@endsection

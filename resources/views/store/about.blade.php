@extends('layouts.store')

@section('title', 'Tentang Kami')

@section('content')
    <section class="page-shell py-12 sm:py-16">
        <div class="grid items-stretch gap-6 lg:grid-cols-[1.2fr_.8fr]">
            <div class="rounded-3xl bg-sand p-7 sm:p-10 lg:p-14">
                <p class="eyebrow">Tentang {{ $companySettings['company_name'] }}</p>
                <h1 class="section-title mt-4 max-w-3xl">{{ $companySettings['tagline'] }}</h1>
                <p class="mt-7 max-w-2xl text-lg leading-8 text-ink/65">{{ $companySettings['business_summary'] }}</p>
                <p class="mt-4 max-w-2xl leading-7 text-ink/60">Portofolio kami mencakup ayam dan produk unggas, ikan, telur, beras, minyak, serta kebutuhan pangan pendukung lainnya. Jaringan pemasok dan proses distribusi kami dikembangkan untuk melayani kebutuhan rutin maupun volume yang lebih besar.</p>
            </div>
            <div class="flex flex-col justify-between rounded-3xl bg-ink p-7 text-white sm:p-10">
                <x-brand-logo class="text-white" />
                <div class="mt-16">
                    <p class="text-sm leading-6 text-white/55">Badan usaha</p>
                    <p class="mt-2 text-2xl font-semibold">{{ $companySettings['legal_name'] }}</p>
                    <p class="mt-5 text-sm leading-6 text-white/55">{{ $companySettings['legal_status'] }}<br>{{ $companySettings['location'] }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="page-shell py-10 sm:py-16">
        <div class="max-w-2xl"><p class="eyebrow">Prinsip kerja</p><h2 class="section-title mt-3">Profesional hari ini. Terpercaya untuk jangka panjang.</h2></div>
        <div class="mt-9 grid gap-4 md:grid-cols-3">
            <article class="card p-6 sm:p-8"><span class="text-sm font-semibold text-brand-deep">01</span><h3 class="mt-5 font-display text-2xl">Akuntabilitas</h3><p class="mt-3 leading-7 text-ink/60">Bertanggung jawab terhadap komitmen, proses, dan hasil kerja.</p></article>
            <article class="card p-6 sm:p-8"><span class="text-sm font-semibold text-brand-deep">02</span><h3 class="mt-5 font-display text-2xl">Kualitas</h3><p class="mt-3 leading-7 text-ink/60">Menjaga kesesuaian produk, jumlah, kondisi, dan spesifikasi.</p></article>
            <article class="card p-6 sm:p-8"><span class="text-sm font-semibold text-brand-deep">03</span><h3 class="mt-5 font-display text-2xl">Kemitraan</h3><p class="mt-3 leading-7 text-ink/60">Membangun hubungan bisnis yang sehat dan berkelanjutan.</p></article>
        </div>
    </section>

    <section class="bg-ink py-16 text-white sm:py-20">
        <div class="page-shell grid gap-12 lg:grid-cols-2">
            <div>
                <p class="eyebrow text-brand">Visi</p>
                <h2 class="mt-4 font-display text-3xl font-semibold leading-[1.12] tracking-[-.025em] sm:text-[2.75rem]">Menjadi perusahaan distribusi dan pengadaan pangan yang profesional, terpercaya, dan berdaya saing nasional.</h2>
            </div>
            <div>
                <p class="eyebrow text-brand">Misi</p>
                <ul class="mt-5 space-y-4 text-white/70">
                    <li class="border-b border-white/10 pb-4">Menyediakan bahan pangan yang berkualitas, segar, dan aman dikonsumsi.</li>
                    <li class="border-b border-white/10 pb-4">Menjaga konsistensi pasokan serta ketepatan waktu pengiriman.</li>
                    <li class="border-b border-white/10 pb-4">Menerapkan proses operasional yang higienis dan profesional.</li>
                    <li class="border-b border-white/10 pb-4">Membangun kerja sama jangka panjang dengan integritas dan transparansi.</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="page-shell py-16 sm:py-20">
        <p class="eyebrow">Alur pengadaan</p>
        <h2 class="section-title mt-3">Dari sumber hingga pengiriman.</h2>
        <ol class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ([['Seleksi', 'Pemasok dan ketersediaan'], ['Verifikasi', 'Spesifikasi, jumlah, jadwal'], ['Pemeriksaan', 'Kondisi dan kemasan'], ['Penanganan', 'Sesuai karakteristik produk'], ['Pengiriman', 'Koordinasi dengan pelanggan']] as [$title, $description])
                <li class="card p-5"><span class="flex size-8 items-center justify-center rounded-full bg-brand text-xs font-bold text-ink">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3 class="mt-5 font-semibold">{{ $title }}</h3><p class="mt-2 text-sm leading-6 text-ink/55">{{ $description }}</p></li>
            @endforeach
        </ol>
    </section>

    <section class="bg-sand/60 py-16 sm:py-20">
        <div class="page-shell grid gap-12 lg:grid-cols-[.8fr_1.2fr]">
            <div><p class="eyebrow">Legalitas usaha</p><h2 class="section-title mt-3">Identitas perusahaan yang jelas.</h2><p class="mt-5 leading-7 text-ink/60">Informasi berikut diringkas dari dokumen resmi perusahaan. Dokumen legal lengkap tersedia bagi calon mitra melalui permintaan resmi.</p><a class="btn mt-7" href="{{ route('contact') }}">Hubungi tim {{ $companySettings['company_name'] }}</a></div>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="card p-6"><dt class="text-xs uppercase tracking-[.18em] text-ink/45">Nomor Induk Berusaha</dt><dd class="mt-3 text-xl font-semibold">{{ $companySettings['nib'] }}</dd><dd class="mt-2 text-sm text-ink/55">Diterbitkan melalui OSS pada {{ $companySettings['nib_issued_at'] }}.</dd></div>
                <div class="card p-6"><dt class="text-xs uppercase tracking-[.18em] text-ink/45">Bidang utama</dt><dd class="mt-3 text-xl font-semibold">Pengadaan bahan pangan</dd><dd class="mt-2 text-sm text-ink/55">Perdagangan, supply, dan distribusi.</dd></div>
                <div class="card p-6"><dt class="text-xs uppercase tracking-[.18em] text-ink/45">Domisili</dt><dd class="mt-3 text-xl font-semibold">{{ $companySettings['location'] }}</dd><dd class="mt-2 text-sm text-ink/55">Indonesia.</dd></div>
                <div class="card p-6"><dt class="text-xs uppercase tracking-[.18em] text-ink/45">Kepatuhan</dt><dd class="mt-3 text-xl font-semibold">Komitmen K3L</dd><dd class="mt-2 text-sm text-ink/55">Pernyataan mandiri tersimpan melalui sistem OSS.</dd></div>
            </dl>
        </div>
    </section>

    <section class="page-shell py-16 text-center sm:py-20">
        <p class="eyebrow">Kebutuhan bisnis & institusi</p>
        <h2 class="section-title mx-auto mt-3 max-w-3xl">Pasokan rutin maupun volume besar dapat dibahas sesuai kebutuhan operasional.</h2>
        <p class="mx-auto mt-5 max-w-2xl leading-7 text-ink/60">Kami melayani dapur dan katering, restoran dan F&B, industri makanan, serta institusi atau program pangan.</p>
        <a class="btn mt-8" href="{{ route('contact') }}">Mulai percakapan</a>
    </section>
@endsection

@extends('layouts.store')

@section('title', 'Daftar Pelanggan')

@section('content')
    <section class="page-shell py-12">
        <div class="mx-auto grid max-w-5xl overflow-hidden rounded-3xl bg-white shadow-xl lg:grid-cols-[0.85fr_1.15fr]">
            <div class="relative min-h-64 bg-ink">
                <img class="absolute inset-0 h-full w-full object-cover opacity-55" src="/uploads/products/novastra-fresh-collection.webp" alt="Bahan masak segar">
                <div class="relative flex h-full items-end p-8 text-white sm:p-10">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/60">Akun pribadi</p>
                        <h1 class="mt-3 font-display text-4xl">Pesanan dan alamat Anda tetap privat.</h1>
                    </div>
                </div>
            </div>

            <form class="p-7 sm:p-10" method="post" action="{{ route('register.store') }}">
                @csrf
                <p class="eyebrow">Pelanggan baru</p>
                <h2 class="mt-3 font-display text-4xl">Buat akun.</h2>
                <p class="mt-3 text-sm leading-6 text-ink/60">Gunakan akun pribadi untuk checkout dan melacak pesanan dengan aman.</p>

                <div class="mt-7 grid gap-5 sm:grid-cols-2">
                    <label class="sm:col-span-2">Nama lengkap
                        <input class="field" name="name" value="{{ old('name') }}" autocomplete="name" required>
                    </label>
                    <label>Username
                        <input class="field" name="username" value="{{ old('username') }}" autocomplete="username" pattern="[a-zA-Z0-9._-]+" required>
                    </label>
                    <label>Email
                        <input class="field" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                    </label>
                    <label>Password
                        <input class="field" name="password" type="password" minlength="8" autocomplete="new-password" required>
                    </label>
                    <label>Ulangi password
                        <input class="field" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
                    </label>
                </div>

                <button class="btn mt-7 w-full">Daftar dan lanjut checkout</button>
                <p class="mt-5 text-center text-sm text-ink/60">Sudah punya akun? <a class="font-semibold text-brand" href="{{ route('login') }}">Masuk</a></p>
            </form>
        </div>
    </section>
@endsection

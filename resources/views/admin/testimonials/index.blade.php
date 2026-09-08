@extends('layouts.admin')

@section('title', 'Testimoni')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="eyebrow">Konten publik</p><h1 class="section-title mt-3">Testimoni.</h1><p class="mt-4 text-sm text-ink/55">Hanya testimoni Published dan Featured yang tampil di homepage.</p></div>
        <a class="btn" href="{{ route('admin.testimonials.create') }}">Tambah testimoni</a>
    </div>
    <div class="card mt-8 overflow-x-auto">
        <table class="w-full min-w-[820px] text-left text-sm">
            <thead><tr class="text-ink/50"><th class="p-4">Nama</th><th>Rating</th><th>Isi</th><th>Status</th><th>Urutan</th><th><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>
                @forelse($testimonials as $testimonial)
                    <tr class="border-t align-top">
                        <td class="p-4"><strong>{{ $testimonial->display_name }}</strong>@if($testimonial->customer_type)<span class="mt-1 block text-xs text-ink/45">{{ $testimonial->customer_type }}</span>@endif</td>
                        <td class="text-amber-600">{{ $testimonial->rating ? str_repeat('★', $testimonial->rating) : '—' }}</td>
                        <td class="max-w-md py-4 pr-5 leading-6 text-ink/60">{{ $testimonial->content }}</td>
                        <td><span class="badge">{{ $testimonial->status }}</span>@if($testimonial->featured)<span class="mt-1 block text-xs font-semibold text-brand-deep">Featured</span>@endif</td>
                        <td>{{ $testimonial->sort_order }}</td>
                        <td><a class="font-semibold text-brand-deep" href="{{ route('admin.testimonials.edit', $testimonial) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="border-t p-10 text-center text-ink/50">Belum ada testimoni.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $testimonials->links() }}</div>
@endsection

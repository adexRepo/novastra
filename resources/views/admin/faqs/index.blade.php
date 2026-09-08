@extends('layouts.admin')

@section('title', 'FAQ')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="eyebrow">Bantuan pelanggan</p><h1 class="section-title mt-3">FAQ.</h1></div>
        <a class="btn" href="{{ route('admin.faqs.create') }}">Tambah FAQ</a>
    </div>
    <div class="card mt-8 overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead><tr class="text-ink/50"><th class="p-4">Pertanyaan</th><th>Status</th><th>Homepage</th><th>Urutan</th><th><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>
                @forelse($faqs as $faq)
                    <tr class="border-t"><td class="p-4 font-semibold">{{ $faq->question }}</td><td><span class="badge">{{ $faq->is_active ? 'ACTIVE' : 'INACTIVE' }}</span></td><td>{{ $faq->show_on_home ? 'Ya' : 'Tidak' }}</td><td>{{ $faq->sort_order }}</td><td><a class="font-semibold text-brand-deep" href="{{ route('admin.faqs.edit', $faq) }}">Edit</a></td></tr>
                @empty
                    <tr><td colspan="5" class="border-t p-10 text-center text-ink/50">Belum ada FAQ.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $faqs->links() }}</div>
@endsection

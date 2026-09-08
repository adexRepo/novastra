@extends('layouts.admin')

@section('title', $faq->exists ? 'Edit FAQ' : 'Tambah FAQ')

@section('content')
    <a href="{{ route('admin.faqs.index') }}" class="text-sm">← Semua FAQ</a>
    <p class="eyebrow mt-7">Bantuan pelanggan</p>
    <h1 class="section-title mt-3">{{ $faq->exists ? 'Edit FAQ.' : 'FAQ baru.' }}</h1>
    <form class="card mt-8 max-w-3xl p-5 sm:p-7" method="post" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}">
        @csrf
        @if($faq->exists) @method('PUT') @endif
        <div class="grid gap-5 sm:grid-cols-2">
            <label class="sm:col-span-2">Pertanyaan<input class="field" name="question" value="{{ old('question', $faq->question) }}" required></label>
            <label class="sm:col-span-2">Jawaban<textarea class="field min-h-40 py-3" name="answer" required>{{ old('answer', $faq->answer) }}</textarea></label>
            <label>Urutan<input class="field" type="number" min="0" max="9999" name="sort_order" value="{{ old('sort_order', $faq->sort_order ?? 0) }}" required></label>
            <div class="space-y-3 self-end pb-2"><label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $faq->is_active))> FAQ aktif</label><label class="flex items-center gap-2"><input type="checkbox" name="show_on_home" value="1" @checked(old('show_on_home', $faq->show_on_home))> Tampilkan di homepage</label></div>
        </div>
        <button class="btn mt-7">Simpan FAQ</button>
    </form>
    @if($faq->exists)<form class="mt-6 max-w-3xl" method="post" action="{{ route('admin.faqs.destroy', $faq) }}" onsubmit="return confirm('Hapus FAQ ini?')">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-700">Hapus FAQ</button></form>@endif
@endsection

@extends('layouts.admin')

@section('title', $testimonial->exists ? 'Edit Testimoni' : 'Tambah Testimoni')

@section('content')
    <a href="{{ route('admin.testimonials.index') }}" class="text-sm">← Semua testimoni</a>
    <p class="eyebrow mt-7">Konten publik</p>
    <h1 class="section-title mt-3">{{ $testimonial->exists ? 'Edit testimoni.' : 'Testimoni baru.' }}</h1>

    <form class="card mt-8 max-w-3xl p-5 sm:p-7" method="post" action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}">
        @csrf
        @if ($testimonial->exists) @method('PUT') @endif
        @if ($testimonial->feedback_id)<input type="hidden" name="feedback_id" value="{{ $testimonial->feedback_id }}">@endif
        @if ($sourceFeedback)<p class="mb-6 rounded-xl bg-sand p-4 text-sm">Sumber: feedback {{ $sourceFeedback->source }} dari <strong>{{ $sourceFeedback->name }}</strong>. Isi di bawah dapat disamarkan sebelum dipublikasikan.</p>@endif
        <div class="grid gap-5 sm:grid-cols-2">
            <label>Nama tampilan<input class="field" name="display_name" value="{{ old('display_name', $testimonial->display_name) }}" placeholder="Contoh: Budi S." required></label>
            <label>Jenis pelanggan (opsional)<input class="field" name="customer_type" value="{{ old('customer_type', $testimonial->customer_type) }}" placeholder="Contoh: Pemilik katering"></label>
            <label>Rating<select class="field" name="rating"><option value="">Tanpa rating</option>@foreach(range(5, 1) as $rating)<option value="{{ $rating }}" @selected((string) old('rating', $testimonial->rating) === (string) $rating)>{{ $rating }} bintang</option>@endforeach</select></label>
            <label>Urutan<input class="field" type="number" min="0" max="9999" name="sort_order" value="{{ old('sort_order', $testimonial->sort_order ?? 0) }}" required></label>
            <label>Status<select class="field" name="status" required>@foreach(['DRAFT' => 'Draft', 'PUBLISHED' => 'Published', 'HIDDEN' => 'Hidden'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $testimonial->status ?: 'DRAFT') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label class="flex items-center gap-2 self-end pb-3"><input type="checkbox" name="featured" value="1" @checked(old('featured', $testimonial->featured))> Tampilkan di carousel homepage</label>
            <label class="sm:col-span-2">Isi testimoni<textarea class="field min-h-32 py-3" name="content" required>{{ old('content', $testimonial->content) }}</textarea></label>
        </div>
        <button class="btn mt-7">Simpan testimoni</button>
    </form>

    @if($testimonial->exists)
        <form class="mt-6 max-w-3xl" method="post" action="{{ route('admin.testimonials.destroy', $testimonial) }}" onsubmit="return confirm('Hapus testimoni ini? Feedback sumber tidak akan ikut terhapus.')">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-700">Hapus testimoni</button></form>
    @endif
@endsection

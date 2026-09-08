@extends('layouts.admin')

@section('title', $feedbackItem->exists ? 'Edit Feedback WhatsApp' : 'Tambah Feedback WhatsApp')

@section('content')
    <a href="{{ route('admin.feedback.index') }}" class="text-sm">← Semua feedback</a>
    <p class="eyebrow mt-7">WhatsApp</p>
    <h1 class="section-title mt-3">{{ $feedbackItem->exists ? 'Edit feedback.' : 'Feedback baru.' }}</h1>
    <p class="mt-4 max-w-2xl text-sm leading-6 text-ink/60">Screenshot hanya menjadi bukti internal dan tidak ditampilkan kepada pengunjung.</p>

    <form class="card mt-8 max-w-3xl p-5 sm:p-7" method="post" enctype="multipart/form-data" action="{{ $feedbackItem->exists ? route('admin.feedback.update', $feedbackItem) : route('admin.feedback.store') }}">
        @csrf
        @if ($feedbackItem->exists) @method('PUT') @endif
        <div class="grid gap-5 sm:grid-cols-2">
            <label>Nama pelanggan<input class="field" name="name" value="{{ old('name', $feedbackItem->name) }}" required></label>
            <label>Email (opsional)<input class="field" type="email" name="email" value="{{ old('email', $feedbackItem->email) }}"></label>
            <label>Rating (opsional)<select class="field" name="rating"><option value="">Tanpa rating</option>@foreach(range(5, 1) as $rating)<option value="{{ $rating }}" @selected((string) old('rating', $feedbackItem->rating) === (string) $rating)>{{ $rating }} bintang</option>@endforeach</select></label>
            <label>Tanggal feedback<input class="field" type="date" name="occurred_at" value="{{ old('occurred_at', $feedbackItem->occurred_at?->format('Y-m-d')) }}"></label>
            <label class="sm:col-span-2">Pesanan terkait (opsional)<select class="field" name="order_id"><option value="">Tanpa pesanan</option>@foreach($orders as $order)<option value="{{ $order->id }}" @selected((string) old('order_id', $feedbackItem->order_id) === (string) $order->id)>{{ $order->order_number }}</option>@endforeach</select></label>
            <label class="sm:col-span-2">Isi feedback<textarea class="field min-h-32 py-3" name="message" required>{{ old('message', $feedbackItem->message) }}</textarea></label>
            <label class="sm:col-span-2">Screenshot WhatsApp (opsional)<input class="field py-2" type="file" name="screenshot" accept="image/jpeg,image/png,image/webp"><span class="mt-2 block text-xs text-ink/45">JPG, PNG, atau WebP · maksimal 2 MB.</span></label>
        </div>
        <button class="btn mt-7">Simpan feedback</button>
    </form>

    @if ($feedbackItem->exists)
        <form class="mt-6 max-w-3xl" method="post" action="{{ route('admin.feedback.destroy', $feedbackItem) }}" onsubmit="return confirm('Hapus feedback WhatsApp ini?')">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-700">Hapus feedback</button></form>
    @endif
@endsection

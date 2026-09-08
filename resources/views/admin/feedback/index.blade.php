@extends('layouts.admin')

@section('title', 'Feedback')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="eyebrow">Masukan pelanggan</p><h1 class="section-title mt-3">Feedback.</h1></div>
        <a class="btn" href="{{ route('admin.feedback.create') }}">Tambah dari WhatsApp</a>
    </div>

    <div class="card mt-8 overflow-x-auto">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead><tr class="text-ink/50"><th class="p-4">Pelanggan</th><th>Sumber</th><th>Rating</th><th>Feedback</th><th>Order / tanggal</th><th><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>
                @forelse ($feedback as $item)
                    <tr class="border-t align-top">
                        <td class="p-4"><strong>{{ $item->name }}</strong>@if($item->email)<span class="mt-1 block text-xs text-ink/45">{{ $item->email }}</span>@endif</td>
                        <td><span class="badge">{{ $item->source }}</span></td>
                        <td class="text-amber-600">{{ $item->rating ? str_repeat('★', $item->rating) : '—' }}</td>
                        <td class="max-w-md py-4 pr-5 leading-6 text-ink/65">{{ $item->message }}</td>
                        <td class="py-4 pr-5 text-xs text-ink/50">{{ $item->order?->order_number ?? 'Tanpa order' }}<br>{{ ($item->occurred_at ?? $item->created_at)->format('d/m/Y') }}</td>
                        <td class="py-4 pr-4">
                            <div class="flex flex-col items-start gap-2">
                                @if ($item->screenshot_path)<a class="font-semibold text-brand-deep" target="_blank" href="{{ route('admin.feedback.screenshot', $item) }}">Lihat screenshot</a>@endif
                                @if (! $item->testimonial)<a class="font-semibold text-brand-deep" href="{{ route('admin.testimonials.create', ['feedback' => $item->id]) }}">Jadikan testimoni</a>@else<span class="text-xs text-ink/40">Sudah dijadikan testimoni</span>@endif
                                @if ($item->source === 'WHATSAPP')<a class="font-semibold text-brand-deep" href="{{ route('admin.feedback.edit', $item) }}">Edit</a>@endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="border-t p-10 text-center text-ink/50">Belum ada feedback.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $feedback->links() }}</div>
@endsection

@extends('layouts.admin')

@section('title', 'Informasi Situs')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">Konfigurasi publik</p>
            <h1 class="section-title mt-3">Informasi situs.</h1>
            <p class="mt-4 max-w-2xl text-sm leading-6 text-ink/60">Nilai yang disimpan di sini menggantikan default dari ENV. Secret, password, dan kredensial server tidak dapat diakses dari halaman ini.</p>
        </div>
        <form method="post" action="{{ route('admin.settings.reset') }}" onsubmit="return confirm('Kembalikan seluruh informasi situs ke nilai default ENV?')">
            @csrf
            @method('DELETE')
            <button class="btn-outline whitespace-nowrap" type="submit">Gunakan default ENV</button>
        </form>
    </div>

    <form class="mt-8" method="post" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        <div class="card overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-sand/55 text-xs uppercase tracking-[.14em] text-ink/50">
                    <tr><th class="px-5 py-4">Informasi</th><th class="px-5 py-4">Nilai publik</th></tr>
                </thead>
                @foreach (collect($definitions)->groupBy('group', true) as $group => $items)
                    <tbody>
                        <tr class="border-t bg-ink/[.035]"><th colspan="2" class="px-5 py-3 text-xs font-semibold uppercase tracking-[.16em] text-ink/55">{{ $group }}</th></tr>
                        @foreach ($items as $key => $definition)
                            <tr class="border-t align-top">
                                <th class="w-[34%] px-5 py-5 font-medium">
                                    <label for="setting-{{ $key }}">{{ $definition['label'] }}</label>
                                    <span class="mt-1 block text-xs font-normal leading-5 text-ink/45">{{ $definition['help'] }}</span>
                                </th>
                                <td class="px-5 py-4">
                                    @if ($definition['type'] === 'textarea')
                                        <textarea id="setting-{{ $key }}" class="field mt-0 min-h-24 py-3" name="settings[{{ $key }}]" required>{{ old("settings.{$key}", $values[$key]) }}</textarea>
                                    @elseif ($definition['type'] === 'boolean')
                                        <select id="setting-{{ $key }}" class="field mt-0" name="settings[{{ $key }}]" required>
                                            <option value="1" @selected((string) old("settings.{$key}", $values[$key]) === '1')>Aktif</option>
                                            <option value="0" @selected((string) old("settings.{$key}", $values[$key]) === '0')>Nonaktif</option>
                                        </select>
                                    @else
                                        <input id="setting-{{ $key }}" class="field mt-0" type="{{ $definition['type'] }}" name="settings[{{ $key }}]" value="{{ old("settings.{$key}", $values[$key]) }}" required>
                                    @endif
                                    @error("settings.{$key}")<p class="mt-2 text-xs text-red-700">{{ $message }}</p>@enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @endforeach
            </table>
        </div>

        <div class="mt-5 flex justify-end"><button class="btn" type="submit">Simpan informasi</button></div>
    </form>
@endsection

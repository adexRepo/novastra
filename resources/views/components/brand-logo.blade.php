<span {{ $attributes->class(['inline-flex items-center gap-3']) }}>
    <img
        src="{{ asset('logo-novastra-mark.webp') }}?v=1"
        alt=""
        width="512"
        height="512"
        class="size-11 shrink-0 rounded-xl object-cover"
        aria-hidden="true"
    >
    <span class="flex flex-col leading-none">
        <span class="font-sans text-xl font-semibold tracking-tight">{{ $companySettings['company_name'] }}</span>
        <span class="mt-1 text-[9px] font-semibold uppercase tracking-[0.24em] text-brand">{{ $companySettings['brand_suffix'] }}</span>
    </span>
</span>

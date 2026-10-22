@props(['spent', 'goal'])
@php
    $cx = 120; $cy = 120; $r = 100; $stroke = 18;
    $hasGoal = $goal > 0;
    $ratio = $hasGoal ? min($spent / $goal, 1) : 0;
    $remaining = $goal - $spent;
    $over = $hasGoal && $remaining < 0;
    $angle = M_PI * (1 - $ratio);
    $endX = round($cx + $r * cos($angle), 2);
    $endY = round($cy - $r * sin($angle), 2);
    $arcColor = $over ? 'danger' : 'accent';
    $big = $hasGoal ? \App\Support\Format::rupiah(abs($remaining)) : 'No goal set';
    $small = $hasGoal ? ($over ? 'Over budget' : 'Remaining') : 'Tap Goal to set one';
@endphp

<div class="rounded-md border border-border bg-surface px-6 py-8">
    <svg viewBox="0 0 240 140" class="mx-auto w-full max-w-xs" role="img" aria-label="{{ $big }} - {{ $small }}">
        <path d="M {{ $cx - $r }} {{ $cy }} A {{ $r }} {{ $r }} 0 0 1 {{ $cx + $r }} {{ $cy }}"
              fill="none" stroke-width="{{ $stroke }}" stroke-linecap="round" style="stroke: rgb(var(--surface-raised))" />
        @if ($ratio > 0)
            <path d="M {{ $cx - $r }} {{ $cy }} A {{ $r }} {{ $r }} 0 0 1 {{ $endX }} {{ $endY }}"
                  fill="none" stroke-width="{{ $stroke }}" stroke-linecap="round" style="stroke: rgb(var(--{{ $arcColor }}))" />
        @endif
        <text x="{{ $cx }}" y="86" text-anchor="middle" font-size="22" font-weight="600" class="font-display" style="fill: rgb(var(--ink))">{{ $big }}</text>
        <text x="{{ $cx }}" y="108" text-anchor="middle" font-size="12" style="fill: rgb(var(--muted))">{{ $small }}</text>
    </svg>

    {{-- "open" belongs to the Goals page, where this button opens the overall goal box --}}
    <button type="button" @click="open = -1"
            class="mx-auto mt-4 block rounded-full bg-surface-raised px-10 py-3 font-display text-sm font-medium text-ink transition-colors hover:text-accent">
        Goal <span class="text-accent">@rupiah($goal)</span>
    </button>
    <p class="mt-3 text-center text-xs text-muted">@rupiah($spent) spent this month</p>
</div>
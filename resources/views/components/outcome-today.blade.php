@props(['spent', 'top'])
@php
    $colors = ['accent', 'ink', 'muted'];
    $segments = [];
    foreach ($top as $i => $c) {
        $segments[] = ['value' => $c['total'], 'color' => $colors[$i]];
    }
    $segments[] = ['value' => $spent - array_sum(array_column($top, 'total')), 'color' => 'faint'];

    $size = 150;
    $thickness = 20;
    $center = $size / 2;
    $radius = ($size - $thickness) / 2;
    $circumference = 2 * M_PI * $radius;
    $total = array_sum(array_column($segments, 'value'));
    $offset = 0;
@endphp

<section class="mb-10 rounded-md border border-border bg-surface p-6">
    <div class="flex items-start justify-between gap-6">
        <div>
            <h3 class="text-sm font-medium text-accent">Outcome today</h3>
            <p class="mt-3 font-display text-4xl font-semibold text-ink">@rupiah($spent)</p>
        </div>

        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}" class="shrink-0" role="img" aria-label="Spending today by category">
            <g transform="rotate(-90 {{ $center }} {{ $center }})">
                <circle cx="{{ $center }}" cy="{{ $center }}" r="{{ $radius }}" fill="none" stroke-width="{{ $thickness }}" style="stroke: rgb(var(--surface-raised))" />
                @if ($total > 0)
                    @foreach ($segments as $segment)
                        @php $length = $segment['value'] / $total * $circumference; @endphp
                        <circle cx="{{ $center }}" cy="{{ $center }}" r="{{ $radius }}" fill="none" stroke-width="{{ $thickness }}"
                                stroke-dasharray="{{ round($length, 2) }} {{ round($circumference - $length, 2) }}"
                                stroke-dashoffset="{{ round(-$offset, 2) }}"
                                style="stroke: rgb(var(--{{ $segment['color'] }}))" />
                        @php $offset += $length; @endphp
                    @endforeach
                @endif
            </g>
        </svg>
    </div>

    <div class="mt-6 space-y-2">
        @forelse ($top as $i => $c)
            <div class="flex items-center justify-between text-sm">
                <span class="flex items-center gap-3 text-ink">
                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: rgb(var(--{{ $colors[$i] }}))"></span>
                    {{ $c['category'] }}
                </span>
                <span class="font-display text-ink">@rupiah($c['total'])</span>
            </div>
        @empty
            <p class="text-sm text-muted">No spending today.</p>
        @endforelse
    </div>
</section>
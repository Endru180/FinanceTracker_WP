@props(['label', 'value'])
<div class="relative h-20">
    <div class="group absolute inset-x-0 top-0 rounded-md border border-border bg-surface px-4 py-4 transition-colors hover:z-20 hover:border-accent focus-within:z-20 focus-within:border-accent">
        <p class="mb-1 text-xs text-muted">{{ $label }}</p>
        <p class="font-display text-base font-medium text-ink">{{ $value }}</p>

        <div class="max-h-0 overflow-hidden opacity-0 transition-all duration-300 group-hover:max-h-64 group-hover:opacity-100 group-focus-within:max-h-64 group-focus-within:opacity-100">
            <div class="mt-4 border-t border-border pt-3">{{ $slot }}</div>
        </div>
    </div>
</div>
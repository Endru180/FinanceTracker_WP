@props(['show', 'close', 'title', 'max' => 'max-w-lg'])
<div x-show="{{ $show }}" x-cloak
     x-on:keydown.escape.window="{{ $close }}"
     @click="{{ $close }}"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
    <div role="dialog" aria-modal="true" aria-label="{{ $title }}" @click.stop
         class="max-h-[90vh] w-full {{ $max }} overflow-y-auto rounded-md border border-border bg-surface p-8">
        <h2 class="mb-6 font-display text-xl font-semibold text-accent">{{ $title }}</h2>
        {{ $slot }}
    </div>
</div>
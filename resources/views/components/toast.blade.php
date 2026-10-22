@php
    $isError = $errors->any();
    $message = $isError ? $errors->first() : session('status');
@endphp

@if ($message)
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         role="{{ $isError ? 'alert' : 'status' }}"
         class="fixed right-6 top-6 z-[60] flex max-w-sm items-start gap-3 rounded-md border bg-surface-raised px-4 py-3 text-sm shadow-lg {{ $isError ? 'border-danger text-danger' : 'border-accent text-ink' }}">
        <span class="flex-1">{{ $message }}</span>
        <button type="button" @click="show = false" aria-label="Dismiss" class="text-muted hover:text-ink">×</button>
    </div>
@endif
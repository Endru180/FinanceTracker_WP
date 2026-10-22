@php $reminder = session('reminder'); @endphp

@if ($reminder)
    <div x-data="{ open: true }">
        <x-modal show="open" close="open = false" title="Budget reminder" max="max-w-sm">
            <p class="text-sm text-ink">
                You've reached {{ $reminder['percent'] }}% of your {{ $reminder['category'] }} goal this month.
            </p>
            <p class="mt-2 text-sm text-muted">
                @rupiah($reminder['spent']) spent of @rupiah($reminder['goal']).
            </p>
            <div class="mt-8 flex justify-end">
                <button type="button" @click="open = false"
                        class="rounded-md bg-accent px-5 py-2.5 text-sm font-medium text-bg transition-opacity hover:opacity-90">
                    Got it
                </button>
            </div>
        </x-modal>
    </div>
@endif
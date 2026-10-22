@php
    $input = 'w-full rounded-md border border-border bg-surface px-3 py-2.5 text-sm text-ink placeholder:text-muted transition-colors focus:border-accent focus:outline-none';
    $presets = [
        ['half', 'Half of the budget is used', 50],
        ['three_quarters', '3/4 of the budget is used', 75],
        ['ninety', '9/10 of the budget is used', 90],
    ];
    $cancel = 'rounded-md border border-border px-5 py-2.5 text-sm font-medium text-muted transition-colors hover:bg-surface-raised hover:text-ink';
    $save = 'rounded-md bg-accent px-5 py-2.5 text-sm font-medium text-bg transition-opacity hover:opacity-90';
@endphp

<x-layout title="Goals">
    <main class="ml-60 px-10 py-10" x-data="{ open: null }" x-on:keydown.escape.window="open = null">

        @if ($viewAll)
            <a href="{{ route('goals.index') }}" aria-label="Back to goals"
               class="mb-6 flex h-9 w-9 items-center justify-center rounded-md border border-border text-muted transition-colors hover:border-accent hover:text-ink">&lt;</a>
            <h1 class="mb-1 font-display text-2xl font-semibold text-ink">Category goals</h1>
            <p class="mb-8 text-sm text-muted">Select a category to set its goal and reminders.</p>
        @else
            <h1 class="mb-8 font-display text-2xl font-semibold text-ink">Goals</h1>

            <h2 class="mb-3 text-sm font-medium text-accent">Budget reminder</h2>
            <div class="mb-10 max-w-md">
                <x-gauge :spent="$spentThisMonth" :goal="$overallGoal" />
            </div>

            <h2 class="mb-3 text-sm font-medium text-accent">Goal</h2>
        @endif

        {{-- Category goal list --}}
        <div class="max-w-2xl border-t border-border">
            @forelse ($rows as $i => $row)
                @php $goalAmount = $row['goal']['amount']; @endphp
                <button type="button" @click="open = {{ $i }}" class="group block w-full border-b border-border py-3 text-left">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-ink transition-colors group-hover:text-accent">{{ $row['category'] }}</p>
                            <p class="text-xs text-muted">@rupiah($row['spent']) spent this month</p>
                        </div>
                        <div class="text-right">
                            <p class="font-display text-sm text-accent">@rupiah($goalAmount)</p>
                            <p class="text-xs text-muted">Goal</p>
                        </div>
                    </div>
                    @if ($goalAmount > 0)
                        <div class="mt-2 h-1 rounded-full bg-surface-raised">
                            <div class="h-1 rounded-full {{ $row['spent'] > $goalAmount ? 'bg-danger' : 'bg-accent' }}"
                                 style="width: {{ round(min($row['spent'] / $goalAmount, 1) * 100, 1) }}%"></div>
                        </div>
                    @endif
                </button>
            @empty
                <p class="py-3 text-sm text-muted">Add a transaction to see your biggest categories here.</p>
            @endforelse
        </div>

        @unless ($viewAll)
            <a href="{{ route('goals.index', ['view' => 'all']) }}" class="mt-3 inline-block text-xs text-accent hover:underline">View detail →</a>
        @endunless

        {{-- Overall goal box (opened by the Goal button under the gauge) --}}
        @unless ($viewAll)
            <x-modal show="open === -1" close="open = null" title="Overall goal" max="max-w-xl">
                <form method="POST" action="{{ route('goals.overall') }}" class="space-y-4">
                    @csrf
                    <section class="rounded-md border border-border bg-bg p-4">
                        <h3 class="text-sm font-medium text-ink">Goal</h3>
                        <p class="mb-3 mt-1 text-xs text-muted">The most you plan to spend across all categories this month.</p>
                        <label for="overall-amount" class="sr-only">Goal amount in Rp</label>
                        <input id="overall-amount" type="number" name="amount" min="0" placeholder="0" value="{{ $overallGoal ?: '' }}" class="{{ $input }}">
                    </section>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="open = null" class="{{ $cancel }}">Cancel</button>
                        <button type="submit" class="{{ $save }}">Save</button>
                    </div>
                </form>
            </x-modal>
        @endunless

        {{-- Goal + Timer box for each category --}}
        @foreach ($rows as $i => $row)
            @php $r = $row['goal']['reminders']; @endphp
            <x-modal :show="'open === '.$i" close="open = null" :title="$row['category'].' goal'" max="max-w-xl">
                <form method="POST" action="{{ route('goals.category') }}" class="space-y-4"
                      x-data="{ amount: @js($row['goal']['amount'] ?: ''), custom: @js((bool) $r['custom_enabled']) }">
                    @csrf
                    <input type="hidden" name="category" value="{{ $row['category'] }}">

                    <section class="rounded-md border border-border bg-bg p-4">
                        <h3 class="text-sm font-medium text-ink">Goal</h3>
                        <p class="mb-3 mt-1 text-xs text-muted">The most you plan to spend on {{ $row['category'] }} this month.</p>
                        <label for="goal-amount-{{ $i }}" class="sr-only">Goal amount in Rp</label>
                        <input id="goal-amount-{{ $i }}" type="number" name="amount" min="0" placeholder="0" x-model.number="amount" class="{{ $input }}">
                    </section>

                    <section class="rounded-md border border-border bg-bg p-4">
                        <h3 class="text-sm font-medium text-ink">Timer</h3>
                        <p class="mb-3 mt-1 text-xs text-muted" x-text="amount > 0 ? 'Remind me when:' : 'Set a goal above to turn reminders on.'"></p>

                        <div class="space-y-3">
                            @foreach ($presets as $preset)
                                <label class="flex cursor-pointer items-center justify-between gap-3 text-sm text-ink">
                                    <span class="flex items-center gap-3">
                                        <input type="checkbox" name="reminders[{{ $preset[0] }}]" value="1" @checked($r[$preset[0]]) class="h-4 w-4 accent-accent">
                                        {{ $preset[1] }}
                                    </span>
                                    <span class="text-xs text-muted"
                                          x-text="amount > 0 ? 'Rp ' + Math.round(amount * {{ $preset[2] }} / 100).toLocaleString('id-ID') : ''"></span>
                                </label>
                            @endforeach

                            <div class="flex items-center justify-between gap-3 text-sm text-ink">
                                <label class="flex cursor-pointer items-center gap-3">
                                    <input type="checkbox" name="reminders[custom_enabled]" value="1" x-model="custom" class="h-4 w-4 accent-accent">
                                    Custom
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="reminders[custom_percent]" min="1" max="100" placeholder="60"
                                           value="{{ $r['custom_percent'] }}" :disabled="!custom" aria-label="Custom reminder percentage"
                                           class="w-20 rounded-md border border-border bg-surface px-2 py-1.5 text-sm text-ink focus:border-accent focus:outline-none disabled:opacity-40">
                                    <span class="text-muted">% of the budget</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="open = null" class="{{ $cancel }}">Cancel</button>
                        <button type="submit" class="{{ $save }}">Save</button>
                    </div>
                </form>
            </x-modal>
        @endforeach
    </main>
</x-layout>
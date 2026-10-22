@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $fmt = fn ($amount) => \App\Support\Format::rupiah($amount);
    $row = 'flex items-center justify-between gap-3 py-1.5 text-sm';
    $link = 'mt-2 inline-block text-xs text-accent hover:underline';
@endphp

<x-layout title="Dashboard">
    <main class="ml-60 px-10 py-10" x-data="{ cat: null }">
        <div class="xl:grid xl:grid-cols-[minmax(0,1fr)_20rem] xl:gap-8">
            <div class="min-w-0">
                <h2 class="mb-1 font-display text-3xl font-semibold text-ink">
                    {{ $greeting }}, <span class="text-accent">{{ $user['name'] }}</span>
                </h2>
                <p class="mb-3 text-sm text-muted">Here is your balance for this month</p>
                <h1 class="mb-10 font-display text-5xl font-semibold text-ink">@rupiah($totalBalance)</h1>

                <x-outcome-today :spent="$spentToday" :top="$topToday" />

                <div class="mb-10 grid grid-cols-3 gap-4">
                    {{-- This month --}}
                    <x-stat-card label="This month" :value="$fmt($spentThisMonth)">
                        @forelse ($latestThree as $t)
                            <div class="{{ $row }}">
                                <span class="truncate text-ink">{{ $t['name'] }}</span>
                                <span class="shrink-0 text-muted">-@rupiah($t['amount'])</span>
                            </div>
                        @empty
                            <p class="py-1.5 text-sm text-muted">No transactions this month.</p>
                        @endforelse
                        <a href="{{ route('transactions.index') }}" class="{{ $link }}">Details →</a>
                    </x-stat-card>

                    {{-- Top category: each category opens a popup --}}
                    <x-stat-card label="Top category" :value="$topCategories[0]['category'] ?? 'None yet'">
                        @forelse ($topCategories as $i => $c)
                            <button type="button" @click="cat = {{ $i }}"
                                    class="{{ $row }} w-full text-left text-ink transition-colors hover:text-accent">
                                <span class="truncate">{{ $c['category'] }}</span>
                                <span class="shrink-0 text-muted">-@rupiah($c['total'])</span>
                            </button>
                        @empty
                            <p class="py-1.5 text-sm text-muted">No spending this month.</p>
                        @endforelse
                        <a href="{{ route('transactions.index') }}" class="{{ $link }}">Details →</a>
                    </x-stat-card>

                    {{-- Accounts --}}
                    <x-stat-card label="Accounts" :value="count($accounts).' active'">
                        @foreach ($accounts as $a)
                            <div class="{{ $row }}">
                                <span class="truncate text-ink">{{ $a['name'] }}</span>
                                <span class="shrink-0 text-muted">@rupiah($a['balance'])</span>
                            </div>
                        @endforeach
                        <a href="{{ route('profile') }}" class="{{ $link }}">Add +</a>
                    </x-stat-card>
                </div>

                <h3 class="mb-3 text-sm font-medium text-accent">Recent transactions</h3>
                <div class="border-t border-border">
                    @forelse (array_slice($transactions, 0, 5) as $t)
                        <a href="{{ route('transactions.index') }}" class="group flex items-center justify-between border-b border-border py-3">
                            <div>
                                <p class="text-sm text-ink transition-colors group-hover:text-accent">{{ $t['name'] }}</p>
                                <p class="text-xs text-muted">{{ $t['category'] }}</p>
                            </div>
                            <p class="font-display text-sm text-ink">-@rupiah($t['amount'])</p>
                        </a>
                    @empty
                        <p class="py-3 text-sm text-muted">No transactions yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Sticky advisor chat --}}
            <aside class="mt-10 h-[32rem] xl:sticky xl:top-10 xl:mt-0 xl:h-[calc(100vh-5rem)] xl:self-start">
                <x-advisor-chat />
            </aside>
        </div>

        {{-- Popup: latest transactions of the clicked category --}}
        @foreach ($topCategories as $i => $c)
            <x-modal :show="'cat === '.$i" close="cat = null" :title="$c['category']" max="max-w-md">
                <p class="mb-3 text-xs text-muted">Latest transactions</p>
                <div class="border-t border-border">
                    @foreach ($latestByCategory[$c['category']] as $t)
                        <div class="flex items-center justify-between border-b border-border py-3">
                            <div>
                                <p class="text-sm text-ink">{{ $t['name'] }}</p>
                                <p class="text-xs text-muted">@fdate($t['date'])</p>
                            </div>
                            <p class="font-display text-sm text-ink">-@rupiah($t['amount'])</p>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('transactions.index', ['category' => $c['category']]) }}" class="mt-4 inline-block text-xs text-accent hover:underline">Details →</a>
            </x-modal>
        @endforeach
    </main>
</x-layout>
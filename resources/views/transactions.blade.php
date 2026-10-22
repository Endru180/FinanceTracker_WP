@php
    $chip = 'inline-flex items-center gap-2 rounded-md border border-border bg-surface px-3 py-1 text-xs text-ink';
    $hasFilters = $activeCategory || $activeAccountId || $search !== '';
    $option = 'flex w-full items-center justify-between px-4 py-2 text-left text-sm transition-colors hover:bg-surface';
@endphp

<x-layout title="Transactions">
    <main class="ml-60 px-10 py-10 pb-28"
          x-data="{
              filterOpen: false, group: null,
              pop: null, px: 0, py: 0,
              editing: null, adding: false,
              openPop(e, id) {
                  const rect = e.currentTarget.getBoundingClientRect();
                  const keyboard = e.detail === 0;
                  this.pop = id;
                  this.px = keyboard ? rect.left + 24 : e.clientX;
                  this.py = keyboard ? rect.bottom - 12 : e.clientY;
              }
          }"
          x-on:keydown.escape.window="filterOpen = false; pop = null; editing = null; adding = false">

        <h1 class="mb-1 font-display text-2xl font-semibold text-ink">Transactions</h1>
        <p class="mb-6 text-sm text-muted">Select a transaction to view or edit it.</p>

        {{-- Filter button + search box --}}
        <div class="mb-4 flex items-center gap-3">
            <div class="relative">
                <button type="button" @click="filterOpen = true; group = null"
                        class="rounded-md border border-border px-4 py-2.5 text-sm font-medium text-ink transition-colors hover:border-accent hover:bg-surface-raised">
                    Filter
                </button>

                <div x-show="filterOpen" x-cloak class="fixed inset-0 z-20" @click="filterOpen = false"></div>

                <div x-show="filterOpen" x-cloak class="absolute left-full top-0 z-30 ml-2 flex items-start gap-2">
                    <div class="w-36 rounded-md border border-border bg-surface-raised py-1 shadow-lg">
                        <button type="button" @click="group = 'category'" :class="group === 'category' ? 'text-accent' : 'text-ink'" class="{{ $option }}">
                            Category
                            @if ($activeCategory)<span class="h-1.5 w-1.5 rounded-full bg-accent"></span>@endif
                        </button>
                        <button type="button" @click="group = 'account'" :class="group === 'account' ? 'text-accent' : 'text-ink'" class="{{ $option }}">
                            Accounts
                            @if ($activeAccountId)<span class="h-1.5 w-1.5 rounded-full bg-accent"></span>@endif
                        </button>
                    </div>

                    <div x-show="group === 'category'" x-cloak class="w-44 rounded-md border border-border bg-surface-raised py-1 shadow-lg">
                        @foreach ($categories as $c)
                            @php $selected = $activeCategory === $c; @endphp
                            <a href="{{ request()->fullUrlWithQuery(['category' => $selected ? null : $c]) }}"
                               class="{{ $option }} {{ $selected ? 'text-accent' : 'text-ink' }}">
                                {{ $c }}
                                @if ($selected)<span aria-hidden="true">✓</span>@endif
                            </a>
                        @endforeach
                    </div>

                    <div x-show="group === 'account'" x-cloak class="w-44 rounded-md border border-border bg-surface-raised py-1 shadow-lg">
                        @foreach ($accounts as $a)
                            @php $selected = $activeAccountId === $a['id']; @endphp
                            <a href="{{ request()->fullUrlWithQuery(['account' => $selected ? null : $a['id']]) }}"
                               class="{{ $option }} {{ $selected ? 'text-accent' : 'text-ink' }}">
                                {{ $a['name'] }}
                                @if ($selected)<span aria-hidden="true">✓</span>@endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('transactions.index') }}" class="w-full max-w-sm">
                @if ($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory }}">@endif
                @if ($activeAccountId)<input type="hidden" name="account" value="{{ $activeAccountId }}">@endif
                <input type="search" name="q" value="{{ $search }}" placeholder="Search transactions (press Enter)" aria-label="Search transactions"
                       class="w-full rounded-md border border-border bg-surface px-3 py-2.5 text-sm text-ink placeholder:text-muted transition-colors focus:border-accent focus:outline-none">
            </form>
        </div>

        @if ($activeCategory || $activeAccountId)
            <div class="mb-4 flex flex-wrap items-center gap-2">
                @if ($activeCategory)
                    <span class="{{ $chip }}">
                        Category: {{ $activeCategory }}
                        <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" aria-label="Remove category filter" class="text-muted transition-colors hover:text-accent">×</a>
                    </span>
                @endif
                @if ($activeAccountId)
                    <span class="{{ $chip }}">
                        Account: {{ $accountNames[$activeAccountId] }}
                        <a href="{{ request()->fullUrlWithQuery(['account' => null]) }}" aria-label="Remove account filter" class="text-muted transition-colors hover:text-accent">×</a>
                    </span>
                @endif
            </div>
        @endif

        {{-- The list --}}
        @if ($totalCount === 0)
            <p class="text-sm text-muted">No transactions yet. Use "Add transaction" to record your first one.</p>
        @elseif (count($transactions) === 0)
            <p class="text-sm text-muted">No transactions match your search or filters.</p>
            @if ($hasFilters)
                <a href="{{ route('transactions.index') }}" class="mt-2 inline-block text-xs text-accent hover:underline">Clear filters</a>
            @endif
        @else
            <div class="border-t border-border">
                @foreach ($transactions as $t)
                    <button type="button" @click="openPop($event, {{ $t['id'] }})"
                            class="group flex w-full items-center justify-between border-b border-border py-3 text-left">
                        <div>
                            <p class="text-sm text-ink transition-colors group-hover:text-accent">{{ $t['name'] }}</p>
                            <p class="text-xs text-muted">{{ $t['category'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-display text-sm text-ink">-@rupiah($t['amount'])</p>
                            <p class="text-xs text-muted">@fdate($t['date'])</p>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Small "Details" box that appears where the user clicked --}}
        <div x-show="pop !== null" x-cloak class="fixed inset-0 z-30" @click="pop = null"></div>
        <div x-show="pop !== null" x-cloak
             :style="'left:' + Math.min(px, window.innerWidth - 140) + 'px; top:' + Math.min(py + 8, window.innerHeight - 60) + 'px'"
             class="fixed z-40 rounded-md border border-border bg-surface-raised shadow-lg">
            <button type="button" @click="editing = pop; pop = null"
                    class="w-full px-5 py-2 text-left text-sm text-ink transition-colors hover:text-accent">
                Details
            </button>
        </div>

        {{-- Big centered box: edit one transaction --}}
        @foreach ($transactions as $t)
            <x-modal :show="'editing === '.$t['id']" close="editing = null" title="Transaction details">
                <form method="POST" action="{{ route('transactions.update', $t['id']) }}">
                    @csrf
                    <x-transaction-fields :tx="$t" :prefix="'edit'.$t['id']" :categories="$categories" :accounts="$accounts" />
                    <div class="mt-8 flex justify-end gap-3">
                        <button type="submit" formaction="{{ route('transactions.destroy', $t['id']) }}" formnovalidate
                                class="rounded-md border border-border px-5 py-2.5 text-sm font-medium text-muted transition-colors hover:border-danger hover:text-danger">
                            Delete
                        </button>
                        <button type="submit"
                                class="rounded-md bg-accent px-5 py-2.5 text-sm font-medium text-bg transition-opacity hover:opacity-90">
                            Done
                        </button>
                    </div>
                </form>
            </x-modal>
        @endforeach

        {{-- Big centered box: add a new transaction --}}
        <x-modal show="adding" close="adding = false" title="Add transaction">
            <form method="POST" action="{{ route('transactions.store') }}">
                @csrf
                <x-transaction-fields prefix="add" :categories="$categories" :accounts="$accounts" />
                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" @click="adding = false"
                            class="rounded-md border border-border px-5 py-2.5 text-sm font-medium text-muted transition-colors hover:bg-surface-raised hover:text-ink">
                        Cancel
                    </button>
                    <button type="submit"
                            class="rounded-md bg-accent px-5 py-2.5 text-sm font-medium text-bg transition-opacity hover:opacity-90">
                        Add
                    </button>
                </div>
            </form>
        </x-modal>

        {{-- Small button, bottom right --}}
        <button type="button" @click="adding = true"
                class="fixed bottom-8 right-8 rounded-md bg-accent px-4 py-2.5 text-sm font-medium text-bg shadow-lg transition-opacity hover:opacity-90">
            Add transaction
        </button>
    </main>
</x-layout>
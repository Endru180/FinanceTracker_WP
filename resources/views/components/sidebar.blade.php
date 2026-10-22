@inject('store', 'App\Services\FinanceStore')
@php
    $user = $store->profile();
    $items = [
        ['label' => 'Dashboard', 'icon' => '⊞', 'route' => 'dashboard', 'match' => 'dashboard'],
        ['label' => 'Transactions', 'icon' => '≡', 'route' => 'transactions.index', 'match' => 'transactions.*'],
        ['label' => 'Goals', 'icon' => '◆', 'route' => 'goals.index', 'match' => 'goals.*'],
    ];
    $profileActive = request()->routeIs('profile');
    $profileClass = 'flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left transition-colors hover:bg-surface-raised '
        .($profileActive ? 'bg-surface-raised' : '');
    $initial = mb_strtoupper(mb_substr($user['name'], 0, 1));
@endphp

<aside x-data="{
           light: document.documentElement.dataset.theme === 'light',
           toggle() {
               this.light = !this.light;
               const theme = this.light ? 'light' : 'dark';
               document.documentElement.dataset.theme = theme;
               try { localStorage.setItem('finance-theme', theme); } catch (e) {}
           }
       }"
       class="fixed left-0 top-0 flex h-screen w-60 flex-col border-r border-border bg-surface">
    <div class="px-6 py-6">
        <span class="font-display text-lg font-semibold text-ink">Finance<span class="text-accent">.</span></span>
    </div>

    <nav class="flex-1 px-3">
        @foreach ($items as $item)
            @php $active = request()->routeIs($item['match']); @endphp
            <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
               class="mb-1 flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors {{ $active ? 'bg-surface-raised text-accent' : 'text-muted hover:bg-surface-raised hover:text-ink' }}">
                <span class="text-base">{{ $item['icon'] }}</span>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="border-t border-border p-3">
        <button type="button" @click="toggle()"
                class="mb-1 flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium text-muted transition-colors hover:bg-surface-raised hover:text-ink">
            <span class="text-base" aria-hidden="true" x-text="light ? '☾' : '☀'"></span>
            <span x-text="light ? 'Dark mode' : 'Light mode'"></span>
        </button>

        @if ($user['signed_in'])
            <a href="{{ route('profile') }}" class="{{ $profileClass }}" @if ($profileActive) aria-current="page" @endif>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-border font-display text-sm text-accent">{{ $initial }}</span>
                <span class="min-w-0">
                    <span class="block truncate text-sm {{ $profileActive ? 'text-accent' : 'text-ink' }}">{{ $user['name'] }}</span>
                    <span class="block truncate text-xs text-muted">{{ $user['email'] }}</span>
                </span>
            </a>
        @else
            <button type="button" @click="$dispatch('open-auth')" aria-haspopup="dialog" class="{{ $profileClass }}">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-border font-display text-sm text-accent">{{ $initial }}</span>
                <span class="min-w-0">
                    <span class="block truncate text-sm text-ink">{{ $user['name'] }}</span>
                    <span class="block truncate text-xs text-muted">Not logged in</span>
                </span>
            </button>
        @endif
    </div>
</aside>
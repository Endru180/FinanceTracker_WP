@php
    $input = 'w-full rounded-md border border-border bg-bg px-3 py-2.5 text-sm text-ink placeholder:text-muted transition-colors focus:border-accent focus:outline-none';
    $label = 'mb-1.5 block text-sm font-medium text-muted';
    $primary = 'rounded-md bg-accent px-5 py-2.5 text-sm font-medium text-bg transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40';
    $secondary = 'rounded-md border border-border px-5 py-2.5 text-sm font-medium text-muted transition-colors hover:bg-surface-raised hover:text-ink';
    $dangerButton = 'rounded-md border border-border px-5 py-2.5 text-sm font-medium text-muted transition-colors disabled:cursor-not-allowed disabled:opacity-40 enabled:hover:border-danger enabled:hover:text-danger';
    $section = 'mb-6 rounded-md border border-border bg-surface p-6';
    $sectionTitle = 'font-display text-base font-semibold text-accent';
@endphp

<x-layout title="Profile">
    <main class="ml-60 px-10 py-10" x-data="{ modal: null }" x-on:keydown.escape.window="modal = null">
        <h1 class="mb-8 font-display text-2xl font-semibold text-ink">Profile</h1>

        <div class="max-w-2xl">
            @if ($user['signed_in'])

                {{-- Name --}}
                <section class="{{ $section }}">
                    <h2 class="{{ $sectionTitle }}">Name</h2>
                    <p class="mb-5 mt-1 text-xs text-muted">Shown in the greeting on your dashboard.</p>
                    <form method="POST" action="{{ route('profile.name') }}" class="flex items-end gap-3">
                        @csrf
                        <div class="flex-1">
                            <label for="profile-name" class="{{ $label }}">Your name</label>
                            <input id="profile-name" name="name" type="text" value="{{ $user['name'] }}" maxlength="60" required class="{{ $input }}">
                        </div>
                        <button type="submit" class="{{ $primary }}">Save</button>
                    </form>
                </section>

                {{-- Login --}}
                <section class="{{ $section }}">
                    <h2 class="{{ $sectionTitle }}">Login</h2>
                    <p class="mb-5 mt-1 text-xs text-muted">How you sign in to this app.</p>
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm text-ink">{{ $user['method'] === 'google' ? 'Google account' : 'Email account' }}</p>
                            <p class="truncate text-xs text-muted">{{ $user['email'] }}</p>
                        </div>
                        <form method="POST" action="{{ route('auth.logout') }}">
                            @csrf
                            <button type="submit" class="{{ $secondary }}">Log out</button>
                        </form>
                    </div>
                </section>

                {{-- Payment accounts --}}
                <section class="{{ $section }}">
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <h2 class="{{ $sectionTitle }}">Payment accounts</h2>
                            <p class="mt-1 text-xs text-muted">Your wallets and bank accounts. They show on the dashboard and when you add a transaction.</p>
                        </div>
                        <button type="button" @click="modal = 0" class="shrink-0 {{ $secondary }}">Add account</button>
                    </div>

                    <div class="border-t border-border">
                        @foreach ($accounts as $a)
                            <button type="button" @click="modal = {{ $a['id'] }}" class="group flex w-full items-center justify-between border-b border-border py-3 text-left">
                                <div>
                                    <p class="text-sm text-ink transition-colors group-hover:text-accent">{{ $a['name'] }}</p>
                                    <p class="text-xs text-muted">{{ $a['type'] }}</p>
                                </div>
                                <p class="font-display text-sm text-ink">@rupiah($a['balance'])</p>
                            </button>
                        @endforeach
                    </div>
                </section>

                {{-- Categories --}}
                <section class="{{ $section }}">
                    <h2 class="{{ $sectionTitle }}">Categories</h2>
                    <p class="mb-5 mt-1 text-xs text-muted">The spending categories you can pick when adding a transaction.</p>

                    <form method="POST" action="{{ route('categories.store') }}" class="mb-5 flex items-start gap-3">
                        @csrf
                        <label for="category-name" class="sr-only">New category name</label>
                        <input id="category-name" name="name" type="text" maxlength="30" placeholder="e.g. Education" required class="{{ $input }} flex-1">
                        <button type="submit" class="{{ $primary }}">Add</button>
                    </form>

                    <div class="border-t border-border">
                        @foreach ($categoryRows as $row)
                            <div class="flex items-center justify-between gap-4 border-b border-border py-3">
                                <div>
                                    <p class="text-sm text-ink">{{ $row['name'] }}</p>
                                    <p class="text-xs text-muted">{{ $row['count'] === 0 ? 'No transactions' : $row['count'].' transaction'.($row['count'] === 1 ? '' : 's') }}</p>
                                </div>
                                <form method="POST" action="{{ route('categories.destroy') }}">
                                    @csrf
                                    <input type="hidden" name="name" value="{{ $row['name'] }}">
                                    <button type="submit" @disabled(! $row['can_delete']) aria-label="Delete {{ $row['name'] }}"
                                            class="rounded-md border border-border px-4 py-2 text-sm font-medium text-muted transition-colors disabled:cursor-not-allowed disabled:opacity-40 enabled:hover:border-danger enabled:hover:text-danger">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-4 text-xs text-muted">
                        A category that has transactions can't be deleted. Change or delete those transactions first. You also need at least one category.
                    </p>
                </section>

            @else
                <section class="rounded-md border border-border bg-surface p-6">
                    <h2 class="{{ $sectionTitle }}">You are not logged in</h2>
                    <p class="mt-1 text-sm text-muted">Log in to manage your name and payment accounts.</p>
                    <button type="button" @click="$dispatch('open-auth')" class="{{ $primary }} mt-5">Log in</button>
                </section>
            @endif
        </div>

        @if ($user['signed_in'])
            {{-- Add account box (modal = 0) --}}
            <x-modal show="modal === 0" close="modal = null" title="Add account">
                <form method="POST" action="{{ route('accounts.store') }}">
                    @csrf
                    <div class="space-y-5">
                        <div>
                            <label for="new-account-name" class="{{ $label }}">Account name</label>
                            <input id="new-account-name" name="name" type="text" placeholder="e.g. Bank Mandiri" required class="{{ $input }}">
                        </div>
                        <div>
                            <label for="new-account-type" class="{{ $label }}">Type</label>
                            <select id="new-account-type" name="type" class="{{ $input }}">
                                @foreach ($accountTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="new-account-balance" class="{{ $label }}">Starting balance (Rp)</label>
                            <input id="new-account-balance" name="balance" type="number" placeholder="0" required class="{{ $input }}">
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end gap-3">
                        <button type="button" @click="modal = null" class="{{ $secondary }}">Cancel</button>
                        <button type="submit" class="{{ $primary }}">Add</button>
                    </div>
                </form>
            </x-modal>

            {{-- Edit account boxes (modal = the account's id) --}}
            @foreach ($accounts as $a)
                @php $canDelete = $a['transaction_count'] === 0 && count($accounts) > 1; @endphp
                <x-modal :show="'modal === '.$a['id']" close="modal = null" title="Edit account">
                    <form method="POST" action="{{ route('accounts.update', $a['id']) }}">
                        @csrf
                        <div class="space-y-5">
                            <div>
                                <label for="account-name-{{ $a['id'] }}" class="{{ $label }}">Account name</label>
                                <input id="account-name-{{ $a['id'] }}" name="name" type="text" value="{{ $a['name'] }}" required class="{{ $input }}">
                            </div>
                            <div>
                                <label for="account-type-{{ $a['id'] }}" class="{{ $label }}">Type</label>
                                <select id="account-type-{{ $a['id'] }}" name="type" class="{{ $input }}">
                                    @foreach ($accountTypes as $type)
                                        <option value="{{ $type }}" @selected($a['type'] === $type)>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="account-balance-{{ $a['id'] }}" class="{{ $label }}">Balance (Rp)</label>
                                <input id="account-balance-{{ $a['id'] }}" name="balance" type="number" value="{{ $a['balance'] }}" required class="{{ $input }}">
                            </div>
                        </div>

                        @unless ($canDelete)
                            <p class="mt-5 text-xs text-muted">
                                @if ($a['transaction_count'] > 0)
                                    This account has {{ $a['transaction_count'] }} transaction{{ $a['transaction_count'] === 1 ? '' : 's' }}. Change or delete {{ $a['transaction_count'] === 1 ? 'it' : 'them' }} on the Transactions page before removing the account.
                                @else
                                    You need at least one account.
                                @endif
                            </p>
                        @endunless

                        <div class="mt-8 flex justify-end gap-3">
                            <button type="submit" formaction="{{ route('accounts.destroy', $a['id']) }}" formnovalidate @disabled(! $canDelete) class="{{ $dangerButton }}">Delete</button>
                            <button type="submit" class="{{ $primary }}">Done</button>
                        </div>
                    </form>
                </x-modal>
            @endforeach
        @endif
    </main>
</x-layout>
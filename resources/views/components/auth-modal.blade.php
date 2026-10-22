@php
    $input = 'w-full rounded-md border border-border bg-bg px-3 py-2.5 text-sm text-ink placeholder:text-muted transition-colors focus:border-accent focus:outline-none';
    $label = 'mb-1.5 block text-sm font-medium text-muted';
    $submit = 'w-full rounded-md bg-accent px-5 py-2.5 text-sm font-medium text-bg transition-opacity hover:opacity-90';
@endphp

<div x-data="{ open: @js((bool) session('open_auth')), mode: 'login' }"
     x-on:open-auth.window="open = true">
    <x-modal show="open" close="open = false" title="Log in to continue" max="max-w-md">
        <p class="-mt-3 mb-5 text-sm text-muted">Log in to view your profile and manage your payment accounts.</p>

        <div class="mb-5 flex gap-2">
            <button type="button" @click="mode = 'login'" :aria-pressed="mode === 'login'"
                    :class="mode === 'login' ? 'bg-surface-raised text-accent' : 'text-muted hover:text-ink'"
                    class="rounded-md px-4 py-2 text-sm font-medium transition-colors">Log in</button>
            <button type="button" @click="mode = 'signup'" :aria-pressed="mode === 'signup'"
                    :class="mode === 'signup' ? 'bg-surface-raised text-accent' : 'text-muted hover:text-ink'"
                    class="rounded-md px-4 py-2 text-sm font-medium transition-colors">Create account</button>
        </div>

        {{-- Log in --}}
        <form x-show="mode === 'login'" method="POST" action="{{ route('auth.login') }}" class="space-y-4">
            @csrf
            <div>
                <label for="login-email" class="{{ $label }}">Email</label>
                <input id="login-email" name="email" type="email" autocomplete="email" required class="{{ $input }}">
            </div>
            <div>
                <label for="login-password" class="{{ $label }}">Password</label>
                <input id="login-password" name="password" type="password" autocomplete="current-password" required class="{{ $input }}">
            </div>
            <button type="submit" class="{{ $submit }}">Log in</button>
        </form>

        {{-- Create account --}}
        <form x-show="mode === 'signup'" x-cloak method="POST" action="{{ route('auth.signup') }}" class="space-y-4">
            @csrf
            <div>
                <label for="signup-name" class="{{ $label }}">Name</label>
                <input id="signup-name" name="name" type="text" autocomplete="name" required class="{{ $input }}">
            </div>
            <div>
                <label for="signup-email" class="{{ $label }}">Email</label>
                <input id="signup-email" name="email" type="email" autocomplete="email" required class="{{ $input }}">
            </div>
            <div>
                <label for="signup-password" class="{{ $label }}">Password</label>
                <input id="signup-password" name="password" type="password" minlength="8" autocomplete="new-password" required class="{{ $input }}">
                <p class="mt-1.5 text-xs text-muted">At least 8 characters.</p>
            </div>
            <button type="submit" class="{{ $submit }}">Create account</button>
        </form>

        <div class="my-5 flex items-center gap-3 text-xs text-muted">
            <span class="h-px flex-1 bg-border"></span>or<span class="h-px flex-1 bg-border"></span>
        </div>

        <form method="POST" action="{{ route('auth.google') }}">
            @csrf
            <button type="submit" class="w-full rounded-md border border-border px-5 py-2.5 text-sm font-medium text-ink transition-colors hover:bg-surface-raised">
                Continue with Google
            </button>
        </form>

        <p class="mt-5 text-xs text-muted">Demo only. No real account is checked or created.</p>
    </x-modal>
</div>
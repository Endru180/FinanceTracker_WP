@props(['tx' => null, 'prefix', 'categories', 'accounts'])
@php
    $v = $tx ?? [
        'name' => '',
        'description' => '',
        'amount' => '',
        'category' => $categories[0],
        'account_id' => $accounts[0]['id'],
        'date' => now()->toDateString(),
    ];
    $input = 'w-full rounded-md border border-border bg-bg px-3 py-2.5 text-sm text-ink placeholder:text-muted transition-colors focus:border-accent focus:outline-none';
    $label = 'mb-1.5 block text-sm font-medium text-muted';
@endphp

<div class="space-y-5">
    <div>
        <label for="{{ $prefix }}-name" class="{{ $label }}">Name</label>
        <input id="{{ $prefix }}-name" name="name" type="text" placeholder="e.g. Lunch at Warteg" value="{{ $v['name'] }}" class="{{ $input }}">
    </div>

    <div>
        <label for="{{ $prefix }}-description" class="{{ $label }}">Description</label>
        <textarea id="{{ $prefix }}-description" name="description" rows="3" placeholder="Optional notes about this transaction" class="{{ $input }} resize-y">{{ $v['description'] }}</textarea>
    </div>

    <div>
        <label for="{{ $prefix }}-amount" class="{{ $label }}">Amount (Rp)</label>
        <input id="{{ $prefix }}-amount" name="amount" type="number" min="1" placeholder="0" value="{{ $v['amount'] }}" required class="{{ $input }}">
    </div>

    <div>
        <label for="{{ $prefix }}-category" class="{{ $label }}">Category</label>
        <select id="{{ $prefix }}-category" name="category" class="{{ $input }}">
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected($v['category'] === $category)>{{ $category }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="{{ $prefix }}-account" class="{{ $label }}">Account</label>
        <select id="{{ $prefix }}-account" name="account_id" class="{{ $input }}">
            @foreach ($accounts as $account)
                <option value="{{ $account['id'] }}" @selected($v['account_id'] === $account['id'])>
                    {{ $account['name'] }} (@rupiah($account['balance']))
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="{{ $prefix }}-date" class="{{ $label }}">Date</label>
        <input id="{{ $prefix }}-date" name="date" type="date" value="{{ $v['date'] }}" required class="{{ $input }}">
    </div>
</div>
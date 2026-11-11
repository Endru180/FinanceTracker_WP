<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use App\Models\Goal;

class FinanceStore
{
    public const ACCOUNT_TYPES = ['Cash', 'Bank', 'E-wallet'];

    public const DEFAULT_REMINDERS = [
        'half' => false,
        'three_quarters' => false,
        'ninety' => false,
        'custom_enabled' => false,
        'custom_percent' => null,
    ];

    // ---------------------------------------------------------------- profile

    public function profile(): array
    {
        $user = Auth::user();

        if (! $user) {
            return ['name' => 'Guest', 'email' => '', 'method' => null, 'signed_in' => false];
        }

        return [
            'name' => $user->username,
            'email' => $user->email,
            'method' => 'email',
            'signed_in' => true,
        ];
    }

    public function updateName(string $name): void
    {
        $name = trim($name);
        if ($name !== '' && Auth::check()) {
            Auth::user()->update(['username' => $name]);
        }
    }

    // --------------------------------------------------------------- accounts

    public function accounts(): array
    {
        return Account::where('user_id', Auth::id())
            ->withCount('transactions')
            ->get()
            ->map(fn(Account $a) => [
                'id' => $a->id,
                'name' => $a->account_name,
                'type' => $a->account_type,
                'starting_balance' => (float) $a->balance,
                'balance' => $a->currentBalance(),
                'transaction_count' => $a->transactions_count,
            ])
            ->all();
    }

    public function totalBalance(): float
    {
        return Account::where('user_id', Auth::id())->get()->sum(fn(Account $a) => $a->currentBalance());
    }

    public function addAccount(string $name, string $type, int $balance): void
    {
        Account::create([
            'account_name' => trim($name),
            'account_type' => $type,
            'balance' => $balance,
            'user_id' => Auth::id(),
        ]);
    }

    public function updateAccount(int $id, string $name, string $type, int $balance): void
    {
        $account = Account::where('user_id', Auth::id())->findOrFail($id);
        $net = $account->currentBalance() - (float) $account->balance;

        $account->update([
            'account_name' => trim($name),
            'account_type' => $type,
            'balance' => $balance - $net,
        ]);
    }

    public function deleteAccount(int $id): ?string
    {
        $account = Account::where('user_id', Auth::id())->findOrFail($id);
        $count = $account->transactions()->count();

        if ($count > 0) {
            return "This account has {$count} transaction(s). Change or delete them first.";
        }
        if (Account::where('user_id', Auth::id())->count() <= 1) {
            return 'You need at least one account.';
        }

        $account->delete();

        return null;
    }

    public function addCategory(string $raw, string $type = 'Expense'): ?string
    {
        $name = trim($raw);

        if ($name === '') {
            return 'Enter a category name.';
        }
        if (mb_strlen($name) > 30) {
            return 'Use 30 characters or fewer.';
        }
        if (! in_array($type, ['Expense', 'Income'], true)) {
            return 'Invalid category type.';
        }
        if (Category::whereRaw('LOWER(category_name) = ?', [mb_strtolower($name)])->exists()) {
            return "\"{$name}\" already exists.";
        }

        Category::create(['category_name' => $name, 'type' => $type]);

        return null;
    }

    // ------------------------------------------------------------- categories

    public function categories(): array
    {
        return Category::orderBy('category_name')->pluck('category_name')->all();
    }

    public function categoryCount(string $name): int
    {
        // Kategori itu global, jadi ini ngitung transaksi dari SEMUA user, bukan cuma lu.
        return Transaction::whereHas('category', fn($q) => $q->where('category_name', $name))->count();
    }

    public function deleteCategory(string $name): ?string
    {
        $category = Category::where('category_name', $name)->first();
        if (! $category) {
            return 'That category no longer exists.';
        }

        $count = $this->categoryCount($name);
        if ($count > 0) {
            return "{$name} has {$count} transaction(s). Change or delete them first.";
        }
        if (Category::count() <= 1) {
            return 'You need at least one category.';
        }

        $category->delete();

        return null;
    }

    // ----------------------------------------------------------- transactions

    public function transactions(): array
    {
        return Transaction::query()
            ->whereHas('account', fn($q) => $q->where('user_id', Auth::id()))
            ->with('category')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn(Transaction $t) => $this->toArray($t))
            ->all();
    }

    private function toArray(Transaction $t): array
    {
        return [
            'id' => $t->id,
            'name' => $t->name ?: $t->category->category_name,
            'description' => (string) $t->description,
            'category' => $t->category->category_name,
            'amount' => (int) round((float) $t->transaction_amount),
            'account_id' => $t->account_id,
            'date' => $t->transaction_date->toDateString(),
        ];
    }

    public function addTransaction(array $data): ?array
    {
        $account = Account::where('user_id', Auth::id())->findOrFail((int) $data['account_id']);
        $categoryId = Category::where('category_name', $data['category'])->value('id');
        $name = trim((string) ($data['name'] ?? ''));

        $transaction = Transaction::create([
            'account_id' => $account->id,
            'category_id' => $categoryId,
            'name' => $name !== '' ? $name : $data['category'],
            'description' => trim((string) ($data['description'] ?? '')),
            'transaction_amount' => (int) round((float) $data['amount']),
            'transaction_date' => $data['date'],
        ]);

        return $this->reminderFor($this->toArray($transaction));
    }

    public function updateTransaction(int $id, array $data): void
    {
        $transaction = Transaction::whereHas('account', fn($q) => $q->where('user_id', Auth::id()))->findOrFail($id);
        $categoryId = Category::where('category_name', $data['category'])->value('id');
        $name = trim((string) ($data['name'] ?? ''));

        $transaction->update([
            'account_id' => (int) $data['account_id'],
            'category_id' => $categoryId,
            'name' => $name !== '' ? $name : $data['category'],
            'description' => trim((string) ($data['description'] ?? '')),
            'transaction_amount' => (int) round((float) $data['amount']),
            'transaction_date' => $data['date'],
        ]);
    }

    public function deleteTransaction(int $id): void
    {
        Transaction::whereHas('account', fn($q) => $q->where('user_id', Auth::id()))->findOrFail($id)->delete();
    }

    // ------------------------------------------------------------------ goals
        // ------------------------------------------------------------------ goals

    public function categoryGoal(string $category): array
    {
        $categoryId = Category::where('category_name', $category)->value('id');
        $goal = Goal::where('user_id', Auth::id())->where('category_id', $categoryId)->first();

        return [
            'amount' => $goal->amount ?? 0,
            'reminders' => ($goal->reminders ?? []) + self::DEFAULT_REMINDERS,
        ];
    }

    public function saveCategoryGoal(string $category, int $amount, array $reminders): void
    {
        $categoryId = Category::where('category_name', $category)->value('id');

        Goal::updateOrCreate(
            ['user_id' => Auth::id(), 'category_id' => $categoryId],
            ['amount' => $amount, 'reminders' => $reminders]
        );
    }

    public function overallGoal(): int
    {
        return (int) (Goal::where('user_id', Auth::id())->whereNull('category_id')->value('amount') ?? 0);
    }

    public function saveOverallGoal(int $amount): void
    {
        Goal::updateOrCreate(
            ['user_id' => Auth::id(), 'category_id' => null],
            ['amount' => $amount]
        );
    }

    public static function thresholds(array $reminders): array
    {
        $list = [];
        if (! empty($reminders['half'])) $list[] = 50;
        if (! empty($reminders['three_quarters'])) $list[] = 75;
        if (! empty($reminders['ninety'])) $list[] = 90;
        $custom = (int) ($reminders['custom_percent'] ?? 0);
        if (! empty($reminders['custom_enabled']) && $custom >= 1 && $custom <= 100) {
            $list[] = $custom;
        }

        return $list;
    }

    private function reminderFor(array $new): ?array
    {
        $categoryId = Category::where('category_name', $new['category'])->value('id');
        $goal = Goal::where('user_id', Auth::id())->where('category_id', $categoryId)->first();

        if (! $goal || $goal->amount <= 0) {
            return null;
        }

        $month = now()->format('Y-m');
        if (! str_starts_with($new['date'], $month)) {
            return null;
        }

        $before = (int) Transaction::whereHas('account', fn ($q) => $q->where('user_id', Auth::id()))
            ->where('category_id', $categoryId)
            ->where('id', '!=', $new['id'])
            ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('transaction_amount');

        $after = $before + $new['amount'];
        $reminders = ($goal->reminders ?? []) + self::DEFAULT_REMINDERS;

        $crossed = array_filter(
            self::thresholds($reminders),
            fn ($p) => $before * 100 < $goal->amount * $p && $after * 100 >= $goal->amount * $p
        );

        if (! $crossed) {
            return null;
        }

        return [
            'category' => $new['category'],
            'percent' => max($crossed),
            'spent' => $after,
            'goal' => $goal->amount,
        ];
    }

    // --------------------------------------------------------------- spending

    public function monthTransactions(): array
    {
        $month = now()->format('Y-m');

        return array_values(array_filter($this->transactions(), fn($t) => str_starts_with($t['date'], $month)));
    }

    public function todayTransactions(): array
    {
        $today = now()->toDateString();

        return array_values(array_filter($this->transactions(), fn($t) => $t['date'] === $today));
    }

    public static function sum(array $list): int
    {
        return array_sum(array_column($list, 'amount'));
    }

    public static function totalsByCategory(array $list): array
    {
        $totals = [];
        foreach ($list as $t) {
            $totals[$t['category']] = ($totals[$t['category']] ?? 0) + $t['amount'];
        }
        arsort($totals);

        $result = [];
        foreach ($totals as $category => $total) {
            $result[] = ['category' => (string) $category, 'total' => $total];
        }

        return $result;
    }
}

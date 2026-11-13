<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Goal;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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

    private const CATEGORY_TYPES = ['Expense', 'Income'];

    private const MAX_CATEGORY_NAME_LENGTH = 30;

    private const REMINDER_PRESETS = [
        'half' => 50,
        'three_quarters' => 75,
        'ninety' => 90,
    ];

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

    public function accounts(): array
    {
        return $this->ownAccounts()
            ->withCount('transactions')
            ->get()
            ->map(fn (Account $account) => [
                'id' => $account->id,
                'name' => $account->account_name,
                'type' => $account->account_type,
                'starting_balance' => (float) $account->balance,
                'balance' => $account->currentBalance(),
                'transaction_count' => $account->transactions_count,
            ])
            ->all();
    }

    public function totalBalance(): float
    {
        return $this->ownAccounts()
            ->get()
            ->sum(fn (Account $account) => $account->currentBalance());
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
        $account = $this->ownAccounts()->findOrFail($id);

        // The stored balance is the opening balance. Shift it by the effect of the
        // transactions so the current balance matches the figure the user typed.
        $transactionEffect = $account->currentBalance() - (float) $account->balance;

        $account->update([
            'account_name' => trim($name),
            'account_type' => $type,
            'balance' => $balance - $transactionEffect,
        ]);
    }

    public function deleteAccount(int $id): ?string
    {
        $account = $this->ownAccounts()->findOrFail($id);
        $transactionCount = $account->transactions()->count();

        if ($transactionCount > 0) {
            return "This account has {$transactionCount} transaction(s). Change or delete them first.";
        }

        if ($this->ownAccounts()->count() <= 1) {
            return 'You need at least one account.';
        }

        $account->delete();

        return null;
    }

    public function categories(): array
    {
        return Category::orderBy('category_name')->pluck('category_name')->all();
    }

    public function categoryCount(string $name): int
    {
        return Transaction::whereHas('category', fn ($query) => $query->where('category_name', $name))->count();
    }

    public function addCategory(string $raw, string $type = 'Expense'): ?string
    {
        $name = trim($raw);

        if ($name === '') {
            return 'Enter a category name.';
        }

        if (mb_strlen($name) > self::MAX_CATEGORY_NAME_LENGTH) {
            return 'Use '.self::MAX_CATEGORY_NAME_LENGTH.' characters or fewer.';
        }

        if (! in_array($type, self::CATEGORY_TYPES, true)) {
            return 'Invalid category type.';
        }

        if (Category::whereRaw('LOWER(category_name) = ?', [mb_strtolower($name)])->exists()) {
            return "\"{$name}\" already exists.";
        }

        Category::create(['category_name' => $name, 'type' => $type]);

        return null;
    }

    public function deleteCategory(string $name): ?string
    {
        $category = Category::where('category_name', $name)->first();

        if (! $category) {
            return 'That category no longer exists.';
        }

        $transactionCount = $this->categoryCount($name);

        if ($transactionCount > 0) {
            return "{$name} has {$transactionCount} transaction(s). Change or delete them first.";
        }

        if (Category::count() <= 1) {
            return 'You need at least one category.';
        }

        $category->delete();

        return null;
    }

    public function transactions(): array
    {
        return $this->listTransactions($this->ownTransactions());
    }

    public function monthTransactions(): array
    {
        return $this->listTransactions(
            $this->ownTransactions()->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
        );
    }

    public function todayTransactions(): array
    {
        return $this->listTransactions(
            $this->ownTransactions()->whereDate('transaction_date', now()->toDateString())
        );
    }

    /** Returns a reminder when the new transaction pushes a goal past one of its reminder points. */
    public function addTransaction(array $data): ?array
    {
        $account = $this->ownAccounts()->findOrFail((int) $data['account_id']);
        $transaction = Transaction::create($this->transactionAttributes($data, $account));

        return $this->reminderFor($this->formatTransaction($transaction));
    }

    public function updateTransaction(int $id, array $data): void
    {
        $transaction = $this->ownTransactions()->findOrFail($id);
        $account = $this->ownAccounts()->findOrFail((int) $data['account_id']);

        $transaction->update($this->transactionAttributes($data, $account));
    }

    public function deleteTransaction(int $id): void
    {
        $this->ownTransactions()->findOrFail($id)->delete();
    }

    public function categoryGoal(string $category): array
    {
        $goal = $this->goalFor($this->categoryId($category));

        return [
            'amount' => $goal->amount ?? 0,
            'reminders' => $this->remindersOf($goal),
        ];
    }

    public function saveCategoryGoal(string $category, int $amount, array $reminders): void
    {
        Goal::updateOrCreate(
            ['user_id' => Auth::id(), 'category_id' => $this->categoryId($category)],
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

    /** Turns the reminder settings into a list of percentages, for example [50, 90]. */
    public static function thresholds(array $reminders): array
    {
        $thresholds = [];

        foreach (self::REMINDER_PRESETS as $key => $percent) {
            if (! empty($reminders[$key])) {
                $thresholds[] = $percent;
            }
        }

        $customPercent = (int) ($reminders['custom_percent'] ?? 0);

        if (! empty($reminders['custom_enabled']) && $customPercent >= 1 && $customPercent <= 100) {
            $thresholds[] = $customPercent;
        }

        return $thresholds;
    }

    public static function sum(array $transactions): int
    {
        return array_sum(array_column($transactions, 'amount'));
    }

    /** Returns [['category' => ..., 'total' => ...], ...] from the biggest total to the smallest. */
    public static function totalsByCategory(array $transactions): array
    {
        return collect($transactions)
            ->groupBy('category')
            // PHP turns numeric array keys into integers, so cast the name back to a string
            ->map(fn ($group, $category) => [
                'category' => (string) $category,
                'total' => $group->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    private function ownAccounts(): Builder
    {
        return Account::where('user_id', Auth::id());
    }

    private function ownTransactions(): Builder
    {
        return Transaction::whereHas('account', fn ($query) => $query->where('user_id', Auth::id()));
    }

    private function listTransactions(Builder $query): array
    {
        return $query
            ->with('category')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Transaction $transaction) => $this->formatTransaction($transaction))
            ->all();
    }

    private function formatTransaction(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'name' => $transaction->name ?: $transaction->category->category_name,
            'description' => (string) $transaction->description,
            'category' => $transaction->category->category_name,
            'amount' => (int) round((float) $transaction->transaction_amount),
            'account_id' => $transaction->account_id,
            'date' => $transaction->transaction_date->toDateString(),
        ];
    }

    private function transactionAttributes(array $data, Account $account): array
    {
        $name = trim((string) ($data['name'] ?? ''));

        return [
            'account_id' => $account->id,
            'category_id' => $this->categoryId($data['category']),
            'name' => $name !== '' ? $name : $data['category'],
            'description' => trim((string) ($data['description'] ?? '')),
            'transaction_amount' => (int) round((float) $data['amount']),
            'transaction_date' => $data['date'],
        ];
    }

    private function categoryId(string $name): ?int
    {
        return Category::where('category_name', $name)->value('id');
    }

    private function goalFor(?int $categoryId): ?Goal
    {
        return Goal::where('user_id', Auth::id())->where('category_id', $categoryId)->first();
    }

    private function remindersOf(?Goal $goal): array
    {
        return ($goal->reminders ?? []) + self::DEFAULT_REMINDERS;
    }

    private function reminderFor(array $transaction): ?array
    {
        $categoryId = $this->categoryId($transaction['category']);
        $goal = $this->goalFor($categoryId);

        if (! $goal || $goal->amount <= 0) {
            return null;
        }

        if (! str_starts_with($transaction['date'], now()->format('Y-m'))) {
            return null;
        }

        $spentBefore = (int) $this->ownTransactions()
            ->where('category_id', $categoryId)
            ->where('id', '!=', $transaction['id'])
            ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('transaction_amount');

        $spentAfter = $spentBefore + $transaction['amount'];

        $crossed = array_filter(
            self::thresholds($this->remindersOf($goal)),
            fn ($percent) => $spentBefore * 100 < $goal->amount * $percent
                && $spentAfter * 100 >= $goal->amount * $percent
        );

        if (! $crossed) {
            return null;
        }

        return [
            'category' => $transaction['category'],
            'percent' => max($crossed),
            'spent' => $spentAfter,
            'goal' => $goal->amount,
        ];
    }
}

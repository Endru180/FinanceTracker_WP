<?php

namespace App\Services;

/**
 * Keeps all demo data in the user's session, so the app works without a database.
 * When the MySQL tables are ready, replace the bodies of these methods with Eloquent queries.
 */
class FinanceStore
{
    private const KEY = 'finance';

    public const ACCOUNT_TYPES = ['Cash', 'Bank', 'E-wallet'];

    public const DEFAULT_REMINDERS = [
        'half' => false,
        'three_quarters' => false,
        'ninety' => false,
        'custom_enabled' => false,
        'custom_percent' => null,
    ];

    private array $state;

    public function __construct()
    {
        $this->state = session(self::KEY) ?? $this->seed();
        $this->save();
    }

    private function save(): void
    {
        session([self::KEY => $this->state]);
    }

    private function nextId(): int
    {
        return $this->state['next_id']++;
    }

    private function seed(): array
    {
        // Sample dates always land in the current month
        $date = fn (int $daysAgo) => now()
            ->subDays(min($daysAgo, now()->day - 1))
            ->toDateString();

        return [
            'profile' => ['name' => 'Alex', 'email' => '', 'method' => null],
            'accounts' => [
                ['id' => 1, 'name' => 'Cash', 'type' => 'Cash', 'starting_balance' => 900000],
                ['id' => 2, 'name' => 'Bank BCA', 'type' => 'Bank', 'starting_balance' => 3000000],
                ['id' => 3, 'name' => 'GoPay', 'type' => 'E-wallet', 'starting_balance' => 500000],
            ],
            'categories' => ['Food', 'Fashion', 'Entertainment', 'Transport', 'Health', 'Other'],
            'transactions' => [
                ['id' => 1, 'name' => 'Groceries', 'description' => 'Weekly shopping at the supermarket', 'category' => 'Food', 'amount' => 120000, 'account_id' => 2, 'date' => $date(1)],
                ['id' => 2, 'name' => 'Coffee', 'description' => '', 'category' => 'Food', 'amount' => 25000, 'account_id' => 1, 'date' => $date(0)],
                ['id' => 3, 'name' => 'Movie ticket', 'description' => 'Weekend movie with friends', 'category' => 'Entertainment', 'amount' => 60000, 'account_id' => 3, 'date' => $date(0)],
                ['id' => 4, 'name' => 'Ojek online', 'description' => '', 'category' => 'Transport', 'amount' => 35000, 'account_id' => 3, 'date' => $date(0)],
                ['id' => 5, 'name' => 'New sneakers', 'description' => 'Running shoes, on sale', 'category' => 'Fashion', 'amount' => 450000, 'account_id' => 2, 'date' => $date(6)],
                ['id' => 6, 'name' => 'Lunch at Warteg', 'description' => '', 'category' => 'Food', 'amount' => 30000, 'account_id' => 1, 'date' => $date(0)],
                ['id' => 7, 'name' => 'Vitamins', 'description' => 'Monthly supply', 'category' => 'Health', 'amount' => 20000, 'account_id' => 1, 'date' => $date(0)],
            ],
            'goals' => [],
            'overall_goal' => 0,
            'next_id' => 100,
        ];
    }

    // ---------------------------------------------------------------- profile

    public function profile(): array
    {
        $profile = $this->state['profile'];

        return $profile + ['signed_in' => $profile['method'] !== null];
    }

    public function updateName(string $name): void
    {
        $name = trim($name);
        if ($name !== '') {
            $this->state['profile']['name'] = $name;
            $this->save();
        }
    }

    // The sign-in methods are placeholders. Replace them with real authentication later.
    public function signUp(string $name, string $email): void
    {
        $name = trim($name);
        if ($name !== '') {
            $this->state['profile']['name'] = $name;
        }
        $this->state['profile']['email'] = trim($email);
        $this->state['profile']['method'] = 'email';
        $this->save();
    }

    public function logIn(string $email): void
    {
        $this->state['profile']['email'] = trim($email);
        $this->state['profile']['method'] = 'email';
        $this->save();
    }

    public function logInWithGoogle(): void
    {
        $this->state['profile']['email'] = 'alex@gmail.com';
        $this->state['profile']['method'] = 'google';
        $this->save();
    }

    public function logOut(): void
    {
        $this->state['profile']['email'] = '';
        $this->state['profile']['method'] = null;
        $this->save();
    }

    // --------------------------------------------------------------- accounts

    private function spentFrom(int $accountId): int
    {
        return array_sum(array_column(
            array_filter($this->state['transactions'], fn ($t) => $t['account_id'] === $accountId),
            'amount'
        ));
    }

    /** Balance = starting balance minus everything spent from that account. */
    public function accounts(): array
    {
        return array_map(function (array $account) {
            $account['balance'] = $account['starting_balance'] - $this->spentFrom($account['id']);
            $account['transaction_count'] = count(array_filter(
                $this->state['transactions'],
                fn ($t) => $t['account_id'] === $account['id']
            ));

            return $account;
        }, $this->state['accounts']);
    }

    public function totalBalance(): int
    {
        return array_sum(array_column($this->accounts(), 'balance'));
    }

    public function addAccount(string $name, string $type, int $balance): void
    {
        $this->state['accounts'][] = [
            'id' => $this->nextId(),
            'name' => trim($name),
            'type' => $type,
            'starting_balance' => $balance,
        ];
        $this->save();
    }

    /** The user edits the current balance; the starting balance is adjusted to match. */
    public function updateAccount(int $id, string $name, string $type, int $balance): void
    {
        $spent = $this->spentFrom($id);

        $this->state['accounts'] = array_map(
            fn ($a) => $a['id'] === $id
                ? [...$a, 'name' => trim($name), 'type' => $type, 'starting_balance' => $balance + $spent]
                : $a,
            $this->state['accounts']
        );
        $this->save();
    }

    /** Returns an error message, or null when the account was deleted. */
    public function deleteAccount(int $id): ?string
    {
        $count = count(array_filter($this->state['transactions'], fn ($t) => $t['account_id'] === $id));

        if ($count > 0) {
            return "This account has {$count} transaction(s). Change or delete them first.";
        }
        if (count($this->state['accounts']) <= 1) {
            return 'You need at least one account.';
        }

        $this->state['accounts'] = array_values(
            array_filter($this->state['accounts'], fn ($a) => $a['id'] !== $id)
        );
        $this->save();

        return null;
    }

    // ------------------------------------------------------------- categories

    public function categories(): array
    {
        return $this->state['categories'];
    }

    public function categoryCount(string $name): int
    {
        return count(array_filter($this->state['transactions'], fn ($t) => $t['category'] === $name));
    }

    /** Returns an error message, or null when the category was added. */
    public function addCategory(string $raw): ?string
    {
        $name = trim($raw);

        if ($name === '') {
            return 'Enter a category name.';
        }
        if (mb_strlen($name) > 30) {
            return 'Use 30 characters or fewer.';
        }
        foreach ($this->state['categories'] as $existing) {
            if (mb_strtolower($existing) === mb_strtolower($name)) {
                return "\"{$name}\" already exists.";
            }
        }

        $this->state['categories'][] = $name;
        $this->save();

        return null;
    }

    /** Returns an error message, or null when the category was deleted. */
    public function deleteCategory(string $name): ?string
    {
        if (! in_array($name, $this->state['categories'], true)) {
            return 'That category no longer exists.';
        }

        $count = $this->categoryCount($name);
        if ($count > 0) {
            return "{$name} has {$count} transaction(s). Change or delete them first.";
        }
        if (count($this->state['categories']) <= 1) {
            return 'You need at least one category.';
        }

        $this->state['categories'] = array_values(
            array_filter($this->state['categories'], fn ($c) => $c !== $name)
        );
        unset($this->state['goals'][$name]);
        $this->save();

        return null;
    }

    // ----------------------------------------------------------- transactions

    /** Newest first. */
    public function transactions(): array
    {
        $list = $this->state['transactions'];
        usort($list, fn ($a, $b) => [$b['date'], $b['id']] <=> [$a['date'], $a['id']]);

        return $list;
    }

    private function clean(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));

        return [
            'name' => $name !== '' ? $name : $data['category'],
            'description' => trim((string) ($data['description'] ?? '')),
            'category' => $data['category'],
            'amount' => (int) round((float) $data['amount']),
            'account_id' => (int) $data['account_id'],
            'date' => $data['date'],
        ];
    }

    /** Returns a reminder (array) when this transaction crosses a goal reminder point, otherwise null. */
    public function addTransaction(array $data): ?array
    {
        $transaction = ['id' => $this->nextId()] + $this->clean($data);
        $reminder = $this->reminderFor($transaction);

        $this->state['transactions'][] = $transaction;
        $this->save();

        return $reminder;
    }

    public function updateTransaction(int $id, array $data): void
    {
        $this->state['transactions'] = array_map(
            fn ($t) => $t['id'] === $id ? ['id' => $id] + $this->clean($data) : $t,
            $this->state['transactions']
        );
        $this->save();
    }

    /** Deleting a transaction automatically gives the money back, because balances are calculated. */
    public function deleteTransaction(int $id): void
    {
        $this->state['transactions'] = array_values(
            array_filter($this->state['transactions'], fn ($t) => $t['id'] !== $id)
        );
        $this->save();
    }

    // ------------------------------------------------------------------ goals

    public function categoryGoal(string $category): array
    {
        $goal = $this->state['goals'][$category] ?? ['amount' => 0, 'reminders' => []];
        $goal['reminders'] = $goal['reminders'] + self::DEFAULT_REMINDERS;

        return $goal;
    }

    public function saveCategoryGoal(string $category, int $amount, array $reminders): void
    {
        $this->state['goals'][$category] = ['amount' => $amount, 'reminders' => $reminders];
        $this->save();
    }

    public function overallGoal(): int
    {
        return $this->state['overall_goal'];
    }

    public function saveOverallGoal(int $amount): void
    {
        $this->state['overall_goal'] = $amount;
        $this->save();
    }

    public static function thresholds(array $reminders): array
    {
        $list = [];
        if (! empty($reminders['half'])) {
            $list[] = 50;
        }
        if (! empty($reminders['three_quarters'])) {
            $list[] = 75;
        }
        if (! empty($reminders['ninety'])) {
            $list[] = 90;
        }
        $custom = (int) ($reminders['custom_percent'] ?? 0);
        if (! empty($reminders['custom_enabled']) && $custom >= 1 && $custom <= 100) {
            $list[] = $custom;
        }

        return $list;
    }

    private function reminderFor(array $new): ?array
    {
        $goal = $this->state['goals'][$new['category']] ?? null;
        if (! $goal || $goal['amount'] <= 0) {
            return null;
        }

        $month = now()->format('Y-m');
        if (! str_starts_with($new['date'], $month)) {
            return null;
        }

        $before = array_sum(array_column(
            array_filter(
                $this->state['transactions'],
                fn ($t) => $t['category'] === $new['category'] && str_starts_with($t['date'], $month)
            ),
            'amount'
        ));
        $after = $before + $new['amount'];

        $crossed = array_filter(
            self::thresholds($goal['reminders']),
            fn ($p) => $before * 100 < $goal['amount'] * $p && $after * 100 >= $goal['amount'] * $p
        );

        if (! $crossed) {
            return null;
        }

        return [
            'category' => $new['category'],
            'percent' => max($crossed),
            'spent' => $after,
            'goal' => $goal['amount'],
        ];
    }

    // --------------------------------------------------------------- spending

    public function monthTransactions(): array
    {
        $month = now()->format('Y-m');

        return array_values(array_filter(
            $this->transactions(),
            fn ($t) => str_starts_with($t['date'], $month)
        ));
    }

    public function todayTransactions(): array
    {
        $today = now()->toDateString();

        return array_values(array_filter($this->transactions(), fn ($t) => $t['date'] === $today));
    }

    public static function sum(array $list): int
    {
        return array_sum(array_column($list, 'amount'));
    }

    /** Returns [['category' => ..., 'total' => ...], ...] from biggest to smallest. */
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
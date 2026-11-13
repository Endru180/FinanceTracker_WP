<?php

namespace App\Http\Controllers;

use App\Services\FinanceStore;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request, FinanceStore $store)
    {
        $transactions = $store->transactions();
        $categories = $store->categories();
        $accounts = $store->accounts();
        $accountNames = array_column($accounts, 'name', 'id');

        // Filters come from the URL: /transactions?category=Food&account=2&q=coffee
        $category = $this->resolveCategory($request, $categories);
        $accountId = $this->resolveAccountId($request, $accountNames);
        $search = trim((string) $request->query('q', ''));
        $needle = mb_strtolower($search);

        $visible = array_values(array_filter(
            $transactions,
            fn (array $t) => $this->matchesFilters($t, $category, $accountId, $needle, $accountNames)
        ));

        return view('transactions', [
            'transactions' => $visible,
            'totalCount' => count($transactions),
            'categories' => $categories,
            'accounts' => $accounts,
            'accountNames' => $accountNames,
            'activeCategory' => $category,
            'activeAccountId' => $accountId,
            'search' => $search,
        ]);
    }

    public function store(Request $request, FinanceStore $store)
    {
        $reminder = $store->addTransaction($this->validated($request, $store));

        return back()->with('reminder', $reminder);
    }

    public function update(Request $request, FinanceStore $store, int $id)
    {
        $store->updateTransaction($id, $this->validated($request, $store));

        return back()->with('status', 'Transaction updated.');
    }

    public function destroy(FinanceStore $store, int $id)
    {
        $store->deleteTransaction($id);

        return back()->with('status', 'Transaction deleted. The balance was restored.');
    }

    private function resolveCategory(Request $request, array $categories): ?string
    {
        $category = $request->query('category');

        return in_array($category, $categories, true) ? $category : null;
    }

    private function resolveAccountId(Request $request, array $accountNames): ?int
    {
        $accountId = (int) $request->query('account');

        return isset($accountNames[$accountId]) ? $accountId : null;
    }

    private function matchesFilters(
        array $transaction,
        ?string $category,
        ?int $accountId,
        string $needle,
        array $accountNames
    ): bool {
        if ($category && $transaction['category'] !== $category) {
            return false;
        }

        if ($accountId && $transaction['account_id'] !== $accountId) {
            return false;
        }

        return $needle === '' || $this->matchesSearch($transaction, $needle, $accountNames);
    }

    private function matchesSearch(array $transaction, string $needle, array $accountNames): bool
    {
        $searchable = [
            $transaction['name'],
            $transaction['description'],
            $transaction['category'],
            $accountNames[$transaction['account_id']] ?? '',
        ];

        foreach ($searchable as $text) {
            if (str_contains(mb_strtolower($text), $needle)) {
                return true;
            }
        }

        return false;
    }

    private function validated(Request $request, FinanceStore $store): array
    {
        return $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:1'],
            'category' => ['required', Rule::in($store->categories())],
            'account_id' => ['required', Rule::in(array_column($store->accounts(), 'id'))],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);
    }
}

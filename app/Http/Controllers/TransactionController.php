<?php

namespace App\Http\Controllers;

use App\Services\FinanceStore;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request, FinanceStore $store)
    {
        $categories = $store->categories();
        $accounts = $store->accounts();
        $accountNames = array_column($accounts, 'name', 'id');

        // Filters come from the URL: /transactions?category=Food&account=2&q=coffee
        $category = in_array($request->query('category'), $categories, true)
            ? $request->query('category')
            : null;

        $accountId = (int) $request->query('account');
        $accountId = isset($accountNames[$accountId]) ? $accountId : null;

        $search = trim((string) $request->query('q', ''));
        $needle = mb_strtolower($search);

        $visible = array_values(array_filter(
            $store->transactions(),
            function ($t) use ($category, $accountId, $needle, $accountNames) {
                if ($category && $t['category'] !== $category) {
                    return false;
                }
                if ($accountId && $t['account_id'] !== $accountId) {
                    return false;
                }
                if ($needle === '') {
                    return true;
                }
                $haystack = [$t['name'], $t['description'], $t['category'], $accountNames[$t['account_id']] ?? ''];
                foreach ($haystack as $text) {
                    if (str_contains(mb_strtolower($text), $needle)) {
                        return true;
                    }
                }

                return false;
            }
        ));

        return view('transactions', [
            'transactions' => $visible,
            'totalCount' => count($store->transactions()),
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

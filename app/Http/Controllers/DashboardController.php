<?php

namespace App\Http\Controllers;

use App\Services\FinanceStore;

class DashboardController extends Controller
{
    private const TOP_LIMIT = 3;

    public function index(FinanceStore $store)
    {
        $all = $store->transactions();
        $month = $store->monthTransactions();
        $today = $store->todayTransactions();

        $topCategories = $this->topCategories($month);

        return view('dashboard', [
            'user' => $store->profile(),
            'accounts' => $store->accounts(),
            'totalBalance' => $store->totalBalance(),
            'transactions' => $all,
            'spentThisMonth' => FinanceStore::sum($month),
            'latestThree' => array_slice($month, 0, self::TOP_LIMIT),
            'topCategories' => $topCategories,
            'latestByCategory' => $this->latestByCategory($all, $topCategories),
            'spentToday' => FinanceStore::sum($today),
            'topToday' => $this->topCategories($today),
        ]);
    }

    private function topCategories(array $transactions): array
    {
        return array_slice(FinanceStore::totalsByCategory($transactions), 0, self::TOP_LIMIT);
    }

    // The 3 latest transactions of each top category (for the popup)
    private function latestByCategory(array $transactions, array $topCategories): array
    {
        $latest = [];

        foreach ($topCategories as $top) {
            $name = $top['category'];

            $inCategory = array_filter($transactions, fn (array $t) => $t['category'] === $name);

            $latest[$name] = array_slice(array_values($inCategory), 0, self::TOP_LIMIT);
        }

        return $latest;
    }
}

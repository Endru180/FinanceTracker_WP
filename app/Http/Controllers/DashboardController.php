<?php

namespace App\Http\Controllers;

use App\Services\FinanceStore;

class DashboardController extends Controller
{
    public function index(FinanceStore $store)
    {
        $all = $store->transactions();
        $month = $store->monthTransactions();
        $today = $store->todayTransactions();

        $topCategories = array_slice(FinanceStore::totalsByCategory($month), 0, 3);

        // The 3 latest transactions of each top category (for the popup)
        $latestByCategory = [];
        foreach ($topCategories as $c) {
            $latestByCategory[$c['category']] = array_slice(
                array_values(array_filter($all, fn ($t) => $t['category'] === $c['category'])),
                0,
                3
            );
        }

        return view('dashboard', [
            'user' => $store->profile(),
            'accounts' => $store->accounts(),
            'totalBalance' => $store->totalBalance(),
            'transactions' => $all,
            'spentThisMonth' => FinanceStore::sum($month),
            'latestThree' => array_slice($month, 0, 3),
            'topCategories' => $topCategories,
            'latestByCategory' => $latestByCategory,
            'spentToday' => FinanceStore::sum($today),
            'topToday' => array_slice(FinanceStore::totalsByCategory($today), 0, 3),
        ]);
    }
}
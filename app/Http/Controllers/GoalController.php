<?php

namespace App\Http\Controllers;

use App\Services\FinanceStore;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GoalController extends Controller
{
    public function index(Request $request, FinanceStore $store)
    {
        $month = $store->monthTransactions();
        $totals = FinanceStore::totalsByCategory($month);
        $spentByCategory = array_column($totals, 'total', 'category');

        // ?view=all shows every category; otherwise only the 3 biggest this month
        $viewAll = $request->query('view') === 'all';
        $names = $viewAll
            ? $store->categories()
            : array_column(array_slice($totals, 0, 3), 'category');

        $rows = array_map(fn ($name) => [
            'category' => (string) $name,
            'spent' => $spentByCategory[$name] ?? 0,
            'goal' => $store->categoryGoal((string) $name),
        ], $names);

        return view('goals', [
            'viewAll' => $viewAll,
            'rows' => $rows,
            'spentThisMonth' => FinanceStore::sum($month),
            'overallGoal' => $store->overallGoal(),
        ]);
    }

    public function saveOverall(Request $request, FinanceStore $store)
    {
        $data = $request->validate(['amount' => ['nullable', 'numeric', 'min:0']]);
        $store->saveOverallGoal((int) ($data['amount'] ?? 0));

        return back()->with('status', 'Overall goal saved.');
    }

    public function saveCategory(Request $request, FinanceStore $store)
    {
        $data = $request->validate([
            'category' => ['required', Rule::in($store->categories())],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'reminders.custom_percent' => ['nullable', 'integer', 'between:1,100', 'required_if:reminders.custom_enabled,1'],
        ], [
            'reminders.custom_percent.required_if' => 'Enter a custom reminder between 1 and 100.',
            'reminders.custom_percent.between' => 'Enter a custom reminder between 1 and 100.',
            'reminders.custom_percent.integer' => 'Enter a custom reminder between 1 and 100.',
        ]);

        $reminders = [
            'half' => $request->boolean('reminders.half'),
            'three_quarters' => $request->boolean('reminders.three_quarters'),
            'ninety' => $request->boolean('reminders.ninety'),
            'custom_enabled' => $request->boolean('reminders.custom_enabled'),
            'custom_percent' => $request->filled('reminders.custom_percent')
                ? (int) $request->input('reminders.custom_percent')
                : null,
        ];

        $store->saveCategoryGoal($data['category'], (int) ($data['amount'] ?? 0), $reminders);

        return back()->with('status', "{$data['category']} goal saved.");
    }
}
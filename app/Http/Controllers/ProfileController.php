<?php

namespace App\Http\Controllers;

use App\Services\FinanceStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(FinanceStore $store)
    {
        $user = $store->profile();

        // Visiting the profile while logged out opens the login box right away
        if (! $user['signed_in']) {
            session()->now('open_auth', true);
        }

        return view('profile', [
            'user' => $user,
            'accounts' => $store->accounts(),
            'accountTypes' => FinanceStore::ACCOUNT_TYPES,
            'categoryRows' => $this->categoryRows($store),
        ]);
    }

    public function updateName(Request $request, FinanceStore $store)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:60']]);
        $store->updateName($data['name']);

        return back()->with('status', 'Name saved.');
    }

    public function storeAccount(Request $request, FinanceStore $store)
    {
        $data = $this->validatedAccount($request);
        $store->addAccount($data['name'], $data['type'], (int) $data['balance']);

        return back()->with('status', 'Account added.');
    }

    public function updateAccount(Request $request, FinanceStore $store, int $id)
    {
        $data = $this->validatedAccount($request);
        $store->updateAccount($id, $data['name'], $data['type'], (int) $data['balance']);

        return back()->with('status', 'Account updated.');
    }

    public function destroyAccount(FinanceStore $store, int $id)
    {
        return $this->respond($store->deleteAccount($id), 'Account deleted.');
    }

    public function storeCategory(Request $request, FinanceStore $store)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'type' => ['required', Rule::in(['Expense', 'Income'])],
        ]);

        return $this->respond($store->addCategory($data['name'], $data['type']), 'Category added.');
    }

    public function destroyCategory(Request $request, FinanceStore $store)
    {
        $data = $request->validate(['name' => ['required', 'string']]);

        return $this->respond($store->deleteCategory($data['name']), 'Category deleted.');
    }

    private function categoryRows(FinanceStore $store): array
    {
        $categories = $store->categories();
        $hasMultiple = count($categories) > 1;

        return array_map(function (string $name) use ($store, $hasMultiple) {
            $count = $store->categoryCount($name);

            return [
                'name' => $name,
                'count' => $count,
                'can_delete' => $count === 0 && $hasMultiple,
            ];
        }, $categories);
    }

    private function respond(?string $error, string $successMessage): RedirectResponse
    {
        return $error
            ? back()->withErrors(['error' => $error])
            : back()->with('status', $successMessage);
    }

    private function validatedAccount(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'type' => ['required', Rule::in(FinanceStore::ACCOUNT_TYPES)],
            'balance' => ['required', 'numeric'],
        ]);
    }
}

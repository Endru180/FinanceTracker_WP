<?php

namespace App\Http\Controllers;

use App\Services\FinanceStore;
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

        $categories = $store->categories();
        $categoryRows = array_map(fn ($name) => [
            'name' => $name,
            'count' => $store->categoryCount($name),
            'can_delete' => $store->categoryCount($name) === 0 && count($categories) > 1,
        ], $categories);

        return view('profile', [
            'user' => $user,
            'accounts' => $store->accounts(),
            'accountTypes' => FinanceStore::ACCOUNT_TYPES,
            'categoryRows' => $categoryRows,
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
        $error = $store->deleteAccount($id);

        return $error
            ? back()->withErrors(['error' => $error])
            : back()->with('status', 'Account deleted.');
    }

    public function storeCategory(Request $request, FinanceStore $store)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:30']]);
        $error = $store->addCategory($data['name']);

        return $error
            ? back()->withErrors(['error' => $error])
            : back()->with('status', 'Category added.');
    }

    public function destroyCategory(Request $request, FinanceStore $store)
    {
        $data = $request->validate(['name' => ['required', 'string']]);
        $error = $store->deleteCategory($data['name']);

        return $error
            ? back()->withErrors(['error' => $error])
            : back()->with('status', 'Category deleted.');
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
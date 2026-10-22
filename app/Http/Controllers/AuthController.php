<?php

namespace App\Http\Controllers;

use App\Services\FinanceStore;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request, FinanceStore $store)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        $store->logIn($data['email']);

        return redirect()->route('profile');
    }

    public function signup(Request $request, FinanceStore $store)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ]);
        $store->signUp($data['name'], $data['email']);

        return redirect()->route('profile');
    }

    public function google(FinanceStore $store)
    {
        $store->logInWithGoogle();

        return redirect()->route('profile');
    }

    public function logout(FinanceStore $store)
    {
        $store->logOut();

        return redirect()->route('dashboard');
    }
}
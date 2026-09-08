<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $username = $request->input('username');
        $email = $request->input('email');

        if (is_string($username) && is_string($email)) {
            $request->merge([
                'username' => Str::lower(trim($username)),
                'email' => Str::lower(trim($email)),
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:80', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users')],
            'email' => ['required', 'email', 'max:160', Rule::unique('users')],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ]);

        $customer = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'CUSTOMER',
            'provider' => 'credentials',
        ]);

        Auth::login($customer);
        $request->session()->regenerate();

        return redirect()->intended(route('checkout'));
    }
}

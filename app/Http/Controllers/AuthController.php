<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function customerForm(Request $request)
    {
        return view('auth.customer-login', ['callback' => $this->safeCallback($request->query('callback', '/checkout'))]);
    }

    public function customerLogin(Request $request)
    {
        if (is_string($request->input('username'))) {
            $request->merge(['username' => Str::lower(trim($request->input('username')))]);
        }

        $credentials = $request->validate(['username' => ['required', 'string', 'max:80'], 'password' => ['required', 'string', 'max:200'], 'callback' => ['nullable', 'string', 'max:200']]);
        if (! Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'role' => 'CUSTOMER'])) {
            return back()->withErrors(['username' => 'Username atau password tidak sesuai.'])->onlyInput('username');
        }
        $request->session()->regenerate();

        return redirect()->intended($this->safeCallback($credentials['callback'] ?? '/checkout'));
    }

    public function adminForm()
    {
        return view('auth.admin-login');
    }

    public function adminLogin(Request $request)
    {
        if (is_string($request->input('username'))) {
            $request->merge(['username' => Str::lower(trim($request->input('username')))]);
        }

        $credentials = $request->validate(['username' => ['required', 'string', 'max:80'], 'password' => ['required', 'string', 'max:200']]);
        if (! Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'role' => 'ADMIN'])) {
            return back()->withErrors(['username' => 'Username atau password tidak sesuai.'])->onlyInput('username');
        }
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function safeCallback(?string $value): string
    {
        if (! $value || ! str_starts_with($value, '/') || str_starts_with($value, '//') || str_contains($value, '\\') || preg_match('/[\\x00-\\x1F\\x7F]/', $value)) {
            return '/checkout';
        }

        $parts = parse_url($value);

        return $parts !== false && ! isset($parts['scheme']) && ! isset($parts['host']) ? $value : '/checkout';
    }
}

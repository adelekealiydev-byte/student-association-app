<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function create()
    {
        if (request()->wantsJson()) {
            return response()->json(['message' => 'Login endpoint']);
        }

        try {
            return Inertia::render('auth/Login');
        } catch (\Throwable) {
            return response('Login Form');
        }
    }

    public function store(LoginRequest $request)
    {
        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $ok = Auth::attempt([
            $field => $request->login,
            'password' => $request->password, // checked against password_hash
            'is_verified' => true,
            'membership_status' => MembershipStatus::Active->value,
        ]);

        if (! $ok) {
            throw ValidationException::withMessages([
                'login' => 'These credentials do not match, or the account is not active.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}


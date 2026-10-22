<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Http\Requests\LoginRequest;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function create()
    {
        if (request()->wantsJson()) {
            return response()->json(['message' => 'Login endpoint']);
        }

        return Inertia::render('auth/Login');
    }

    public function store(LoginRequest $request)
    {
        $throttleKey = Str::transliterate(Str::lower($request->login).'|'.$request->ip());

        // Lockout after 5 failed attempts in 10 minutes (Section 7.3)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'login' => sprintf('Too many login attempts. Please wait %d seconds or contact support.', $seconds),
            ]);
        }

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $ok = Auth::attempt([
            $field => $request->login,
            'password' => $request->password,
            'is_verified' => true,
            'membership_status' => MembershipStatus::Active->value,
        ]);

        if ($ok) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        RateLimiter::hit($throttleKey, 600); // 10 minutes window

        // Check for specific account states (Section 7.3)
        $member = Member::where('email', $request->login)
            ->orWhere('username', $request->login)
            ->first();

        if ($member && Hash::check($request->password, $member->password_hash)) {
            if (! $member->is_verified) {
                throw ValidationException::withMessages([
                    'login' => 'This account is not active yet. Complete your invitation form first.',
                ]);
            }

            if ($member->membership_status === MembershipStatus::Suspended) {
                throw ValidationException::withMessages([
                    'login' => 'This account is suspended. Contact the office.',
                ]);
            }
        }

        throw ValidationException::withMessages([
            'login' => 'These credentials do not match our records.',
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PasswordSetupController extends Controller
{
    public function show(Member $member)
    {
        abort_if($member->password_hash !== null, 403);

        if (view()->exists('auth.password-setup')) {
            return view('auth.password-setup', ['member' => $member]);
        }

        return response('Password setup form for ' . e($member->username));
    }

    public function store(Request $request, Member $member)
    {
        abort_if($member->password_hash !== null, 403);

        $request->validate(['password' => ['required', 'confirmed', Password::min(12)]]);

        $member->update(['password_hash' => $request->password]);

        return redirect('/login')->with('status', 'Password saved. You can sign in now.');
    }
}


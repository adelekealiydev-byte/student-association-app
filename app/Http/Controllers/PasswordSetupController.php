<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PasswordSetupController extends Controller
{
    public function show(Member $member)
    {
        $alreadySet = $member->password_hash !== null;

        return \Inertia\Inertia::render('auth/PasswordSetup', [
            'member' => [
                'member_id' => $member->member_id,
                'username' => $member->username,
                'email' => $member->email,
            ],
            'alreadySet' => $alreadySet,
        ]);
    }

    public function store(Request $request, Member $member)
    {
        abort_if($member->password_hash !== null, 403);

        $request->validate(['password' => ['required', 'confirmed', Password::min(12)]]);

        $member->update(['password_hash' => $request->password]);

        return redirect('/login')->with('status', 'Password saved. You can sign in now.');
    }
}


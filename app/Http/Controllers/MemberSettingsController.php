<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class MemberSettingsController extends Controller
{
    public function show(Request $request)
    {
        return Inertia::render('me/Settings', [
            'member' => [
                'email' => $request->user()->email,
                'username' => $request->user()->username,
            ],
        ]);
    }

    public function updatePassword(Request $request)
    {
        $member = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        if (! Hash::check($request->current_password, $member->password_hash)) {
            return back()->withErrors([
                'current_password' => 'The provided current password does not match our records.',
            ]);
        }

        $member->update([
            'password_hash' => $request->password,
        ]);

        return back()->with('status', 'Your password has been changed successfully.');
    }
}


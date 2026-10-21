<?php

namespace App\Http\Controllers;

use App\Mail\PasswordSetupMail;
use App\Services\InvitationService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class GoogleFormWebhookController extends Controller
{
    public function __invoke(Request $request, InvitationService $invitations)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'size:64'],
            'username' => ['required', 'string', 'max:50', 'unique:members,username'],
        ]);

        try {
            $member = $invitations->completeForm($data['token'], $data['username']);
        } catch (DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        Mail::to($member->email)->send(new PasswordSetupMail($member));

        return response()->json(['status' => 'verified']);
    }
}


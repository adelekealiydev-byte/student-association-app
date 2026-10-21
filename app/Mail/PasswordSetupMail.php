<?php

namespace App\Mail;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class PasswordSetupMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $setupUrl;

    public function __construct(public Member $member)
    {
        $this->setupUrl = URL::temporarySignedRoute(
            'password.setup',
            now()->addHours(24),
            ['member' => $member->member_id]
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Set up your Student Association Password',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: sprintf(
                '<p>Hello %s,</p><p>Your invitation has been confirmed! Please set up your account password here: <a href="%s">%s</a></p>',
                e($this->member->username ?? 'Member'),
                e($this->setupUrl),
                e($this->setupUrl)
            ),
        );
    }
}


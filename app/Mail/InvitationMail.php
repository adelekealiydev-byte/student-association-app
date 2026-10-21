<?php

namespace App\Mail;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Member $member,
        public string $plainToken,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Student Association Membership Invitation',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: sprintf(
                '<p>Hello,</p><p>You have been invited to join the Student Association. Your invitation token is: <strong>%s</strong></p>',
                e($this->plainToken)
            ),
        );
    }
}


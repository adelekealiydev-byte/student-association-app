<?php

namespace App\Enums;

enum InvitationStatus: string
{
    case Pending = 'Pending';
    case Sent = 'Sent';
    case FormSubmitted = 'Form_Submitted';
    case Expired = 'Expired';
    case Revoked = 'Revoked';
}


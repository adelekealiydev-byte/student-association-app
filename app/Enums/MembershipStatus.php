<?php

namespace App\Enums;

enum MembershipStatus: string
{
    case PendingInvite = 'Pending Invite';
    case Active = 'Active';
    case Suspended = 'Suspended';
    case Alumni = 'Alumni';
    case Expired = 'Expired';
}


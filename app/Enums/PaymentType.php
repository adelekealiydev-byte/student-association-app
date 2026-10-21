<?php

namespace App\Enums;

enum PaymentType: string
{
    case Dues = 'Dues';
    case EventTicket = 'Event Ticket';
    case Merch = 'Merch';
}


<?php

return [
    'invitation_ttl_days' => (int) env('INVITATION_TTL_DAYS', 7),
    'google_form_url'     => env('GOOGLE_FORM_URL'),
    'webhook_secret'      => env('GOOGLE_FORM_WEBHOOK_SECRET'),
    'dues_amount'         => env('DUES_AMOUNT', '25.00'),
];


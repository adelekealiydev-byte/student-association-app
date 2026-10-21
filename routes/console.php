<?php

use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    TeamInvitation::query()
        ->whereNotNull('expires_at')
        ->where('expires_at', '<', now())
        ->delete();
})->daily()->description('Delete expired team invitations');

Schedule::call(fn () => app(\App\Services\InvitationService::class)->expireStale())
    ->hourly()
    ->description('Expire stale member invitations');

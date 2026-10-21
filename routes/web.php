<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoogleFormWebhookController;
use App\Http\Controllers\IdCardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MemberRoleController;
use App\Http\Controllers\PasswordSetupController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ReportController;
use App\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

// Webhooks: signature-checked, no session, no CSRF
Route::post('/webhooks/google-form', GoogleFormWebhookController::class)
    ->middleware(['throttle:30,1', VerifyWebhookSignature::class]);
Route::post('/webhooks/payments', PaymentWebhookController::class);

// Set a password after verification (signed link from the email)
Route::get('/password/setup/{member}', [PasswordSetupController::class, 'show'])
    ->name('password.setup')->middleware('signed');
Route::post('/password/setup/{member}', [PasswordSetupController::class, 'store'])->middleware('signed');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/me/card', [IdCardController::class, 'show'])->name('cards.show');
    Route::post('/payments/dues', [PaymentController::class, 'storeDues'])->name('payments.dues');

    Route::middleware('role:Admin,Secretary')->group(function () {
        Route::post('/members/invitations', [InvitationController::class, 'store'])->name('invitations.store');
        Route::post('/members/{member}/invitations', [InvitationController::class, 'resend'])->name('invitations.resend');
    });

    Route::post('/members/{member}/roles', [MemberRoleController::class, 'store'])->name('members.roles.store')->middleware('role:Admin');
    Route::get('/reports/overdue-dues', [ReportController::class, 'overdueDues'])->name('reports.overdue-dues')->middleware('role:Admin,Treasurer');
});

require __DIR__.'/settings.php';

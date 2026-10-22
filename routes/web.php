<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\GoogleFormWebhookController;
use App\Http\Controllers\IdCardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\InvitationManagementController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MemberManagementController;
use App\Http\Controllers\MemberProfileController;
use App\Http\Controllers\MemberRoleController;
use App\Http\Controllers\MemberSettingsController;
use App\Http\Controllers\PasswordSetupController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');
Route::inertia('/help', 'Help')->name('help');
Route::inertia('/join/expired', 'auth/JoinExpired')->name('join.expired');

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

    // Member Section (Profile, Dues, ID Card, Settings)
    Route::get('/me/membership', [MemberProfileController::class, 'show'])->name('me.membership');
    Route::patch('/me/membership', [MemberProfileController::class, 'update'])->name('me.membership.update');
    Route::get('/me/dues', [PaymentController::class, 'showDues'])->name('me.dues');
    Route::post('/payments/dues', [PaymentController::class, 'storeDues'])->name('payments.dues');
    Route::get('/me/card', [IdCardController::class, 'show'])->name('cards.show');
    Route::get('/me/settings', [MemberSettingsController::class, 'show'])->name('me.settings');
    Route::put('/me/settings/password', [MemberSettingsController::class, 'updatePassword'])->name('me.settings.password');

    // Officer Areas: Admin & Secretary
    Route::middleware('role:Admin,Secretary')->group(function () {
        Route::get('/members', [MemberManagementController::class, 'index'])->name('members.index');
        Route::get('/members/invite', [MemberManagementController::class, 'create'])->name('members.create');
        Route::post('/members/invite', [MemberManagementController::class, 'store'])->name('members.store');
        Route::get('/members/{member}', [MemberManagementController::class, 'show'])->name('members.show');

        Route::get('/invitations', [InvitationManagementController::class, 'index'])->name('invitations.index');
        Route::post('/invitations/{invitation}/revoke', [InvitationManagementController::class, 'revoke'])->name('invitations.revoke');
        Route::post('/invitations/{invitation}/resend', [InvitationManagementController::class, 'resend'])->name('invitations.resend_item');

        Route::post('/members/invitations', [InvitationController::class, 'store'])->name('invitations.store');
        Route::post('/members/{member}/invitations', [InvitationController::class, 'resend'])->name('invitations.resend');
    });

    // Officer Areas: Admin Only
    Route::middleware('role:Admin')->group(function () {
        Route::post('/members/{member}/suspend', [MemberManagementController::class, 'suspend'])->name('members.suspend');
        Route::post('/members/{member}/activate', [MemberManagementController::class, 'activate'])->name('members.activate');

        Route::get('/roles', [RoleManagementController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleManagementController::class, 'store'])->name('roles.store');
        Route::delete('/members/{member}/roles/{role}', [RoleManagementController::class, 'destroy'])->name('roles.destroy');

        Route::post('/members/{member}/roles', [MemberRoleController::class, 'store'])->name('members.roles.store');
    });

    // Officer Areas: Admin & Treasurer
    Route::middleware('role:Admin,Treasurer')->group(function () {
        Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('/finance/unpaid', [FinanceController::class, 'unpaid'])->name('finance.unpaid');
        Route::get('/finance/unpaid/export', [FinanceController::class, 'exportUnpaid'])->name('finance.unpaid.export');
        Route::get('/finance/payments', [FinanceController::class, 'payments'])->name('finance.payments');

        Route::get('/reports/overdue-dues', [ReportController::class, 'overdueDues'])->name('reports.overdue-dues');
    });
});

require __DIR__.'/settings.php';

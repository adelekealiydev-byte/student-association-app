<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $memberData = null;
        if ($user) {
            $memberData = [
                'member_id' => $user->member_id ?? $user->id,
                'email' => $user->email,
                'student_number' => $user->student_number ?? null,
                'username' => $user->username ?? $user->name ?? null,
                'membership_status' => is_object($user->membership_status ?? null) 
                    ? $user->membership_status->value 
                    : ($user->membership_status ?? 'Active'),
                'is_verified' => (bool) ($user->is_verified ?? true),
                'roles' => method_exists($user, 'roles') 
                    ? $user->roles()->pluck('role_name')->all() 
                    : [],
            ];
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $memberData,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}

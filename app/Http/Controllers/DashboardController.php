<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $widgets = method_exists($user, 'dashboardWidgets') ? $user->dashboardWidgets('dashboard') : [];

        if ($request->wantsJson()) {
            return response()->json([
                'member' => $user,
                'widgets' => $widgets,
            ]);
        }

        try {
            return Inertia::render('Dashboard', [
                'widgets' => $widgets,
                'member' => $user,
            ]);
        } catch (\Throwable) {
            return response()->json([
                'member' => $user,
                'widgets' => $widgets,
            ]);
        }
    }
}

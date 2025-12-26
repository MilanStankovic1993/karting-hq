<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Guest
        if (! $user) {
            return $next($request);
        }

        // ✅ SUPER ADMIN uvek može (team_id mu je null)
        if (($user->role ?? null) === \App\Models\User::ROLE_SUPER_ADMIN || ($user->role ?? null) === 'SUPER_ADMIN') {
            return $next($request);
        }

        // 1) User mora biti aktivan
        if (! (bool) ($user->is_active ?? true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/admin/login')
                ->with('error', 'Your account is inactive. Please contact the administrator.');
        }

        // 2) Tim mora postojati i biti aktivan (ako user ima team_id)
        if ($user->team_id) {
            $team = $user->team;

            if (! $team || ! $team->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/admin/login')
                    ->with('error', 'Your team subscription is inactive. Please contact the administrator.');
            }
        }

        return $next($request);
    }
}

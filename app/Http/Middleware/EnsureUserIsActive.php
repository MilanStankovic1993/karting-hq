<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redirect;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Ako nije ulogovan - pusti dalje (guest)
        if (! $user) {
            return $next($request);
        }

        // Super admin uvek može
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $next($request);
        }

        // 1) User mora biti aktivan
        if (property_exists($user, 'is_active') || isset($user->is_active)) {
            if (! (bool) $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

            return redirect()->to('/admin/login')
                ->with('error', 'Your account is inactive. Please contact the administrator.');
            }
        }

        // 2) Tim mora postojati i biti aktivan (ako user ima team_id)
        // (Ako želiš: ovo je pravi "subscription lock")
        if ($user->team_id) {
            $team = $user->team;

            if (! $team || ! $team->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

            return redirect()->to('/admin/login')
                ->with('error', 'Your team subscription is inactive. Please contact the administrator.');
            }

            // Ako želiš strogo po datumima, odkomentariši:
            // if (method_exists($team, 'hasAccess') && ! $team->hasAccess()) {
            //     Auth::logout();
            //     $request->session()->invalidate();
            //     $request->session()->regenerateToken();
            //     abort(403, 'Your team subscription has expired.');
            // }
        }

        return $next($request);
    }
}

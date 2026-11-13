<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dynamic role-based access control middleware (Option A).
 *
 * Usage in routes:
 *   ->middleware('role:admin')
 *   ->middleware('role:admin,customer')   // any one of the listed roles
 *
 * Behaviour:
 *   - Unauthenticated visitors are redirected to the login page (401 for JSON).
 *   - Authenticated users whose role is not in the allowed list receive 403.
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  string  ...$roles  One or more allowed role names.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.', [], route('login'));
        }

        $allowedRoles = array_map('strtolower', array_filter(array_map('trim', $roles)));

        if (! in_array(strtolower((string) $user->role), $allowedRoles, true)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}

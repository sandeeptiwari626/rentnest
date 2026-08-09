<?php

namespace App\Http\Middleware;

use App\Enums\OrganizationRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        $expected = OrganizationRole::tryFrom($role);

        if ($expected === null || $user->organizationRole() !== $expected) {
            abort(403);
        }

        return $next($request);
    }
}

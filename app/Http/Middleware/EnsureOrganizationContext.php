<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationContext
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        if ($user->current_organization_id === null) {
            $organization = $user->organizations()->first();

            if ($organization === null) {
                abort(403, 'No organization membership found.');
            }

            $user->forceFill([
                'current_organization_id' => $organization->id,
            ])->save();
        }

        return $next($request);
    }
}

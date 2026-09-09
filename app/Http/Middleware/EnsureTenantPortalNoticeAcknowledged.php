<?php

namespace App\Http\Middleware;

use App\Services\TenantPortalNoticeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantPortalNoticeAcknowledged
{
    public function __construct(private TenantPortalNoticeService $notices) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isTenant()) {
            return $next($request);
        }

        if ($user->currentOrganization) {
            $this->notices->ensureDefault($user->currentOrganization, $user);
        }

        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        if (! $this->notices->userMustAcknowledge($user)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Please acknowledge the Tenant Portal Notice before continuing.');
        }

        return redirect()->route('tenant.legal-notice.show');
    }

    protected function shouldSkip(Request $request): bool
    {
        if ($request->routeIs([
            'tenant.legal-notice.*',
            'logout',
            'password.*',
            'verification.*',
        ])) {
            return true;
        }

        return false;
    }
}

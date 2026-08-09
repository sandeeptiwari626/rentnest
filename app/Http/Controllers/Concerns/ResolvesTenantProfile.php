<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

trait ResolvesTenantProfile
{
    protected function tenantProfile(): Tenant
    {
        $user = Auth::user();
        $tenant = $user?->tenantProfile;

        if ($tenant === null) {
            abort(403, 'No tenant profile linked to this account.');
        }

        if (method_exists($this, 'organizationId')
            && (int) $tenant->organization_id !== $this->organizationId()
        ) {
            abort(403, 'Tenant profile does not belong to the current organization.');
        }

        return $tenant;
    }
}

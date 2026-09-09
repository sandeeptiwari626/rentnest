<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

trait ResolvesTenantProfile
{
    protected function tenantProfile(): Tenant
    {
        $user = Auth::user();

        if ($user === null) {
            abort(403, 'No tenant profile linked to this account.');
        }

        $organizationId = method_exists($this, 'organizationId')
            ? $this->organizationId()
            : $user->current_organization_id;

        $tenant = Tenant::query()
            ->where('user_id', $user->id)
            ->when($organizationId, fn ($query) => $query->where('organization_id', $organizationId))
            ->first();

        if ($tenant === null) {
            abort(403, 'No tenant profile linked to this account.');
        }

        return $tenant;
    }
}

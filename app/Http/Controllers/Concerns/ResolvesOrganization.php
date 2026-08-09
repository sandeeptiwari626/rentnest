<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;

trait ResolvesOrganization
{
    protected function organizationId(): int
    {
        $organizationId = Auth::user()?->current_organization_id;

        if ($organizationId === null) {
            abort(403, 'No organization context.');
        }

        return (int) $organizationId;
    }
}

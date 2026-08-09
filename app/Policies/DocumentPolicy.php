<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class DocumentPolicy
{
    use ChecksOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $user->isLandlord() || $user->isTenant();
    }

    public function view(User $user, Document $document): bool
    {
        if ($this->landlordInOrganization($user, $document)) {
            return true;
        }

        return $this->tenantCanAccess($user, $document);
    }

    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }

    public function create(User $user): bool
    {
        return $user->isLandlord();
    }

    public function update(User $user, Document $document): bool
    {
        return $this->landlordInOrganization($user, $document);
    }

    public function delete(User $user, Document $document): bool
    {
        return $this->landlordInOrganization($user, $document);
    }

    protected function tenantCanAccess(User $user, Document $document): bool
    {
        if (! $user->isTenant()
            || ! $document->visible_to_tenant
            || ! $this->sameOrganization($user, $document)
        ) {
            return false;
        }

        $tenant = $user->tenantProfile;

        if ($tenant === null) {
            return false;
        }

        $documentable = $document->documentable;

        if ($documentable === null) {
            return false;
        }

        if ($documentable instanceof Tenant) {
            return (int) $documentable->id === (int) $tenant->id;
        }

        if ($documentable instanceof Lease) {
            return (int) $documentable->tenant_id === (int) $tenant->id;
        }

        if ($documentable instanceof Property) {
            return Lease::query()
                ->where('tenant_id', $tenant->id)
                ->where('property_id', $documentable->id)
                ->exists();
        }

        if ($documentable instanceof MaintenanceRequest) {
            return (int) $documentable->tenant_id === (int) $tenant->id
                || (int) $documentable->reported_by === (int) $user->id;
        }

        return false;
    }
}

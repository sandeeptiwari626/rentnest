<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Tenant;
use App\Models\TenantLegalAcknowledgement;
use App\Models\TenantPortalNotice;
use App\Models\User;
use App\Support\TenantPortalNoticeContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TenantPortalNoticeService
{
    public function ensureDefault(Organization $organization, ?User $createdBy = null): TenantPortalNotice
    {
        $active = $this->activeForOrganization($organization->id);

        if ($active) {
            return $active;
        }

        $latest = TenantPortalNotice::query()
            ->forOrganization($organization->id)
            ->latest('id')
            ->first();

        if ($latest) {
            $this->activate($latest);

            return $latest->fresh();
        }

        return TenantPortalNotice::query()->create([
            'organization_id' => $organization->id,
            'created_by' => $createdBy?->id,
            'title' => TenantPortalNoticeContent::title(),
            'version' => TenantPortalNoticeContent::version(),
            'sections' => TenantPortalNoticeContent::sections(),
            'is_active' => true,
            'published_at' => now(),
        ]);
    }

    public function activeForOrganization(int $organizationId): ?TenantPortalNotice
    {
        return TenantPortalNotice::query()
            ->forOrganization($organizationId)
            ->where('is_active', true)
            ->first();
    }

    public function tenantHasAcknowledged(?Tenant $tenant, ?TenantPortalNotice $notice): bool
    {
        if ($tenant === null || $notice === null) {
            return true;
        }

        return TenantLegalAcknowledgement::query()
            ->where('tenant_id', $tenant->id)
            ->where('notice_id', $notice->id)
            ->where('status', 'acknowledged')
            ->exists();
    }

    public function userMustAcknowledge(User $user): bool
    {
        if (! $user->isTenant()) {
            return false;
        }

        $organizationId = $user->current_organization_id;

        if ($organizationId === null) {
            return false;
        }

        $notice = $this->activeForOrganization((int) $organizationId);

        if ($notice === null) {
            return false;
        }

        $tenant = $user->tenantProfile;

        if ($tenant === null) {
            return false;
        }

        return ! $this->tenantHasAcknowledged($tenant, $notice);
    }

    public function acknowledge(
        User $user,
        Tenant $tenant,
        TenantPortalNotice $notice,
        Request $request
    ): TenantLegalAcknowledgement {
        if ((int) $tenant->user_id !== (int) $user->id) {
            abort(403, 'This tenant profile does not belong to the signed-in account.');
        }

        if ((int) $tenant->organization_id !== (int) $notice->organization_id) {
            abort(403, 'This notice does not belong to the current organization.');
        }

        if (! $notice->is_active) {
            throw ValidationException::withMessages([
                'accepted' => 'This notice is no longer the active Tenant Portal Notice.',
            ]);
        }

        $existing = TenantLegalAcknowledgement::query()
            ->where('tenant_id', $tenant->id)
            ->where('notice_id', $notice->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return TenantLegalAcknowledgement::query()->create([
            'organization_id' => $notice->organization_id,
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'notice_id' => $notice->id,
            'notice_version' => $notice->version,
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
        ]);
    }

    public function activate(TenantPortalNotice $notice): void
    {
        DB::transaction(function () use ($notice) {
            TenantPortalNotice::query()
                ->forOrganization($notice->organization_id)
                ->where('id', '!=', $notice->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $notice->update([
                'is_active' => true,
                'published_at' => $notice->published_at ?? now(),
            ]);
        });
    }
}

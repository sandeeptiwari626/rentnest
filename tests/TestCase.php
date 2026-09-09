<?php

namespace Tests;

use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantPortalNoticeService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;

abstract class TestCase extends BaseTestCase
{
    protected function acknowledgeCurrentPortalNotice(User $user, ?Organization $organization = null): void
    {
        if ($organization === null) {
            $user->syncCurrentOrganization();
            $user->refresh();
            $organization = $user->currentOrganization;
        }

        if ($organization === null) {
            return;
        }

        $service = app(TenantPortalNoticeService::class);
        $notice = $service->ensureDefault($organization, $user);

        $tenant = Tenant::query()
            ->where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->first();

        if ($tenant === null) {
            return;
        }

        $service->acknowledge(
            $user,
            $tenant,
            $notice,
            Request::create('/tenant/legal-notice', 'POST', [], [], [], [
                'REMOTE_ADDR' => '127.0.0.1',
                'HTTP_USER_AGENT' => 'PHPUnit',
            ])
        );
    }
}

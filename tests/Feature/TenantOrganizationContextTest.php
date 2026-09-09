<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantOrganizationContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_home_uses_organization_that_has_the_tenant_profile(): void
    {
        $oldOrg = Organization::query()->create([
            'name' => 'Old Org',
            'slug' => 'old-org',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
        ]);

        $newOrg = Organization::query()->create([
            'name' => 'New Org',
            'slug' => 'new-org',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
        ]);

        $user = User::factory()->create([
            'current_organization_id' => $oldOrg->id,
        ]);

        $oldOrg->users()->attach($user->id, ['role' => OrganizationRole::Tenant->value]);
        $newOrg->users()->attach($user->id, ['role' => OrganizationRole::Tenant->value]);

        Tenant::query()->create([
            'organization_id' => $newOrg->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $this->acknowledgeCurrentPortalNotice($user, $newOrg);

        $this->actingAs($user)
            ->get('/tenant/home')
            ->assertOk();

        $this->assertSame($newOrg->id, $user->fresh()->current_organization_id);
    }

    public function test_inviting_existing_user_as_tenant_switches_their_organization(): void
    {
        $landlordOrg = Organization::query()->create([
            'name' => 'Landlord Org',
            'slug' => 'landlord-org',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
        ]);

        $otherOrg = Organization::query()->create([
            'name' => 'Other Org',
            'slug' => 'other-org',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
        ]);

        $landlord = User::factory()->create([
            'current_organization_id' => $landlordOrg->id,
        ]);
        $landlordOrg->users()->attach($landlord->id, ['role' => OrganizationRole::Landlord->value]);

        $existing = User::factory()->create([
            'email' => 'existing-tenant@example.com',
            'current_organization_id' => $otherOrg->id,
        ]);
        $otherOrg->users()->attach($existing->id, ['role' => OrganizationRole::Tenant->value]);

        $this->actingAs($landlord)
            ->post('/landlord/tenants', [
                'name' => 'Existing Tenant',
                'email' => 'existing-tenant@example.com',
                'create_account' => true,
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect();

        $this->assertSame($landlordOrg->id, $existing->fresh()->current_organization_id);

        $invited = $existing->fresh();
        $this->acknowledgeCurrentPortalNotice($invited);

        $this->actingAs($invited)
            ->get('/tenant/home')
            ->assertOk();
    }
}

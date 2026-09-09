<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\TenantLegalAcknowledgement;
use App\Models\TenantPortalNotice;
use App\Models\User;
use App\Support\TenantPortalNoticeContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantLegalNoticeTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_tenant_must_acknowledge_notice_before_portal_access(): void
    {
        [, $tenantUser] = $this->makeTenant();

        $this->actingAs($tenantUser)
            ->get('/tenant/home')
            ->assertRedirect(route('tenant.legal-notice.show'));

        $this->actingAs($tenantUser)
            ->get(route('tenant.legal-notice.show'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Tenant/LegalNotice')
                ->where('notice.version', '1.0')
                ->has('notice.sections', 12));
    }

    public function test_acknowledgement_unlocks_dashboard_and_is_not_required_again(): void
    {
        [, $tenantUser, $tenant] = $this->makeTenant();
        $notice = TenantPortalNotice::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($tenantUser)
            ->post(route('tenant.legal-notice.store'), [
                'notice_id' => $notice->id,
                'accepted' => '1',
            ])
            ->assertRedirect(route('tenant.home'));

        $this->actingAs($tenantUser)->get('/tenant/home')->assertOk();
        $this->actingAs($tenantUser)->get('/tenant/payments')->assertOk();

        $this->post('/logout');
        $this->post('/login', [
            'email' => $tenantUser->email,
            'password' => 'password',
        ])->assertRedirect('/tenant/home');

        $this->assertDatabaseHas('tenant_legal_acknowledgements', [
            'tenant_id' => $tenant->id,
            'user_id' => $tenantUser->id,
            'notice_id' => $notice->id,
            'notice_version' => '1.0',
            'status' => 'acknowledged',
        ]);

        $this->actingAs($tenantUser)
            ->get(route('tenant.legal-notice.show'))
            ->assertRedirect(route('tenant.home'));
    }

    public function test_acknowledgement_requires_confirmation_and_matching_active_notice(): void
    {
        [, $tenantUser] = $this->makeTenant();
        $notice = TenantPortalNotice::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($tenantUser)
            ->from(route('tenant.legal-notice.show'))
            ->post(route('tenant.legal-notice.store'), [
                'notice_id' => $notice->id,
            ])
            ->assertRedirect(route('tenant.legal-notice.show'))
            ->assertSessionHasErrors('accepted');

        $this->actingAs($tenantUser)
            ->from(route('tenant.legal-notice.show'))
            ->post(route('tenant.legal-notice.store'), [
                'notice_id' => $notice->id + 99,
                'accepted' => '1',
            ])
            ->assertRedirect(route('tenant.legal-notice.show'));

        $this->assertDatabaseCount('tenant_legal_acknowledgements', 0);
    }

    public function test_new_active_version_requires_a_fresh_acknowledgement(): void
    {
        [$organization, $tenantUser, $tenant] = $this->makeTenant();
        $landlord = $this->makeLandlord($organization);
        $original = TenantPortalNotice::query()->where('organization_id', $organization->id)->firstOrFail();

        $this->actingAs($tenantUser)
            ->post(route('tenant.legal-notice.store'), [
                'notice_id' => $original->id,
                'accepted' => '1',
            ])
            ->assertRedirect(route('tenant.home'));

        $this->actingAs($landlord)
            ->post(route('landlord.portal-notice.store'), [
                'title' => 'Tenant Portal Notice',
                'version' => '1.1',
                'activate' => true,
                'sections' => TenantPortalNoticeContent::sections(),
            ])
            ->assertRedirect(route('landlord.portal-notice.index'));

        $this->actingAs($tenantUser)
            ->get('/tenant/home')
            ->assertRedirect(route('tenant.legal-notice.show'));

        $updated = TenantPortalNotice::query()
            ->where('organization_id', $organization->id)
            ->where('version', '1.1')
            ->firstOrFail();

        $this->actingAs($tenantUser)
            ->post(route('tenant.legal-notice.store'), [
                'notice_id' => $updated->id,
                'accepted' => '1',
            ])
            ->assertRedirect(route('tenant.home'));

        $this->actingAs($tenantUser)->get('/tenant/home')->assertOk();

        $this->assertDatabaseCount('tenant_legal_acknowledgements', 2);
        $this->assertDatabaseHas('tenant_legal_acknowledgements', [
            'tenant_id' => $tenant->id,
            'notice_id' => $original->id,
            'notice_version' => '1.0',
        ]);
        $this->assertDatabaseHas('tenant_legal_acknowledgements', [
            'tenant_id' => $tenant->id,
            'notice_id' => $updated->id,
            'notice_version' => '1.1',
        ]);
    }

    public function test_backend_blocks_direct_portal_and_json_access_without_acknowledgement(): void
    {
        [, $tenantUser] = $this->makeTenant();

        $this->actingAs($tenantUser)
            ->get('/tenant/my-home')
            ->assertRedirect(route('tenant.legal-notice.show'));

        $this->actingAs($tenantUser)
            ->post('/tenant/maintenance', [
                'title' => 'Leaky tap',
                'description' => 'Kitchen sink',
            ])
            ->assertRedirect(route('tenant.legal-notice.show'));

        $this->actingAs($tenantUser)
            ->getJson('/tenant/home')
            ->assertForbidden()
            ->assertJsonFragment(['message' => 'Please acknowledge the Tenant Portal Notice before continuing.']);

        $this->actingAs($tenantUser)
            ->postJson('/tenant/maintenance', [
                'title' => 'Leaky tap',
                'description' => 'Kitchen sink',
            ])
            ->assertForbidden();
    }

    public function test_acknowledgement_records_are_isolated_and_never_overwritten(): void
    {
        [$organization, $firstUser, $firstTenant] = $this->makeTenant('First Org');
        [, $secondUser] = $this->makeTenant('Second Org');
        $otherOrg = Organization::query()->where('name', 'Second Org')->firstOrFail();
        $otherLandlord = $this->makeLandlord($otherOrg);

        $notice = TenantPortalNotice::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->firstOrFail();

        $this->travelTo(now()->subHour());

        $this->actingAs($firstUser)
            ->post(route('tenant.legal-notice.store'), [
                'notice_id' => $notice->id,
                'accepted' => '1',
            ])
            ->assertRedirect(route('tenant.home'));

        $acknowledgedAt = TenantLegalAcknowledgement::query()
            ->where('tenant_id', $firstTenant->id)
            ->value('acknowledged_at');

        $this->travelBack();

        $this->actingAs($firstUser)
            ->post(route('tenant.legal-notice.store'), [
                'notice_id' => $notice->id,
                'accepted' => '1',
            ]);

        $this->assertSame(
            (string) $acknowledgedAt,
            (string) TenantLegalAcknowledgement::query()->where('tenant_id', $firstTenant->id)->value('acknowledged_at')
        );
        $this->assertDatabaseCount('tenant_legal_acknowledgements', 1);

        $this->actingAs($secondUser)
            ->get('/tenant/home')
            ->assertRedirect(route('tenant.legal-notice.show'));

        $this->actingAs($secondUser)
            ->post(route('tenant.legal-notice.store'), [
                'notice_id' => $notice->id,
                'accepted' => '1',
            ])
            ->assertRedirect(route('tenant.legal-notice.show'));

        $this->assertDatabaseMissing('tenant_legal_acknowledgements', [
            'user_id' => $secondUser->id,
        ]);

        $this->actingAs($otherLandlord)
            ->get(route('landlord.portal-notice.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Landlord/PortalNotice/Index')
                ->has('acknowledgements', 0));
    }

    public function test_login_logout_and_landlord_admin_remain_available(): void
    {
        [$organization, $tenantUser] = $this->makeTenant();
        $landlord = $this->makeLandlord($organization);
        $password = 'password';

        $this->post('/login', [
            'email' => $tenantUser->email,
            'password' => $password,
        ])->assertRedirect(route('tenant.legal-notice.show'));

        $this->get('/tenant/home')->assertRedirect(route('tenant.legal-notice.show'));

        $this->get(route('tenant.legal-notice.download'))
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        $this->post('/login', [
            'email' => $tenantUser->email,
            'password' => $password,
        ])->assertRedirect(route('tenant.legal-notice.show'));

        $this->get('/tenant/home')->assertRedirect(route('tenant.legal-notice.show'));

        $this->actingAs($landlord)
            ->get('/landlord/dashboard')
            ->assertOk();

        $this->actingAs($landlord)
            ->get(route('landlord.portal-notice.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Landlord/PortalNotice/Index'));

        $this->actingAs($landlord)
            ->get(route('landlord.portal-notice.create'))
            ->assertOk();

        $this->actingAs($tenantUser)
            ->get(route('landlord.portal-notice.index'))
            ->assertForbidden();
    }

    /**
     * @return array{0: Organization, 1: User, 2: Tenant}
     */
    private function makeTenant(string $organizationName = 'Notice Org'): array
    {
        $organization = Organization::query()->firstOrCreate(
            ['name' => $organizationName],
            [
                'slug' => Organization::uniqueSlug($organizationName),
                'timezone' => 'Asia/Kolkata',
                'currency' => 'INR',
            ]
        );

        $user = User::factory()->create([
            'current_organization_id' => $organization->id,
        ]);

        $organization->users()->attach($user->id, ['role' => OrganizationRole::Tenant->value]);

        $tenant = Tenant::query()->create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);

        return [$organization, $user, $tenant];
    }

    private function makeLandlord(Organization $organization): User
    {
        $user = User::factory()->create([
            'current_organization_id' => $organization->id,
        ]);

        $organization->users()->attach($user->id, ['role' => OrganizationRole::Landlord->value]);

        return $user;
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentNestSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_landlord_can_access_dashboard(): void
    {
        $landlord = User::where('email', 'landlord@rentnest.test')->firstOrFail();

        $this->actingAs($landlord)
            ->get('/landlord/dashboard')
            ->assertOk();
    }

    public function test_tenant_cannot_access_landlord_dashboard(): void
    {
        $tenant = User::where('email', 'tenant@rentnest.test')->firstOrFail();

        $this->actingAs($tenant)
            ->get('/landlord/dashboard')
            ->assertForbidden();
    }

    public function test_tenant_can_access_home(): void
    {
        $tenant = User::where('email', 'tenant@rentnest.test')->firstOrFail();

        $this->actingAs($tenant)
            ->get('/tenant/home')
            ->assertOk();
    }

    public function test_landlord_cannot_access_tenant_home(): void
    {
        $landlord = User::where('email', 'landlord@rentnest.test')->firstOrFail();

        $this->actingAs($landlord)
            ->get('/tenant/home')
            ->assertForbidden();
    }

    public function test_landlord_can_view_properties_tenants_payments(): void
    {
        $landlord = User::where('email', 'landlord@rentnest.test')->firstOrFail();

        $this->actingAs($landlord)->get('/landlord/properties')->assertOk();
        $this->actingAs($landlord)->get('/landlord/tenants')->assertOk();
        $this->actingAs($landlord)->get('/landlord/leases')->assertOk();
        $this->actingAs($landlord)->get('/landlord/payments')->assertOk();
        $this->actingAs($landlord)->get('/landlord/maintenance')->assertOk();
        $this->actingAs($landlord)->get('/landlord/documents')->assertOk();
        $this->actingAs($landlord)->get('/landlord/expenses')->assertOk();
        $this->actingAs($landlord)->get('/landlord/notices')->assertOk();
        $this->actingAs($landlord)->get('/landlord/reports')->assertOk();
    }

    public function test_tenant_portal_pages(): void
    {
        $tenant = User::where('email', 'tenant@rentnest.test')->firstOrFail();

        $this->actingAs($tenant)->get('/tenant/my-home')->assertOk();
        $this->actingAs($tenant)->get('/tenant/payments')->assertOk();
        $this->actingAs($tenant)->get('/tenant/maintenance')->assertOk();
        $this->actingAs($tenant)->get('/tenant/documents')->assertOk();
        $this->actingAs($tenant)->get('/tenant/notices')->assertOk();
    }

    public function test_login_works_for_demo_accounts(): void
    {
        $this->post('/login', [
            'email' => 'landlord@rentnest.test',
            'password' => 'password',
        ])->assertRedirect('/landlord/dashboard');

        $this->post('/logout');

        $this->post('/login', [
            'email' => 'tenant@rentnest.test',
            'password' => 'password',
        ])->assertRedirect('/tenant/home');
    }
}

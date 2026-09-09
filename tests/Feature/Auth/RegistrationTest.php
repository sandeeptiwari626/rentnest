<?php

namespace Tests\Feature\Auth;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_new_landlords_can_register_and_reach_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Landlord',
            'organization_name' => 'Sunrise Homes',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('landlord.dashboard', absolute: false));

        $user = User::query()->where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        $organization = Organization::query()->where('slug', 'sunrise-homes')->first();
        $this->assertNotNull($organization);
        $this->assertSame('Sunrise Homes', $organization->name);
        $this->assertSame($organization->id, $user->current_organization_id);
        $this->assertTrue($user->isLandlord());
        $this->assertSame(OrganizationRole::Landlord, $user->organizationRole());
    }

    public function test_organization_name_defaults_from_landlord_name(): void
    {
        $this->post('/register', [
            'name' => 'Asha Patel',
            'email' => 'asha@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('landlord.dashboard', absolute: false));

        $this->assertDatabaseHas('organizations', [
            'name' => "Asha Patel's Properties",
            'slug' => 'asha-patels-properties',
        ]);
    }
}

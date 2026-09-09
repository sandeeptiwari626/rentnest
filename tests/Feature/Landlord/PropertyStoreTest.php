<?php

namespace Tests\Feature\Landlord;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_landlord_can_create_a_property_with_default_unit(): void
    {
        $this->post('/register', [
            'name' => 'Test Landlord',
            'organization_name' => 'Sunrise Homes',
            'email' => 'landlord-create@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('landlord.dashboard', absolute: false));

        $user = User::query()->where('email', 'landlord-create@example.com')->firstOrFail();

        $response = $this->actingAs($user)->post(route('landlord.properties.store'), [
            'name' => 'Lakeview Residency',
            'type' => 'apartment',
            'status' => 'vacant',
            'address' => '12 MG Road',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'postal_code' => '411001',
            'description' => 'Corner plot',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'area' => 950,
            'area_unit' => 'sqft',
            'rent_amount' => 18500,
        ]);

        $property = Property::query()->where('name', 'Lakeview Residency')->first();

        $this->assertNotNull($property);
        $response->assertRedirect(route('landlord.properties.show', $property, absolute: false));

        $this->assertSame($user->current_organization_id, $property->organization_id);
        $this->assertSame(2, $property->bedrooms);

        $unit = Unit::query()->where('property_id', $property->id)->first();

        $this->assertNotNull($unit);
        $this->assertSame('Unit 1', $unit->name);
        $this->assertSame('vacant', $unit->status->value);
        $this->assertSame(2, $unit->bedrooms);
        $this->assertEquals(18500, $unit->rent_amount);
    }

    public function test_landlord_can_create_a_property_without_optional_unit_details(): void
    {
        $this->post('/register', [
            'name' => 'Test Landlord',
            'organization_name' => 'Sunrise Homes',
            'email' => 'landlord-create-empty@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::query()->where('email', 'landlord-create-empty@example.com')->firstOrFail();

        $this->actingAs($user)->post(route('landlord.properties.store'), [
            'name' => 'Empty Unit Property',
            'type' => 'house',
            'address' => '1 Park Lane',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400001',
            'bedrooms' => '',
            'bathrooms' => '',
            'area' => '',
            'rent_amount' => '',
        ])->assertRedirect();

        $property = Property::query()->where('name', 'Empty Unit Property')->firstOrFail();
        $unit = Unit::query()->where('property_id', $property->id)->firstOrFail();

        $this->assertSame('Unit 1', $unit->name);
        $this->assertNull($unit->bedrooms);
        $this->assertNull($unit->rent_amount);
    }
}

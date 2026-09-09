<?php

namespace Tests\Feature\Landlord;

use App\Models\Document;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_landlord_can_upload_a_document(): void
    {
        Storage::fake('local');

        $this->post('/register', [
            'name' => 'Test Landlord',
            'organization_name' => 'Sunrise Homes',
            'email' => 'docs@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::query()->where('email', 'docs@example.com')->firstOrFail();
        $file = UploadedFile::fake()->create('lease.pdf', 120, 'application/pdf');

        $this->actingAs($user)->post(route('landlord.documents.store'), [
            'title' => 'Signed lease',
            'type' => 'lease_agreement',
            'file' => $file,
            'visible_to_tenant' => 1,
        ])->assertRedirect(route('landlord.documents.index', absolute: false));

        $document = Document::query()->where('title', 'Signed lease')->first();

        $this->assertNotNull($document);
        $this->assertSame('lease.pdf', $document->original_name);
        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_landlord_can_attach_a_document_to_a_property(): void
    {
        Storage::fake('local');

        $this->post('/register', [
            'name' => 'Test Landlord',
            'organization_name' => 'Sunrise Homes',
            'email' => 'docs-property@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::query()->where('email', 'docs-property@example.com')->firstOrFail();

        $property = Property::query()->create([
            'organization_id' => $user->current_organization_id,
            'name' => 'Lakeview',
            'type' => 'apartment',
            'status' => 'vacant',
            'address' => '12 MG Road',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'postal_code' => '411001',
        ]);

        $this->actingAs($user)->post(route('landlord.documents.store'), [
            'title' => 'Property papers',
            'type' => 'property',
            'documentable_type' => 'property',
            'property_id' => $property->id,
            'file' => UploadedFile::fake()->image('plan.jpg'),
        ])->assertRedirect(route('landlord.documents.index', absolute: false));

        $document = Document::query()->where('title', 'Property papers')->firstOrFail();

        $this->assertSame(Property::class, $document->documentable_type);
        $this->assertSame($property->id, $document->documentable_id);
        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_landlord_can_save_property_photos(): void
    {
        Storage::fake('local');

        $this->post('/register', [
            'name' => 'Test Landlord',
            'organization_name' => 'Sunrise Homes',
            'email' => 'photos@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::query()->where('email', 'photos@example.com')->firstOrFail();

        $this->actingAs($user)->post(route('landlord.properties.store'), [
            'name' => 'Photo House',
            'type' => 'house',
            'address' => '1 Park Lane',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400001',
            'photos' => [UploadedFile::fake()->image('front.jpg', 800, 600)],
        ])->assertRedirect();

        $property = Property::query()->where('name', 'Photo House')->firstOrFail();

        $this->assertNotEmpty($property->photos);
        Storage::disk('local')->assertExists($property->photos[0]);
    }
}

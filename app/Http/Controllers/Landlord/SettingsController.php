<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Property;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    use ResolvesOrganization;

    public function index(): Response
    {
        $this->authorize('viewAny', Property::class);

        $organization = Organization::query()->findOrFail($this->organizationId());

        return Inertia::render('Landlord/Settings/Index', [
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'email' => $organization->email,
                'phone' => $organization->phone,
                'timezone' => $organization->timezone,
                'currency' => $organization->currency,
            ],
        ]);
    }
}

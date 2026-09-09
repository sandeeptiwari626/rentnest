<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\LeaseStatus;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\StorePropertyRequest;
use App\Http\Requests\Landlord\UpdatePropertyRequest;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PropertyController extends Controller
{
    use ResolvesOrganization;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Property::class);

        $orgId = $this->organizationId();
        $search = trim((string) $request->string('search'));

        $properties = Property::query()
            ->forOrganization($orgId)
            ->withCount([
                'units',
                'units as occupied_units_count' => fn ($q) => $q->where('status', PropertyStatus::Occupied),
            ])
            ->with(['units' => fn ($q) => $q->select('id', 'property_id', 'rent_amount', 'status')])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Property $property) => [
                'id' => $property->id,
                'name' => $property->name,
                'type' => $property->type?->value,
                'type_label' => $property->type?->label(),
                'status' => $property->status?->value,
                'status_label' => $property->status?->label(),
                'status_color' => $property->status?->color(),
                'city' => $property->city,
                'address' => $property->address,
                'units_count' => $property->units_count,
                'occupied_units_count' => $property->occupied_units_count,
                'rent_from' => $property->units->min('rent_amount'),
                'photos_count' => count($property->photos ?? []),
            ]);

        return Inertia::render('Landlord/Properties/Index', [
            'properties' => $properties,
            'filters' => [
                'search' => $search,
                'status' => $request->string('status')->toString(),
            ],
            'statusOptions' => $this->enumOptions(PropertyStatus::class),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Property::class);

        return Inertia::render('Landlord/Properties/Create', [
            'typeOptions' => $this->enumOptions(PropertyType::class),
            'statusOptions' => $this->enumOptions(PropertyStatus::class),
        ]);
    }

    public function store(StorePropertyRequest $request): RedirectResponse
    {
        $this->authorize('create', Property::class);

        $orgId = $this->organizationId();
        $data = $request->safe()->except(['photos', 'rent_amount']);

        $property = DB::transaction(function () use ($request, $orgId, $data) {
            $property = Property::query()->create([
                'organization_id' => $orgId,
                'name' => $data['name'],
                'type' => $data['type'],
                'status' => $data['status'] ?? PropertyStatus::Vacant->value,
                'address' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'postal_code' => $data['postal_code'],
                'description' => $data['description'] ?? null,
                'bedrooms' => $data['bedrooms'] ?? null,
                'bathrooms' => $data['bathrooms'] ?? null,
                'area' => $data['area'] ?? null,
                'area_unit' => $data['area_unit'] ?? 'sqft',
                'photos' => [],
            ]);

            Unit::query()->create([
                'organization_id' => $orgId,
                'property_id' => $property->id,
                'name' => 'Unit 1',
                'status' => PropertyStatus::Vacant,
                'bedrooms' => $data['bedrooms'] ?? null,
                'bathrooms' => $data['bathrooms'] ?? null,
                'area' => $data['area'] ?? null,
                'rent_amount' => $request->validated('rent_amount'),
            ]);

            $photos = $request->file('photos');

            if (is_array($photos) && $photos !== []) {
                $paths = [];
                foreach ($photos as $photo) {
                    if ($photo === null) {
                        continue;
                    }

                    $paths[] = $photo->store("properties/{$property->id}", 'local');
                }

                if ($paths !== []) {
                    $property->update(['photos' => $paths]);
                }
            }

            return $property;
        });

        return redirect()
            ->route('landlord.properties.show', $property)
            ->with('success', 'Property created successfully.');
    }

    public function show(Property $property): Response
    {
        $this->authorize('view', $property);

        $property->load([
            'units' => fn ($q) => $q->orderBy('name'),
        ]);

        $activeLease = $property->leases()
            ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring])
            ->with(['tenant:id,name,email,phone', 'unit:id,name'])
            ->latest('start_date')
            ->first();

        return Inertia::render('Landlord/Properties/Show', [
            'property' => [
                'id' => $property->id,
                'name' => $property->name,
                'type' => $property->type?->value,
                'type_label' => $property->type?->label(),
                'status' => $property->status?->value,
                'status_label' => $property->status?->label(),
                'status_color' => $property->status?->color(),
                'address' => $property->address,
                'city' => $property->city,
                'state' => $property->state,
                'postal_code' => $property->postal_code,
                'description' => $property->description,
                'bedrooms' => $property->bedrooms,
                'bathrooms' => $property->bathrooms,
                'area' => $property->area,
                'area_unit' => $property->area_unit,
                'photos' => collect($property->photos ?? [])->values()->map(fn ($path, $index) => [
                    'path' => $path,
                    'url' => route('landlord.properties.photo', [$property, $index]),
                ]),
                'units' => $property->units->map(fn (Unit $unit) => [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'status' => $unit->status?->value,
                    'status_label' => $unit->status?->label(),
                    'status_color' => $unit->status?->color(),
                    'bedrooms' => $unit->bedrooms,
                    'bathrooms' => $unit->bathrooms,
                    'area' => $unit->area,
                    'rent_amount' => $unit->rent_amount !== null ? (float) $unit->rent_amount : null,
                    'notes' => $unit->notes,
                ]),
                'active_lease' => $activeLease ? [
                    'id' => $activeLease->id,
                    'monthly_rent' => (float) $activeLease->monthly_rent,
                    'start_date' => $activeLease->start_date?->toDateString(),
                    'end_date' => $activeLease->end_date?->toDateString(),
                    'status' => $activeLease->status?->value,
                    'status_label' => $activeLease->status?->label(),
                    'status_color' => $activeLease->status?->color(),
                    'unit' => $activeLease->unit?->name,
                    'tenant' => $activeLease->tenant ? [
                        'id' => $activeLease->tenant->id,
                        'name' => $activeLease->tenant->name,
                        'email' => $activeLease->tenant->email,
                        'phone' => $activeLease->tenant->phone,
                    ] : null,
                ] : null,
            ],
        ]);
    }

    public function edit(Property $property): Response
    {
        $this->authorize('update', $property);

        return Inertia::render('Landlord/Properties/Edit', [
            'property' => [
                'id' => $property->id,
                'name' => $property->name,
                'type' => $property->type?->value,
                'status' => $property->status?->value,
                'address' => $property->address,
                'city' => $property->city,
                'state' => $property->state,
                'postal_code' => $property->postal_code,
                'description' => $property->description,
                'bedrooms' => $property->bedrooms,
                'bathrooms' => $property->bathrooms,
                'area' => $property->area,
                'area_unit' => $property->area_unit,
                'photos' => collect($property->photos ?? [])->values()->map(fn ($path, $index) => [
                    'path' => $path,
                    'url' => route('landlord.properties.photo', [$property, $index]),
                ]),
            ],
            'typeOptions' => $this->enumOptions(PropertyType::class),
            'statusOptions' => $this->enumOptions(PropertyStatus::class),
        ]);
    }

    public function update(UpdatePropertyRequest $request, Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

        $data = $request->safe()->except(['photos', 'remove_photos']);
        $photos = collect($property->photos ?? []);

        if ($request->filled('remove_photos')) {
            $toRemove = collect($request->input('remove_photos', []));
            foreach ($toRemove as $path) {
                if ($photos->contains($path)) {
                    Storage::disk('local')->delete($path);
                }
            }
            $photos = $photos->reject(fn ($path) => $toRemove->contains($path))->values();
        }

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photos->push($photo->store("properties/{$property->id}", 'local'));
            }
        }

        $property->update([
            ...$data,
            'photos' => $photos->values()->all(),
        ]);

        return redirect()
            ->route('landlord.properties.show', $property)
            ->with('success', 'Property updated successfully.');
    }

    public function destroy(Property $property): RedirectResponse
    {
        $this->authorize('delete', $property);

        foreach ($property->photos ?? [] as $path) {
            Storage::disk('local')->delete($path);
        }

        $property->delete();

        return redirect()
            ->route('landlord.properties.index')
            ->with('success', 'Property deleted.');
    }

    public function photo(Property $property, int $index): StreamedResponse
    {
        $this->authorize('view', $property);

        $photos = $property->photos ?? [];

        if (! isset($photos[$index])) {
            abort(404);
        }

        $path = $photos[$index];

        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path);
    }

    /**
     * @param  class-string<\BackedEnum>  $enum
     * @return array<int, array{value: string, label: string}>
     */
    protected function enumOptions(string $enum): array
    {
        return collect($enum::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => method_exists($case, 'label') ? $case->label() : $case->name,
        ])->values()->all();
    }
}

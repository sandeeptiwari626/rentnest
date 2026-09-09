<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\DocumentType;
use App\Enums\LeaseStatus;
use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\StoreDocumentRequest;
use App\Models\Document;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\DocumentUploadedNotification;
use App\Support\PrivateUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    use ResolvesOrganization;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Document::class);

        $orgId = $this->organizationId();

        $documents = Document::query()
            ->forOrganization($orgId)
            ->with(['uploadedBy:id,name'])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->string('search'));
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('original_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Document $document) => [
                'id' => $document->id,
                'title' => $document->title,
                'type' => $document->type?->value,
                'type_label' => $document->type?->label(),
                'original_name' => $document->original_name,
                'file_size' => $document->file_size,
                'visible_to_tenant' => $document->visible_to_tenant,
                'uploaded_by' => $document->uploadedBy?->name,
                'created_at' => $document->created_at?->toDateString(),
                'documentable_type' => class_basename((string) $document->documentable_type),
            ]);

        return Inertia::render('Landlord/Documents/Index', [
            'documents' => $documents,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'type' => $request->string('type')->toString(),
            ],
            'typeOptions' => $this->enumOptions(DocumentType::class),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Document::class);
        $orgId = $this->organizationId();

        return Inertia::render('Landlord/Documents/Create', [
            'typeOptions' => $this->enumOptions(DocumentType::class),
            'properties' => Property::query()->forOrganization($orgId)->orderBy('name')->get(['id', 'name']),
            'tenants' => Tenant::query()->forOrganization($orgId)->orderBy('name')->get(['id', 'name']),
            'leases' => Lease::query()
                ->forOrganization($orgId)
                ->with(['property:id,name', 'tenant:id,name'])
                ->latest()
                ->get(['id', 'property_id', 'tenant_id'])
                ->map(fn (Lease $lease) => [
                    'id' => $lease->id,
                    'label' => ($lease->property?->name ?? 'Property').' · '.($lease->tenant?->name ?? 'Tenant'),
                ]),
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $this->authorize('create', Document::class);

        $file = $request->file('file');
        $path = PrivateUpload::store($file, 'documents/'.$this->organizationId());

        [$type, $id] = $this->resolveDocumentable($request);

        $document = Document::query()->create([
            'organization_id' => $this->organizationId(),
            'uploaded_by' => $request->user()->id,
            'documentable_type' => $type,
            'documentable_id' => $id,
            'type' => $request->input('type'),
            'title' => $request->string('title')->toString(),
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'visible_to_tenant' => $request->boolean('visible_to_tenant', true),
        ]);

        if ($document->visible_to_tenant) {
            $recipients = $this->documentRecipients($document);

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new DocumentUploadedNotification($document));
            }
        }

        return redirect()
            ->route('landlord.documents.index')
            ->with('success', 'Document uploaded successfully.');
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('download', $document);

        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_name
        );
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return redirect()
            ->route('landlord.documents.index')
            ->with('success', 'Document deleted.');
    }

    /**
     * @return array{0: ?string, 1: ?int}
     */
    protected function resolveDocumentable(StoreDocumentRequest $request): array
    {
        if ($request->filled('lease_id') || $request->input('documentable_type') === 'lease') {
            $id = $request->integer('lease_id') ?: $request->integer('documentable_id');

            return [Lease::class, $id];
        }

        if ($request->filled('tenant_id') || $request->input('documentable_type') === 'tenant') {
            $id = $request->integer('tenant_id') ?: $request->integer('documentable_id');

            return [Tenant::class, $id];
        }

        if ($request->filled('property_id') || $request->input('documentable_type') === 'property') {
            $id = $request->integer('property_id') ?: $request->integer('documentable_id');

            return [Property::class, $id];
        }

        return [null, null];
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    protected function documentRecipients(Document $document)
    {
        $document->loadMissing('documentable');

        $documentable = $document->documentable;

        if ($documentable instanceof Tenant) {
            $documentable->loadMissing('user');

            return collect($documentable->user ? [$documentable->user] : []);
        }

        if ($documentable instanceof Lease) {
            $documentable->loadMissing('tenant.user');
            $user = $documentable->tenant?->user;

            return collect($user ? [$user] : []);
        }

        if ($documentable instanceof Property) {
            $tenantIds = Lease::query()
                ->forOrganization($document->organization_id)
                ->where('property_id', $documentable->id)
                ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring])
                ->pluck('tenant_id')
                ->unique()
                ->filter();

            return Tenant::query()
                ->forOrganization($document->organization_id)
                ->whereIn('id', $tenantIds)
                ->whereNotNull('user_id')
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter()
                ->unique('id')
                ->values();
        }

        return collect();
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

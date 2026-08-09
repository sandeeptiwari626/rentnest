<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Concerns\ResolvesTenantProfile;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    use ResolvesOrganization;
    use ResolvesTenantProfile;

    public function index(): Response
    {
        $this->authorize('viewAny', Document::class);

        $tenant = $this->tenantProfile();
        $organizationId = $this->organizationId();

        $documents = $this->tenantDocumentsQuery($tenant, $organizationId)
            ->latest()
            ->paginate(15)
            ->through(fn (Document $document) => [
                'id' => $document->id,
                'title' => $document->title,
                'type' => $document->type?->value,
                'type_label' => $document->type?->label(),
                'original_name' => $document->original_name,
                'mime_type' => $document->mime_type,
                'file_size' => $document->file_size,
                'file_size_label' => $this->formatBytes($document->file_size),
                'created_at' => $document->created_at?->toDateString(),
            ]);

        return Inertia::render('Tenant/Documents/Index', [
            'documents' => $documents,
        ]);
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('download', $document);

        $tenant = $this->tenantProfile();

        if ((int) $document->organization_id !== $this->organizationId()
            || ! $document->visible_to_tenant
            || ! $this->documentBelongsToTenant($document, $tenant)
        ) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($document->file_path)
            && ! Storage::disk('public')->exists($document->file_path)
        ) {
            abort(404, 'File not found.');
        }

        $disk = Storage::disk('local')->exists($document->file_path) ? 'local' : 'public';

        return Storage::disk($disk)->download(
            $document->file_path,
            $document->original_name ?: basename($document->file_path)
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Document>
     */
    protected function tenantDocumentsQuery(Tenant $tenant, int $organizationId)
    {
        $leaseIds = Lease::query()->where('tenant_id', $tenant->id)->pluck('id');
        $propertyIds = Lease::query()->where('tenant_id', $tenant->id)->pluck('property_id');
        $maintenanceIds = MaintenanceRequest::query()
            ->where('tenant_id', $tenant->id)
            ->pluck('id');

        return Document::query()
            ->forOrganization($organizationId)
            ->where('visible_to_tenant', true)
            ->where(function ($q) use ($tenant, $leaseIds, $propertyIds, $maintenanceIds) {
                $q->where(function ($inner) use ($tenant) {
                    $inner->where('documentable_type', Tenant::class)
                        ->where('documentable_id', $tenant->id);
                })
                    ->orWhere(function ($inner) use ($leaseIds) {
                        $inner->where('documentable_type', Lease::class)
                            ->whereIn('documentable_id', $leaseIds);
                    })
                    ->orWhere(function ($inner) use ($propertyIds) {
                        $inner->where('documentable_type', Property::class)
                            ->whereIn('documentable_id', $propertyIds);
                    })
                    ->orWhere(function ($inner) use ($maintenanceIds) {
                        $inner->where('documentable_type', MaintenanceRequest::class)
                            ->whereIn('documentable_id', $maintenanceIds);
                    });
            });
    }

    protected function documentBelongsToTenant(Document $document, Tenant $tenant): bool
    {
        return $this->tenantDocumentsQuery($tenant, $this->organizationId())
            ->where('documents.id', $document->id)
            ->exists();
    }

    protected function formatBytes(?int $bytes): string
    {
        if ($bytes === null || $bytes <= 0) {
            return '—';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return round($bytes / (1024 ** $power), 1).' '.$units[$power];
    }
}

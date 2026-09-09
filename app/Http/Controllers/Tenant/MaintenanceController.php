<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\LeaseStatus;
use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Concerns\ResolvesTenantProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreMaintenanceCommentRequest;
use App\Http\Requests\Tenant\StoreMaintenanceRequest;
use App\Models\Lease;
use App\Models\MaintenanceComment;
use App\Models\MaintenanceRequest;
use App\Support\PrivateUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaintenanceController extends Controller
{
    use ResolvesOrganization;
    use ResolvesTenantProfile;

    public function index(): Response
    {
        $this->authorize('viewAny', MaintenanceRequest::class);

        $tenant = $this->tenantProfile();
        $organizationId = $this->organizationId();

        $requests = MaintenanceRequest::query()
            ->forOrganization($organizationId)
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->paginate(12)
            ->through(fn (MaintenanceRequest $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'category' => $item->category?->value,
                'category_label' => $item->category?->label(),
                'priority' => $item->priority?->value,
                'priority_label' => $item->priority?->label(),
                'priority_color' => $item->priority?->color(),
                'status' => $item->status?->value,
                'status_label' => $item->status?->label(),
                'status_color' => $item->status?->color(),
                'created_at' => $item->created_at?->toIso8601String(),
            ]);

        return Inertia::render('Tenant/Maintenance/Index', [
            'requests' => $requests,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', MaintenanceRequest::class);

        $tenant = $this->tenantProfile();
        $lease = $this->activeLease($tenant->id);

        return Inertia::render('Tenant/Maintenance/Create', [
            'categories' => collect(MaintenanceCategory::options())
                ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                ->values(),
            'priorities' => collect(MaintenancePriority::options())
                ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                ->values(),
            'home' => $lease ? [
                'property_name' => $lease->property?->name,
                'unit_name' => $lease->unit?->name,
            ] : null,
        ]);
    }

    public function store(StoreMaintenanceRequest $request): RedirectResponse
    {
        $this->authorize('create', MaintenanceRequest::class);

        $tenant = $this->tenantProfile();
        $organizationId = $this->organizationId();
        $lease = $this->activeLease($tenant->id);

        if ($lease === null) {
            return back()->with('error', 'You need an active lease to submit a maintenance request.');
        }

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = PrivateUpload::store($photo, 'maintenance/'.$tenant->id, 'photos');
            }
        }

        $maintenance = MaintenanceRequest::query()->create([
            'organization_id' => $organizationId,
            'property_id' => $lease->property_id,
            'unit_id' => $lease->unit_id,
            'tenant_id' => $tenant->id,
            'reported_by' => $request->user()->id,
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'category' => $request->validated('category'),
            'priority' => $request->validated('priority'),
            'status' => MaintenanceStatus::Open,
            'photos' => $photoPaths ?: null,
        ]);

        MaintenanceComment::query()->create([
            'maintenance_request_id' => $maintenance->id,
            'user_id' => $request->user()->id,
            'body' => 'Maintenance request submitted.',
            'event_type' => 'status_change',
            'from_status' => null,
            'to_status' => MaintenanceStatus::Open->value,
        ]);

        return redirect()
            ->route('tenant.maintenance.show', $maintenance)
            ->with('success', 'Maintenance request submitted.');
    }

    public function show(MaintenanceRequest $maintenance): Response
    {
        $this->ensureTenantRequest($maintenance);
        $this->authorize('view', $maintenance);

        $maintenance->load([
            'property:id,name',
            'unit:id,name',
            'comments.user:id,name',
        ]);

        $timeline = $maintenance->comments
            ->sortBy('created_at')
            ->values()
            ->map(function (MaintenanceComment $comment) {
                $isStatus = $comment->event_type === 'status_change';

                return [
                    'title' => $isStatus
                        ? ($comment->to_status
                            ? 'Status → '.MaintenanceStatus::tryFrom($comment->to_status)?->label()
                            : 'Status updated')
                        : ($comment->user?->name ?? 'Comment'),
                    'description' => $comment->body,
                    'date' => $comment->created_at?->timezone(config('app.timezone'))->format('d M Y, g:i A'),
                    'status' => $isStatus ? 'info' : 'default',
                ];
            });

        $photos = collect($maintenance->photos ?? [])
            ->values()
            ->map(fn ($path, $index) => [
                'index' => $index,
                'url' => route('tenant.maintenance.photos.show', [$maintenance, $index]),
                'name' => basename($path),
            ]);

        return Inertia::render('Tenant/Maintenance/Show', [
            'request' => [
                'id' => $maintenance->id,
                'title' => $maintenance->title,
                'description' => $maintenance->description,
                'category' => $maintenance->category?->value,
                'category_label' => $maintenance->category?->label(),
                'priority' => $maintenance->priority?->value,
                'priority_label' => $maintenance->priority?->label(),
                'priority_color' => $maintenance->priority?->color(),
                'status' => $maintenance->status?->value,
                'status_label' => $maintenance->status?->label(),
                'status_color' => $maintenance->status?->color(),
                'property_name' => $maintenance->property?->name,
                'unit_name' => $maintenance->unit?->name,
                'created_at' => $maintenance->created_at?->toIso8601String(),
                'acknowledged_at' => $maintenance->acknowledged_at?->toIso8601String(),
                'resolved_at' => $maintenance->resolved_at?->toIso8601String(),
                'photos' => $photos,
            ],
            'timeline' => $timeline,
        ]);
    }

    public function storeComment(
        StoreMaintenanceCommentRequest $request,
        MaintenanceRequest $maintenance
    ): RedirectResponse {
        $this->ensureTenantRequest($maintenance);
        $this->authorize('comment', $maintenance);

        MaintenanceComment::query()->create([
            'maintenance_request_id' => $maintenance->id,
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
            'event_type' => 'comment',
        ]);

        return back()->with('success', 'Comment added.');
    }

    public function showPhoto(MaintenanceRequest $maintenance, int $index): StreamedResponse
    {
        $this->ensureTenantRequest($maintenance);
        $this->authorize('view', $maintenance);

        $photos = $maintenance->photos ?? [];

        if (! isset($photos[$index])) {
            abort(404);
        }

        $path = $photos[$index];

        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path);
    }

    protected function ensureTenantRequest(MaintenanceRequest $maintenance): void
    {
        $tenant = $this->tenantProfile();

        if ((int) $maintenance->organization_id !== $this->organizationId()
            || (int) $maintenance->tenant_id !== (int) $tenant->id
        ) {
            abort(404);
        }
    }

    protected function activeLease(int $tenantId): ?Lease
    {
        return Lease::query()
            ->forOrganization($this->organizationId())
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring])
            ->with(['property:id,name', 'unit:id,name'])
            ->orderByDesc('start_date')
            ->first();
    }
}

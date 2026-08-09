<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\StoreMaintenanceCommentRequest;
use App\Http\Requests\Landlord\UpdateMaintenanceRequest;
use App\Models\MaintenanceComment;
use App\Models\MaintenanceRequest;
use App\Notifications\MaintenanceUpdatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceController extends Controller
{
    use ResolvesOrganization;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', MaintenanceRequest::class);

        $orgId = $this->organizationId();

        $requests = MaintenanceRequest::query()
            ->forOrganization($orgId)
            ->with(['property:id,name', 'tenant:id,name', 'unit:id,name'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (MaintenanceRequest $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'property' => $item->property?->name,
                'unit' => $item->unit?->name,
                'tenant' => $item->tenant?->name,
                'category' => $item->category?->value,
                'category_label' => $item->category?->label(),
                'priority' => $item->priority?->value,
                'priority_label' => $item->priority?->label(),
                'priority_color' => $item->priority?->color(),
                'status' => $item->status?->value,
                'status_label' => $item->status?->label(),
                'status_color' => $item->status?->color(),
                'scheduled_at' => $item->scheduled_at?->toDateString(),
                'created_at' => $item->created_at?->toDateString(),
            ]);

        return Inertia::render('Landlord/Maintenance/Index', [
            'requests' => $requests,
            'filters' => [
                'status' => $request->string('status')->toString(),
                'priority' => $request->string('priority')->toString(),
            ],
            'statusOptions' => $this->enumOptions(MaintenanceStatus::class),
            'priorityOptions' => $this->enumOptions(MaintenancePriority::class),
        ]);
    }

    public function show(MaintenanceRequest $maintenance): Response
    {
        $this->authorize('view', $maintenance);

        $maintenance->load([
            'property:id,name,address,city',
            'unit:id,name',
            'tenant:id,name,email,phone',
            'reportedBy:id,name',
            'comments.user:id,name',
        ]);

        $timeline = $maintenance->comments
            ->sortBy('created_at')
            ->values()
            ->map(fn (MaintenanceComment $comment) => [
                'title' => $comment->event_type === 'status_change'
                    ? 'Status changed'
                    : 'Comment',
                'description' => $comment->body,
                'date' => $comment->created_at?->format('d M Y, g:i A'),
                'status' => $comment->event_type === 'status_change' ? 'warning' : 'info',
                'user' => $comment->user?->name,
            ]);

        return Inertia::render('Landlord/Maintenance/Show', [
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
                'scheduled_at' => $maintenance->scheduled_at?->format('Y-m-d\TH:i'),
                'acknowledged_at' => $maintenance->acknowledged_at?->toDateTimeString(),
                'resolved_at' => $maintenance->resolved_at?->toDateTimeString(),
                'created_at' => $maintenance->created_at?->toDateTimeString(),
                'property' => $maintenance->property,
                'unit' => $maintenance->unit?->name,
                'tenant' => $maintenance->tenant,
                'reported_by' => $maintenance->reportedBy?->name,
            ],
            'timeline' => $timeline,
            'statusOptions' => $this->enumOptions(MaintenanceStatus::class),
            'priorityOptions' => $this->enumOptions(MaintenancePriority::class),
        ]);
    }

    public function update(UpdateMaintenanceRequest $request, MaintenanceRequest $maintenance): RedirectResponse
    {
        $this->authorize('update', $maintenance);

        $fromStatus = $maintenance->status;
        $toStatus = MaintenanceStatus::from($request->string('status')->toString());

        $data = [
            'status' => $toStatus,
        ];

        if ($request->filled('priority')) {
            $data['priority'] = $request->input('priority');
        }

        if ($request->filled('scheduled_at')) {
            $data['scheduled_at'] = $request->input('scheduled_at');
        }

        if ($toStatus === MaintenanceStatus::Acknowledged && ! $maintenance->acknowledged_at) {
            $data['acknowledged_at'] = now();
        }

        if (in_array($toStatus, [MaintenanceStatus::Resolved, MaintenanceStatus::Closed], true) && ! $maintenance->resolved_at) {
            $data['resolved_at'] = now();
        }

        $maintenance->update($data);

        $commentBody = $request->input('comment');
        if ($fromStatus !== $toStatus) {
            MaintenanceComment::query()->create([
                'maintenance_request_id' => $maintenance->id,
                'user_id' => $request->user()->id,
                'body' => $commentBody
                    ?: 'Status changed from '.$fromStatus->label().' to '.$toStatus->label().'.',
                'event_type' => 'status_change',
                'from_status' => $fromStatus->value,
                'to_status' => $toStatus->value,
            ]);

            $maintenance->loadMissing('tenant.user');
            $tenantUser = $maintenance->tenant?->user;

            if ($tenantUser) {
                $tenantUser->notify(new MaintenanceUpdatedNotification($maintenance));
            }
        } elseif (filled($commentBody)) {
            MaintenanceComment::query()->create([
                'maintenance_request_id' => $maintenance->id,
                'user_id' => $request->user()->id,
                'body' => $commentBody,
                'event_type' => 'comment',
            ]);
        }

        return back()->with('success', 'Maintenance request updated.');
    }

    public function storeComment(StoreMaintenanceCommentRequest $request, MaintenanceRequest $maintenance): RedirectResponse
    {
        $this->authorize('comment', $maintenance);

        MaintenanceComment::query()->create([
            'maintenance_request_id' => $maintenance->id,
            'user_id' => $request->user()->id,
            'body' => $request->string('body')->toString(),
            'event_type' => 'comment',
        ]);

        return back()->with('success', 'Comment added.');
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

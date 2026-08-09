<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\LeaseStatus;
use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\StoreNoticeRequest;
use App\Models\Lease;
use App\Models\Notice;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\NewNoticeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class NoticeController extends Controller
{
    use ResolvesOrganization;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Notice::class);

        $orgId = $this->organizationId();

        $notices = Notice::query()
            ->forOrganization($orgId)
            ->with(['property:id,name', 'tenant:id,name', 'createdBy:id,name'])
            ->latest('publish_date')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Notice $notice) => [
                'id' => $notice->id,
                'title' => $notice->title,
                'publish_date' => $notice->publish_date?->toDateString(),
                'expiry_date' => $notice->expiry_date?->toDateString(),
                'property' => $notice->property?->name,
                'tenant' => $notice->tenant?->name,
                'created_by' => $notice->createdBy?->name,
                'is_active' => $notice->publish_date?->lte(now())
                    && ($notice->expiry_date === null || $notice->expiry_date->gte(now()->startOfDay())),
            ]);

        return Inertia::render('Landlord/Notices/Index', [
            'notices' => $notices,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Notice::class);
        $orgId = $this->organizationId();

        return Inertia::render('Landlord/Notices/Create', [
            'properties' => Property::query()->forOrganization($orgId)->orderBy('name')->get(['id', 'name']),
            'tenants' => Tenant::query()->forOrganization($orgId)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreNoticeRequest $request): RedirectResponse
    {
        $this->authorize('create', Notice::class);

        $notice = Notice::query()->create([
            ...$request->validated(),
            'organization_id' => $this->organizationId(),
            'created_by' => $request->user()->id,
        ]);

        $recipients = $this->noticeRecipients($notice);

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new NewNoticeNotification($notice));
        }

        return redirect()
            ->route('landlord.notices.show', $notice)
            ->with('success', 'Notice published successfully.');
    }

    public function show(Notice $notice): Response
    {
        $this->authorize('view', $notice);

        $notice->load(['property:id,name', 'tenant:id,name', 'createdBy:id,name', 'reads']);

        return Inertia::render('Landlord/Notices/Show', [
            'notice' => [
                'id' => $notice->id,
                'title' => $notice->title,
                'message' => $notice->message,
                'publish_date' => $notice->publish_date?->toDateString(),
                'expiry_date' => $notice->expiry_date?->toDateString(),
                'property' => $notice->property?->name,
                'tenant' => $notice->tenant?->name,
                'created_by' => $notice->createdBy?->name,
                'reads_count' => $notice->reads->count(),
            ],
        ]);
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        $this->authorize('delete', $notice);

        $notice->delete();

        return redirect()
            ->route('landlord.notices.index')
            ->with('success', 'Notice deleted.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    protected function noticeRecipients(Notice $notice)
    {
        $orgId = $notice->organization_id;

        if ($notice->tenant_id) {
            $user = Tenant::query()
                ->forOrganization($orgId)
                ->whereKey($notice->tenant_id)
                ->with('user')
                ->first()
                ?->user;

            return collect($user ? [$user] : []);
        }

        $tenantQuery = Tenant::query()
            ->forOrganization($orgId)
            ->whereNotNull('user_id')
            ->with('user');

        if ($notice->property_id) {
            $tenantIds = Lease::query()
                ->forOrganization($orgId)
                ->where('property_id', $notice->property_id)
                ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring])
                ->pluck('tenant_id')
                ->unique()
                ->filter();

            $tenantQuery->whereIn('id', $tenantIds);
        }

        return $tenantQuery->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->values();
    }
}

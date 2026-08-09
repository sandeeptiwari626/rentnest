<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Concerns\ResolvesTenantProfile;
use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\Notice;
use App\Models\NoticeRead;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NoticeController extends Controller
{
    use ResolvesOrganization;
    use ResolvesTenantProfile;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Notice::class);

        $user = $request->user();
        $tenant = $this->tenantProfile();
        $organizationId = $this->organizationId();

        $notices = $this->visibleNoticesQuery($tenant, $organizationId)
            ->withExists(['reads as is_read' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderByDesc('publish_date')
            ->paginate(12)
            ->through(fn (Notice $notice) => [
                'id' => $notice->id,
                'title' => $notice->title,
                'message_preview' => str($notice->message)->limit(120)->toString(),
                'publish_date' => $notice->publish_date?->toDateString(),
                'expiry_date' => $notice->expiry_date?->toDateString(),
                'is_read' => (bool) $notice->is_read,
            ]);

        return Inertia::render('Tenant/Notices/Index', [
            'notices' => $notices,
        ]);
    }

    public function show(Request $request, Notice $notice): Response
    {
        $this->ensureVisible($notice);
        $this->authorize('view', $notice);

        NoticeRead::query()->firstOrCreate(
            [
                'notice_id' => $notice->id,
                'user_id' => $request->user()->id,
            ],
            [
                'read_at' => now(),
            ]
        );

        return Inertia::render('Tenant/Notices/Show', [
            'notice' => [
                'id' => $notice->id,
                'title' => $notice->title,
                'message' => $notice->message,
                'publish_date' => $notice->publish_date?->toDateString(),
                'expiry_date' => $notice->expiry_date?->toDateString(),
            ],
        ]);
    }

    protected function ensureVisible(Notice $notice): void
    {
        $tenant = $this->tenantProfile();

        if ((int) $notice->organization_id !== $this->organizationId()
            || ! $this->visibleNoticesQuery($tenant, $this->organizationId())
                ->where('notices.id', $notice->id)
                ->exists()
        ) {
            abort(404);
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Notice>
     */
    protected function visibleNoticesQuery(Tenant $tenant, int $organizationId)
    {
        $propertyIds = Lease::query()
            ->where('tenant_id', $tenant->id)
            ->pluck('property_id');

        return Notice::query()
            ->forOrganization($organizationId)
            ->whereDate('publish_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', now());
            })
            ->where(function ($q) use ($tenant, $propertyIds) {
                $q->where(function ($inner) {
                    $inner->whereNull('tenant_id')->whereNull('property_id');
                })
                    ->orWhere('tenant_id', $tenant->id)
                    ->orWhereIn('property_id', $propertyIds);
            });
    }
}

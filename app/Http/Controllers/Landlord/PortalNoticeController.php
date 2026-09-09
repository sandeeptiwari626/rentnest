<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\StorePortalNoticeRequest;
use App\Models\TenantLegalAcknowledgement;
use App\Models\TenantPortalNotice;
use App\Services\TenantPortalNoticeService;
use App\Support\TenantPortalNoticeContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalNoticeController extends Controller
{
    use ResolvesOrganization;

    public function __construct(private TenantPortalNoticeService $notices) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', TenantPortalNotice::class);

        $orgId = $this->organizationId();
        $this->notices->ensureDefault($request->user()->currentOrganization, $request->user());

        $notices = TenantPortalNotice::query()
            ->forOrganization($orgId)
            ->withCount('acknowledgements')
            ->latest('id')
            ->get()
            ->map(fn (TenantPortalNotice $notice) => [
                'id' => $notice->id,
                'title' => $notice->title,
                'version' => $notice->version,
                'display_title' => $notice->displayTitle(),
                'is_active' => $notice->is_active,
                'published_at' => $notice->published_at?->toDateTimeString(),
                'acknowledgements_count' => $notice->acknowledgements_count,
            ]);

        $active = TenantPortalNotice::query()
            ->forOrganization($orgId)
            ->where('is_active', true)
            ->first();

        $acknowledgements = TenantLegalAcknowledgement::query()
            ->forOrganization($orgId)
            ->with(['tenant:id,name,email', 'user:id,name,email', 'notice:id,version,title'])
            ->latest('acknowledged_at')
            ->limit(100)
            ->get()
            ->map(fn (TenantLegalAcknowledgement $row) => [
                'id' => $row->id,
                'tenant' => $row->tenant?->name,
                'tenant_email' => $row->tenant?->email,
                'user_email' => $row->user?->email,
                'notice_version' => $row->notice_version,
                'status' => $row->status,
                'acknowledged_at' => $row->acknowledged_at?->toDateTimeString(),
                'is_current' => $active !== null && (int) $row->notice_id === (int) $active->id,
            ]);

        return Inertia::render('Landlord/PortalNotice/Index', [
            'notices' => $notices,
            'acknowledgements' => $acknowledgements,
            'activeNoticeId' => $active?->id,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', TenantPortalNotice::class);

        $latest = TenantPortalNotice::query()
            ->forOrganization($this->organizationId())
            ->latest('id')
            ->first();

        return Inertia::render('Landlord/PortalNotice/Create', [
            'suggestedVersion' => $this->nextVersion($latest?->version),
            'sections' => $latest?->sections ?: TenantPortalNoticeContent::sections(),
            'title' => $latest?->title ?: TenantPortalNoticeContent::title(),
        ]);
    }

    public function store(StorePortalNoticeRequest $request): RedirectResponse
    {
        $this->authorize('create', TenantPortalNotice::class);

        $notice = TenantPortalNotice::query()->create([
            'organization_id' => $this->organizationId(),
            'created_by' => $request->user()->id,
            'title' => $request->string('title')->toString(),
            'version' => $request->string('version')->toString(),
            'sections' => $request->input('sections'),
            'is_active' => false,
            'published_at' => null,
        ]);

        if ($request->boolean('activate')) {
            $this->notices->activate($notice);
        }

        return redirect()
            ->route('landlord.portal-notice.index')
            ->with('success', 'Portal notice version saved.');
    }

    public function activate(TenantPortalNotice $portalNotice): RedirectResponse
    {
        $this->authorize('update', $portalNotice);

        $this->notices->activate($portalNotice);

        return redirect()
            ->route('landlord.portal-notice.index')
            ->with('success', 'Tenants will be asked to acknowledge version '.$portalNotice->version.' at next login.');
    }

    protected function nextVersion(?string $current): string
    {
        if ($current === null || ! preg_match('/^(\d+)(?:\.(\d+))?/', $current, $matches)) {
            return '1.1';
        }

        $major = (int) $matches[1];
        $minor = isset($matches[2]) ? ((int) $matches[2]) + 1 : 1;

        return $major.'.'.$minor;
    }
}

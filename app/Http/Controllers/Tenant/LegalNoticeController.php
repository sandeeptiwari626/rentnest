<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Concerns\ResolvesTenantProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\AcknowledgeLegalNoticeRequest;
use App\Services\TenantPortalNoticeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class LegalNoticeController extends Controller
{
    use ResolvesOrganization;
    use ResolvesTenantProfile;

    public function __construct(private TenantPortalNoticeService $notices) {}

    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $organization = $user?->currentOrganization;

        if ($organization === null) {
            abort(403, 'No organization context.');
        }

        $notice = $this->notices->ensureDefault($organization, $user);

        $this->authorize('view', $notice);

        if (! $this->notices->userMustAcknowledge($user)) {
            return redirect()->route('tenant.home');
        }

        return Inertia::render('Tenant/LegalNotice', [
            'notice' => $this->noticePayload($notice),
        ]);
    }

    public function store(AcknowledgeLegalNoticeRequest $request): RedirectResponse
    {
        $user = $request->user();
        $tenant = $this->tenantProfile();
        $notice = $this->notices->activeForOrganization($this->organizationId());

        if ($notice === null || (int) $notice->id !== (int) $request->integer('notice_id')) {
            return redirect()
                ->route('tenant.legal-notice.show')
                ->with('error', 'The Tenant Portal Notice has changed. Please review the current version.');
        }

        $this->authorize('acknowledge', $notice);

        $this->notices->acknowledge($user, $tenant, $notice, $request);

        return redirect()
            ->route('tenant.home')
            ->with('success', 'Thank you. You can now use the tenant portal.');
    }

    public function download(Request $request): HttpResponse
    {
        $user = $request->user();
        $notice = $this->notices->activeForOrganization($this->organizationId());

        if ($notice === null) {
            abort(404);
        }

        $this->authorize('view', $notice);

        $pdf = Pdf::loadView('pdf.tenant-portal-notice', [
            'notice' => $notice,
            'organization' => $user->currentOrganization,
        ]);

        $filename = 'tenant-portal-notice-v'.$notice->version.'.pdf';

        return $pdf->download($filename);
    }

    /**
     * @return array<string, mixed>
     */
    protected function noticePayload($notice): array
    {
        return [
            'id' => $notice->id,
            'title' => $notice->title,
            'version' => $notice->version,
            'display_title' => $notice->displayTitle(),
            'sections' => $notice->sections,
            'published_at' => $notice->published_at?->toDateString(),
        ];
    }
}

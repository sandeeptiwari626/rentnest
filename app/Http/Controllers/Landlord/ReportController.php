<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\RentPayment;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    use ResolvesOrganization;

    public function index(): Response
    {
        $this->authorize('viewAny', Property::class);

        $orgId = $this->organizationId();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $yearStart = now()->startOfYear();
        $yearEnd = now()->endOfYear();

        $rentCollectedMonth = (float) RentPayment::query()
            ->forOrganization($orgId)
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount_paid');

        $rentCollectedYear = (float) RentPayment::query()
            ->forOrganization($orgId)
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('payment_date', [$yearStart->toDateString(), $yearEnd->toDateString()])
            ->sum('amount_paid');

        $outstanding = (float) RentPayment::query()
            ->forOrganization($orgId)
            ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Late, PaymentStatus::Partial])
            ->selectRaw('SUM(amount - amount_paid) as outstanding')
            ->value('outstanding');

        $expensesMonth = (float) Expense::query()
            ->forOrganization($orgId)
            ->whereBetween('expense_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount');

        $expensesYear = (float) Expense::query()
            ->forOrganization($orgId)
            ->whereBetween('expense_date', [$yearStart->toDateString(), $yearEnd->toDateString()])
            ->sum('amount');

        $maintenanceCosts = (float) Expense::query()
            ->forOrganization($orgId)
            ->whereIn('category', ['maintenance', 'repairs'])
            ->whereBetween('expense_date', [$yearStart->toDateString(), $yearEnd->toDateString()])
            ->sum('amount');

        $openMaintenance = MaintenanceRequest::query()
            ->forOrganization($orgId)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        $paymentHistory = collect(range(5, 0))->map(function (int $monthsAgo) use ($orgId) {
            $start = now()->subMonths($monthsAgo)->startOfMonth();
            $end = now()->subMonths($monthsAgo)->endOfMonth();

            $collected = (float) RentPayment::query()
                ->forOrganization($orgId)
                ->where('status', PaymentStatus::Paid)
                ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()])
                ->sum('amount_paid');

            $spent = (float) Expense::query()
                ->forOrganization($orgId)
                ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
                ->sum('amount');

            return [
                'label' => $start->format('M Y'),
                'collected' => $collected,
                'expenses' => $spent,
                'net' => $collected - $spent,
            ];
        })->values();

        return Inertia::render('Landlord/Reports/Index', [
            'summary' => [
                'rent_collected_month' => $rentCollectedMonth,
                'rent_collected_year' => $rentCollectedYear,
                'outstanding' => (float) ($outstanding ?? 0),
                'expenses_month' => $expensesMonth,
                'expenses_year' => $expensesYear,
                'net_income_month' => $rentCollectedMonth - $expensesMonth,
                'net_income_year' => $rentCollectedYear - $expensesYear,
                'maintenance_costs' => $maintenanceCosts,
                'open_maintenance' => $openMaintenance,
            ],
            'chart' => [
                'labels' => $paymentHistory->pluck('label'),
                'collected' => $paymentHistory->pluck('collected'),
                'expenses' => $paymentHistory->pluck('expenses'),
                'net' => $paymentHistory->pluck('net'),
            ],
            'history' => $paymentHistory,
        ]);
    }
}

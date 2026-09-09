<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\ExpenseCategory;
use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\Property;
use App\Support\PrivateUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    use ResolvesOrganization;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Expense::class);

        $orgId = $this->organizationId();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $expenses = Expense::query()
            ->forOrganization($orgId)
            ->with(['property:id,name', 'recordedBy:id,name'])
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->latest('expense_date')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Expense $expense) => [
                'id' => $expense->id,
                'category' => $expense->category?->value,
                'category_label' => $expense->category?->label(),
                'amount' => (float) $expense->amount,
                'expense_date' => $expense->expense_date?->toDateString(),
                'vendor' => $expense->vendor,
                'description' => $expense->description,
                'property' => $expense->property?->name,
                'recorded_by' => $expense->recordedBy?->name,
            ]);

        $monthlyTotal = (float) Expense::query()
            ->forOrganization($orgId)
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $byCategory = Expense::query()
            ->forOrganization($orgId)
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get()
            ->map(function ($row) {
                $category = $row->category instanceof ExpenseCategory
                    ? $row->category
                    : ExpenseCategory::tryFrom((string) $row->category);

                return [
                    'category' => $category?->value ?? (string) $row->category,
                    'label' => $category?->label() ?? (string) $row->category,
                    'total' => (float) $row->total,
                ];
            });

        return Inertia::render('Landlord/Expenses/Index', [
            'expenses' => $expenses,
            'summary' => [
                'monthly_total' => $monthlyTotal,
                'by_category' => $byCategory,
                'month_label' => now()->format('F Y'),
            ],
            'filters' => [
                'category' => $request->string('category')->toString(),
            ],
            'categoryOptions' => $this->enumOptions(ExpenseCategory::class),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Expense::class);

        return Inertia::render('Landlord/Expenses/Create', [
            'categoryOptions' => $this->enumOptions(ExpenseCategory::class),
            'properties' => Property::query()
                ->forOrganization($this->organizationId())
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $this->authorize('create', Expense::class);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = PrivateUpload::store(
                $request->file('receipt'),
                'expenses/'.$this->organizationId(),
                'receipt'
            );
        }

        $expense = Expense::query()->create([
            'organization_id' => $this->organizationId(),
            'property_id' => $request->input('property_id'),
            'recorded_by' => $request->user()->id,
            'category' => $request->input('category'),
            'amount' => $request->input('amount'),
            'expense_date' => $request->input('expense_date'),
            'vendor' => $request->input('vendor'),
            'description' => $request->input('description'),
            'receipt_path' => $receiptPath,
        ]);

        return redirect()
            ->route('landlord.expenses.show', $expense)
            ->with('success', 'Expense recorded successfully.');
    }

    public function show(Expense $expense): Response
    {
        $this->authorize('view', $expense);

        $expense->load(['property:id,name', 'recordedBy:id,name']);

        return Inertia::render('Landlord/Expenses/Show', [
            'expense' => [
                'id' => $expense->id,
                'category' => $expense->category?->value,
                'category_label' => $expense->category?->label(),
                'amount' => (float) $expense->amount,
                'expense_date' => $expense->expense_date?->toDateString(),
                'vendor' => $expense->vendor,
                'description' => $expense->description,
                'property' => $expense->property?->name,
                'recorded_by' => $expense->recordedBy?->name,
                'has_receipt' => filled($expense->receipt_path),
            ],
        ]);
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);

        if ($expense->receipt_path) {
            Storage::disk('local')->delete($expense->receipt_path);
        }

        $expense->delete();

        return redirect()
            ->route('landlord.expenses.index')
            ->with('success', 'Expense deleted.');
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

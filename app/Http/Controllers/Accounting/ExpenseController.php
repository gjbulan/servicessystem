<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    use ResolvesTenantCompany;

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $company->id)],
            'expense_category_id' => ['nullable', Rule::exists('expense_categories', 'id')->where('company_id', $company->id)],
            'status' => ['nullable', Rule::in(array_keys(Expense::STATUSES))],
        ]);

        return view('accounting.expenses.index', $this->formData($company) + [
            'expenses' => Expense::query()
                ->with(['branch', 'category', 'creator'])
                ->where('company_id', $company->id)
                ->when(! empty($filters['date_from']), fn ($query) => $query->whereDate('expense_date', '>=', $filters['date_from']))
                ->when(! empty($filters['date_to']), fn ($query) => $query->whereDate('expense_date', '<=', $filters['date_to']))
                ->when(! empty($filters['branch_id']), fn ($query) => $query->where('branch_id', $filters['branch_id']))
                ->when(! empty($filters['expense_category_id']), fn ($query) => $query->where('expense_category_id', $filters['expense_category_id']))
                ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
                ->latest('expense_date')
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('accounting.expenses.create', $this->formData($company) + [
            'expense' => new Expense([
                'expense_date' => now()->toDateString(),
                'status' => 'recorded',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $data = $this->validatedData($request, $company);
        unset($data['attachment']);

        $data['attachment_path'] = $this->storeAttachment($request);

        $expense = Expense::create($data + [
            'company_id' => $company->id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('expenses.show', $expense)
            ->with('status', 'Expense recorded successfully.');
    }

    public function show(Request $request, string $expense): View
    {
        $company = $this->tenantCompany($request);
        $expense = $this->tenantRecord($company, Expense::class, $expense);
        $expense->load(['branch', 'category', 'creator']);

        return view('accounting.expenses.show', [
            'expense' => $expense,
            'statuses' => Expense::STATUSES,
        ]);
    }

    public function edit(Request $request, string $expense): View
    {
        $company = $this->tenantCompany($request);
        $expense = $this->tenantRecord($company, Expense::class, $expense);

        return view('accounting.expenses.edit', $this->formData($company) + [
            'expense' => $expense,
        ]);
    }

    public function update(Request $request, string $expense): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $expense = $this->tenantRecord($company, Expense::class, $expense);
        $data = $this->validatedData($request, $company);
        unset($data['attachment']);

        $attachmentPath = $this->storeAttachment($request);

        if ($attachmentPath !== null) {
            if ($expense->attachment_path) {
                Storage::disk('public')->delete($expense->attachment_path);
            }

            $data['attachment_path'] = $attachmentPath;
        }

        $expense->update($data);

        return redirect()
            ->route('expenses.show', $expense)
            ->with('status', 'Expense updated successfully.');
    }

    public function destroy(Request $request, string $expense): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $expense = $this->tenantRecord($company, Expense::class, $expense);
        $expense->delete();

        return redirect()
            ->route('expenses.index')
            ->with('status', 'Expense deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Company $company): array
    {
        return [
            'branches' => Branch::query()
                ->where('company_id', $company->id)
                ->orderBy('name')
                ->get(),
            'categories' => ExpenseCategory::query()
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'statuses' => Expense::STATUSES,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, Company $company): array
    {
        return $request->validate([
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $company->id)],
            'expense_category_id' => ['nullable', Rule::exists('expense_categories', 'id')->where('company_id', $company->id)],
            'expense_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120'],
            'status' => ['required', Rule::in(array_keys(Expense::STATUSES))],
        ]);
    }

    private function storeAttachment(Request $request): ?string
    {
        if (! $request->hasFile('attachment')) {
            return null;
        }

        return $request->file('attachment')->store('expenses', 'public');
    }
}

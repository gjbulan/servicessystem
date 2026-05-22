<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseCategoryController extends Controller
{
    use ResolvesTenantCompany;

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('accounting.expense-categories.index', [
            'categories' => ExpenseCategory::query()
                ->where('company_id', $company->id)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(10),
            'statuses' => ExpenseCategory::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $this->tenantCompany($request);

        return view('accounting.expense-categories.create', [
            'category' => new ExpenseCategory(['status' => 'active']),
            'statuses' => ExpenseCategory::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $category = ExpenseCategory::create($this->validatedData($request) + ['company_id' => $company->id]);

        return redirect()
            ->route('expense-categories.show', $category)
            ->with('status', 'Expense category created successfully.');
    }

    public function show(Request $request, string $expenseCategory): View
    {
        $company = $this->tenantCompany($request);
        $category = $this->tenantRecord($company, ExpenseCategory::class, $expenseCategory);

        return view('accounting.expense-categories.show', [
            'category' => $category,
            'statuses' => ExpenseCategory::STATUSES,
        ]);
    }

    public function edit(Request $request, string $expenseCategory): View
    {
        $company = $this->tenantCompany($request);
        $category = $this->tenantRecord($company, ExpenseCategory::class, $expenseCategory);

        return view('accounting.expense-categories.edit', [
            'category' => $category,
            'statuses' => ExpenseCategory::STATUSES,
        ]);
    }

    public function update(Request $request, string $expenseCategory): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $category = $this->tenantRecord($company, ExpenseCategory::class, $expenseCategory);
        $category->update($this->validatedData($request));

        return redirect()
            ->route('expense-categories.show', $category)
            ->with('status', 'Expense category updated successfully.');
    }

    public function destroy(Request $request, string $expenseCategory): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $category = $this->tenantRecord($company, ExpenseCategory::class, $expenseCategory);
        $category->delete();

        return redirect()
            ->route('expense-categories.index')
            ->with('status', 'Expense category deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(array_keys(ExpenseCategory::STATUSES))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);
    }
}

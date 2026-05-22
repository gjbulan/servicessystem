<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemCategoryController extends Controller
{
    use ResolvesTenantCompany;

    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('inventory.categories.index', [
            'categories' => ItemCategory::query()
                ->where('company_id', $company->id)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(10),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $this->tenantCompany($request);

        return view('inventory.categories.create', [
            'category' => new ItemCategory(['status' => 'active']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $category = ItemCategory::create($this->validatedData($request) + ['company_id' => $company->id]);

        return redirect()
            ->route('inventory.categories.show', $category)
            ->with('status', 'Category created successfully.');
    }

    public function show(Request $request, string $category): View
    {
        $company = $this->tenantCompany($request);
        $category = $this->tenantRecord($company, ItemCategory::class, $category);

        return view('inventory.categories.show', [
            'category' => $category,
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(Request $request, string $category): View
    {
        $company = $this->tenantCompany($request);
        $category = $this->tenantRecord($company, ItemCategory::class, $category);

        return view('inventory.categories.edit', [
            'category' => $category,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, string $category): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $category = $this->tenantRecord($company, ItemCategory::class, $category);
        $category->update($this->validatedData($request));

        return redirect()
            ->route('inventory.categories.show', $category)
            ->with('status', 'Category updated successfully.');
    }

    public function destroy(Request $request, string $category): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $category = $this->tenantRecord($company, ItemCategory::class, $category);
        $category->delete();

        return redirect()
            ->route('inventory.categories.index')
            ->with('status', 'Category deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);
    }
}

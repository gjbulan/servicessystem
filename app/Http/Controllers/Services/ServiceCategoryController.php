<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceCategoryController extends Controller
{
    use ResolvesTenantCompany;

    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('services.categories.index', [
            'categories' => ServiceCategory::query()
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

        return view('services.categories.create', [
            'category' => new ServiceCategory(['status' => 'active']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $category = ServiceCategory::create($this->validatedData($request) + ['company_id' => $company->id]);

        return redirect()
            ->route('service-categories.show', $category)
            ->with('status', 'Service category created successfully.');
    }

    public function show(Request $request, string $serviceCategory): View
    {
        $company = $this->tenantCompany($request);
        $category = $this->tenantRecord($company, ServiceCategory::class, $serviceCategory);

        return view('services.categories.show', [
            'category' => $category,
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(Request $request, string $serviceCategory): View
    {
        $company = $this->tenantCompany($request);
        $category = $this->tenantRecord($company, ServiceCategory::class, $serviceCategory);

        return view('services.categories.edit', [
            'category' => $category,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, string $serviceCategory): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $category = $this->tenantRecord($company, ServiceCategory::class, $serviceCategory);
        $category->update($this->validatedData($request));

        return redirect()
            ->route('service-categories.show', $category)
            ->with('status', 'Service category updated successfully.');
    }

    public function destroy(Request $request, string $serviceCategory): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $category = $this->tenantRecord($company, ServiceCategory::class, $serviceCategory);
        $category->delete();

        return redirect()
            ->route('service-categories.index')
            ->with('status', 'Service category deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}

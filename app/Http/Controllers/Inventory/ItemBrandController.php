<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Http\Controllers\Controller;
use App\Models\ItemBrand;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemBrandController extends Controller
{
    use ResolvesTenantCompany;

    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('inventory.brands.index', [
            'brands' => ItemBrand::query()
                ->where('company_id', $company->id)
                ->orderBy('name')
                ->paginate(10),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $this->tenantCompany($request);

        return view('inventory.brands.create', [
            'brand' => new ItemBrand(['status' => 'active']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $brand = ItemBrand::create($this->validatedData($request) + ['company_id' => $company->id]);

        return redirect()
            ->route('inventory.brands.show', $brand)
            ->with('status', 'Brand created successfully.');
    }

    public function show(Request $request, string $brand): View
    {
        $company = $this->tenantCompany($request);
        $brand = $this->tenantRecord($company, ItemBrand::class, $brand);

        return view('inventory.brands.show', [
            'brand' => $brand,
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(Request $request, string $brand): View
    {
        $company = $this->tenantCompany($request);
        $brand = $this->tenantRecord($company, ItemBrand::class, $brand);

        return view('inventory.brands.edit', [
            'brand' => $brand,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, string $brand): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $brand = $this->tenantRecord($company, ItemBrand::class, $brand);
        $brand->update($this->validatedData($request));

        return redirect()
            ->route('inventory.brands.show', $brand)
            ->with('status', 'Brand updated successfully.');
    }

    public function destroy(Request $request, string $brand): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $brand = $this->tenantRecord($company, ItemBrand::class, $brand);
        $brand->delete();

        return redirect()
            ->route('inventory.brands.index')
            ->with('status', 'Brand deleted successfully.');
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
        ]);
    }
}

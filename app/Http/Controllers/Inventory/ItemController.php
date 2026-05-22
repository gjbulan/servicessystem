<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemBrand;
use App\Models\ItemCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    use ResolvesTenantCompany;

    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('inventory.items.index', [
            'items' => Item::query()
                ->with(['category', 'brand'])
                ->where('company_id', $company->id)
                ->latest()
                ->paginate(10),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('inventory.items.create', $this->formData($company->id) + [
            'item' => new Item(['status' => 'active']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $item = Item::create($this->validatedData($request, $company->id) + ['company_id' => $company->id]);

        return redirect()
            ->route('inventory.items.show', $item)
            ->with('status', 'Item created successfully.');
    }

    public function show(Request $request, string $item): View
    {
        $company = $this->tenantCompany($request);
        $item = $this->tenantRecord($company, Item::class, $item);
        $item->load(['category', 'brand', 'variants']);

        return view('inventory.items.show', [
            'item' => $item,
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(Request $request, string $item): View
    {
        $company = $this->tenantCompany($request);
        $item = $this->tenantRecord($company, Item::class, $item);

        return view('inventory.items.edit', $this->formData($company->id) + [
            'item' => $item,
        ]);
    }

    public function update(Request $request, string $item): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $item = $this->tenantRecord($company, Item::class, $item);
        $item->update($this->validatedData($request, $company->id));

        return redirect()
            ->route('inventory.items.show', $item)
            ->with('status', 'Item updated successfully.');
    }

    public function destroy(Request $request, string $item): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $item = $this->tenantRecord($company, Item::class, $item);
        $item->delete();

        return redirect()
            ->route('inventory.items.index')
            ->with('status', 'Item deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(int $companyId): array
    {
        return [
            'brands' => ItemBrand::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'categories' => ItemCategory::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'statuses' => self::STATUSES,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, int $companyId): array
    {
        return $request->validate([
            'item_category_id' => ['nullable', Rule::exists('item_categories', 'id')->where('company_id', $companyId)],
            'item_brand_id' => ['nullable', Rule::exists('item_brands', 'id')->where('company_id', $companyId)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);
    }
}

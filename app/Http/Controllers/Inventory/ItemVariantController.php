<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemVariant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Json;
use Illuminate\Validation\Rule;

class ItemVariantController extends Controller
{
    use ResolvesTenantCompany;

    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('inventory.variants.index', [
            'variants' => ItemVariant::query()
                ->with(['item.brand', 'item.category'])
                ->where('company_id', $company->id)
                ->latest()
                ->paginate(10),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('inventory.variants.create', [
            'items' => $this->items($company->id),
            'statuses' => self::STATUSES,
            'variant' => new ItemVariant([
                'cost_price' => '0.00',
                'selling_price' => '0.00',
                'status' => 'active',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $variant = ItemVariant::create($this->validatedData($request, $company->id) + ['company_id' => $company->id]);

        return redirect()
            ->route('inventory.variants.show', $variant)
            ->with('status', 'Variant created successfully.');
    }

    public function show(Request $request, string $variant): View
    {
        $company = $this->tenantCompany($request);
        $variant = $this->tenantRecord($company, ItemVariant::class, $variant);
        $variant->load(['item.brand', 'item.category', 'stocks.branch']);

        return view('inventory.variants.show', [
            'statuses' => self::STATUSES,
            'variant' => $variant,
        ]);
    }

    public function edit(Request $request, string $variant): View
    {
        $company = $this->tenantCompany($request);
        $variant = $this->tenantRecord($company, ItemVariant::class, $variant);

        return view('inventory.variants.edit', [
            'items' => $this->items($company->id),
            'statuses' => self::STATUSES,
            'variant' => $variant,
        ]);
    }

    public function update(Request $request, string $variant): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $variant = $this->tenantRecord($company, ItemVariant::class, $variant);
        $variant->update($this->validatedData($request, $company->id, $variant->id));

        return redirect()
            ->route('inventory.variants.show', $variant)
            ->with('status', 'Variant updated successfully.');
    }

    public function destroy(Request $request, string $variant): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $variant = $this->tenantRecord($company, ItemVariant::class, $variant);
        $variant->delete();

        return redirect()
            ->route('inventory.variants.index')
            ->with('status', 'Variant deleted successfully.');
    }

    private function items(int $companyId)
    {
        return Item::query()
            ->with(['brand', 'category'])
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, int $companyId, ?int $variantId = null): array
    {
        $data = $request->validate([
            'item_id' => ['required', Rule::exists('items', 'id')->where('company_id', $companyId)],
            'variant_name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('item_variants', 'sku')
                    ->where('company_id', $companyId)
                    ->ignore($variantId),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('item_variants', 'barcode')
                    ->where('company_id', $companyId)
                    ->ignore($variantId),
            ],
            'cost_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'unit_type' => ['nullable', 'string', 'max:50'],
            'attributes_json' => ['nullable', 'json'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);

        $data['attributes_json'] = $data['attributes_json']
            ? Json::decode($data['attributes_json'])
            : null;

        return $data;
    }
}

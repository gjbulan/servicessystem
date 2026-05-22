<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Item;
use App\Models\ItemBrand;
use App\Models\ItemCategory;
use App\Models\ItemVariant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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
                ->with($company->usesItemVariants() ? ['category', 'brand'] : ['category', 'brand', 'defaultVariant'])
                ->where('company_id', $company->id)
                ->latest()
                ->paginate(10),
            'statuses' => self::STATUSES,
            'usesItemVariants' => $company->usesItemVariants(),
        ]);
    }

    public function create(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('inventory.items.create', $this->formData($company) + [
            'item' => new Item(['status' => 'active']),
            'defaultVariant' => new ItemVariant([
                'cost_price' => '0.00',
                'selling_price' => '0.00',
                'status' => 'active',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $usesItemVariants = $company->usesItemVariants();
        $data = $this->validatedData($request, $company, null, $usesItemVariants);

        $item = DB::transaction(function () use ($company, $data, $usesItemVariants): Item {
            $item = Item::create($this->itemData($data) + ['company_id' => $company->id]);

            if (! $usesItemVariants) {
                $this->saveDefaultVariant($item, $data, $company->id);
            }

            return $item;
        });

        return redirect()
            ->route('inventory.items.show', $item)
            ->with('status', 'Item created successfully.');
    }

    public function show(Request $request, string $item): View
    {
        $company = $this->tenantCompany($request);
        $item = $this->tenantRecord($company, Item::class, $item);
        $item->load($company->usesItemVariants() ? ['category', 'brand', 'variants'] : ['category', 'brand', 'defaultVariant']);

        return view('inventory.items.show', [
            'item' => $item,
            'statuses' => self::STATUSES,
            'usesItemVariants' => $company->usesItemVariants(),
        ]);
    }

    public function edit(Request $request, string $item): View
    {
        $company = $this->tenantCompany($request);
        $item = $this->tenantRecord($company, Item::class, $item);
        $item->load('defaultVariant');

        return view('inventory.items.edit', $this->formData($company) + [
            'item' => $item,
            'defaultVariant' => $item->defaultVariant ?? new ItemVariant([
                'cost_price' => '0.00',
                'selling_price' => '0.00',
                'status' => 'active',
            ]),
        ]);
    }

    public function update(Request $request, string $item): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $item = $this->tenantRecord($company, Item::class, $item);
        $usesItemVariants = $company->usesItemVariants();
        $data = $this->validatedData($request, $company, $item, $usesItemVariants);

        DB::transaction(function () use ($company, $data, $item, $usesItemVariants): void {
            $item->update($this->itemData($data));

            if (! $usesItemVariants) {
                $this->saveDefaultVariant($item, $data, $company->id);
            }
        });

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
    private function formData(Company $company): array
    {
        return [
            'brands' => ItemBrand::query()->where('company_id', $company->id)->orderBy('name')->get(),
            'categories' => ItemCategory::query()->where('company_id', $company->id)->orderBy('name')->get(),
            'statuses' => self::STATUSES,
            'usesItemVariants' => $company->usesItemVariants(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, Company $company, ?Item $item, bool $usesItemVariants): array
    {
        $defaultVariantId = $item?->defaultVariant()->withTrashed()->value('id');

        $rules = [
            'item_category_id' => ['nullable', Rule::exists('item_categories', 'id')->where('company_id', $company->id)],
            'item_brand_id' => ['nullable', Rule::exists('item_brands', 'id')->where('company_id', $company->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ];

        if (! $usesItemVariants) {
            $rules += [
                'sku' => [
                    'nullable',
                    'string',
                    'max:100',
                    Rule::unique('item_variants', 'sku')
                        ->where('company_id', $company->id)
                        ->ignore($defaultVariantId),
                ],
                'barcode' => [
                    'nullable',
                    'string',
                    'max:100',
                    Rule::unique('item_variants', 'barcode')
                        ->where('company_id', $company->id)
                        ->ignore($defaultVariantId),
                ],
                'cost_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
                'selling_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
                'unit_type' => ['nullable', 'string', 'max:50'],
            ];
        }

        return $request->validate($rules);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function itemData(array $data): array
    {
        return Arr::only($data, [
            'item_category_id',
            'item_brand_id',
            'name',
            'description',
            'status',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveDefaultVariant(Item $item, array $data, int $companyId): ItemVariant
    {
        $variant = $item->variants()
            ->withTrashed()
            ->where('variant_name', 'Default')
            ->first();

        if (! $variant) {
            $variant = new ItemVariant([
                'company_id' => $companyId,
                'item_id' => $item->id,
                'variant_name' => 'Default',
            ]);
        }

        $variant->fill([
            'company_id' => $companyId,
            'item_id' => $item->id,
            'variant_name' => 'Default',
            'sku' => $data['sku'] ?? null,
            'barcode' => $data['barcode'] ?? null,
            'cost_price' => $data['cost_price'],
            'selling_price' => $data['selling_price'],
            'unit_type' => $data['unit_type'] ?? null,
            'attributes_json' => null,
            'status' => 'active',
        ]);

        $variant->save();

        if ($variant->trashed()) {
            $variant->restore();
        }

        return $variant;
    }
}

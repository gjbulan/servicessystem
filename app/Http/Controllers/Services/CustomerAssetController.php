<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Http\Controllers\Controller;
use App\Models\AssetType;
use App\Models\Customer;
use App\Models\CustomerAsset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerAssetController extends Controller
{
    use ResolvesTenantCompany;

    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('services.customer-assets.index', [
            'customerAssets' => CustomerAsset::query()
                ->with(['customer', 'assetType'])
                ->where('company_id', $company->id)
                ->latest()
                ->paginate(10),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('services.customer-assets.create', $this->formData($company->id) + [
            'customerAsset' => new CustomerAsset(['status' => 'active']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $customerAsset = CustomerAsset::create($this->validatedData($request, $company->id) + ['company_id' => $company->id]);

        return redirect()
            ->route('customer-assets.show', $customerAsset)
            ->with('status', 'Customer asset created successfully.');
    }

    public function show(Request $request, string $customerAsset): View
    {
        $company = $this->tenantCompany($request);
        $customerAsset = $this->tenantRecord($company, CustomerAsset::class, $customerAsset);
        $customerAsset->load(['customer', 'assetType', 'serviceHistories.jobOrder']);

        return view('services.customer-assets.show', [
            'customerAsset' => $customerAsset,
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(Request $request, string $customerAsset): View
    {
        $company = $this->tenantCompany($request);
        $customerAsset = $this->tenantRecord($company, CustomerAsset::class, $customerAsset);

        return view('services.customer-assets.edit', $this->formData($company->id) + [
            'customerAsset' => $customerAsset,
        ]);
    }

    public function update(Request $request, string $customerAsset): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $customerAsset = $this->tenantRecord($company, CustomerAsset::class, $customerAsset);
        $customerAsset->update($this->validatedData($request, $company->id));

        return redirect()
            ->route('customer-assets.show', $customerAsset)
            ->with('status', 'Customer asset updated successfully.');
    }

    public function destroy(Request $request, string $customerAsset): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $customerAsset = $this->tenantRecord($company, CustomerAsset::class, $customerAsset);
        $customerAsset->delete();

        return redirect()
            ->route('customer-assets.index')
            ->with('status', 'Customer asset deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(int $companyId): array
    {
        return [
            'assetTypes' => AssetType::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'customers' => Customer::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'statuses' => self::STATUSES,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, int $companyId): array
    {
        return $request->validate([
            'customer_id' => ['required', Rule::exists('customers', 'id')->where('company_id', $companyId)],
            'asset_type_id' => ['nullable', Rule::exists('asset_types', 'id')->where('company_id', $companyId)],
            'name' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'string', 'max:50'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'plate_number' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);
    }
}

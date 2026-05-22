<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Http\Controllers\Controller;
use App\Models\AssetType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetTypeController extends Controller
{
    use ResolvesTenantCompany;

    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('services.asset-types.index', [
            'assetTypes' => AssetType::query()
                ->where('company_id', $company->id)
                ->orderBy('name')
                ->paginate(10),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $this->tenantCompany($request);

        return view('services.asset-types.create', [
            'assetType' => new AssetType(['status' => 'active']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $assetType = AssetType::create($this->validatedData($request) + ['company_id' => $company->id]);

        return redirect()
            ->route('asset-types.show', $assetType)
            ->with('status', 'Asset type created successfully.');
    }

    public function show(Request $request, string $assetType): View
    {
        $company = $this->tenantCompany($request);
        $assetType = $this->tenantRecord($company, AssetType::class, $assetType);

        return view('services.asset-types.show', [
            'assetType' => $assetType,
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(Request $request, string $assetType): View
    {
        $company = $this->tenantCompany($request);
        $assetType = $this->tenantRecord($company, AssetType::class, $assetType);

        return view('services.asset-types.edit', [
            'assetType' => $assetType,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, string $assetType): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $assetType = $this->tenantRecord($company, AssetType::class, $assetType);
        $assetType->update($this->validatedData($request));

        return redirect()
            ->route('asset-types.show', $assetType)
            ->with('status', 'Asset type updated successfully.');
    }

    public function destroy(Request $request, string $assetType): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $assetType = $this->tenantRecord($company, AssetType::class, $assetType);
        $assetType->delete();

        return redirect()
            ->route('asset-types.index')
            ->with('status', 'Asset type deleted successfully.');
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
        ]);
    }
}

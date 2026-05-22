<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    use ResolvesTenantCompany;

    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('services.services.index', [
            'services' => Service::query()
                ->with('category')
                ->where('company_id', $company->id)
                ->orderBy('name')
                ->paginate(10),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('services.services.create', $this->formData($company->id) + [
            'service' => new Service([
                'default_price' => '0.00',
                'status' => 'active',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $service = Service::create($this->validatedData($request, $company->id) + ['company_id' => $company->id]);

        return redirect()
            ->route('services.show', $service)
            ->with('status', 'Service created successfully.');
    }

    public function show(Request $request, string $service): View
    {
        $company = $this->tenantCompany($request);
        $service = $this->tenantRecord($company, Service::class, $service);
        $service->load('category');

        return view('services.services.show', [
            'service' => $service,
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(Request $request, string $service): View
    {
        $company = $this->tenantCompany($request);
        $service = $this->tenantRecord($company, Service::class, $service);

        return view('services.services.edit', $this->formData($company->id) + [
            'service' => $service,
        ]);
    }

    public function update(Request $request, string $service): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $service = $this->tenantRecord($company, Service::class, $service);
        $service->update($this->validatedData($request, $company->id));

        return redirect()
            ->route('services.show', $service)
            ->with('status', 'Service updated successfully.');
    }

    public function destroy(Request $request, string $service): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $service = $this->tenantRecord($company, Service::class, $service);
        $service->delete();

        return redirect()
            ->route('services.index')
            ->with('status', 'Service deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(int $companyId): array
    {
        return [
            'categories' => ServiceCategory::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'statuses' => self::STATUSES,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, int $companyId): array
    {
        return $request->validate([
            'service_category_id' => ['nullable', Rule::exists('service_categories', 'id')->where('company_id', $companyId)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'default_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'default_incentive_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);
    }
}

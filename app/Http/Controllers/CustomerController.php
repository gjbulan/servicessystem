<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    use ResolvesTenantCompany;

    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('customers.index', [
            'customers' => Customer::query()
                ->where('company_id', $company->id)
                ->latest()
                ->paginate(10),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $this->tenantCompany($request);

        return view('customers.create', [
            'customer' => new Customer(['status' => 'active']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $data = $this->validatedData($request, $company->id);

        $customer = Customer::create($data + ['company_id' => $company->id]);

        return redirect()
            ->route('customers.show', $customer)
            ->with('status', 'Customer created successfully.');
    }

    public function show(Request $request, string $customer): View
    {
        $company = $this->tenantCompany($request);
        $customer = $this->tenantRecord($company, Customer::class, $customer);

        return view('customers.show', [
            'customer' => $customer,
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(Request $request, string $customer): View
    {
        $company = $this->tenantCompany($request);
        $customer = $this->tenantRecord($company, Customer::class, $customer);

        return view('customers.edit', [
            'customer' => $customer,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, string $customer): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $customer = $this->tenantRecord($company, Customer::class, $customer);
        $customer->update($this->validatedData($request, $company->id, $customer->id));

        return redirect()
            ->route('customers.show', $customer)
            ->with('status', 'Customer updated successfully.');
    }

    public function destroy(Request $request, string $customer): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $customer = $this->tenantRecord($company, Customer::class, $customer);
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('status', 'Customer deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, int $companyId, ?int $customerId = null): array
    {
        return $request->validate([
            'customer_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('customers', 'customer_code')
                    ->where('company_id', $companyId)
                    ->ignore($customerId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);
    }
}

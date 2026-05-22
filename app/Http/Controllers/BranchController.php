<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Models\Branch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    use ResolvesTenantCompany;

    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('branches.index', [
            'branches' => Branch::query()
                ->where('company_id', $company->id)
                ->latest()
                ->paginate(10),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $this->tenantCompany($request);

        return view('branches.create', [
            'branch' => new Branch(['status' => 'active']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $data = $this->validatedData($request, $company->id);

        $branch = Branch::create($data + ['company_id' => $company->id]);

        return redirect()
            ->route('branches.show', $branch)
            ->with('status', 'Branch created successfully.');
    }

    public function show(Request $request, string $branch): View
    {
        $company = $this->tenantCompany($request);
        $branch = $this->tenantRecord($company, Branch::class, $branch);

        return view('branches.show', [
            'branch' => $branch,
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(Request $request, string $branch): View
    {
        $company = $this->tenantCompany($request);
        $branch = $this->tenantRecord($company, Branch::class, $branch);

        return view('branches.edit', [
            'branch' => $branch,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, string $branch): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $branch = $this->tenantRecord($company, Branch::class, $branch);
        $branch->update($this->validatedData($request, $company->id, $branch->id));

        return redirect()
            ->route('branches.show', $branch)
            ->with('status', 'Branch updated successfully.');
    }

    public function destroy(Request $request, string $branch): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $branch = $this->tenantRecord($company, Branch::class, $branch);
        $branch->delete();

        return redirect()
            ->route('branches.index')
            ->with('status', 'Branch deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, int $companyId, ?int $branchId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('branches', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($branchId),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'operating_hours' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    use ResolvesTenantCompany;

    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    private const COMPANY_ROLE_NAMES = [
        'Company Admin',
        'Branch Manager',
        'Technician',
        'Cashier',
        'Inventory Staff',
    ];

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('staff.index', [
            'staff' => User::query()
                ->with(['roles', 'company'])
                ->where('company_id', $company->id)
                ->orderBy('name')
                ->paginate(10),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('staff.create', $this->formData($company->id) + [
            'staffUser' => new User(['status' => 'active']),
            'selectedBranchId' => null,
            'selectedRoleId' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $data = $this->validatedData($request, $company->id);

        $staffUser = User::create([
            'company_id' => $company->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => $data['status'],
        ]);

        $this->syncRole($staffUser, (int) $data['role_id'], $data['branch_id'] ?? null);

        return redirect()
            ->route('staff.show', $staffUser)
            ->with('status', 'Staff user created successfully.');
    }

    public function show(Request $request, string $user): View
    {
        $company = $this->tenantCompany($request);
        $staffUser = $this->tenantStaffUser($company->id, $user);
        $staffUser->load(['roles', 'company']);

        return view('staff.show', [
            'staffUser' => $staffUser,
            'branch' => $this->assignedBranch($staffUser),
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(Request $request, string $user): View
    {
        $company = $this->tenantCompany($request);
        $staffUser = $this->tenantStaffUser($company->id, $user);

        abort_if($staffUser->isSuperAdmin(), 403, 'Company admins cannot edit Super Admin users.');

        $role = $staffUser->roles->first();

        return view('staff.edit', $this->formData($company->id) + [
            'staffUser' => $staffUser,
            'selectedBranchId' => $role?->pivot?->branch_id,
            'selectedRoleId' => $role?->id,
        ]);
    }

    public function update(Request $request, string $user): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $staffUser = $this->tenantStaffUser($company->id, $user);

        abort_if($staffUser->isSuperAdmin(), 403, 'Company admins cannot edit Super Admin users.');

        $data = $this->validatedData($request, $company->id, $staffUser);

        $staffUser->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => $data['status'],
        ]);

        if (! empty($data['password'])) {
            $staffUser->password = Hash::make($data['password']);
        }

        $staffUser->save();
        $this->syncRole($staffUser, (int) $data['role_id'], $data['branch_id'] ?? null);

        return redirect()
            ->route('staff.show', $staffUser)
            ->with('status', 'Staff user updated successfully.');
    }

    public function destroy(Request $request, string $user): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $staffUser = $this->tenantStaffUser($company->id, $user);

        abort_if((int) $staffUser->id === (int) $request->user()->id, 403, 'You cannot delete yourself.');
        abort_if($staffUser->isSuperAdmin(), 403, 'Company admins cannot delete Super Admin users.');

        $staffUser->delete();

        return redirect()
            ->route('staff.index')
            ->with('status', 'Staff user deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(int $companyId): array
    {
        return [
            'branches' => Branch::query()
                ->where('company_id', $companyId)
                ->orderBy('name')
                ->get(),
            'roles' => $this->companyRoles(),
            'statuses' => self::STATUSES,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, int $companyId, ?User $staffUser = null): array
    {
        $passwordRules = $staffUser
            ? ['nullable', 'confirmed', 'min:8']
            : ['required', 'confirmed', 'min:8'];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($staffUser?->id),
            ],
            'password' => $passwordRules,
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'role_id' => ['required', Rule::in($this->companyRoles()->pluck('id')->all())],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
        ]);
    }

    private function tenantStaffUser(int $companyId, int|string $id): User
    {
        return User::query()
            ->with('roles')
            ->where('company_id', $companyId)
            ->whereKey($id)
            ->firstOrFail();
    }

    private function companyRoles()
    {
        return Role::query()
            ->whereNull('company_id')
            ->whereIn('name', self::COMPANY_ROLE_NAMES)
            ->orderBy('name')
            ->get();
    }

    private function syncRole(User $staffUser, int $roleId, int|string|null $branchId): void
    {
        $staffUser->roles()->sync([
            $roleId => [
                'branch_id' => $branchId !== null && $branchId !== '' ? (int) $branchId : null,
            ],
        ]);
    }

    private function assignedBranch(User $staffUser): ?Branch
    {
        $branchId = $staffUser->roles->first()?->pivot?->branch_id;

        if (! $branchId) {
            return null;
        }

        return Branch::query()
            ->where('company_id', $staffUser->company_id)
            ->whereKey($branchId)
            ->first();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    private const PLATFORM_ROLE_NAMES = [
        'Super Admin',
        'Company Admin',
        'Branch Manager',
        'Technician',
        'Cashier',
        'Inventory Staff',
    ];

    public function index(Request $request): View
    {
        $companyFilter = $request->query('company_id');

        $users = User::query()
            ->with(['company', 'roles'])
            ->when($companyFilter === 'unassigned', fn ($query) => $query->whereNull('company_id'))
            ->when(is_numeric($companyFilter), fn ($query) => $query->where('company_id', (int) $companyFilter))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'companies' => $this->companies(),
            'companyFilter' => $companyFilter,
            'statuses' => self::STATUSES,
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', $this->formData() + [
            'platformUser' => new User(['status' => 'active']),
            'selectedBranchId' => null,
            'selectedRoleId' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $role = Role::findOrFail($data['role_id']);
        $data = $this->normalizedAssignmentData($data, $role);

        $platformUser = User::create([
            'company_id' => $data['company_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => $data['status'],
        ]);

        $this->syncRole($platformUser, (int) $data['role_id'], $data['branch_id'] ?? null);

        return redirect()
            ->route('admin.users.show', $platformUser)
            ->with('status', 'User created successfully.');
    }

    public function show(string $user): View
    {
        $platformUser = $this->platformUser($user);
        $platformUser->load(['company', 'roles']);

        return view('admin.users.show', [
            'branch' => $this->assignedBranch($platformUser),
            'platformUser' => $platformUser,
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(string $user): View
    {
        $platformUser = $this->platformUser($user);
        $platformUser->load('roles');
        $role = $platformUser->roles->first();

        return view('admin.users.edit', $this->formData() + [
            'platformUser' => $platformUser,
            'selectedBranchId' => $role?->pivot?->branch_id,
            'selectedRoleId' => $role?->id,
        ]);
    }

    public function update(Request $request, string $user): RedirectResponse
    {
        $platformUser = $this->platformUser($user);
        $data = $this->validatedData($request, $platformUser);
        $role = Role::findOrFail($data['role_id']);
        $data = $this->normalizedAssignmentData($data, $role);

        $platformUser->fill([
            'company_id' => $data['company_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => $data['status'],
        ]);

        if (! empty($data['password'])) {
            $platformUser->password = Hash::make($data['password']);
        }

        $platformUser->save();
        $this->syncRole($platformUser, (int) $data['role_id'], $data['branch_id'] ?? null);

        return redirect()
            ->route('admin.users.show', $platformUser)
            ->with('status', 'User updated successfully.');
    }

    public function destroy(string $user): RedirectResponse
    {
        $platformUser = $this->platformUser($user);

        if ($platformUser->isSuperAdmin() && $this->superAdminCount() <= 1) {
            throw ValidationException::withMessages([
                'user' => 'The last Super Admin user cannot be deleted.',
            ]);
        }

        $platformUser->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'branches' => Branch::query()
                ->with('company')
                ->orderBy('name')
                ->get(),
            'companies' => $this->companies(),
            'roles' => $this->platformRoles(),
            'statuses' => self::STATUSES,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?User $platformUser = null): array
    {
        $passwordRules = $platformUser
            ? ['nullable', 'confirmed', 'min:8']
            : ['required', 'confirmed', 'min:8'];

        return $request->validate([
            'company_id' => ['nullable', Rule::exists('companies', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($platformUser?->id),
            ],
            'password' => $passwordRules,
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'role_id' => ['required', Rule::in($this->platformRoles()->pluck('id')->all())],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizedAssignmentData(array $data, Role $role): array
    {
        if ($role->name === 'Super Admin') {
            $data['company_id'] = null;
            $data['branch_id'] = null;

            return $data;
        }

        if (empty($data['company_id'])) {
            throw ValidationException::withMessages([
                'company_id' => 'A company is required for non-Super Admin users.',
            ]);
        }

        if (! empty($data['branch_id'])) {
            $branchBelongsToCompany = Branch::query()
                ->where('company_id', $data['company_id'])
                ->whereKey($data['branch_id'])
                ->exists();

            if (! $branchBelongsToCompany) {
                throw ValidationException::withMessages([
                    'branch_id' => 'The selected branch must belong to the selected company.',
                ]);
            }
        }

        return $data;
    }

    private function platformUser(int|string $id): User
    {
        return User::query()
            ->with('roles')
            ->whereKey($id)
            ->firstOrFail();
    }

    private function syncRole(User $platformUser, int $roleId, int|string|null $branchId): void
    {
        $platformUser->roles()->sync([
            $roleId => [
                'branch_id' => $branchId !== null && $branchId !== '' ? (int) $branchId : null,
            ],
        ]);
    }

    private function platformRoles()
    {
        return Role::query()
            ->whereNull('company_id')
            ->whereIn('name', self::PLATFORM_ROLE_NAMES)
            ->orderBy('name')
            ->get();
    }

    private function companies()
    {
        return Company::query()
            ->orderBy('name')
            ->get();
    }

    private function assignedBranch(User $platformUser): ?Branch
    {
        $branchId = $platformUser->roles->first()?->pivot?->branch_id;

        if (! $branchId) {
            return null;
        }

        return Branch::query()
            ->whereKey($branchId)
            ->first();
    }

    private function superAdminCount(): int
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'Super Admin'))
            ->count();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CompanyController extends Controller
{
    private const STATUSES = [
        'active' => 'Active',
        'trial' => 'Trial',
        'suspended' => 'Suspended',
        'expired' => 'Expired',
    ];

    /**
     * Display tenant companies.
     */
    public function index(): View
    {
        $companies = Company::query()
            ->withCount(['users', 'modules'])
            ->latest()
            ->paginate(10);

        return view('admin.companies.index', [
            'companies' => $companies,
            'statuses' => self::STATUSES,
        ]);
    }

    /**
     * Show the create company form.
     */
    public function create(): View
    {
        return view('admin.companies.create', [
            'company' => new Company(['status' => 'trial']),
            'statuses' => self::STATUSES,
        ]);
    }

    /**
     * Store a new company tenant.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedCompanyData($request);
        $data['slug'] = $this->validatedSlug($request, $data);

        $company = Company::create($data);
        $company->ensureDefaultModules();

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('status', 'Company created successfully.');
    }

    /**
     * Display one company tenant.
     */
    public function show(Company $company): View
    {
        $company->ensureDefaultModules();

        $company->load([
            'modules' => fn ($query) => $query->orderBy('module_name'),
            'users.roles' => fn ($query) => $query->orderBy('name'),
        ])->loadCount(['users', 'modules']);

        return view('admin.companies.show', [
            'company' => $company,
            'statuses' => self::STATUSES,
        ]);
    }

    /**
     * Show the edit company form.
     */
    public function edit(Company $company): View
    {
        return view('admin.companies.edit', [
            'company' => $company,
            'statuses' => self::STATUSES,
        ]);
    }

    /**
     * Update a company tenant.
     */
    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $this->validatedCompanyData($request, $company);
        $data['slug'] = $this->validatedSlug($request, $data, $company);

        $company->update($data);
        $company->ensureDefaultModules();

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('status', 'Company updated successfully.');
    }

    /**
     * Soft-delete a company tenant.
     */
    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()
            ->route('admin.companies.index')
            ->with('status', 'Company deleted successfully.');
    }

    /**
     * Show company user assignment.
     */
    public function users(Company $company): View
    {
        $company->load([
            'users.roles' => fn ($query) => $query->orderBy('name'),
        ]);

        $users = User::query()
            ->with('company')
            ->orderBy('name')
            ->orderBy('email')
            ->get();

        return view('admin.companies.users', [
            'company' => $company,
            'users' => $users,
            'userStatuses' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
        ]);
    }

    /**
     * Assign an existing user to a company.
     */
    public function assignUser(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ]);

        $user = User::findOrFail($data['user_id']);
        $user->update([
            'company_id' => $company->id,
            'status' => $data['status'],
        ]);

        return back()->with('status', 'User assigned to company successfully.');
    }

    /**
     * @return array<string, string|null>
     */
    private function validatedCompanyData(Request $request, ?Company $company = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', Rule::in(array_keys(self::STATUSES))],
        ]);
    }

    /**
     * @param  array<string, string|null>  $data
     *
     * @throws ValidationException
     */
    private function validatedSlug(Request $request, array $data, ?Company $company = null): string
    {
        $slugWasProvided = $request->filled('slug');
        $baseSlug = Str::slug($slugWasProvided ? (string) $data['slug'] : (string) $data['name']);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'slug' => 'The slug must contain at least one letter or number.',
            ]);
        }

        $existingSlugQuery = Company::withTrashed()
            ->where('slug', $baseSlug)
            ->when($company, fn ($query) => $query->whereKeyNot($company->id));

        if ($slugWasProvided && $existingSlugQuery->exists()) {
            throw ValidationException::withMessages([
                'slug' => 'The slug has already been taken.',
            ]);
        }

        return $this->uniqueSlug($baseSlug, $company);
    }

    private function uniqueSlug(string $baseSlug, ?Company $company = null): string
    {
        $slug = $baseSlug;
        $suffix = 2;

        while (
            Company::withTrashed()
                ->where('slug', $slug)
                ->when($company, fn ($query) => $query->whereKeyNot($company->id))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}

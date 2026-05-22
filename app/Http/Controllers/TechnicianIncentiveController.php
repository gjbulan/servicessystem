<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Models\Branch;
use App\Models\Company;
use App\Models\TechnicianIncentive;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TechnicianIncentiveController extends Controller
{
    use ResolvesTenantCompany;

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);
        $user = $request->user();

        abort_unless($this->canViewIncentives($user), 403, 'You do not have access to technician incentives.');

        $query = TechnicianIncentive::query()
            ->with(['branch', 'jobOrder', 'technician'])
            ->where('company_id', $company->id)
            ->when($this->isTechnicianOnly($user), fn ($query) => $query->where('technician_id', $user->id))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->query('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->query('date_to')))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->integer('branch_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')));

        if ($request->filled('technician_id') && ! $this->isTechnicianOnly($user)) {
            $query->where('technician_id', $request->integer('technician_id'));
        }

        return view('technician-incentives.index', [
            'branches' => Branch::query()->where('company_id', $company->id)->orderBy('name')->get(),
            'canApproveOrPay' => $this->canApproveOrPay($user),
            'canManageIncentives' => $this->canManageIncentives($user),
            'filters' => $request->only(['date_from', 'date_to', 'branch_id', 'technician_id', 'status']),
            'incentives' => $query
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'statuses' => TechnicianIncentive::STATUSES,
            'technicians' => $this->technicians($company, $user),
        ]);
    }

    public function show(Request $request, string $technicianIncentive): View
    {
        $company = $this->tenantCompany($request);
        $user = $request->user();
        $incentive = $this->tenantIncentive($company, $technicianIncentive);
        $this->authorizeViewRecord($user, $incentive);

        $incentive->load(['approver', 'branch', 'jobOrder', 'jobOrderService', 'service', 'technician']);

        return view('technician-incentives.show', [
            'canApproveOrPay' => $this->canApproveOrPay($user),
            'canManageIncentives' => $this->canManageIncentives($user),
            'incentive' => $incentive,
            'statuses' => TechnicianIncentive::STATUSES,
        ]);
    }

    public function edit(Request $request, string $technicianIncentive): View
    {
        $company = $this->tenantCompany($request);
        $incentive = $this->tenantIncentive($company, $technicianIncentive);
        $this->authorizeOverride($request->user(), $incentive);

        return view('technician-incentives.edit', [
            'incentive' => $incentive->load(['branch', 'jobOrder', 'technician']),
            'statuses' => TechnicianIncentive::STATUSES,
        ]);
    }

    public function update(Request $request, string $technicianIncentive): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $incentive = $this->tenantIncentive($company, $technicianIncentive);
        $this->authorizeOverride($request->user(), $incentive);

        $data = $request->validate([
            'override_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'override_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $overrideAmount = $data['override_amount'] ?? null;

        $incentive->update([
            'override_amount' => $overrideAmount !== null && $overrideAmount !== '' ? $overrideAmount : null,
            'override_reason' => $data['override_reason'] ?? null,
            'final_amount' => $overrideAmount !== null && $overrideAmount !== '' ? $overrideAmount : $incentive->default_amount,
        ]);

        return redirect()
            ->route('technician-incentives.show', $incentive)
            ->with('status', 'Technician incentive updated successfully.');
    }

    public function approve(Request $request, string $technicianIncentive): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $incentive = $this->tenantIncentive($company, $technicianIncentive);

        abort_unless($this->canApproveOrPay($request->user()), 403, 'Only company admins can approve incentives.');
        abort_unless($incentive->status === 'pending', 403, 'Only pending incentives can be approved.');

        $incentive->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('technician-incentives.show', $incentive)
            ->with('status', 'Technician incentive approved.');
    }

    public function markPaid(Request $request, string $technicianIncentive): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $incentive = $this->tenantIncentive($company, $technicianIncentive);

        abort_unless($this->canApproveOrPay($request->user()), 403, 'Only company admins can mark incentives as paid.');
        abort_if($incentive->status === 'paid', 403, 'Paid incentives cannot be changed.');

        if ($incentive->status !== 'approved') {
            throw ValidationException::withMessages([
                'status' => 'Only approved incentives can be marked paid.',
            ]);
        }

        $incentive->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return redirect()
            ->route('technician-incentives.show', $incentive)
            ->with('status', 'Technician incentive marked as paid.');
    }

    public function cancel(Request $request, string $technicianIncentive): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $incentive = $this->tenantIncentive($company, $technicianIncentive);

        abort_unless($this->canManageIncentives($request->user()), 403, 'You cannot cancel incentives.');
        abort_if($incentive->status === 'paid', 403, 'Paid incentives cannot be cancelled.');

        $incentive->update([
            'status' => 'cancelled',
        ]);

        return redirect()
            ->route('technician-incentives.show', $incentive)
            ->with('status', 'Technician incentive cancelled.');
    }

    private function tenantIncentive(Company $company, int|string $id): TechnicianIncentive
    {
        return TechnicianIncentive::query()
            ->where('company_id', $company->id)
            ->whereKey($id)
            ->firstOrFail();
    }

    private function authorizeViewRecord(User $user, TechnicianIncentive $incentive): void
    {
        if ($this->canManageIncentives($user)) {
            return;
        }

        abort_unless($user->hasRole('Technician') && (int) $incentive->technician_id === (int) $user->id, 404);
    }

    private function authorizeOverride(User $user, TechnicianIncentive $incentive): void
    {
        abort_unless($this->canManageIncentives($user), 403, 'You cannot edit technician incentives.');
        abort_if(in_array($incentive->status, ['paid', 'cancelled'], true), 403, 'Paid or cancelled incentives are read-only.');
    }

    private function canViewIncentives(User $user): bool
    {
        return $this->canManageIncentives($user) || $user->hasRole('Technician');
    }

    private function canManageIncentives(User $user): bool
    {
        return $user->hasPermission('manage_technician_incentives') || $user->hasRole(['Company Admin', 'Branch Manager']) || $user->isSuperAdmin();
    }

    private function canApproveOrPay(User $user): bool
    {
        return $user->hasRole('Company Admin') || $user->isSuperAdmin();
    }

    private function isTechnicianOnly(User $user): bool
    {
        return $user->hasRole('Technician') && ! $this->canManageIncentives($user);
    }

    private function technicians(Company $company, User $user)
    {
        if ($this->isTechnicianOnly($user)) {
            return collect([$user]);
        }

        return User::query()
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->whereHas('roles', fn ($query) => $query->where('name', 'Technician'))
            ->orderBy('name')
            ->get();
    }
}

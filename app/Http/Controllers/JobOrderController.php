<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Models\Branch;
use App\Models\BranchItemVariantStock;
use App\Models\Company;
use App\Models\CustomerAssetServiceHistory;
use App\Models\InventoryTransaction;
use App\Models\ItemVariant;
use App\Models\JobOrder;
use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class JobOrderController extends Controller
{
    use ResolvesTenantCompany;

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('job-orders.index', [
            'jobOrders' => JobOrder::query()
                ->with(['branch', 'customer', 'customerAsset'])
                ->where('company_id', $company->id)
                ->latest()
                ->paginate(10),
            'statuses' => JobOrder::STATUSES,
        ]);
    }

    public function show(Request $request, string $jobOrder): View
    {
        $company = $this->tenantCompany($request);
        $jobOrder = $this->tenantJobOrder($company, $jobOrder);
        $jobOrder->load([
            'branch',
            'booking',
            'customer',
            'customerAsset.assetType',
            'creator',
            'items.itemVariant.item',
            'serviceHistory',
            'services',
            'technicians.technician',
        ]);

        return view('job-orders.show', [
            'jobOrder' => $jobOrder,
            'statuses' => JobOrder::STATUSES,
        ]);
    }

    public function edit(Request $request, string $jobOrder): View
    {
        $company = $this->tenantCompany($request);
        $jobOrder = $this->tenantJobOrder($company, $jobOrder);
        $jobOrder->load(['items', 'services']);

        abort_if($jobOrder->status === 'completed', 403, 'Completed job orders cannot be edited.');

        return view('job-orders.edit', $this->formData($company) + [
            'itemRows' => $jobOrder->items->map(fn ($item) => [
                'item_variant_id' => $item->item_variant_id,
                'item_name_snapshot' => $item->item_name_snapshot,
                'quantity' => $item->quantity,
                'cost_price_snapshot' => $item->cost_price_snapshot,
                'selling_price_snapshot' => $item->selling_price_snapshot,
                'notes' => $item->notes,
            ])->all(),
            'jobOrder' => $jobOrder,
            'serviceRows' => $jobOrder->services->map(fn ($service) => [
                'service_id' => $service->service_id,
                'service_name_snapshot' => $service->service_name_snapshot,
                'price_snapshot' => $service->price_snapshot,
                'status' => $service->status,
                'notes' => $service->notes,
            ])->all(),
        ]);
    }

    public function update(Request $request, string $jobOrder): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $jobOrder = $this->tenantJobOrder($company, $jobOrder);

        abort_if($jobOrder->status === 'completed', 403, 'Completed job orders cannot be edited.');

        $data = $this->validatedJobOrderData($request, $company);
        $services = $this->preparedServices($data['services'] ?? [], $company);
        $items = $this->preparedItems($data['items'] ?? [], $company);

        DB::transaction(function () use ($data, $items, $jobOrder, $services): void {
            $jobOrder->update([
                'status' => $data['status'],
                'customer_complaint' => $data['customer_complaint'] ?? null,
                'inspection_notes' => $data['inspection_notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'approval_status' => $data['approval_status'] ?? null,
                'approval_notes' => $data['approval_notes'] ?? null,
                'started_at' => $data['started_at'] ?? null,
            ]);

            $jobOrder->services()->delete();
            foreach ($services as $service) {
                $jobOrder->services()->create($service);
            }

            $jobOrder->items()->delete();
            foreach ($items as $item) {
                $jobOrder->items()->create($item);
            }
        });

        return redirect()
            ->route('job-orders.show', $jobOrder)
            ->with('status', 'Job order updated successfully.');
    }

    public function assignTechnicians(Request $request, string $jobOrder): View
    {
        $company = $this->tenantCompany($request);
        $jobOrder = $this->tenantJobOrder($company, $jobOrder);
        $jobOrder->load('technicians');

        return view('job-orders.assign-technicians', [
            'assignedTechnicians' => $jobOrder->technicians->keyBy('technician_id'),
            'jobOrder' => $jobOrder,
            'technicians' => User::query()
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function updateTechnicians(Request $request, string $jobOrder): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $jobOrder = $this->tenantJobOrder($company, $jobOrder);

        $data = $request->validate([
            'technicians' => ['nullable', 'array'],
            'technicians.*.selected' => ['nullable', 'boolean'],
            'technicians.*.technician_id' => ['nullable', Rule::exists('users', 'id')->where('company_id', $company->id)->where('status', 'active')],
            'technicians.*.role' => ['nullable', 'string', 'max:255'],
            'technicians.*.is_primary' => ['nullable', 'boolean'],
            'technicians.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $rows = collect($data['technicians'] ?? []);
        $hasSelectedMarkers = $rows->contains(fn ($row) => array_key_exists('selected', $row));
        $rows = $rows
            ->filter(fn ($row) => ! empty($row['technician_id']) && (! $hasSelectedMarkers || ! empty($row['selected'])))
            ->unique(fn ($row) => (int) $row['technician_id'])
            ->values();

        DB::transaction(function () use ($jobOrder, $rows): void {
            $jobOrder->technicians()->delete();

            foreach ($rows as $row) {
                $jobOrder->technicians()->create([
                    'technician_id' => $row['technician_id'],
                    'role' => $row['role'] ?? null,
                    'is_primary' => ! empty($row['is_primary']),
                    'notes' => $row['notes'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('job-orders.show', $jobOrder)
            ->with('status', 'Technicians assigned successfully.');
    }

    public function complete(Request $request, string $jobOrder): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $jobOrder = $this->tenantJobOrder($company, $jobOrder);

        DB::transaction(function () use ($company, $jobOrder, $request): void {
            $jobOrder = JobOrder::query()
                ->where('company_id', $company->id)
                ->whereKey($jobOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            $jobOrder->load(['booking', 'items', 'services']);
            $this->deductInventoryOnce($jobOrder, $request->user()->id);
            $completedAt = $jobOrder->completed_at ?? now();

            $jobOrder->update([
                'status' => 'completed',
                'started_at' => $jobOrder->started_at ?? $completedAt,
                'completed_at' => $completedAt,
            ]);

            $this->createServiceHistoryOnce($jobOrder);

            if ($jobOrder->booking) {
                $jobOrder->booking->update(['status' => 'completed']);
            }
        });

        return redirect()
            ->route('job-orders.show', $jobOrder)
            ->with('status', 'Job order completed successfully.');
    }

    public function cancel(Request $request, string $jobOrder): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $jobOrder = $this->tenantJobOrder($company, $jobOrder);
        $jobOrder->update(['status' => 'cancelled']);

        if ($jobOrder->booking) {
            $jobOrder->booking->update(['status' => 'cancelled']);
        }

        return redirect()
            ->route('job-orders.show', $jobOrder)
            ->with('status', 'Job order cancelled.');
    }

    private function tenantJobOrder(Company $company, int|string $id): JobOrder
    {
        return JobOrder::query()
            ->where('company_id', $company->id)
            ->whereKey($id)
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Company $company): array
    {
        return [
            'branches' => Branch::query()->where('company_id', $company->id)->orderBy('name')->get(),
            'itemVariants' => ItemVariant::query()
                ->with(['item.brand'])
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->orderBy('variant_name')
                ->get(),
            'services' => Service::query()->where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(),
            'statuses' => JobOrder::STATUSES,
            'usesItemVariants' => $company->usesItemVariants(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedJobOrderData(Request $request, Company $company): array
    {
        return $request->validate([
            'status' => ['required', Rule::in(array_keys(JobOrder::STATUSES))],
            'customer_complaint' => ['nullable', 'string', 'max:3000'],
            'inspection_notes' => ['nullable', 'string', 'max:3000'],
            'internal_notes' => ['nullable', 'string', 'max:3000'],
            'approval_status' => ['nullable', 'string', 'max:255'],
            'approval_notes' => ['nullable', 'string', 'max:3000'],
            'started_at' => ['nullable', 'date'],
            'services' => ['nullable', 'array'],
            'services.*.service_id' => ['nullable', Rule::exists('services', 'id')->where('company_id', $company->id)],
            'services.*.service_name_snapshot' => ['nullable', 'string', 'max:255'],
            'services.*.price_snapshot' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'services.*.status' => ['nullable', 'string', 'max:255'],
            'services.*.notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'array'],
            'items.*.item_variant_id' => ['nullable', Rule::exists('item_variants', 'id')->where('company_id', $company->id)],
            'items.*.item_name_snapshot' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'numeric', 'gt:0', 'max:9999999999.99'],
            'items.*.cost_price_snapshot' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'items.*.selling_price_snapshot' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function preparedServices(array $rows, Company $company): array
    {
        $serviceIds = collect($rows)->pluck('service_id')->filter()->map(fn ($id) => (int) $id)->unique();
        $services = Service::query()
            ->where('company_id', $company->id)
            ->whereIn('id', $serviceIds)
            ->get()
            ->keyBy('id');

        $prepared = [];

        foreach ($rows as $row) {
            $service = ! empty($row['service_id']) ? $services->get((int) $row['service_id']) : null;
            $name = $service?->name ?? ($row['service_name_snapshot'] ?? null);

            if (! $name) {
                continue;
            }

            $prepared[] = [
                'service_id' => $service?->id,
                'service_name_snapshot' => $name,
                'price_snapshot' => $row['price_snapshot'] ?? $service?->default_price ?? 0,
                'status' => $row['status'] ?? null,
                'notes' => $row['notes'] ?? null,
            ];
        }

        return $prepared;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function preparedItems(array $rows, Company $company): array
    {
        $variantIds = collect($rows)->pluck('item_variant_id')->filter()->map(fn ($id) => (int) $id)->unique();
        $variants = ItemVariant::query()
            ->with('item')
            ->where('company_id', $company->id)
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        $prepared = [];

        foreach ($rows as $row) {
            $variant = ! empty($row['item_variant_id']) ? $variants->get((int) $row['item_variant_id']) : null;
            $name = $variant?->item?->name ?? ($row['item_name_snapshot'] ?? null);

            if (! $name || empty($row['quantity'])) {
                continue;
            }

            $prepared[] = [
                'item_variant_id' => $variant?->id,
                'item_name_snapshot' => $name,
                'variant_name_snapshot' => $variant?->variant_name,
                'sku_snapshot' => $variant?->sku,
                'quantity' => $row['quantity'],
                'cost_price_snapshot' => $row['cost_price_snapshot'] ?? $variant?->cost_price ?? 0,
                'selling_price_snapshot' => $row['selling_price_snapshot'] ?? $variant?->selling_price ?? 0,
                'notes' => $row['notes'] ?? null,
            ];
        }

        return $prepared;
    }

    private function deductInventoryOnce(JobOrder $jobOrder, int $userId): void
    {
        $alreadyDeducted = InventoryTransaction::query()
            ->where('company_id', $jobOrder->company_id)
            ->where('transaction_type', 'job_order_usage')
            ->where('reference_type', 'JobOrder')
            ->where('reference_id', $jobOrder->id)
            ->exists();

        if ($alreadyDeducted) {
            return;
        }

        foreach ($jobOrder->items as $item) {
            if (! $item->item_variant_id) {
                continue;
            }

            $stock = BranchItemVariantStock::query()
                ->where('company_id', $jobOrder->company_id)
                ->where('branch_id', $jobOrder->branch_id)
                ->where('item_variant_id', $item->item_variant_id)
                ->lockForUpdate()
                ->first();

            $previousStock = $stock ? (float) $stock->current_stock : 0;
            $quantity = (float) $item->quantity;
            $newStock = $previousStock - $quantity;

            if (! $stock || $newStock < 0) {
                throw ValidationException::withMessages([
                    'items' => "Insufficient stock for {$item->item_name_snapshot}.",
                ]);
            }

            $stock->update([
                'current_stock' => $newStock,
            ]);

            InventoryTransaction::create([
                'company_id' => $jobOrder->company_id,
                'branch_id' => $jobOrder->branch_id,
                'item_variant_id' => $item->item_variant_id,
                'transaction_type' => 'job_order_usage',
                'quantity' => -1 * $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reference_type' => 'JobOrder',
                'reference_id' => $jobOrder->id,
                'notes' => "Job order {$jobOrder->job_order_number}",
                'created_by' => $userId,
            ]);
        }
    }

    private function createServiceHistoryOnce(JobOrder $jobOrder): CustomerAssetServiceHistory
    {
        $summary = $jobOrder->services->pluck('service_name_snapshot')->filter()->implode(', ');

        if ($summary === '') {
            $summary = $jobOrder->customer_complaint ?: 'Service completed.';
        }

        return CustomerAssetServiceHistory::query()->firstOrCreate([
            'job_order_id' => $jobOrder->id,
        ], [
            'company_id' => $jobOrder->company_id,
            'branch_id' => $jobOrder->branch_id,
            'customer_id' => $jobOrder->customer_id,
            'customer_asset_id' => $jobOrder->customer_asset_id,
            'service_summary' => $summary,
            'service_date' => ($jobOrder->completed_at ?? now())->toDateString(),
            'notes' => $jobOrder->inspection_notes,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchItemVariantStock;
use App\Models\Company;
use App\Models\Customer;
use App\Models\InventoryTransaction;
use App\Models\ItemVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    use ResolvesTenantCompany;

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('sales.index', [
            'sales' => Sale::query()
                ->with(['branch', 'customer'])
                ->where('company_id', $company->id)
                ->latest('sale_date')
                ->latest()
                ->paginate(10),
            'statuses' => Sale::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('sales.create', $this->formData($company) + [
            'allowedStatuses' => [
                'draft' => Sale::STATUSES['draft'],
                'unpaid' => Sale::STATUSES['unpaid'],
            ],
            'lineItems' => [
                ['item_variant_id' => '', 'quantity' => '1', 'unit_price' => ''],
            ],
            'sale' => new Sale([
                'sale_date' => now()->toDateString(),
                'status' => 'unpaid',
                'discount_amount' => '0.00',
                'tax_amount' => '0.00',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $data = $this->validatedSaleData($request, $company, ['draft', 'unpaid']);
        $preparedItems = $this->preparedItems($data['items'], $company);
        $totals = $this->totals($preparedItems, $data);

        $sale = DB::transaction(function () use ($company, $data, $preparedItems, $request, $totals): Sale {
            $sale = Sale::create([
                'company_id' => $company->id,
                'branch_id' => $data['branch_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'sale_number' => $this->nextSaleNumber($company->id),
                'status' => $data['status'],
                'sale_date' => $data['sale_date'],
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'tax_amount' => $totals['tax_amount'],
                'total' => $totals['total'],
                'amount_paid' => 0,
                'balance_due' => $totals['total'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            $this->saveSaleItems($sale, $preparedItems);

            return $sale;
        });

        return redirect()
            ->route('sales.show', $sale)
            ->with('status', 'Sale created successfully.');
    }

    public function show(Request $request, string $sale): View
    {
        $company = $this->tenantCompany($request);
        $sale = $this->tenantSale($company, $sale);
        $sale->load(['branch', 'customer', 'creator', 'items.itemVariant.item', 'payments.receiver']);

        return view('sales.show', [
            'sale' => $sale,
            'statuses' => Sale::STATUSES,
        ]);
    }

    public function edit(Request $request, string $sale): View
    {
        $company = $this->tenantCompany($request);
        $sale = $this->tenantSale($company, $sale);

        abort_if($this->cannotEdit($sale), 403, 'Paid, partially paid, or void sales cannot be edited.');

        $sale->load('items');

        return view('sales.edit', $this->formData($company) + [
            'allowedStatuses' => [
                'draft' => Sale::STATUSES['draft'],
                'unpaid' => Sale::STATUSES['unpaid'],
                'void' => Sale::STATUSES['void'],
            ],
            'lineItems' => $sale->items->map(fn (SaleItem $item) => [
                'item_variant_id' => $item->item_variant_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ])->all(),
            'sale' => $sale,
        ]);
    }

    public function update(Request $request, string $sale): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $sale = $this->tenantSale($company, $sale);

        abort_if($this->cannotEdit($sale), 403, 'Paid, partially paid, or void sales cannot be edited.');

        $data = $this->validatedSaleData($request, $company, ['draft', 'unpaid', 'void']);
        $preparedItems = $this->preparedItems($data['items'], $company);
        $totals = $this->totals($preparedItems, $data);

        DB::transaction(function () use ($data, $preparedItems, $sale, $totals): void {
            $sale->update([
                'branch_id' => $data['branch_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'status' => $data['status'],
                'sale_date' => $data['sale_date'],
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'tax_amount' => $totals['tax_amount'],
                'total' => $totals['total'],
                'balance_due' => $totals['total'],
                'notes' => $data['notes'] ?? null,
            ]);

            $sale->items()->delete();
            $this->saveSaleItems($sale, $preparedItems);
        });

        return redirect()
            ->route('sales.show', $sale)
            ->with('status', 'Sale updated successfully.');
    }

    public function payments(Request $request, string $sale): View
    {
        $company = $this->tenantCompany($request);
        $sale = $this->tenantSale($company, $sale);
        $sale->load(['branch', 'customer', 'items', 'payments.receiver']);

        return view('sales.payments', [
            'sale' => $sale,
            'statuses' => Sale::STATUSES,
        ]);
    }

    public function storePayment(Request $request, string $sale): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $sale = $this->tenantSale($company, $sale);

        abort_if($sale->status === 'void', 403, 'Void sales cannot receive payments.');

        $data = $request->validate([
            'payment_method' => ['nullable', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($company, $data, $request, $sale): void {
            $sale = Sale::query()
                ->where('company_id', $company->id)
                ->whereKey($sale->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((float) $sale->balance_due <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'This sale is already fully paid.',
                ]);
            }

            if ((float) $data['amount'] > (float) $sale->balance_due) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount cannot exceed the balance due.',
                ]);
            }

            $sale->payments()->create([
                'company_id' => $company->id,
                'payment_method' => $data['payment_method'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'amount' => $data['amount'],
                'paid_at' => $data['paid_at'],
                'received_by' => $request->user()->id,
                'notes' => $data['notes'] ?? null,
            ]);

            $amountPaid = (float) $sale->amount_paid + (float) $data['amount'];
            $balanceDue = max((float) $sale->total - $amountPaid, 0);

            $sale->update([
                'amount_paid' => $amountPaid,
                'balance_due' => $balanceDue,
                'status' => $balanceDue <= 0 ? 'paid' : 'partial',
            ]);

            if ($balanceDue <= 0) {
                $sale->load('items');
                $this->deductInventoryOnce($sale, $request->user()->id);
            }
        });

        return redirect()
            ->route('sales.show', $sale)
            ->with('status', 'Payment recorded successfully.');
    }

    public function printView(Request $request, string $sale): View
    {
        $company = $this->tenantCompany($request);
        $sale = $this->tenantSale($company, $sale);
        $sale->load(['company', 'branch', 'customer', 'items', 'payments.receiver']);

        return view('sales.print', [
            'sale' => $sale,
            'statuses' => Sale::STATUSES,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Company $company): array
    {
        $usesItemVariants = $company->usesItemVariants();

        return [
            'branches' => Branch::query()
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'customers' => Customer::query()
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'usesItemVariants' => $usesItemVariants,
            'variants' => $this->sellableVariants($company, $usesItemVariants),
        ];
    }

    private function tenantSale(Company $company, int|string $id): Sale
    {
        return Sale::query()
            ->where('company_id', $company->id)
            ->whereKey($id)
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedSaleData(Request $request, Company $company, array $allowedStatuses): array
    {
        return $request->validate([
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('company_id', $company->id)],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('company_id', $company->id)],
            'status' => ['required', Rule::in($allowedStatuses)],
            'sale_date' => ['required', 'date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'tax_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_variant_id' => ['required', Rule::exists('item_variants', 'id')->where('company_id', $company->id)],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function preparedItems(array $items, Company $company): array
    {
        $variantIds = collect($items)->pluck('item_variant_id')->map(fn ($id) => (int) $id)->unique()->values();
        $variants = ItemVariant::query()
            ->with('item')
            ->where('company_id', $company->id)
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        $usesItemVariants = $company->usesItemVariants();
        $prepared = [];

        foreach ($items as $item) {
            $variant = $variants->get((int) $item['item_variant_id']);

            if (! $variant) {
                throw ValidationException::withMessages([
                    'items' => 'One or more selected sale items are invalid.',
                ]);
            }

            if (! $usesItemVariants && $variant->variant_name !== 'Default') {
                throw ValidationException::withMessages([
                    'items' => 'Select valid items for simple inventory mode.',
                ]);
            }

            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];

            $prepared[] = [
                'item_variant_id' => $variant->id,
                'item_name_snapshot' => $variant->item?->name ?? 'Item',
                'variant_name_snapshot' => $usesItemVariants ? $variant->variant_name : null,
                'sku_snapshot' => $variant->sku,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'cost_price_snapshot' => (float) $variant->cost_price,
                'line_total' => round($quantity * $unitPrice, 2),
            ];
        }

        return $prepared;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $data
     * @return array<string, float>
     */
    private function totals(array $items, array $data): array
    {
        $subtotal = round(array_sum(array_column($items, 'line_total')), 2);
        $discountAmount = round((float) ($data['discount_amount'] ?? 0), 2);
        $taxAmount = round((float) ($data['tax_amount'] ?? 0), 2);
        $total = max(round($subtotal - $discountAmount + $taxAmount, 2), 0);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $preparedItems
     */
    private function saveSaleItems(Sale $sale, array $preparedItems): void
    {
        foreach ($preparedItems as $item) {
            $sale->items()->create($item + [
                'company_id' => $sale->company_id,
            ]);
        }
    }

    private function nextSaleNumber(int $companyId): string
    {
        $prefix = 'S'.now()->format('Ymd').'-';
        $next = Sale::withTrashed()
            ->where('company_id', $companyId)
            ->where('sale_number', 'like', $prefix.'%')
            ->count() + 1;

        do {
            $saleNumber = $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (Sale::withTrashed()->where('company_id', $companyId)->where('sale_number', $saleNumber)->exists());

        return $saleNumber;
    }

    private function cannotEdit(Sale $sale): bool
    {
        return (float) $sale->amount_paid > 0 || in_array($sale->status, ['paid', 'partial', 'void'], true);
    }

    private function deductInventoryOnce(Sale $sale, int $userId): void
    {
        $alreadyDeducted = InventoryTransaction::query()
            ->where('company_id', $sale->company_id)
            ->where('transaction_type', 'sale')
            ->where('reference_type', 'Sale')
            ->where('reference_id', $sale->id)
            ->exists();

        if ($alreadyDeducted) {
            return;
        }

        foreach ($sale->items as $item) {
            $stock = BranchItemVariantStock::query()
                ->where('company_id', $sale->company_id)
                ->where('branch_id', $sale->branch_id)
                ->where('item_variant_id', $item->item_variant_id)
                ->lockForUpdate()
                ->first();

            $previousStock = $stock ? (float) $stock->current_stock : 0;
            $quantity = (float) $item->quantity;
            $newStock = $previousStock - $quantity;

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'amount' => "Insufficient stock for {$item->item_name_snapshot}.",
                ]);
            }

            $stock->update([
                'current_stock' => $newStock,
            ]);

            InventoryTransaction::create([
                'company_id' => $sale->company_id,
                'branch_id' => $sale->branch_id,
                'item_variant_id' => $item->item_variant_id,
                'transaction_type' => 'sale',
                'quantity' => -1 * $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reference_type' => 'Sale',
                'reference_id' => $sale->id,
                'notes' => "Sale {$sale->sale_number}",
                'created_by' => $userId,
            ]);
        }
    }

    /**
     * @return Collection<int, ItemVariant>
     */
    private function sellableVariants(Company $company, bool $usesItemVariants): Collection
    {
        return ItemVariant::query()
            ->with(['item.brand', 'item.category'])
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->when(! $usesItemVariants, fn ($query) => $query->where('variant_name', 'Default'))
            ->get()
            ->sortBy(fn (ItemVariant $variant) => strtolower(($variant->item?->name ?? '').' '.$variant->variant_name))
            ->values();
    }
}

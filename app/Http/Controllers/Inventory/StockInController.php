<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchItemVariantStock;
use App\Models\InventoryTransaction;
use App\Models\ItemVariant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StockInController extends Controller
{
    use ResolvesTenantCompany;

    public function create(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('inventory.stock-in.create', [
            'branches' => Branch::query()
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'transactionTypes' => InventoryTransaction::TYPES,
            'variants' => ItemVariant::query()
                ->with(['item.brand', 'item.category'])
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->orderBy('variant_name')
                ->get(),
            'recentTransactions' => InventoryTransaction::query()
                ->with([
                    'branch' => fn ($query) => $query->withTrashed(),
                    'creator',
                    'itemVariant' => fn ($query) => $query
                        ->withTrashed()
                        ->with(['item' => fn ($itemQuery) => $itemQuery->withTrashed()]),
                ])
                ->where('company_id', $company->id)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(25)
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->tenantCompany($request);

        $data = $request->validate([
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('company_id', $company->id)],
            'item_variant_id' => ['required', Rule::exists('item_variants', 'id')->where('company_id', $company->id)],
            'transaction_type' => ['required', Rule::in(array_keys(InventoryTransaction::TYPES))],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($company, $data, $request): void {
            $stock = BranchItemVariantStock::query()
                ->where('company_id', $company->id)
                ->where('branch_id', $data['branch_id'])
                ->where('item_variant_id', $data['item_variant_id'])
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $stock = BranchItemVariantStock::create([
                    'company_id' => $company->id,
                    'branch_id' => $data['branch_id'],
                    'item_variant_id' => $data['item_variant_id'],
                    'current_stock' => 0,
                    'low_stock_threshold' => 0,
                ]);
            }

            $previousStock = (float) $stock->current_stock;
            $quantity = (float) $data['quantity'];
            $newStock = $this->newStock($previousStock, $quantity, $data['transaction_type']);

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'This transaction would make stock negative.',
                ]);
            }

            $stock->update([
                'current_stock' => $newStock,
            ]);

            InventoryTransaction::create([
                'company_id' => $company->id,
                'branch_id' => $data['branch_id'],
                'item_variant_id' => $data['item_variant_id'],
                'transaction_type' => $data['transaction_type'],
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('inventory.stock-in.create')
            ->with('status', 'Inventory transaction recorded successfully.');
    }

    private function newStock(float $previousStock, float $quantity, string $transactionType): float
    {
        return match ($transactionType) {
            'damage' => $previousStock - $quantity,
            default => $previousStock + $quantity,
        };
    }
}

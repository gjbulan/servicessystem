<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\TechnicianIncentive;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountingReportController extends Controller
{
    use ResolvesTenantCompany;

    public function financialSummary(Request $request): View
    {
        $company = $this->tenantCompany($request);
        $filters = $this->filters($request, $company);

        return view('accounting.reports.financial-summary', $this->reportData($company, $filters));
    }

    public function incomeStatement(Request $request): View
    {
        $company = $this->tenantCompany($request);
        $filters = $this->filters($request, $company);

        return view('accounting.reports.income-statement', $this->reportData($company, $filters));
    }

    public function branchProfitability(Request $request): View
    {
        $company = $this->tenantCompany($request);
        $filters = $this->filters($request, $company, allowBranch: false);

        $branches = Branch::query()
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->get()
            ->map(function (Branch $branch) use ($filters): array {
                $branchFilters = array_merge($filters, ['branch_id' => $branch->id]);
                $summary = $this->summary($branch->company_id, $branchFilters);

                return [
                    'branch' => $branch,
                    'summary' => $summary,
                ];
            });

        return view('accounting.reports.branch-profitability', [
            'branches' => $branches,
            'filters' => $filters,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function reportData(Company $company, array $filters): array
    {
        return [
            'branches' => Branch::query()->where('company_id', $company->id)->orderBy('name')->get(),
            'filters' => $filters,
            'summary' => $this->summary($company->id, $filters),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request, Company $company, bool $allowBranch = true): array
    {
        return $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'branch_id' => $allowBranch
                ? ['nullable', Rule::exists('branches', 'id')->where('company_id', $company->id)]
                : ['nullable'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, float|int>
     */
    private function summary(int $companyId, array $filters): array
    {
        $revenue = $this->paidSalesQuery($companyId, $filters)->sum('total');
        $salesCount = $this->paidSalesQuery($companyId, $filters)->count();
        $cogs = $this->cogs($companyId, $filters);
        $expenses = $this->recordedExpensesQuery($companyId, $filters)->sum('amount');
        $expenseCount = $this->recordedExpensesQuery($companyId, $filters)->count();
        $technicianIncentivesPaid = $this->paidTechnicianIncentivesQuery($companyId, $filters)->sum('final_amount');
        $outstandingBalance = $this->outstandingSalesQuery($companyId, $filters)->sum('balance_due');
        $outstandingSalesCount = $this->outstandingSalesQuery($companyId, $filters)->count();
        $grossProfit = (float) $revenue - (float) $cogs;
        $netProfit = $grossProfit - (float) $expenses - (float) $technicianIncentivesPaid;

        return [
            'revenue' => round((float) $revenue, 2),
            'cogs' => round((float) $cogs, 2),
            'gross_profit' => round($grossProfit, 2),
            'expenses' => round((float) $expenses, 2),
            'technician_incentives_paid' => round((float) $technicianIncentivesPaid, 2),
            'net_profit' => round($netProfit, 2),
            'outstanding_balance' => round((float) $outstandingBalance, 2),
            'sales_count' => $salesCount,
            'expense_count' => $expenseCount,
            'outstanding_sales_count' => $outstandingSalesCount,
        ];
    }

    private function paidSalesQuery(int $companyId, array $filters)
    {
        return Sale::query()
            ->where('company_id', $companyId)
            ->where('status', 'paid')
            ->when(! empty($filters['date_from']), fn ($query) => $query->whereDate('sale_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn ($query) => $query->whereDate('sale_date', '<=', $filters['date_to']))
            ->when(! empty($filters['branch_id']), fn ($query) => $query->where('branch_id', $filters['branch_id']));
    }

    private function outstandingSalesQuery(int $companyId, array $filters)
    {
        return Sale::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->when(! empty($filters['date_from']), fn ($query) => $query->whereDate('sale_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn ($query) => $query->whereDate('sale_date', '<=', $filters['date_to']))
            ->when(! empty($filters['branch_id']), fn ($query) => $query->where('branch_id', $filters['branch_id']));
    }

    private function recordedExpensesQuery(int $companyId, array $filters)
    {
        return Expense::query()
            ->where('company_id', $companyId)
            ->where('status', 'recorded')
            ->when(! empty($filters['date_from']), fn ($query) => $query->whereDate('expense_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn ($query) => $query->whereDate('expense_date', '<=', $filters['date_to']))
            ->when(! empty($filters['branch_id']), fn ($query) => $query->where('branch_id', $filters['branch_id']));
    }

    private function paidTechnicianIncentivesQuery(int $companyId, array $filters)
    {
        return TechnicianIncentive::query()
            ->where('company_id', $companyId)
            ->where('status', 'paid')
            ->when(! empty($filters['date_from']), fn ($query) => $query->whereDate('paid_at', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn ($query) => $query->whereDate('paid_at', '<=', $filters['date_to']))
            ->when(! empty($filters['branch_id']), fn ($query) => $query->where('branch_id', $filters['branch_id']));
    }

    private function cogs(int $companyId, array $filters): float
    {
        return (float) SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sale_items.company_id', $companyId)
            ->where('sales.company_id', $companyId)
            ->where('sales.status', 'paid')
            ->whereNull('sales.deleted_at')
            ->when(! empty($filters['date_from']), fn ($query) => $query->whereDate('sales.sale_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn ($query) => $query->whereDate('sales.sale_date', '<=', $filters['date_to']))
            ->when(! empty($filters['branch_id']), fn ($query) => $query->where('sales.branch_id', $filters['branch_id']))
            ->selectRaw('COALESCE(SUM(sale_items.quantity * sale_items.cost_price_snapshot), 0) as cogs_total')
            ->value('cogs_total');
    }
}

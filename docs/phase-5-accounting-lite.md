# Phase 5 Accounting Lite

## Goal

Phase 5 gives tenant companies basic financial visibility without building a full accounting or ERP system.

This phase builds expense tracking and lightweight financial reports only. It intentionally excludes a general ledger, journal entries, bank reconciliation, payroll, tax filing, purchase orders, subscriptions, and full accounting workflows.

## Data Model

New tables:

- `expense_categories`
- `expenses`

Important relationships:

- A company has many expense categories.
- A company has many expenses.
- A branch has many expenses.
- An expense category has many expenses.
- An expense belongs to one company, optional branch, optional category, and optional creator user.

## Expense Rules

- Expenses are tenant-scoped by `company_id`.
- Expenses may optionally belong to a branch.
- Expenses may optionally belong to an expense category.
- Only `recorded` expenses affect reports.
- `void` expenses remain visible in CRUD but are excluded from financial totals.
- Attachments are optional and stored on the public disk when uploaded.
- There is no approval workflow in Phase 5.

## Revenue Rules

- Recognized revenue comes from paid sales only.
- The report uses `sales.status = paid`.
- Revenue is the sum of `sales.total`.
- Unpaid and partial sales do not count as revenue.
- Unpaid and partial sales contribute to outstanding balances through `sales.balance_due`.

Completed job order service prices are intentionally excluded from Phase 5 revenue to avoid double counting when service work is later converted into a sale or invoice.

## COGS Rules

COGS comes from paid sale item snapshots:

- `sale_items.cost_price_snapshot * sale_items.quantity`
- counted only when the parent sale is paid

## Technician Incentive Rules

Paid technician incentives are included as a separate operating expense line:

- `technician_incentives.status = paid`
- amount uses `technician_incentives.final_amount`
- date filter uses `paid_at`

Technician incentives are not converted into payroll.

## Reports

Added routes:

- `/reports/financial-summary`
- `/reports/income-statement`
- `/reports/branch-profitability`

Reports support date filters. Financial Summary and Income Statement also support an optional branch filter.

Financial Summary shows:

- Total revenue
- Total COGS
- Gross profit
- Total expenses
- Technician incentives paid
- Net profit
- Outstanding unpaid/partial balances
- Sales count
- Expense count

Income Statement shows:

- Revenue
- COGS
- Gross Profit
- Operating Expenses
- Technician Incentives Paid
- Net Profit

Branch Profitability shows each branch's revenue, COGS, gross profit, branch expenses, paid technician incentives, and net profit. Company-wide expenses without a branch are not allocated across branches in Phase 5.

## Access

Accounting Lite routes require:

- `auth`
- `verified`
- `company.access`
- `module:accounting`

If the accounting module is disabled, expense and financial report routes are blocked and hidden from navigation.

## Verification

Focused tests cover:

- Expense category creation.
- Expense creation.
- Tenant-scoped expense visibility.
- Paid-sales-only revenue.
- Outstanding unpaid and partial sales balances.
- COGS from sale item cost snapshots.
- Paid technician incentives shown separately.
- Accounting module blocking.
- Cross-tenant expense and report isolation.

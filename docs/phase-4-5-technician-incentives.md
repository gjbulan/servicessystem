# Phase 4.5 Technician Incentives

## Goal

Phase 4.5 tracks mechanic and technician incentives generated from completed job order services. It uses the service catalog default incentive amount as the starting value, then lets authorized managers override, approve, and mark incentives as paid.

This phase does not build accounting, payroll, purchase orders, subscriptions, percentage incentives, or advanced reports.

## Data Model

New table:

- `technician_incentives`

Important relationships:

- A company has many technician incentives.
- A branch has many technician incentives.
- A job order has many technician incentives.
- A job order service has many technician incentives.
- A service has many technician incentives.
- A technician incentive belongs to a technician user.
- A technician incentive may belong to an approving user through `approved_by`.

Important fields:

- `default_amount` comes from `services.default_incentive_amount`.
- `override_amount` is nullable and can be set before payment.
- `final_amount` is `override_amount` when present; otherwise it is `default_amount`.
- `status` can be `pending`, `approved`, `paid`, or `cancelled`.

## Generation Rules

- Incentives are generated only when a job order is completed.
- Generation runs after job order stock deduction and service history creation.
- The company must have the `technician_incentives` module enabled.
- Existing incentive rows for the job order prevent duplicate generation.
- Only assigned users with the Technician role receive incentives.
- Each assigned technician receives one incentive per job order service.
- For the MVP, each assigned technician receives the full service incentive amount.
- Percentage-based and split-based incentive rules are intentionally not included.

## Management Rules

- Technician incentive routes use `module:technician_incentives`.
- All incentive records are tenant-scoped by `company_id`.
- Technician users can view only their own incentive records.
- Branch Managers and Company Admins can override unpaid, non-cancelled incentives.
- Company Admins can approve incentives and mark approved incentives as paid.
- Paid incentives are read-only.
- Cancelled incentives do not count toward unpaid dashboard totals.

## User Interface

Added screens:

- `/technician-incentives`
- `/technician-incentives/{technicianIncentive}`
- `/technician-incentives/{technicianIncentive}/edit`

Added actions:

- Override incentive amount and reason.
- Approve incentive.
- Mark approved incentive as paid.
- Cancel incentive.

The incentive list supports date range, branch, technician, and status filters.

## Dashboard

Company users with incentive management access see:

- Pending incentives total.
- Approved unpaid incentives total.
- Paid incentives this month.

Technician users see:

- My pending incentives.
- My approved unpaid incentives.
- My paid incentives this month.

## Verification

Focused tests cover:

- Completing a job order generates incentives.
- Completing the same job order twice does not duplicate incentives.
- Generated incentives use the service default incentive amount.
- Overrides update the final amount.
- Paid incentives cannot be edited.
- Technicians see only their own incentives.
- Incentives are skipped when the module is disabled.
- Tenants cannot see another company's incentives.

# Coding Rules

## Laravel Practices

- Use Laravel migrations for database changes.
- Use Eloquent models and relationships for data access.
- Use seeders for default roles and permissions.
- Use middleware for access checks.
- Keep Phase 1 foundation code separate from future business modules.
- Use route middleware aliases from `bootstrap/app.php` for access rules.
- Prefer idempotent seeders with `updateOrCreate` and `sync`.

## Multi-Tenant Rules

- Tenant-owned records should belong to a company unless explicitly documented as system-level data.
- Super admin users may have a `null` `company_id`.
- System roles may have a `null` `company_id`.
- Tenant users should have exactly one assigned company.
- Company access is valid only for companies with `active` or `trial` status.

## RBAC Rules

- Permission checks use the `permissions.key` value.
- Role checks use the human-readable `roles.name` value.
- Super Admin is treated as a platform-wide bypass role.
- `user_roles.branch_id` is reserved for future branch-scoped roles and does not create branch functionality in Phase 1.

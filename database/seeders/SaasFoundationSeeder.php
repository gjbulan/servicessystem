<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class SaasFoundationSeeder extends Seeder
{
    /**
     * Seed the application's SaaS foundation roles and permissions.
     */
    public function run(): void
    {
        $this->seedPermissions();
        $this->seedSystemRoles();
        $this->syncRolePermissions();
    }

    private function seedPermissions(): void
    {
        $permissions = [
            ['module' => 'companies', 'action' => 'manage', 'key' => 'manage_companies', 'description' => 'Manage platform company tenants.'],
            ['module' => 'branches', 'action' => 'manage', 'key' => 'manage_branches', 'description' => 'Manage company branches when branch management is added.'],
            ['module' => 'users', 'action' => 'manage', 'key' => 'manage_users', 'description' => 'Manage users and user access.'],
            ['module' => 'customers', 'action' => 'manage', 'key' => 'manage_customers', 'description' => 'Manage customer records when the customer module is added.'],
            ['module' => 'services', 'action' => 'manage', 'key' => 'manage_services', 'description' => 'Manage service catalog records when the services module is added.'],
            ['module' => 'bookings', 'action' => 'manage', 'key' => 'manage_bookings', 'description' => 'Manage bookings when the booking module is added.'],
            ['module' => 'job_orders', 'action' => 'manage', 'key' => 'manage_job_orders', 'description' => 'Manage job orders when the job order module is added.'],
            ['module' => 'technician_incentives', 'action' => 'manage', 'key' => 'manage_technician_incentives', 'description' => 'Manage technician incentive records.'],
            ['module' => 'sales', 'action' => 'manage', 'key' => 'manage_sales', 'description' => 'Manage sales and payment records.'],
            ['module' => 'invoices', 'action' => 'manage', 'key' => 'manage_invoices', 'description' => 'Manage invoices when the invoice module is added.'],
            ['module' => 'inventory', 'action' => 'manage', 'key' => 'manage_inventory', 'description' => 'Manage inventory when the inventory module is added.'],
            ['module' => 'reports', 'action' => 'view', 'key' => 'view_reports', 'description' => 'View reports when reporting is added.'],
            ['module' => 'settings', 'action' => 'manage', 'key' => 'manage_settings', 'description' => 'Manage company settings.'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['key' => $permission['key']],
                $permission,
            );
        }
    }

    private function seedSystemRoles(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'description' => 'Platform-wide administrator with access to all system permissions.'],
            ['name' => 'Company Admin', 'description' => 'Company administrator for tenant operations and settings.'],
            ['name' => 'Branch Manager', 'description' => 'Branch-level manager for daily operations.'],
            ['name' => 'Technician', 'description' => 'Service technician role for service and job order workflows.'],
            ['name' => 'Cashier', 'description' => 'Cashier role for customer, booking, and invoice workflows.'],
            ['name' => 'Inventory Staff', 'description' => 'Inventory staff role for stock workflows.'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                [
                    'company_id' => null,
                    'name' => $role['name'],
                ],
                [
                    'description' => $role['description'],
                    'is_system_role' => true,
                ],
            );
        }
    }

    private function syncRolePermissions(): void
    {
        $assignments = [
            'Super Admin' => [
                'manage_companies',
                'manage_branches',
                'manage_users',
                'manage_customers',
                'manage_services',
                'manage_bookings',
                'manage_job_orders',
                'manage_technician_incentives',
                'manage_sales',
                'manage_invoices',
                'manage_inventory',
                'view_reports',
                'manage_settings',
            ],
            'Company Admin' => [
                'manage_branches',
                'manage_users',
                'manage_customers',
                'manage_services',
                'manage_bookings',
                'manage_job_orders',
                'manage_technician_incentives',
                'manage_sales',
                'manage_invoices',
                'manage_inventory',
                'view_reports',
                'manage_settings',
            ],
            'Branch Manager' => [
                'manage_customers',
                'manage_services',
                'manage_bookings',
                'manage_job_orders',
                'manage_technician_incentives',
                'manage_sales',
                'manage_invoices',
                'manage_inventory',
                'view_reports',
            ],
            'Technician' => [
                'manage_services',
                'manage_job_orders',
            ],
            'Cashier' => [
                'manage_customers',
                'manage_bookings',
                'manage_sales',
                'manage_invoices',
            ],
            'Inventory Staff' => [
                'manage_inventory',
            ],
        ];

        foreach ($assignments as $roleName => $permissionKeys) {
            $role = Role::query()
                ->whereNull('company_id')
                ->where('name', $roleName)
                ->firstOrFail();

            $permissionIds = Permission::query()
                ->whereIn('key', $permissionKeys)
                ->pluck('id')
                ->all();

            $role->permissions()->sync($permissionIds);
        }
    }
}

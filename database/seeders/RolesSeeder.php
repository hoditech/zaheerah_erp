<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run()
    {
        // Define global permissions
        $globalPermissions = [
            // User and role management
            'manage_users',
            'view_users',
            'manage_roles',
            'view_roles',
            'manage_permissions',
            'view_permissions',

            // Sales and transactions
            'create_sales',
            'view_sales',
            'refund_sales',
            'generate_sales_reports',

            // Product and inventory management
            'create_products',
            'edit_products',
            'delete_products',
            'view_products',
            'manage_inventory',

            // Supplier and purchase order management
            'manage_suppliers',
            'create_purchase_orders',
            'view_purchase_orders',
            'update_purchase_orders',
            'delete_purchase_orders',

            // Production management
            'manage_productions',
            'create_productions',
            'view_productions',
            'update_productions',
            'delete_productions',

            // Dashboard and analytics
            'view_dashboard',
            'generate_reports',
        ];

        // Create global permissions
        foreach ($globalPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $roles = [
            'Super Admin',
            'Admin',
            'Manager',
            'HR',
            'Employee',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Assign global permissions to roles
        $superAdmin = Role::findByName('Super Admin');
        $admin = Role::findByName('Admin');
        $manager = Role::findByName('Manager');
        $employee = Role::findByName('Employee');

        // Super Admin: All permissions
        $superAdmin->syncPermissions(Permission::all());

        // Admin: View permissions
        $viewPermissions = Permission::where('name', 'LIKE', 'view_%')->get();
        $admin->syncPermissions($viewPermissions);

        $manager->syncPermissions([
            'create_purchase_orders',
            'manage_productions',
            'view_products',
            'view_purchase_orders',
            'create_productions',
            'create_products',
            'manage_inventory',
            'view_productions',
            'edit_products',
            'manage_suppliers',
            'update_productions'
        ]);

        // Employee: Sales-related permissions
        $employee->syncPermissions(['create_sales', 'view_sales']);

        // Manager: Category-specific permissions
        $categories = ['water', 'soap', 'electronics', 'furniture'];
        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($categories as $category) {
            foreach ($actions as $action) {
                $permissionName = "{$action}_category_{$category}";
                $permission = Permission::firstOrCreate(['name' => $permissionName]);
                //$manager->givePermissionTo($permission);
            }
        }

        // Optionally assign Super Admin to a default user (e.g., User ID 1)
        $superAdminUser = User::find(1);
        if ($superAdminUser) {
            $superAdminUser->assignRole($superAdmin);
        }

        echo "Roles and permissions seeded successfully.";
    }
}

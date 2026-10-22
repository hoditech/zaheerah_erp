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

            // Bloom page pricing (managed in the ERP, mirrored to the frontsite)
            'view_bloom_pricing',
            'manage_bloom_pricing',

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
            'Finance',
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
        $finance = Role::findByName('Finance');
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
            'update_productions',
            'view_bloom_pricing',
            'manage_bloom_pricing',
        ]);

        $finance->syncPermissions([
            'view_dashboard',
            'generate_reports',
            'generate_sales_reports',
        ]);

        // Employee: Sales-related permissions
        $employee->syncPermissions(['create_sales', 'view_sales']);

        // Manager: Category-specific permissions
        $categories = ['water', 'cosmetics', 'agriculture', 'restaurant', 'trade', ];
        $actions = ['view', 'create', 'edit', 'delete']; 

        foreach ($categories as $category) {
            foreach ($actions as $action) {
                $permissionName = "{$action}_category_{$category}";
                $permission = Permission::firstOrCreate(['name' => $permissionName]);
                //$manager->givePermissionTo($permission);
            }
        }

        // Give the accounts created by CategorySeeder their real roles.
        // Resolving by email avoids "whoever happens to have id 1 becomes
        // Super Admin" and fixes admin/manager/employee@gmail.com, which were
        // left with no role (and therefore a 403 on every permission-gated page).
        $staffRoles = [
            'fattyqadir@yahoo.com' => 'Super Admin',
            'huduyahaya001@gmail.com' => 'Super Admin',
            'admin@gmail.com' => 'Admin',
            'manager@gmail.com' => 'Manager',
            'employee@gmail.com' => 'Employee',
        ];

        foreach ($staffRoles as $email => $roleName) {
            if ($staffUser = User::where('email', $email)->first()) {
                $staffUser->assignRole($roleName);
            }
        }

        echo "Roles and permissions seeded successfully.";
    }
}

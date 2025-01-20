<?php

namespace Database\Seeders;

use App\Models\Category;
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
        $user = User::find(1);
        $superAdmin = Role::findByName('Super Admin');

        $user->assignRole($superAdmin);

        // Assign all permissions to Super Admin
        $user->givePermissionTo($globalPermissions);

        // Assign selected permissions to other roles
        //$admin->givePermissionTo(['manage_users', 'view_users', 'manage_roles', 'view_roles']);
        //$manager->givePermissionTo(['view_sales', 'create_sales', 'view_productions', 'create_productions', 'update_productions']);
        //$hr->givePermissionTo(['view_users', 'manage_users']);
        //$employee->givePermissionTo(['view_productions', 'view_sales']);

        // Define category-specific permissions
        $categories = ['water', 'soap', 'electronics', 'furniture'];
        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($categories as $category) {
            foreach ($actions as $action) {
                $permissionName = "{$action}_category_{$category}";
                $permission = Permission::firstOrCreate(['name' => $permissionName]);

                // Assign permissions to roles
                // if (in_array($action, ['view', 'edit'])) {
                //     $manager->givePermissionTo($permission);
                // }

                // if ($action === 'view') {
                //     $employee->givePermissionTo($permission);
                // }
            }
        }

        echo "Permissions and roles seeded successfully.";
    }

}

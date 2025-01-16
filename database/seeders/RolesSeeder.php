<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Define roles
        $roles = [
            'Technical Manager',
            'Super Admin',
            'Admin',
            'HR',
            'Manager',
            'Employee',
        ];

        // Create or fetch roles
        $roleInstances = [];
        foreach ($roles as $roleName) {
            $roleInstances[$roleName] = Role::firstOrCreate(['name' => $roleName]);
        }

        $technicalManager = $roleInstances['Technical Manager'];
        $superAdmin = $roleInstances['Super Admin'];
        $manager = $roleInstances['Manager'];
        $employee = $roleInstances['Employee'];

        // Define global permissions
        $globalPermissions = [
            'manage_all_categories',
            'view_all_categories',
            'manage_sales',
            'view_sales',
        ];

        // Create global permissions and assign to roles
        foreach ($globalPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
        }
        $technicalManager->givePermissionTo($globalPermissions);
        $superAdmin->givePermissionTo(['view_all_categories', 'view_sales']);

        // Define actions for categories
        $actions = ['view', 'edit', 'delete', 'update'];

        // Fetch categories
        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Skipping category-specific permissions.');
            return;
        }

        // Dynamically create category-specific permissions
        foreach ($categories as $category) {
            foreach ($actions as $action) {
                $permissionName = "{$action}_category_{$category->id}";
                $permission = Permission::firstOrCreate(['name' => $permissionName]);

                // Assign permissions to roles
                if (in_array($action, ['view', 'edit', 'update'])) {
                    $manager->givePermissionTo($permission);
                }

                if ($action === 'view') {
                    $employee->givePermissionTo($permission);
                }
            }
        }

        // Grant all permissions to the Technical Manager
        $technicalManager->givePermissionTo(Permission::all());
    }
}

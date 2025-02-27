<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees.
     */
    public function index()
    {
        $userCategoryId = auth()->user()->category_id;

        $employees = User::with(['category', 'roles'])
            ->where('id', '!=', 1)
            ->get();

        return Inertia::render('HR/Index', [
            'employees' => $employees,
        ]);
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $categories = Category::all();
        $roles = Role::all();
        $permissions = Permission::all(); // Fetch all permissions

        return Inertia::render('HR/Create', [
            'categories' => $categories,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
                'phone' => 'nullable|string|max:15',
                'role_id' => 'required|exists:roles,id',
                'can_login' => 'required|boolean',
                'category_id' => 'required|exists:categories,id',
                'password' => 'required|string|min:8|confirmed',
                'selected_permissions' => 'array',
                'selected_permissions.*' => 'exists:permissions,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            //dd($e->errors()); // Display validation errors
        }
        
        $validated['password'] = bcrypt($validated['password']);

        $role = Role::find($validated['role_id']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'category_id' => $validated['category_id'],
            'password' => $validated['password'],
            'can_login' => $validated['can_login'],
            'employee_id'=> User::generateUserCode(),
        ]);

        $user->assignRole($role);
        $user->syncPermissions($validated['selected_permissions'] ?? []);

        // Log the activity
        log_activity('Create Employee', 'Created employee: ' . $user->name);

        return redirect()->route('employees.index')
            ->with('success', 'Employee added successfully.');
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(User $employee)
    {
        $categories = Category::all();
        $roles = Role::all();
        $permissions = Permission::all();

        // Fetch the employee's current role and permissions
        $employeeRole = $employee->roles->pluck('id')->first();
        $employeePermissions = $employee->getAllPermissions()->pluck('id');

        return Inertia::render('HR/Edit', [
            'employee' => $employee,
            'categories' => $categories,
            'roles' => $roles,
            'permissions' => $permissions,
            'employeeRole' => $employeeRole,
            'employeePermissions' => $employeePermissions,
        ]);
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, User $employee)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:15',
                'can_login' => 'required|boolean',
                'category_id' => 'required|exists:categories,id',
                'password' => 'nullable|string|min:8|confirmed', // Password is optional
                'role_id' => 'required|exists:roles,id',
                'selected_permissions' => 'array',
                'selected_permissions.*' => 'exists:permissions,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e->errors()); // Display validation errors
        }

        // Update password only if provided
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']); // Avoid overwriting the password
        }

        \DB::transaction(function () use ($employee, $validated) {
            // Update the employee
            $employee->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'can_login' => $validated['can_login'],
                'category_id' => $validated['category_id'],
            ]);

            // Sync the role
            $employee->syncRoles([$validated['role_id']]);

            // Sync permissions
            if (isset($validated['selected_permissions'])) {
                $employee->syncPermissions($validated['selected_permissions']);
            }
        });

        // Log the activity
        log_activity('Update Employee', 'Updated employee: ' . $employee->name);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(User $employee)
    {
        // Log the activity before deleting
        log_activity('Delete Employee', 'Deleted employee: ' . $employee->name);

        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}

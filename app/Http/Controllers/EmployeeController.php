<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $employees = User::with(['category', 'roles'])
            ->whereNotIn('employee_id', ['ZVL/2025/HR/SS/001', 'ZVL/2025/HR/SS/002'])
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
        $permissions = Permission::all();

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
        // Validation errors must surface as a normal form error. The old code
        // swallowed the ValidationException in an empty catch and then crashed
        // with "Undefined variable $validated" (HTTP 500).
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

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'category_id' => $validated['category_id'],
                'password' => bcrypt($validated['password']),
                'can_login' => $validated['can_login'],
                'employee_id' => User::generateUserCode(),
            ]);

            $user->assignRole(Role::findById($validated['role_id']));
            $user->syncPermissions($validated['selected_permissions'] ?? []);

            return $user;
        });

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
     * The employee detail page is the edit form.
     */
    public function show(User $employee)
    {
        return redirect()->route('employees.edit', $employee);
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, User $employee)
    {
        // Errors must redirect back with messages, never dd() the request.
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

        DB::transaction(function () use ($employee, $validated) {
            $attributes = [
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'can_login' => $validated['can_login'],
                'category_id' => $validated['category_id'],
            ];

            // Update password only if provided
            if (! empty($validated['password'])) {
                $attributes['password'] = bcrypt($validated['password']);
            }

            $employee->update($attributes);

            $employee->syncRoles(Role::findById($validated['role_id']));

            if (isset($validated['selected_permissions'])) {
                $employee->syncPermissions($validated['selected_permissions']);
            }
        });

        log_activity('Update Employee', 'Updated employee: ' . $employee->name);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee from storage.
     *
     * Employees are soft-deleted: a hard delete cascaded into their payroll and
     * activity-log history, silently rewriting financial history. Deactivating
     * "can login" remains the way to revoke access immediately.
     */
    public function destroy(User $employee)
    {
        if ($employee->is(auth()->user())) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        if ($employee->hasRole('Super Admin') && User::role('Super Admin')->count() <= 1) {
            return back()->withErrors(['error' => 'The last Super Admin cannot be deleted.']);
        }

        $employeeName = $employee->name;
        $employee->delete();

        log_activity('Delete Employee', 'Deleted employee: ' . $employeeName);

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}

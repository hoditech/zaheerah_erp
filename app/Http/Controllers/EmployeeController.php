<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees.
     */
    public function index()
    {
        $employees = User::with('category')->get();

        return Inertia::render('HR/Index', [
            'employees' => $employees,
            'categories' => Category::all(),
        ]);
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $categories = Category::all();
        $roles = Role::all();


        return Inertia::render('HR/Create', [
            'categories' => $categories,
            'roles' => $roles,

        ]);
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'nullable|string|max:15',
            'can_login' => 'required|boolean',
            'category_id' => 'required|exists:categories,id',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['password'] = bcrypt($validated['password']);

       
        $role = Role::findByName('Manager');


        $user = User::create($validated);
        $user->assignRole($role);

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

        return Inertia::render('HR/Edit', [
            'employee' => $employee,
            'categories' => $categories,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
            'can_login' => 'required|boolean',
            'category_id' => 'required|exists:categories,id',
            'password' => 'required|string|min:8|confirmed',

        ]);

        $validated['password'] = bcrypt($validated['password']);

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(User $employee)
    {
        $employee->delete();

        return back()->with('success', 'Employee deleted successfully.');
    }
}

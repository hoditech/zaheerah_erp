<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Display a listing of payrolls.
     */
    public function index()
    {
        $payrolls = Payroll::with('user')->latest()->get();

        return response()->json([
            'payrolls' => $payrolls,
        ]);
    }

    /**
     * Store a new payroll entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'base_salary' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:bank_transfer,cash,mobile_money',
            'payment_status' => 'required|in:pending,paid',
            'payment_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'working_days' => 'nullable|numeric|min:0',
            'bank' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'transport' => 'nullable|numeric|min:0',
            'house_rent' => 'nullable|numeric|min:0',
            'medicals' => 'nullable|numeric|min:0',
            'others' => 'nullable|numeric|min:0',
            'income_tax' => 'nullable|numeric|min:0',
            'cra' => 'nullable|numeric|min:0',
            'pension' => 'nullable|numeric|min:0',
            'nhf' => 'nullable|numeric|min:0',
            'nhis' => 'nullable|numeric|min:0',
            'salary_advance' => 'nullable|numeric|min:0',
        ]);
    
        // Set default 0 for numeric fields
        $numericFields = [
            'base_salary', 'overtime_pay', 'bonuses', 'deductions', 'tax', 'commission',
            'transport', 'house_rent', 'medicals', 'others', 'income_tax', 'cra',
            'pension', 'nhf', 'nhis', 'salary_advance'
        ];
    
        foreach ($numericFields as $field) {
            $validated[$field] = $validated[$field] ?? 0;
        }
    
        $payroll = Payroll::create($validated);
    
        log_activity('Create Payroll', 'Created payroll for employee: ' . $payroll->user->name);
    
        return response()->json([
            'message' => 'Payroll record added successfully',
            'data' => $payroll
        ]);
    }

    /**
     * Update a payroll entry.
     */
    public function update(Request $request, Payroll $payroll)
{
    $validated = $request->validate([
        'base_salary' => 'nullable|numeric|min:0',
        'overtime_pay' => 'nullable|numeric|min:0',
        'bonuses' => 'nullable|numeric|min:0',
        'deductions' => 'nullable|numeric|min:0',
        'tax' => 'nullable|numeric|min:0',
        'commission' => 'nullable|numeric|min:0',
        'payment_method' => 'required|in:bank_transfer,cash,mobile_money',
        'payment_status' => 'required|in:pending,paid',
        'payment_date' => 'nullable|date',
        'location' => 'nullable|string|max:255',
        'designation' => 'nullable|string|max:255',
        'working_days' => 'nullable|numeric|min:0',
        'bank' => 'nullable|string|max:255',
        'account_number' => 'nullable|string|max:255',
        'transport' => 'nullable|numeric|min:0',
        'house_rent' => 'nullable|numeric|min:0',
        'medicals' => 'nullable|numeric|min:0',
        'others' => 'nullable|numeric|min:0',
        'income_tax' => 'nullable|numeric|min:0',
        'cra' => 'nullable|numeric|min:0',
        'pension' => 'nullable|numeric|min:0',
        'nhf' => 'nullable|numeric|min:0',
        'nhis' => 'nullable|numeric|min:0',
        'salary_advance' => 'nullable|numeric|min:0',
    ]);

    // Update the model - boot() will handle calculations
    $payroll->update($validated);

    log_activity('Update Payroll', 'Updated payroll for employee: ' . $payroll->user->name);

    return response()->json([
        'message' => 'Payroll updated successfully',
        'data' => $payroll
    ]);
}

    /**
     * Delete a payroll entry.
     */
    public function destroy(Payroll $payroll)
    {
        $employee = $payroll->user;

        // Log the activity
        log_activity('Delete Payroll', 'Deleted payroll for employee: ' . $employee->name);

        $payroll->delete();

        return response()->json([
            'message' => 'Payroll deleted successfully.',
        ]);
    }
}
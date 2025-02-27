<?php

namespace Database\Seeders;

use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Fetch all users
         $users = User::all();
        
         if ($users->isEmpty()) {
             $this->command->warn("No users found! Please seed users first.");
             return;
         }
 
         foreach ($users as $user) {
             Payroll::create([
                 'user_id' => $user->id,
                 'base_salary' => 50000,
                 'overtime_pay' => 5000,
                 'bonuses' => 2000,
                 'deductions' => 3000,
                 'tax' => 4000,
                 'commission' => 1500,
                 'payment_method' => 'bank_transfer',
                 'payment_status' => 'paid',
                 'payment_date' => Carbon::now()->subDays(5),
                 'location' => 'Abuja',
                 'designation' => 'Software Engineer',
                 'working_days' => 22,
                 'bank' => 'Zenith Bank',
                 'account_number' => '1234567890',
                 'transport' => 3000,
                 'house_rent' => 10000,
                 'medicals' => 2500,
                 'others' => 1500,
                 'income_tax' => 5000,
                 'cra' => 1000,
                 'pension' => 2500,
                 'nhf' => 1200,
                 'nhis' => 800,
                 'salary_advance' => 5000,
             ]);
         }
    }
}

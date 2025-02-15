export interface Payroll {
    id?: number;
    user_id: string;
    base_salary: number;
    overtime_pay: number;
    bonuses: number;
    deductions: number;
    tax: number;
    commission: number;
    gross_salary: number;
    total_deduction: number;
    net_salary: number;
    payment_method: string;
    payment_status: 'paid' | 'pending';
    payment_date: string;
    location: string;
    designation: string;
    working_days: number;
    bank: string;
    account_number: string;
    transport: number;
    house_rent: number;
    medicals: number;
    others: number;
    income_tax: number;
    cra: number;
    pension: number;
    nhf: number;
    nhis: number;
    salary_advance: number;
}

export interface PayrollWithUser extends Payroll {
    user: {
        name: string;
    };
}
import axios from 'axios';

export const fetchUsers = async () => {
    try {
        const response = await axios.get('/users');
        return response.data.users;
    } catch (error) {
        console.error('Error fetching users:', error);
        return [];
    }
};

export const fetchPayrols = async () => {
    try {
        const response = await axios.get('/payrolls');
        return response.data.payrolls;
    } catch (error) {
        console.error('Error fetching payrolls:', error);
        return [];
    }
};

export const submitPayroll = async (payrollData: object) => {
    try {
        console.log('Submitting payroll data:', payrollData); // Debug request data
        const response = await axios.post('/payrolls', payrollData);
        return response.data;
    } catch (error: any) {
        if (error.response) {
            console.error('Validation Errors:', error.response.data); // Log server response
        } else {
            console.error('Error submitting payroll:', error);
        }
        throw error;
    }
};
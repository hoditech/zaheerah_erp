<template>
    <AuthenticatedLayout>
        <div class="bg-white dark:bg-gray-800 min-h-screen p-6">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-6">ERP Settings</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- User Management Section -->
                <section class="bg-gray-100 dark:bg-gray-700 rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">User Management</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1">Add User</label>
                            <input type="text" v-model="newUser" placeholder="Enter user email" class="w-full p-3 border border-gray-300 rounded-lg dark:bg-gray-600 dark:border-gray-500 dark:text-gray-200" />
                            <button @click="addUser" class="mt-2 w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition">Add User</button>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 dark:text-gray-300">Existing Users:</h4>
                            <ul>
                                <li v-for="user in users" :key="user.id" class="flex justify-between items-center border-b border-gray-300 dark:border-gray-500 py-2">
                                    <span>{{ user.email }}</span>
                                    <button @click="removeUser(user.id)" class="text-red-600 hover:text-red-700">Remove</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Module Settings Section -->
                <section class="bg-gray-100 dark:bg-gray-700 rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Module Settings</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" v-model="modules.sales" class="mr-2" />
                            <label class="text-gray-700 dark:text-gray-300">Sales Module</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" v-model="modules.inventory" class="mr-2" />
                            <label class="text-gray-700 dark:text-gray-300">Inventory Module</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" v-model="modules.crm" class="mr-2" />
                            <label class="text-gray-700 dark:text-gray-300">CRM Module</label>
                        </div>
                        <button @click="saveModules" class="mt-4 w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition">Save Module Settings</button>
                    </div>
                </section>

                <!-- Permission Management Section -->
                <section class="bg-gray-100 dark:bg-gray-700 rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Permission Management</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1">Select User</label>
                            <select v-model="selectedUser" class="w-full p-3 border border-gray-300 rounded-lg dark:bg-gray-600 dark:border-gray-500 dark:text-gray-200">
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.email }}</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" v-model="userPermissions.view" class="mr-2" />
                            <label class="text-gray-700 dark:text-gray-300">View Permissions</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" v-model="userPermissions.edit" class="mr-2" />
                            <label class="text-gray-700 dark:text-gray-300">Edit Permissions</label>
                        </div>
                        <button @click="savePermissions" class="mt-4 w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition">Save Permissions</button>
                    </div>
                </section>

                <!-- System Preferences Section -->
                <section class="bg-gray-100 dark:bg-gray-700 rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">System Preferences</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1">Currency</label>
                            <select v-model="systemSettings.currency" class="w-full p-3 border border-gray-300 rounded-lg dark:bg-gray-600 dark:border-gray-500 dark:text-gray-200">
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1">Default Language</label>
                            <select v-model="systemSettings.language" class="w-full p-3 border border-gray-300 rounded-lg dark:bg-gray-600 dark:border-gray-500 dark:text-gray-200">
                                <option value="en">English</option>
                                <option value="fr">French</option>
                                <option value="es">Spanish</option>
                            </select>
                        </div>
                        <button @click="saveSystemSettings" class="mt-4 w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition">Save System Preferences</button>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Ensure the path is correct

// State for user management
const newUser = ref('');
const users = ref([{ id: 1, email: 'user1@example.com' }, { id: 2, email: 'user2@example.com' }]); // Sample users

// State for module settings
const modules = ref({
    sales: false,
    inventory: false,
    crm: false
});

// State for user permissions
const selectedUser = ref(users.value[0]?.id);
const userPermissions = ref({
    view: false,
    edit: false
});

// State for system preferences
const systemSettings = ref({
    currency: 'USD',
    language: 'en'
});

// Functions for managing users
const addUser = () => {
    if (newUser.value) {
        users.value.push({ id: Date.now(), email: newUser.value });
        newUser.value = '';
    }
};

const removeUser = (id: number) => {
    users.value = users.value.filter(user => user.id !== id);
};

// Functions for managing modules
const saveModules = () => {
    // Logic to save module settings
    alert('Module settings saved!');
};

// Functions for managing permissions
const savePermissions = () => {
    // Logic to save permissions for the selected user
    alert(`Permissions for user ${selectedUser.value} saved!`);
};

// Functions for managing system preferences
const saveSystemSettings = () => {
    // Logic to save system preferences
    alert('System preferences saved!');
};
</script>

<style scoped>
/* Add any custom styles for hover effects or additional styling as needed */
</style>

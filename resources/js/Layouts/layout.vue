<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const sidebarOpen = ref(true); // Open by default on desktop
const isMobile = ref(window.innerWidth < 768);

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const updateIsMobile = () => {
    isMobile.value = window.innerWidth < 768;
};

onMounted(() => {
    window.addEventListener('resize', updateIsMobile);
});
onUnmounted(() => {
    window.removeEventListener('resize', updateIsMobile);
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-screen bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 relative">
        
        <!-- Sidebar -->
        <aside 
        :class="{
            'fixed inset-0 z-30 w-64': sidebarOpen && isMobile,
            'hidden md:flex': !sidebarOpen && isMobile,
            'w-64': sidebarOpen && !isMobile,
            'w-20': !sidebarOpen && !isMobile,
            'flex flex-col h-full bg-white shadow-lg dark:bg-gray-800 transition-all duration-200 md:relative': true
        }"
    >
        <!-- Sidebar Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 v-if="sidebarOpen || isMobile" class="text-lg font-bold text-gray-700 dark:text-gray-300">My Dashboard</h2>
            <button @click="toggleSidebar" class="p-2">
                <span v-if="isMobile">X</span>
                <span v-else-if="sidebarOpen">➖</span>
                <span v-else>➕</span>
            </button>
        </div>
        
        <!-- Navigation Links -->
        <nav class="flex-grow">
            <ul>
                <li :class="{'p-4 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center': true, 'justify-center': !sidebarOpen, 'space-x-2': sidebarOpen}">
                    <span>🏠</span>
                    <span v-if="sidebarOpen || isMobile">Dashboard</span>
                </li>
                <li :class="{'p-4 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center': true, 'justify-center': !sidebarOpen, 'space-x-2': sidebarOpen}">
                    <span>📁</span>
                    <span v-if="sidebarOpen || isMobile">Projects</span>
                </li>
                <li :class="{'p-4 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center': true, 'justify-center': !sidebarOpen, 'space-x-2': sidebarOpen}">
                    <span>📊</span>
                    <span v-if="sidebarOpen || isMobile">Reports</span>
                </li>
                <li :class="{'p-4 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center': true, 'justify-center': !sidebarOpen, 'space-x-2': sidebarOpen}">
                    <span>⚙️</span>
                    <span v-if="sidebarOpen || isMobile">Settings</span>
                </li>
            </ul>
        </nav>
        
        <!-- Logout Button with Icon-Only in Collapsed Mode -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            <button class="w-full py-2 text-sm font-semibold text-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-lg flex items-center justify-center">
                <span>🔓</span> <!-- Logout Icon -->
                <span v-if="sidebarOpen || isMobile">Log Out</span> <!-- Text shown only when expanded -->
            </button>
        </div>
    </aside>

        <!-- Overlay for Mobile Sidebar -->
        <div v-if="sidebarOpen && isMobile" @click="toggleSidebar" class="fixed inset-0 bg-black opacity-50 z-20"></div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <header class="flex items-center justify-between px-4 py-4 md:px-6 bg-white shadow-md dark:bg-gray-800">
                <button @click="toggleSidebar" class="p-2 md:hidden">
                    <span>≡</span>
                </button>
                <h1 class="text-xl md:text-2xl font-semibold leading-tight">Dashboard</h1>
                <div class="flex items-center space-x-4">
                    <input 
                        type="text" 
                        placeholder="Search..." 
                        class="hidden sm:block px-4 py-2 text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 rounded-lg"
                    />
                    <button class="flex items-center p-2 bg-gray-100 dark:bg-gray-700 rounded-full">
                        <img src="/path/to/profile.jpg" class="w-8 h-8 rounded-full" alt="User Profile" />
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50 dark:bg-gray-900">
                <div class="p-4 md:p-6 bg-white shadow-lg dark:bg-gray-800 rounded-lg">
                    <slot /> <!-- Content for each page -->
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.transition-width {
    transition-property: width;
    transition-duration: 0.2s;
}
</style>

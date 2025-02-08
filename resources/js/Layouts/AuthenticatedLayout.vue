<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

import { Link } from '@inertiajs/vue3';
// @ts-ignore
import { is } from 'laravel-permission-to-vuejs'

const showingNavigationDropdown = ref(false);

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
    <!-- <Head title="Dashboard" /> -->

    <div class="flex h-screen bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 relative">

        <!-- Sidebar -->
        <aside :class="{
            'fixed inset-0 z-30 w-64': sidebarOpen && isMobile,
            'hidden md:flex': !sidebarOpen && isMobile,
            'w-44': sidebarOpen && !isMobile,
            'w-20': !sidebarOpen && !isMobile,
            'flex flex-col h-full bg-white shadow-lg dark:bg-gray-800 transition-all duration-200 md:relative': true
        }">
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <h2 v-if="sidebarOpen || isMobile" class="text-lg font-bold text-green-700 dark:text-gray-300">
                    <img src="/images/logo2.png" alt="Description" class="inline-block w-18 h-10">
                </h2>

                <button @click="toggleSidebar" class="p-2">
                    <span v-if="isMobile">X</span>
                    <span v-else-if="sidebarOpen">➖</span>
                    <span v-else>➕</span>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-grow">
                <ul>
                    <li :class="{
                        'p-4 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center': true,
                        'justify-center': !sidebarOpen,
                        'space-x-2': sidebarOpen,
                        'active-link': route().current('dashboard')
                    }">
                        <Link :href="route('dashboard')" :class="{
                            'flex items-center w-full': true,
                            'justify-center': !sidebarOpen,
                            'space-x-2': sidebarOpen
                        }">
                        <span>🏠</span>
                        <span v-if="sidebarOpen || isMobile">Home</span>
                        </Link>
                    </li>

                    <!-- eslint-disable-next-line -->
                    <li v-if="is('Manager | Employee | Super Admin')" :class="{
                        'p-4 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center': true,
                        'justify-center': !sidebarOpen,
                        'space-x-2': sidebarOpen,
                        'active-link': route().current('pos')
                    }">
                        <Link :href="route('pos')" :class="{
                            'flex items-center w-full': true,
                            'justify-center': !sidebarOpen,
                            'space-x-2': sidebarOpen
                        }">
                        <span>💳</span>
                        <span v-if="sidebarOpen || isMobile">POS</span>
                        </Link>
                    </li>

                    <li v-if="is('HR | Super Admin | Admin')" :class="{
                        'p-4 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center': true,
                        'justify-center': !sidebarOpen,
                        'space-x-2': sidebarOpen,
                        'active-link': route().current('employees.index')
                    }">
                        <Link :href="route('employees.index')" class="flex items-center space-x-2">
                        <span>👨‍💼</span> <!-- Product Icon -->
                        <span v-if="sidebarOpen || isMobile">HR</span>
                        </Link>
                    </li>
                    <li v-if="is('Manager  | Super Admin|Admin')" :class="{
                        'p-4 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center': true,
                        'justify-center': !sidebarOpen,
                        'space-x-2': sidebarOpen,
                        'active-link': route().current('products')
                    }">
                        <Link :href="route('products')" class="flex items-center space-x-2">
                        <span>📦</span> <!-- Product Icon -->
                        <span v-if="sidebarOpen || isMobile">Inventory</span>
                        </Link>
                    </li>
                    <li v-if="is('Manager  | Super Admin|Admin')" :class="{
                        'p-4 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center': true,
                        'justify-center': !sidebarOpen,
                        'space-x-2': sidebarOpen,
                        'active-link': route().current('productions.index')
                    }">
                        <Link :href="route('productions.index')" class="flex items-center space-x-2">
                        <span>🏭</span> <!-- Product Icon -->
                        <span v-if="sidebarOpen || isMobile">Production</span>
                        </Link>
                    </li>
                    <li v-if="is('Technical Manager | Super Admin|Admin')" :class="{
                        'p-4 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center': true,
                        'justify-center': !sidebarOpen,
                        'space-x-2': sidebarOpen,
                        'active-link': route().current('suppliers.index')
                    }">
                        <Link :href="route('suppliers.index')" class="flex items-center space-x-2">
                        <span>🚚</span> <!-- Product Icon -->
                        <span v-if="sidebarOpen || isMobile">Suppliers</span>
                        </Link>
                    </li>
                    <li v-if="is('Manager | Super Admin|Admin')" :class="{
                        'p-4 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center': true,
                        'justify-center': !sidebarOpen,
                        'space-x-2': sidebarOpen,
                        'active-link': route().current('purchase-orders.index')
                    }">
                        <Link :href="route('purchase-orders.index')" class="flex items-center space-x-2">
                        <span> 🛒</span> <!-- Product Icon -->
                        <span v-if="sidebarOpen || isMobile">Purchase Order</span>
                        </Link>
                    </li>
                    <li v-if="is('Super Admin| Admin')" :class="{
                        'p-4 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center': true,
                        'justify-center': !sidebarOpen,
                        'space-x-2': sidebarOpen,
                        'active-link': route().current('finance.index')
                    }">
                        <Link :href="route('finance.index')" class="flex items-center space-x-2">
                        <span> 🏦</span> <!-- Product Icon -->
                        <span v-if="sidebarOpen || isMobile">Finance</span>
                        </Link>
                    </li>
                    <!-- <li v-if="is('Technical Manager | Super Admin')"
                    :class="{
                        'p-4 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center': true,
                        'justify-center': !sidebarOpen,
                        'space-x-2': sidebarOpen,
                        'active-link': route().current('users')
                    }">
                        <Link :href="route('users')" class="flex items-center space-x-2">
                        <span>👤</span>  Users Icon
                        <span v-if="sidebarOpen || isMobile">Users</span>
                        </Link>
                    </li> -->


                </ul>
            </nav>

            <!-- Logout Button with Icon-Only in Collapsed Mode -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <Link :href="route('logout')" method="post" as="button"
                    class="w-full py-2 text-sm font-semibold text-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-lg flex items-center justify-center">
                <span>🔓</span> <!-- Logout Icon -->
                <span v-if="sidebarOpen || isMobile">Log Out</span> <!-- Text shown only when expanded -->
                </Link>
            </div>

        </aside>

        <!-- Overlay for Mobile Sidebar -->
        <div v-if="sidebarOpen && isMobile" @click="toggleSidebar" class="fixed inset-0 bg-black opacity-50 z-20"></div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <header class="flex items-center justify-between px-4 py-4 md:px-6 bg-white shadow-md dark:bg-gray-800"
                v-if="$slots.header">
                <button @click="toggleSidebar" class="p-2 md:hidden">
                    <span>≡</span>
                </button>
                <h1 class="text-xl md:text-2xl font-semibold leading-tight">
                    <slot name="header"></slot>
                </h1>
                <div class="flex items-center space-x-4">
                    <Link :href="route('profile.edit')"
                        class="flex items-center p-2 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200">
                    <img src="/images/avata.png" class="w-8 h-8 rounded-full" alt="User Profile" />
                    <span class="ml-2 text-gray-800 dark:text-gray-200 font-semibold text-sm md:text-base">
                        {{ $page.props.auth.user.name }}
                    </span>
                    </Link>

                </div>
            </header>


            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-100 dark:bg-gray-900">
                <div class="p-4 md:p-2 g-white dark:bg-gray-800 rounded-lg">
                    <slot />
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

.active-link {
    background-color: #f3f4f6;
    transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
}

.active-link .flex {
    transition: transform 0.3s ease;
}

.active-link:hover .flex {
    transform: translateX(5px);
    /* Small slide effect on hover */
}

.transition-width {
    transition-property: width;
    transition-duration: 0.2s;
}
</style>
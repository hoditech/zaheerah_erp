<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    laravelVersion: string;
    phpVersion: string;
}>();

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}

const socialLinks = [
    { icon: 'fab fa-facebook-f', url: '#' },
    { icon: 'fab fa-twitter', url: '#' },
    { icon: 'fab fa-linkedin-in', url: '#' },
    { icon: 'fab fa-instagram', url: '#' },
];

</script>

<template>

    <Head title="Welcome" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">

        <div
            class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">

                <main class="mt-6">
                    <div class="text-center px-6   p-8 rounded-lg">
                        <!-- Added background and padding -->
                        <!-- User Icon -->
                        <div class="flex justify-center items-center py-8">
                            <img src="/images/logo.png" height="100" width="100" alt="Logo">
                        </div>
                        <h1 class="text-5xl font-bold text-green-600 mb-2">Welcome to the Employee Portal</h1>
                        <p class="text-lg text-gray-700 mb-4 max-w-lg mx-auto">Your gateway to success and
                            collaboration.</p>
                        <!-- Added Tagline -->
                        <a :href="route('login')"
                            class="inline-block px-8 py-3 bg-green-600 text-white font-semibold rounded-md shadow-md transition-transform duration-300 transform hover:bg-green-700 hover:scale-105">
                            Login to Your Account
                        </a>
                    </div>
                </main>

                <footer class="text-white text-center p-4 mt-8">
                    <h2 class="text-lg mb-2 text-gray-600">Follow Us</h2>
                    <div class="flex justify-center space-x-4">
                        <template v-for="(link, index) in socialLinks" :key="index">
                            <a :href="link.url" class="text-gray-400 hover:text-green-700">
                                <i :class="link.icon"></i>
                            </a>
                        </template>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</template>

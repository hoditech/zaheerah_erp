<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const categories = ["All", "Drinks", "Snacks", "Bakery", "Desserts"];
const products = [
    { id: 1, name: "Americano", price: 2.5, description: "Hot coffee made with espresso", image: "/images/feature-two-img-1.jpg" },
    { id: 2, name: "Bagel", price: 1.0, description: "Freshly baked bagel", image: "/images/gallery-1-4.jpg" },
    { id: 3, name: "Croissant", price: 1.5, description: "Buttery and flaky croissant", image: "/images/lemon.jpg" },
    { id: 4, name: "Muffin", price: 2.0, description: "greenberry muffin", image: "/images/flat-lay-raw-beans-sortiment_23-2148660397.jpg" },
    // Add more products as needed
];
const cartItems = ref([]);
const selectedCategory = ref("All");
const searchQuery = ref("");

const addToCart = (product) => {
    const existingItem = cartItems.value.find((item) => item.id === product.id);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cartItems.value.push({ ...product, quantity: 1 });
    }
};

const removeFromCart = (productId) => {
    cartItems.value = cartItems.value.filter((item) => item.id !== productId);
};

const updateQuantity = (productId, quantity) => {
    const item = cartItems.value.find((item) => item.id === productId);
    if (item) item.quantity = quantity > 0 ? quantity : 1;
};

const calculateTotal = () => {
    return cartItems.value.reduce((total, item) => total + item.price * item.quantity, 0).toFixed(2);
};

const filteredProducts = () => {
    return products.filter(product => {
        return (selectedCategory.value === "All" || product.category === selectedCategory.value) &&
               product.name.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
};
</script>

<template>
    <Head title="POS" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-200">Point of Sale</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl ">
                
                <!-- POS Layout -->
                <div class="flex flex-col lg:flex-row bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 rounded-lg shadow-lg overflow-hidden">
                    
                    <!-- Sidebar: Categories and Search -->
                    <!-- <aside class="w-full lg:w-1/6 bg-white dark:bg-gray-800 p-4 lg:p-6 border-b lg:border-r lg:border-b-0 border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Categories</h3>
                        <ul class="space-y-3 mb-4">
                            <li v-for="category in categories" :key="category" @click="selectedCategory.value = category" :class="{'bg-green-500 text-white': selectedCategory.value === category}"
                                class="px-4 py-2 rounded-lg hover:bg-green-500 hover:text-white cursor-pointer text-center transition">
                                {{ category }}
                            </li>
                        </ul>
                        <input v-model="searchQuery" type="text" placeholder="Search products..." 
                               class="w-full p-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </aside> -->

                    <!-- Main Content: Products & Cart -->
                    <main class="flex-1 flex flex-col lg:flex-row lg:space-x-8 p-4 lg:p-8">
                        <!-- Product Grid -->
                        <section class="w-full lg:w-2/3 mb-8 lg:mb-0">
                            <h3 class="text-lg font-semibold mb-4 lg:mb-6 text-gray-700 dark:text-gray-300">Products</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 lg:gap-6">
                                <div v-for="product in filteredProducts()" :key="product.id"
                                     class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 transition hover:shadow-lg">
                                    <div class="h-32 sm:h-40 bg-gray-200 dark:bg-gray-700 rounded-md mb-3 flex items-center justify-center overflow-hidden">
                                        <!-- Placeholder or actual product image -->
                                        <img :src="product.image" alt="Product Image" class="object-cover h-full w-full" />
                                    </div>
                                    <h4 class="text-sm sm:text-lg font-semibold text-gray-700 dark:text-gray-300">{{ product.name }}</h4>
                                    <!-- <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">{{ product.description }}</p> -->
                                    <p class="text-sm sm:text-md font-bold mt-2 text-gray-800 dark:text-gray-100">${{ product.price.toFixed(2) }}</p>
                                    <button @click="addToCart(product)" class="mt-3 w-full py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition">
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                        </section>

                        <!-- Cart Section -->
                        <section class="w-full lg:w-1/3 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 lg:p-6">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Cart</h3>
                            
                            <!-- Cart Items -->
                            <ul class="mt-4 lg:mt-6 space-y-4">
                                <li v-for="item in cartItems" :key="item.id" class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ item.name }}</h4>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <input type="number" v-model.number="item.quantity" @change="updateQuantity(item.id, item.quantity)"
                                                   class="w-12 sm:w-16 p-1 border border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600 text-center focus:outline-none" min="1" />
                                            <p class="text-xs text-gray-500 dark:text-gray-400">x ${{ item.price.toFixed(2) }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <p class="font-semibold text-gray-800 dark:text-gray-100">${{ (item.price * item.quantity).toFixed(2) }}</p>
                                        <button @click="removeFromCart(item.id)" class="text-red-500 hover:text-red-600 text-sm font-bold">✕</button>
                                    </div>
                                </li>
                            </ul>

                            <!-- Cart Summary and Actions -->
                            <div class="mt-6 lg:mt-8 pt-4 border-t border-gray-200 dark:border-gray-700 space-y-4">
                                <div class="flex justify-between text-md sm:text-lg font-semibold text-gray-700 dark:text-gray-300">
                                    <span>Total:</span>
                                    <span>${{ calculateTotal() }}</span>
                                </div>
                                <button class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-md transition">Checkout</button>
                                <button @click="cartItems.value = []" class="w-full py-3 bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold rounded-lg shadow-md transition dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-gray-200">
                                    Clear Cart
                                </button>
                            </div>
                        </section>
                    </main>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

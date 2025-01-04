<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import ProductModal from './ProductModal.vue'; // Import the modal component


interface Product {
    id: number;
    name: string;
    price: number;
    description: string;
    image: string;
    category: string;
}

interface CartItem extends Product {
    quantity: number;
}


const categories = ["All", "Drinks", "Snacks", "Bakery", "Desserts"];
const products = [
    { id: 1, name: "Americano", price: 2.5, description: "Hot coffee made with espresso", image: "coffee.jpg" },
    { id: 2, name: "Bagel", price: 1.0, description: "Freshly baked bagel", image: "bagel.jpg" },
    { id: 3, name: "Croissant", price: 1.5, description: "Buttery and flaky croissant", image: "croissant.jpg" },
    { id: 4, name: "Muffin", price: 2.0, description: "Blueberry muffin", image: "muffin.jpg" },
    { id: 1, name: "Americano", price: 2.5, description: "Hot coffee made with espresso", image: "coffee.jpg" },
    { id: 2, name: "Bagel", price: 1.0, description: "Freshly baked bagel", image: "bagel.jpg" },
    { id: 3, name: "Croissant", price: 1.5, description: "Buttery and flaky croissant", image: "croissant.jpg" },
    { id: 4, name: "Muffin", price: 2.0, description: "Blueberry muffin", image: "muffin.jpg" },
    // Add more products as needed
];

const cartItems = ref<CartItem[]>([]);
const selectedCategory = ref("All");
const searchQuery = ref("");
const showModal = ref(false);
const selectedProduct = ref(null);

const addToCart = (product: Product) => {
    const existingItem = cartItems.value.find((item) => item.id === product.id);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cartItems.value.push({ ...product, quantity: 1 });
    }
    closeModal();
};

const removeFromCart = (productId: number) => {
    cartItems.value = cartItems.value.filter((item) => item.id !== productId);
};

const updateQuantity = (productId: number, quantity: number) => {
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

// Show the modal with selected product details
const openModal = (product: Product) => {
    selectedProduct.value = product;
    showModal.value = true;
};

// Close the modal
const closeModal = () => {
    showModal.value = false;
};
</script>

<template>
    <Head title="POS" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-200">Point of Sale</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- POS Layout -->
                <div class="flex flex-col lg:flex-row bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 rounded-lg shadow-lg overflow-hidden">
                    
                    <!-- Sidebar: Categories and Search -->
                    <aside class="w-full lg:w-1/5 bg-white dark:bg-gray-800 p-4 lg:p-6 border-b lg:border-r lg:border-b-0 border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Categories</h3>
                        <ul class="space-y-3 mb-4">
                            <li v-for="category in categories" :key="category" @click="selectedCategory.value = category" :class="{'bg-blue-500 text-white': selectedCategory.value === category}"
                                class="px-4 py-2 rounded-lg hover:bg-blue-500 hover:text-white cursor-pointer text-center transition">
                                {{ category }}
                            </li>
                        </ul>
                        <input v-model="searchQuery" type="text" placeholder="Search products..." 
                               class="w-full p-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </aside>

                    <!-- Main Content: Products & Cart -->
                    <main class="flex-1 flex flex-col lg:flex-row lg:space-x-8 p-4 lg:p-8">
                        
                        <!-- Product Grid -->
                        <section class="w-full lg:w-2/3 mb-8 lg:mb-0">
                            <h3 class="text-lg font-semibold mb-4 lg:mb-6 text-gray-700 dark:text-gray-300">Products</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 lg:gap-6">
                                <div v-for="product in filteredProducts()" :key="product.id"
                                     class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 transition hover:shadow-lg cursor-pointer" @click="openModal(product)">
                                    <div class="h-32 sm:h-40 bg-gray-200 dark:bg-gray-700 rounded-md mb-3 flex items-center justify-center overflow-hidden">
                                        <img :src="product.image" alt="Product Image" class="object-cover h-full w-full" />
                                    </div>
                                    <h4 class="text-sm sm:text-lg font-semibold text-gray-700 dark:text-gray-300">{{ product.name }}</h4>
                                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">{{ product.description }}</p>
                                    <p class="text-sm sm:text-md font-bold mt-2 text-gray-800 dark:text-gray-100">${{ product.price.toFixed(2) }}</p>
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
                                                   class="w-12 sm:w-16 p-1 border border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300" />
                                            <button @click="removeFromCart(item.id)" class="text-red-600 hover:text-red-700">Remove</button>
                                        </div>
                                    </div>
                                    <p class="font-bold text-gray-800 dark:text-gray-100">${{ (item.price * item.quantity).toFixed(2) }}</p>
                                </li>
                            </ul>
                            <div class="flex justify-between mt-4">
                                <span class="font-bold">Total:</span>
                                <span class="font-bold text-lg">${{ calculateTotal() }}</span>
                            </div>
                        </section>
                    </main>
                </div>
            </div>
        </div>

        <!-- Product Modal -->
        <ProductModal :product="selectedProduct" :isVisible="showModal" @close="closeModal" @add-to-cart="addToCart" />
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3'
import axios from 'axios';

// Define Product interface
interface Product {
    id: number;
    name: string;
    price: number;
    image: string;
    category: {
        id: number;
        name: string;
    };
}

interface CartItem extends Product {
    quantity: number;
}

interface ReceiptItem {
    product_id: number;
    name: string,
    quantity: number;
    price: number;
}

// Accept props from backend
const props = defineProps<{
    products: Product[];
}>();


const cartItems = ref<CartItem[]>([]);
const selectedCategory = ref("All");
const searchQuery = ref("");
const receiptTotal = ref(0);

const showReceipt = ref(false);
const receiptItems =  ref<ReceiptItem[]>([]);

// Create category filter options dynamically
const categories = ["All", ...new Set(props.products.map(product => product.category.name))];

const addToCart = (product: Product) => {
    const existingItem = cartItems.value.find((item) => item.id === product.id);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cartItems.value.push({ ...product, quantity: 1 });
    }
};

const removeFromCart = (productId: number) => {
    cartItems.value = cartItems.value.filter((item) => item.id !== productId);
};

const updateQuantity = (productId: number, quantity: number) => {
    const item = cartItems.value.find((item) => item.id === productId);
    if (item) item.quantity = quantity > 0 ? quantity : 1;
};

const calculateTotal = () => {
    return cartItems.value.reduce((total, item) => total + item.price * item.quantity, 0) ;
};

const filteredProducts = () => {
    return props.products.filter(product => {
        return (selectedCategory.value === "All" || product.category.name === selectedCategory.value) &&
               product.name.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
};


const closeReceipt = () => {
    showReceipt.value = false;
};

const printReceipt = () => {
    window.print(); // Simplified for now
};

const checkout = async () => {
    if (cartItems.value.length === 0) {
        alert("Cart is empty!");
        return;
    }

    const saleData = {
        total_amount: calculateTotal(),
        items: cartItems.value.map(item => ({
            product_id: item.id,
            name: item.name,
            quantity: item.quantity,
            price: item.price,
        })),
    };

    try {
        await router.post('/pos/make-sale', saleData);
         // Set receipt data
        receiptItems.value = saleData.items;
        receiptTotal.value = saleData.total_amount;

        // Show the receipt modal
        showReceipt.value = true;

        // Clear the cart
        cartItems.value = [];
        cartItems.value = [];
    } catch (error) {
        console.error(error);
        alert("Failed to complete sale.");
    }
};

const generateReceipt = async () => {
    const saleData = {
        total_amount: calculateTotal(),
        items: cartItems.value.map(item => ({
            name: item.name,
            quantity: item.quantity,
            price: item.price,
        })),
    };

    try {
        const response = await axios.post('/pos/generate-receipt', saleData, {
            responseType: 'blob', // Ensure response is treated as a file
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'receipt.pdf');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link); // Clean up the DOM
    } catch (error) {
        console.error('Error generating receipt:', error);
    }
};


</script>

<template>
    <Head title="POS" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-200">Point of Sale</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- POS Layout -->
                <div class="flex flex-col lg:flex-row bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 rounded-lg shadow-lg overflow-hidden">
                    
                    <!-- Main Content: Products & Cart -->
                    <main class="flex-1 flex flex-col lg:flex-row lg:space-x-8 p-4 lg:p-8">
                        
                        <!-- Product Grid -->
                        <section class="w-full lg:w-2/3 mb-8 lg:mb-0">
                            <!-- Search Bar and Category Filter -->
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Products</h3>
                                <input v-model="searchQuery" type="text" placeholder="Search products..." 
                                       class="w-full sm:w-80 p-3 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500 transition" />
                            </div>
                            <div class="mb-4">
                                <select v-model="selectedCategory" class="w-full sm:w-40 p-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 text-gray-700 dark:text-gray-300">
                                    <option v-for="category in categories" :key="category">{{ category }}</option>
                                </select>
                            </div>

                            <!-- Product Grid Items -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 lg:gap-6">
                                <div v-for="product in filteredProducts()" :key="product.id"
                                     class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 transition hover:shadow-lg">
                                    <div class="h-32 sm:h-40 bg-gray-200 dark:bg-gray-700 rounded-md mb-3 flex items-center justify-center overflow-hidden">
                                        <img :src="product.image" alt="Product Image" class="object-cover h-full w-full" />
                                    </div>
                                    <h4 class="text-sm sm:text-lg font-semibold text-gray-700 dark:text-gray-300">{{ product.name }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ product.category.name }}</p>
                                    <p class="text-sm sm:text-md font-bold mt-2 text-gray-800 dark:text-gray-100">₦{{ product.price  }}</p>
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
                                            <p class="text-xs text-gray-500 dark:text-gray-400">x ₦{{ item.price  }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <p class="font-semibold text-gray-800 dark:text-gray-100">₦{{ (item.price * item.quantity)  }}</p>
                                        <button @click="removeFromCart(item.id)" class="text-red-500 hover:text-red-600 text-sm font-bold">✕</button>
                                    </div>
                                </li>
                            </ul>

                            <!-- Cart Summary and Actions -->
                            <div class="mt-6 lg:mt-8 pt-4 border-t border-gray-200 dark:border-gray-700 space-y-4">
                                <div class="flex justify-between text-md sm:text-lg font-semibold text-gray-700 dark:text-gray-300">
                                    <span>Total:</span>
                                    <span>₦{{ calculateTotal() }}</span>
                                </div>
                                <button @click="checkout"  class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-md transition">Checkout</button>
                                <button @click="cartItems = []" class="w-full py-3 bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold rounded-lg shadow-md transition dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-gray-200">
                                    Clear Cart
                                </button>
                            </div>
                        </section>
                    </main>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Receipt Modal -->
    <div v-if="showReceipt" class="fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Receipt</h3>

            <div class="space-y-2">
                <div v-for="item in receiptItems" :key="item.product_id" class="flex justify-between">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ item.name }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">x{{ item.quantity }}</span>
                    <span class="text-sm text-gray-800 dark:text-gray-100">₦{{ (item.price * item.quantity)  }}</span>
                </div>
            </div>

            <div class="mt-4 border-t border-gray-300 dark:border-gray-700 pt-4">
                <div class="flex justify-between text-lg font-bold">
                    <span>Total:</span>
                    <span>₦{{ receiptTotal }}</span>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button @click="generateReceipt" class="px-4 py-2 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700">
                    Print
                </button>
                <button @click="closeReceipt" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500">
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

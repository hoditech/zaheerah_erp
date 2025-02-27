<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
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

interface ReceiptData {
    receipt_number: string;
    transaction_id: string;
    date: string;
    payment_method: string;
    items: ReceiptItem[];
    total_amount: number;
    store_info: {
        address: string;
        contact: string;
        phone: string;
    };
}

// Accept props from backend
const props = defineProps<{
    products: Product[];
}>();

console.log('Products:', props.products); // Debug the prop value



const cartItems = ref<CartItem[]>([]);
const selectedCategory = ref("All");
const searchQuery = ref("");
const receiptSearch = ref("");

const receiptTotal = ref(0);

const showReceipt = ref(false);
const receiptItems = ref<ReceiptItem[]>([]);
// Initialize receiptData with proper typing
const receiptData = ref<ReceiptData>();
const isLoading = ref(false);
const isSearching = ref(false);

// Create category filter options dynamically
const categories = ["All", ...new Set(props.products.map(product => product.category.name))];

const addToCart = async (product: Product) => {
    try {
        const response = await axios.get(`/products/${product.id}/stock`);
        const availableStock = response.data.stock;



        if (availableStock <= 0) {
            alert("This product is out of stock.");
            return;
        }

        const existingItem = cartItems.value.find((item) => item.id === product.id);
        if (existingItem) {
            if (existingItem.quantity >= availableStock) {
                alert(`Only ${availableStock} units available in stock.`);
                return;
            }
            existingItem.quantity++;
        } else {
            cartItems.value.push({ ...product, quantity: 1 });
        }
    } catch (error) {
        console.error("Failed to fetch stock:", error);
        alert("Failed to check product stock. Please try again.");
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
    return cartItems.value.reduce((total, item) => total + item.price * item.quantity, 0);
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

// const printReceipt = () => {
//     window.print(); // Simplified for now
// };

const checkout = async () => {
    if (cartItems.value.length === 0) {
        alert("Cart is empty!");
        return;
    }

    isLoading.value = true;

    try {
        const response = await axios.post('/pos/make-sale', {
            total_amount: calculateTotal(),
            items: cartItems.value.map(item => ({
                product_id: item.id,
                quantity: item.quantity,
                price: item.price,
            })),
            payment_method: 'cash' // Or get from UI
        });

        if (response.data.success) {
            // Map backend response to frontend state
            receiptItems.value = response.data.receipt_data.items;
            receiptTotal.value = response.data.receipt_data.total_amount;

            receiptData.value = response.data.receipt_data;

            // Add metadata for receipt
            showReceipt.value = true;
            cartItems.value = [];
        }
    } catch (error: any) {
        console.error(error);
        alert(error.response?.data?.message || "Checkout failed");
    } finally {
        isLoading.value = false;
    }
};

const printReceipt = () => {
    // Get the printable content
    const receiptElement = document.getElementById('receipt-printable');

    if (!receiptElement) {
        alert('Receipt content not found');
        return;
    }

    // Clone the element to avoid modifying the original
    const printContent = receiptElement.cloneNode(true) as HTMLElement;

    // Remove action buttons from cloned content
    const buttons = printContent.querySelector('.print-buttons');
    if (buttons) buttons.remove();

    // Create print window
    const printWindow = window.open('', '_blank');
    if (!printWindow) {
        alert('Popup blocked! Please allow popups for printing.');
        return;
    }

    // Build print document
    printWindow.document.write(`
        <html>
            <head>
                <title>Receipt - ${new Date().toLocaleDateString()}</title>
                <script src="https://cdn.tailwindcss.com"><\/script>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    @media print {
                        body { 
                            margin: 20px !important;
                            -webkit-print-color-adjust: exact !important; 
                        }
                        .no-print { display: none !important; }
                    }
                    ${window.getComputedStyle(receiptElement).cssText}
                </style>
            </head>
            <body>
                ${printContent.outerHTML}
                <script>
                    setTimeout(() => window.print(), 500);
                <\/script>
            </body>
        </html>
    `);

    printWindow.document.close();
};

const searchReceipt = async () => {
    if (!receiptSearch.value) return;
    isSearching.value = true;
    try {
        const cleanReceiptNumber = encodeURIComponent(receiptSearch.value.trim());
        const response = await axios.get(`/pos/receipt/${cleanReceiptNumber}`);
        
        if (response.data.success) {
            // Access the nested receipt_data
               receiptItems.value = response.data.receipt_data.items;
        receiptTotal.value = response.data.receipt_data.total_amount;
            receiptData.value = response.data.receipt_data;
            console.log('Received receipt data:', receiptData.value);
            showReceipt.value = true;
        } else {
            alert(response.data.message);
        }
    } catch (error) {
        alert("Receipt not found");
        console.error("Receipt search error:", error);
    }finally{
        isSearching.value = false;
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

            <!-- Search Recieft -->
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Add this near the POS header section -->
                <div class="flex items-center gap-4 mb-4">
                    <input v-model="receiptSearch" type="text" placeholder="Search receipt by number..."
                        @keyup.enter="searchReceipt" class="w-full p-2 border rounded-lg dark:bg-gray-700">
                    <button @click="searchReceipt" :disabled="isSearching"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                           {{ isSearching ? 'Searching...' : 'Search' }}
                    </button>
                </div>

                <!-- POS Layout -->
                <div
                    class="flex flex-col lg:flex-row bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 rounded-lg shadow-lg overflow-hidden">

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
                                <select v-model="selectedCategory"
                                    class="w-full sm:w-40 p-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 text-gray-700 dark:text-gray-300">
                                    <option v-for="category in categories" :key="category">{{ category }}</option>
                                </select>
                            </div>

                            <!-- Product Grid Items -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 lg:gap-6">
                                <div v-for="product in filteredProducts()" :key="product.id"
                                    class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 transition hover:shadow-lg">
                                    <div
                                        class="h-32 sm:h-40 bg-gray-200 dark:bg-gray-700 rounded-md mb-3 flex items-center justify-center overflow-hidden">
                                        <img :src="product.image" alt="Product Image"
                                            class="object-cover h-full w-full" />
                                    </div>
                                    <h4 class="text-sm sm:text-lg font-semibold text-gray-700 dark:text-gray-300">{{
                                        product.name }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ product.category.name }}</p>
                                    <p class="text-sm sm:text-md font-bold mt-2 text-gray-800 dark:text-gray-100">₦{{
                                        product.price }}</p>
                                    <button @click="addToCart(product)" aria-label="Add to Cart"
                                        class="mt-3 w-full py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition">
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
                                <li v-for="item in cartItems" :key="item.id"
                                    class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ item.name
                                            }}</h4>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <input type="number" v-model.number="item.quantity"
                                                @change="updateQuantity(item.id, item.quantity)"
                                                class="w-12 sm:w-16 p-1 border border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600 text-center focus:outline-none"
                                                min="1" />
                                            <p class="text-xs text-gray-500 dark:text-gray-400">x ₦{{ item.price }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <p class="font-semibold text-gray-800 dark:text-gray-100">₦{{ (item.price *
                                            item.quantity) }}</p>
                                        <button @click="removeFromCart(item.id)" aria-label="Remove from Cart"
                                            class="text-red-500 hover:text-red-600 text-sm font-bold">✕</button>
                                    </div>
                                </li>
                            </ul>

                            <!-- Cart Summary and Actions -->
                            <div class="mt-6 lg:mt-8 pt-4 border-t border-gray-200 dark:border-gray-700 space-y-4">
                                <div
                                    class="flex justify-between text-md sm:text-lg font-semibold text-gray-700 dark:text-gray-300">
                                    <span>Total:</span>
                                    <span>₦{{ calculateTotal() }}</span>
                                </div>
                                <button @click="checkout" :disabled="isLoading"
                                    class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-md transition">
                                    {{ isLoading ? 'Processing...' : 'Checkout' }}
                                </button> <button @click="cartItems = []"
                                    class="w-full py-3 bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold rounded-lg shadow-md transition dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-gray-200">
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
    <div v-if="showReceipt" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-2">
        <div id="receipt-printable"
            class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <!-- Header -->
            <header class="bg-gradient-to-r from-emerald-600 to-cyan-500 px-6 py-4 relative text-center">
                <div class="mb-2">
                    <img src="/images/logo2.png" alt="Company Logo" class="mx-auto max-h-12 w-auto">
                </div>
                <div class="absolute top-2 right-2 text-white opacity-20">
                    <i class="fas fa-receipt text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white tracking-wide">RECEIPT</h1>
                <p class="mt-1 text-xs text-cyan-100">{{ receiptData?.store_info.address }}</p>
                <p class="text-xs text-cyan-100">{{ receiptData?.store_info.contact }} • {{
                    receiptData?.store_info.phone }}
                </p>
            </header>

            <!-- Body -->
            <main class="px-6 py-4 space-y-4">
                <!-- Metadata -->
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="block text-gray-500 font-medium">Date Issued</span>
                        <span class="block text-gray-800">
                            {{
                                new Date(receiptData!.date).toLocaleDateString('en-US', {
                                    weekday: 'short',
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric'
                                })
                            }}
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="block text-gray-500 font-medium">Receipt #</span>
                        <span class="block text-gray-800">{{ receiptData?.receipt_number }}</span>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="pb-1 text-left text-gray-500">Item</th>
                                <th class="pb-1 text-right text-gray-500">Qty</th>
                                <th class="pb-1 text-right text-gray-500">Price</th>
                                <th class="pb-1 text-right text-gray-500">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="item in receiptItems" :key="item.product_id">
                                <td class="py-1 text-gray-700">{{ item.name }}</td>
                                <td class="py-1 text-right text-gray-700">{{ item.quantity }}</td>
                                <td class="py-1 text-right text-gray-700">₦{{ item.price }}</td>
                                <td class="py-1 text-right text-gray-700">
                                    ₦{{ (item.quantity * item.price).toFixed(2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="flex justify-end border-t border-gray-200 pt-2">
                    <div class="text-right">
                        <div class="flex items-center justify-between">
                            <span class="text-base font-bold text-emerald-600">Total:</span>
                            <span class="text-base font-bold text-emerald-600">₦{{ Number(receiptTotal).toFixed(2)
                                }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Status -->
                <div class="bg-emerald-50 p-2 rounded flex items-center gap-2">
                    <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
                    <div>
                        <p class="text-emerald-600 font-medium text-xs">Payment Completed</p>
                        <p class="text-xs text-emerald-600">Transaction ID: {{ receiptData?.transaction_id }}</p>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-gray-50 px-6 py-3 text-center border-t border-gray-200">
                <p class="text-xs text-gray-500">Thank you for shopping with us!</p>
                <p class="text-xs text-gray-500 mt-1">Valid official receipt</p>
            </footer>

            <!-- Action Buttons -->
            <div class="px-6 py-3 flex justify-end gap-2 border-t border-gray-200 print-buttons" id="print-buttons">
                <button @click="printReceipt"
                    class="flex items-center gap-1 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs rounded transition duration-200">
                    <i class="fas fa-print"></i>
                    Print
                </button>
                <button @click="closeReceipt"
                    class="px-3 py-1 bg-gray-300 hover:bg-gray-400 text-gray-700 text-xs rounded transition duration-200">
                    Close
                </button>
            </div>
        </div>
    </div>


</template>

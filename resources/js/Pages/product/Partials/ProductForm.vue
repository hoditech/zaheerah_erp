<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

interface Category {
    id: number;
    name: string;
}

interface Supplier {
    id: number;
    name: string;
}


defineProps({
    categories: {
        type: Array as () => Category[],
        required: true,
    },
    suppliers: {
        type: Array as () => Supplier[],
        required: true,
    },
});

const form = useForm({
    name: '',
    price: '',
    stock: '',
    weight: '',
    weight_per_bag: '',
    total_cost: '',
    stock_receive: '',
    stock_release: '',
    remaining_bags: '',
    supplier_id: '',
    exp_date: '',
    batch_number: '',
    storage_location: '',
    category_id: '',
});

const submit = () => {
    form.post(route('products.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <div v-if="$page.props.flash.success" class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
        {{ $page.props.flash.success }}
    </div>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <InputLabel for="name" value="Product Name" />
                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>
            <div>
                <InputLabel for="price" value="Price" />
                <TextInput id="price" type="number" step="0.01" class="mt-1 block w-full" v-model="form.price"
                    required />
                <InputError class="mt-2" :message="form.errors.price" />
            </div>
            <div>
                <InputLabel for="stock" value="Stock" />
                <TextInput id="stock" type="number" class="mt-1 block w-full" v-model="form.stock" required />
                <InputError class="mt-2" :message="form.errors.stock" />
            </div>
            <div>
                <InputLabel for="category" value="Category" />
                <select id="category" v-model="form.category_id"
                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    required>
                    <option value="" disabled>Select a category</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.category_id" />
            </div>
            <div>
                <InputLabel for="weight" value="Weight" />
                <TextInput id="weight" type="number" step="0.01" class="mt-1 block w-full" v-model="form.weight" />
                <InputError class="mt-2" :message="form.errors.weight" />
            </div>
            <div>
                <InputLabel for="weight_per_bag" value="Weight per Bag" />
                <TextInput id="weight_per_bag" type="number" step="0.01" class="mt-1 block w-full"
                    v-model="form.weight_per_bag" />
                <InputError class="mt-2" :message="form.errors.weight_per_bag" />
            </div>
            <div>
                <InputLabel for="total_cost" value="Total Cost" />
                <TextInput id="total_cost" type="number" step="0.01" class="mt-1 block w-full"
                    v-model="form.total_cost" />
                <InputError class="mt-2" :message="form.errors.total_cost" />
            </div>
            <div>
                <InputLabel for="stock_receive" value="Stock Receive" />
                <TextInput id="stock_receive" type="number" class="mt-1 block w-full" v-model="form.stock_receive" />
                <InputError class="mt-2" :message="form.errors.stock_receive" />
            </div>
            <div>
                <InputLabel for="stock_release" value="Stock Release" />
                <TextInput id="stock_release" type="number" class="mt-1 block w-full" v-model="form.stock_release" />
                <InputError class="mt-2" :message="form.errors.stock_release" />
            </div>
            <div>
                <InputLabel for="remaining_bags" value="Remaining Bags" />
                <TextInput id="remaining_bags" type="number" class="mt-1 block w-full" v-model="form.remaining_bags" />
                <InputError class="mt-2" :message="form.errors.remaining_bags" />
            </div>
            <div>
                <InputLabel for="supplier" value="Supplier" />
                <select id="supplier" v-model="form.supplier_id"
                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="" disabled>Select a supplier</option>
                    <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                        {{ supplier.name }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.supplier_id" />
            </div>
            <div>
                <InputLabel for="exp_date" value="Expiration Date" />
                <TextInput id="exp_date" type="date" class="mt-1 block w-full" v-model="form.exp_date" />
                <InputError class="mt-2" :message="form.errors.exp_date" />
            </div>
            <div>
                <InputLabel for="batch_number" value="Batch Number" />
                <TextInput id="batch_number" type="text" class="mt-1 block w-full" v-model="form.batch_number" />
                <InputError class="mt-2" :message="form.errors.batch_number" />
            </div>
            <div>
                <InputLabel for="storage_location" value="Storage Location" />
                <TextInput id="storage_location" type="text" class="mt-1 block w-full"
                    v-model="form.storage_location" />
                <InputError class="mt-2" :message="form.errors.storage_location" />
            </div>
        </div>
        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
        </div>
    </form>
</template>

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

// Every required field here must match a rule in productController::store.
const form = useForm({
    name: '',
    price: '',
    unit_cost: '',
    initial_stock: '',
    category_id: '',
    weight_per_unit: '',
    supplier_id: '',
    exp_date: '',
    batch_number: '',
    storage_location: '',
    image: null as File | null,
});

const onImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    form.image = target.files?.[0] ?? null;
};

const submit = () => {
    // forceFormData keeps the upload working for the image field.
    form.post(route('products.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
        },
        onError: (errors) => {
            console.log('Form errors:', errors);
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
                <InputLabel for="price" value="Selling Price" />
                <TextInput id="price" type="number" step="0.01" class="mt-1 block w-full" v-model="form.price"
                    required />
                <InputError class="mt-2" :message="form.errors.price" />
            </div>
            <div>
                <InputLabel for="unit_cost" value="Unit Cost" />
                <TextInput id="unit_cost" type="number" step="0.01" class="mt-1 block w-full" v-model="form.unit_cost"
                    required />
                <InputError class="mt-2" :message="form.errors.unit_cost" />
            </div>
            <div>
                <InputLabel for="initial_stock" value="Opening Stock" />
                <TextInput id="initial_stock" type="number" class="mt-1 block w-full" v-model="form.initial_stock"
                    required />
                <InputError class="mt-2" :message="form.errors.initial_stock" />
            </div>
            <div>
                <InputLabel for="weight_per_unit" value="Weight per unit (kg)" />
                <TextInput id="weight_per_unit" type="number" step="0.01" class="mt-1 block w-full"
                    v-model="form.weight_per_unit" />
                <InputError class="mt-2" :message="form.errors.weight_per_unit" />
            </div>
            <div>
                <InputLabel for="supplier" value="Supplier" />
                <select id="supplier" v-model="form.supplier_id"
                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">No supplier</option>
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
            <div class="md:col-span-2">
                <InputLabel for="image" value="Product Image" />
                <input id="image" type="file" accept="image/*" @change="onImageChange"
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-3 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                <p class="mt-1 text-xs text-gray-500">
                    Shown on the website shop. JPEG, PNG, GIF or WebP, up to 2 MB.
                </p>
                <InputError class="mt-2" :message="form.errors.image" />
            </div>
        </div>
        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
        </div>
    </form>
</template>

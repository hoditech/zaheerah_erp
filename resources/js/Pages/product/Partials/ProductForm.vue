<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';


defineProps({
    categories: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    name: '',
    price: '',
    stock: '',
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
    <div v-if="$page.props.flash.message" class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
            {{ $page.props.flash.message }}
        </div>
    <form @submit.prevent="submit" class="space-y-6">
        <div>
            <InputLabel for="name" value="Product Name" />
            <TextInput
                id="name"
                type="text"
                class="mt-1 block w-full"
                v-model="form.name"
                required
                autofocus
            />
            <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div>
            <InputLabel for="price" value="Price" />
            <TextInput
                id="price"
                type="number"
                step="0.01"
                class="mt-1 block w-full"
                v-model="form.price"
                required
            />
            <InputError class="mt-2" :message="form.errors.price" />
        </div>

        <div>
            <InputLabel for="stock" value="Stock" />
            <TextInput
                id="stock"
                type="number"
                class="mt-1 block w-full"
                v-model="form.stock"
                required
            />
            <InputError class="mt-2" :message="form.errors.stock" />
        </div>

        <div>
            <InputLabel for="category" value="Category" />
            <select
                id="category"
                v-model="form.category_id"
                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                required
            >
                <option value="" disabled>Select a category</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                </option>
            </select>
            <InputError class="mt-2" :message="form.errors.category_id" />
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
        </div>
    </form>
</template>

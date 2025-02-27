<script setup lang="ts">
// @ts-nocheck
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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

interface Product {
  id: number;
  name: string;
  price: number;
  stock: number;
  weight: number;
  weight_per_unit: number;
  total_cost: number;
  stock_receive: number;
  stock_release: number;
  remaining_units: number;
  supplier_id: number;
  exp_date: string;
  batch_number: string;
  storage_location: string;
  category_id: number;
}

// Define props
const props = defineProps({
  product: {
    type: Object as () => Product,
    required: true,
  },
  categories: {
    type: Array as () => Category[],
    required: true,
  },
  suppliers: {
    type: Array as () => Supplier[],
    required: true,
  },
});

console.log([props.suppliers, props.product]);

// Initialize form with existing product data
const form = useForm({
  name: props.product.name,
  price: props.product.price,
  stock: props.product.stock,
  weight: props.product.weight,
  weight_per_unit: props.product.weight_per_unit,
  total_cost: props.product.total_cost,
  stock_receive: props.product.stock_receive,
  stock_release: props.product.stock_release,
  remaining_units: props.product.remaining_units,
  supplier_id: props.product.supplier_id,
  exp_date: props.product.exp_date,
  batch_number: props.product.batch_number,
  storage_location: props.product.storage_location,
  category_id: props.product.category_id,
});

// Handle form submission
const submit = () => {
  form.put(route('products.update', props.product.id), {
    onSuccess: () => {
      form.reset();
    },
    onError: (errors) => {
      console.log(errors);
    },
  });
};
</script>

<template>

  <Head title="Edit Product" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Add Poduct
      </h2>
    </template>
    <div class="py-12">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
          <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

              <div>
                <InputLabel for="name" value="Product Name" />
                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus />
                <InputError class="mt-2" :message="form.errors.name" />
              </div>
              <div>
                <InputLabel for="price" value="Price" />
                <TextInput id="price" type="number" step="0.01" class="mt-1 block w-full" v-model.number="form.price"
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
                <InputLabel for="weight_per_unit" value="Weight per unit" />
                <TextInput id="weight_per_unit" type="number" step="0.01" class="mt-1 block w-full"
                  v-model="form.weight_per_unit" />
                <InputError class="mt-2" :message="form.errors.weight_per_unit" />
              </div>
              <div>
                <InputLabel for="total_cost" value="Total Cost" />
                <TextInput id="total_cost" type="number" step="0.01" class="mt-1 block w-full"
                  v-model="form.total_cost" />
                <InputError class="mt-2" :message="form.errors.total_cost" />
              </div>
              <div>
                <InputLabel for="stock_receive" value="Stock Receive" />
                <TextInput id="stock_receive" type="number" class="mt-1 block w-full"
                  v-model.number="form.stock_receive" />
                <InputError class="mt-2" :message="form.errors.stock_receive" />
              </div>
              <div>
                <InputLabel for="stock_release" value="Stock Release" />
                <TextInput id="stock_release" type="number" class="mt-1 block w-full" v-model="form.stock_release" />
                <InputError class="mt-2" :message="form.errors.stock_release" />
              </div>
              <div>
                <InputLabel for="remaining_units" value="Remaining units" />
                <TextInput id="remaining_units" type="number" class="mt-1 block w-full"
                  v-model.number="form.remaining_units" />
                <InputError class="mt-2" :message="form.errors.remaining_units" />
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
                <InputLabel for="supplier" value="Supplier" />
                <select id="supplier" v-model="form.supplier_id"
                  class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                  <option value="" disabled>Select a Supplier</option>
                  <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                    {{ supplier.name }}
                  </option>
                </select>
                <InputError class="mt-2" :message="form.errors.category_id" />
              </div>
              <div>
                <InputLabel for="storage_location" value="Storage Location" />
                <TextInput id="storage_location" type="text" class="mt-1 block w-full"
                  v-model="form.storage_location" />
                <InputError class="mt-2" :message="form.errors.storage_location" />
              </div>

            </div>
            <div class="flex items-center gap-4">
              <PrimaryButton :disabled="form.processing">Update</PrimaryButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>

</template>

<script setup lang="ts">
// @ts-nocheck
import { computed } from 'vue';
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
  unit_cost: number;
  stock: number;
  image_url: string | null;
  weight_per_unit: number;
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

// Every required field here must match a rule in productController::update.
const form = useForm({
  name: props.product.name,
  price: props.product.price,
  unit_cost: props.product.unit_cost,
  stock_adjustment: '',
  category_id: props.product.category_id,
  weight_per_unit: props.product.weight_per_unit ?? '',
  supplier_id: props.product.supplier_id ?? '',
  exp_date: props.product.exp_date ?? '',
  batch_number: props.product.batch_number ?? '',
  storage_location: props.product.storage_location ?? '',
  image: null as File | null,
});

// The stored value is either "storage/products/x.jpg" or a full URL when the
// product was imported from the shop.
const imagePreview = computed(() => {
  const url = props.product.image_url;

  if (!url) {
    return null;
  }

  return url.startsWith('http') ? url : `/${url}`;
});

const onImageChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  form.image = target.files?.[0] ?? null;
};

// Handle form submission
const submit = () => {
  form.put(route('products.update', props.product.id), {
    forceFormData: true,
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
        Edit Product
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
                <TextInput id="price" type="number" step="0.01" class="mt-1 block w-full" v-model.number="form.price"
                  required />
                <InputError class="mt-2" :message="form.errors.price" />
              </div>
              <div>
                <InputLabel for="unit_cost" value="Unit Cost" />
                <TextInput id="unit_cost" type="number" step="0.01" class="mt-1 block w-full"
                  v-model.number="form.unit_cost" required />
                <InputError class="mt-2" :message="form.errors.unit_cost" />
              </div>
              <div>
                <InputLabel for="stock" value="Current Stock" />
                <TextInput id="stock" type="number" class="mt-1 block w-full bg-gray-100" :model-value="product.stock"
                  disabled />
                <p class="mt-1 text-xs text-gray-500">
                  Stock changes through sales and the adjustment below, so it is read-only here.
                </p>
              </div>
              <div>
                <InputLabel for="stock_adjustment" value="Stock Adjustment" />
                <TextInput id="stock_adjustment" type="number" class="mt-1 block w-full"
                  v-model.number="form.stock_adjustment" placeholder="e.g. 20 to restock, -5 to write off" />
                <p class="mt-1 text-xs text-gray-500">Leave empty to leave stock unchanged.</p>
                <InputError class="mt-2" :message="form.errors.stock_adjustment" />
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
                <div class="mt-1 flex items-center gap-4">
                  <img v-if="imagePreview" :src="imagePreview" alt="Current product image"
                    class="h-20 w-20 rounded object-cover border border-gray-200" />
                  <span v-else class="text-xs text-gray-400">No image yet</span>
                  <input id="image" type="file" accept="image/*" @change="onImageChange"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-3 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                </div>
                <p class="mt-1 text-xs text-gray-500">
                  Leave empty to keep the current image. JPEG, PNG, GIF or WebP, up to 2 MB.
                </p>
                <InputError class="mt-2" :message="form.errors.image" />
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

<script setup lang="ts">
// @ts-nocheck
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    product: {
        type: Object,
        default: null,
    },
});

const initial: any = {
    name: props.product?.name ?? '',
    pack_description: props.product?.pack_description ?? '',
    price_per_carton: props.product?.price_per_carton ?? '',
    retail_margin: props.product?.retail_margin ?? '',
    badge: props.product?.badge ?? '',
    cta_label: props.product?.cta_label ?? 'Add to Order',
    cta_url: props.product?.cta_url ?? '',
    is_highlighted: props.product ? !!props.product.is_highlighted : false,
    is_active: props.product ? !!props.product.is_active : true,
    sort_order: props.product?.sort_order ?? '',
    image: null,
};

// Uploading an image means the form must post as multipart; PHP does not parse
// a multipart PUT body, so spoof the verb with _method like the product form.
if (props.product) {
    initial._method = 'put';
}

const form = useForm(initial);

const imagePreview = computed(() => {
    const url = props.product?.image_url;

    if (!url) {
        return null;
    }

    return url.startsWith('http') ? url : `/${url}`;
});

const onImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    form.image = target.files?.[0] ?? null;
};

const submit = () => {
    if (props.product) {
        form.post(route('bloom-products.update', props.product.id), {
            forceFormData: true,
            preserveScroll: true,
        });
    } else {
        form.post(route('bloom-products.store'), {
            forceFormData: true,
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8">
        <div v-if="Object.keys(form.errors).length" class="bg-red-100 text-red-700 px-4 py-2 rounded">
            Please fix the highlighted fields.
        </div>

        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-4">Carton card content</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <InputLabel for="name" value="Product name *" />
                    <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus
                        placeholder="Washing Soap (Laundry Bars)" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <div>
                    <InputLabel for="pack_description" value="Pack description" />
                    <TextInput id="pack_description" type="text" class="mt-1 block w-full"
                        v-model="form.pack_description" placeholder="48 bars per carton" />
                    <p class="mt-1 text-xs text-gray-500">Shown under the price, e.g. “48 bars per carton”.</p>
                    <InputError class="mt-2" :message="form.errors.pack_description" />
                </div>
                <div>
                    <InputLabel for="badge" value="Badge" />
                    <TextInput id="badge" type="text" class="mt-1 block w-full" v-model="form.badge"
                        placeholder="High Demand" />
                    <InputError class="mt-2" :message="form.errors.badge" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="image" value="Carton image" />
                    <div class="mt-1 flex items-center gap-4">
                        <img v-if="imagePreview" :src="imagePreview" alt="Current carton image"
                            class="h-16 w-16 rounded object-contain border border-gray-200 bg-white" />
                        <span v-else class="text-xs text-gray-400">Using the site logo</span>
                        <input id="image" type="file" accept="image/*" @change="onImageChange"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-3 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Optional. JPEG, PNG, GIF or WebP, up to 2 MB.</p>
                    <InputError class="mt-2" :message="form.errors.image" />
                </div>
            </div>
        </section>

        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-4">Pricing</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="price_per_carton" value="Price per carton (₦)" />
                    <TextInput id="price_per_carton" type="number" step="0.01" min="0" class="mt-1 block w-full"
                        v-model="form.price_per_carton" placeholder="12500" />
                    <p class="mt-1 text-xs text-gray-500">Leave empty to show “Contact for price”.</p>
                    <InputError class="mt-2" :message="form.errors.price_per_carton" />
                </div>
                <div>
                    <InputLabel for="retail_margin" value="Estimated retail margin (₦)" />
                    <TextInput id="retail_margin" type="number" step="0.01" min="0" class="mt-1 block w-full"
                        v-model="form.retail_margin" placeholder="6000" />
                    <p class="mt-1 text-xs text-gray-500">Displayed as “Est. retail margin: ₦6,000+”.</p>
                    <InputError class="mt-2" :message="form.errors.retail_margin" />
                </div>
            </div>
        </section>

        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-4">Call to action</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="cta_label" value="Button label" />
                    <TextInput id="cta_label" type="text" class="mt-1 block w-full" v-model="form.cta_label"
                        placeholder="Add to Order" />
                    <InputError class="mt-2" :message="form.errors.cta_label" />
                </div>
                <div>
                    <InputLabel for="cta_url" value="Button link" />
                    <TextInput id="cta_url" type="text" class="mt-1 block w-full" v-model="form.cta_url"
                        placeholder="https://bloom.zaheerah.com" />
                    <InputError class="mt-2" :message="form.errors.cta_url" />
                </div>
            </div>
        </section>

        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-4">Display</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="sort_order" value="Sort order" />
                    <TextInput id="sort_order" type="number" min="0" class="mt-1 block w-full" v-model="form.sort_order" />
                    <p class="mt-1 text-xs text-gray-500">Lower numbers appear first.</p>
                    <InputError class="mt-2" :message="form.errors.sort_order" />
                </div>
                <div class="space-y-3">
                    <label class="inline-flex items-center">
                        <input type="checkbox" v-model="form.is_highlighted"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Highlight this card (amber border)</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" v-model="form.is_active"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Visible on the public Bloom page</span>
                    </label>
                </div>
            </div>
        </section>

        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
        </div>
    </form>
</template>

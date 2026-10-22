<script setup lang="ts">
// @ts-nocheck
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    tier: {
        type: Object,
        default: null,
    },
    themes: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.tier?.name ?? '',
    subtitle: props.tier?.subtitle ?? '',
    icon: props.tier?.icon ?? '✨',
    min_cartons: props.tier?.min_cartons ?? '',
    max_cartons: props.tier?.max_cartons ?? '',
    range_label: props.tier?.range_label ?? '',
    unit_label: props.tier?.unit_label ?? 'cartons',
    price_per_carton: props.tier?.price_per_carton ?? '',
    discount_percent: props.tier?.discount_percent ?? '',
    benefits: Array.isArray(props.tier?.benefits) ? props.tier.benefits.join('\n') : '',
    cta_label: props.tier?.cta_label ?? 'Select Plan',
    cta_url: props.tier?.cta_url ?? '',
    footnote: props.tier?.footnote ?? '',
    badge: props.tier?.badge ?? '',
    theme: props.tier?.theme ?? 'cream',
    is_active: props.tier ? !!props.tier.is_active : true,
    sort_order: props.tier?.sort_order ?? '',
});

const submit = () => {
    if (props.tier) {
        form.put(route('bloom-tiers.update', props.tier.id), {
            preserveScroll: true,
        });
    } else {
        form.post(route('bloom-tiers.store'), {
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

        <!-- Card content -->
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-4">Card content</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="name" value="Tier name *" />
                    <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus placeholder="Bloom 1" />
                    <p class="mt-1 text-xs text-gray-500">Shown in uppercase on the card.</p>
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <div>
                    <InputLabel for="subtitle" value="Subtitle" />
                    <TextInput id="subtitle" type="text" class="mt-1 block w-full" v-model="form.subtitle" placeholder="Retail" />
                    <InputError class="mt-2" :message="form.errors.subtitle" />
                </div>
                <div>
                    <InputLabel for="icon" value="Icon (emoji)" />
                    <TextInput id="icon" type="text" class="mt-1 block w-full" v-model="form.icon" placeholder="✨" />
                    <p class="mt-1 text-xs text-gray-500">Used by the Bloom teaser on the frontsite home page.</p>
                    <InputError class="mt-2" :message="form.errors.icon" />
                </div>
                <div>
                    <InputLabel for="badge" value="Badge" />
                    <TextInput id="badge" type="text" class="mt-1 block w-full" v-model="form.badge" placeholder="Most Popular" />
                    <InputError class="mt-2" :message="form.errors.badge" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="theme" value="Card style *" />
                    <select id="theme" v-model="form.theme"
                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        required>
                        <option v-for="(label, value) in themes" :key="value" :value="value">{{ label }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.theme" />
                </div>
            </div>
        </section>

        <!-- Volume and pricing -->
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-4">Volume &amp; pricing</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="min_cartons" value="Minimum cartons" />
                    <TextInput id="min_cartons" type="number" min="0" class="mt-1 block w-full" v-model="form.min_cartons" placeholder="100" />
                    <InputError class="mt-2" :message="form.errors.min_cartons" />
                </div>
                <div>
                    <InputLabel for="max_cartons" value="Maximum cartons" />
                    <TextInput id="max_cartons" type="number" min="0" class="mt-1 block w-full" v-model="form.max_cartons" placeholder="199" />
                    <p class="mt-1 text-xs text-gray-500">Leave empty when the tier has no upper limit.</p>
                    <InputError class="mt-2" :message="form.errors.max_cartons" />
                </div>
                <div>
                    <InputLabel for="range_label" value="Custom range label" />
                    <TextInput id="range_label" type="text" class="mt-1 block w-full" v-model="form.range_label" placeholder="500+" />
                    <p class="mt-1 text-xs text-gray-500">Overrides the generated band. Leave empty to build it from the counts.</p>
                    <InputError class="mt-2" :message="form.errors.range_label" />
                </div>
                <div>
                    <InputLabel for="unit_label" value="Unit label" />
                    <TextInput id="unit_label" type="text" class="mt-1 block w-full" v-model="form.unit_label" placeholder="cartons" />
                    <InputError class="mt-2" :message="form.errors.unit_label" />
                </div>
                <div>
                    <InputLabel for="price_per_carton" value="Price per carton (₦)" />
                    <TextInput id="price_per_carton" type="number" step="0.01" min="0" class="mt-1 block w-full" v-model="form.price_per_carton" placeholder="12500" />
                    <p class="mt-1 text-xs text-gray-500">Optional. Shown on the card when set.</p>
                    <InputError class="mt-2" :message="form.errors.price_per_carton" />
                </div>
                <div>
                    <InputLabel for="discount_percent" value="Discount (%)" />
                    <TextInput id="discount_percent" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" v-model="form.discount_percent" placeholder="5" />
                    <p class="mt-1 text-xs text-gray-500">Optional. Shown as “5% off” under the price.</p>
                    <InputError class="mt-2" :message="form.errors.discount_percent" />
                </div>
            </div>
        </section>

        <!-- Benefits -->
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-4">Benefits</h3>
            <InputLabel for="benefits" value="What this tier includes" />
            <textarea id="benefits" v-model="form.benefits" rows="5"
                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                placeholder="Base wholesale pricing&#10;Small outlet support&#10;Standard delivery"></textarea>
            <p class="mt-1 text-xs text-gray-500">One benefit per line. Each line becomes a tick on the card.</p>
            <InputError class="mt-2" :message="form.errors.benefits" />
        </section>

        <!-- Call to action -->
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-4">Call to action</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="cta_label" value="Button label" />
                    <TextInput id="cta_label" type="text" class="mt-1 block w-full" v-model="form.cta_label" placeholder="Select Plan" />
                    <InputError class="mt-2" :message="form.errors.cta_label" />
                </div>
                <div>
                    <InputLabel for="cta_url" value="Button link" />
                    <TextInput id="cta_url" type="text" class="mt-1 block w-full" v-model="form.cta_url" placeholder="https://bloom.zaheerah.com" />
                    <p class="mt-1 text-xs text-gray-500">Leave empty to use the Bloom site URL.</p>
                    <InputError class="mt-2" :message="form.errors.cta_url" />
                </div>
            </div>
        </section>

        <!-- Display -->
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-4">Display</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="footnote" value="Footnote" />
                    <TextInput id="footnote" type="text" class="mt-1 block w-full" v-model="form.footnote" placeholder="Ideal for small retail shops starting out." />
                    <InputError class="mt-2" :message="form.errors.footnote" />
                </div>
                <div>
                    <InputLabel for="sort_order" value="Sort order" />
                    <TextInput id="sort_order" type="number" min="0" class="mt-1 block w-full" v-model="form.sort_order" />
                    <p class="mt-1 text-xs text-gray-500">Lower numbers appear first.</p>
                    <InputError class="mt-2" :message="form.errors.sort_order" />
                </div>
                <div class="md:col-span-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" v-model="form.is_active"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Visible on the public Bloom page</span>
                    </label>
                    <InputError class="mt-2" :message="form.errors.is_active" />
                </div>
            </div>
        </section>

        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
        </div>
    </form>
</template>

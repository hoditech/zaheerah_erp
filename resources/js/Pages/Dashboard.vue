<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <header
                class="flex items-center justify-between px-4 py-4 bg-gray-50 dark:bg-gray-800 rounded-lg shadow-sm">
                <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Admin Dashboard</h2>
            </header>
        </template>

        <div class="p-6">

            <!-- Loading Indicator -->
            <div v-if="isLoading" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="relative w-16 h-16">
                    <div
                        class="w-full h-full border-4 border-transparent border-t-green-500 border-r-green-500 rounded-full animate-spin">
                    </div>
                    <div
                        class="absolute inset-2 w-12 h-12 border-4 border-transparent border-t-gray-300 border-r-gray-300 rounded-full animate-spin-slow">
                    </div>
                </div>
            </div>



            <!-- Error Message -->
            <div v-if="error" class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <p>{{ error }}</p>
            </div>

            <!-- Stats Section -->
            <div v-if="is('Super Admin')" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Total Revenue</h3>
                    <p class="text-2xl font-bold text-blue-500 dark:text-gray-100">{{ totalRevenue }}</p>
                </div>
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Total Cost</h3>
                    <p class="text-2xl font-bold text-red-600 dark:text-gray-100">{{ totalCost }}</p>
                </div>
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Total Profit</h3>
                    <p class="text-2xl font-bold text-green-600 dark:text-gray-100">{{ totalProfit }}</p>
                </div>
            </div>

            <!-- Stats Section -->
            <div v-if="is('Super Admin | Manager')" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Total Employees</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ usersCount }}</p>
                </div>
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Active Employees</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ activeUsersCount }}</p>
                </div>
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">New Hires (This Month)</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ newUsersCount }}</p>
                </div>
            </div>

            <!-- Notice Board Section -->
            <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-sm mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Notice Board</h3>
                    <button v-if="is('Super Admin')" @click="() => openNoticeModal()"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                        Add Notice
                    </button>
                </div>
                <div class="h-64 overflow-y-auto space-y-4">
                    <div v-for="(notice, index) in notices" :key="notice.id"
                        class="p-4 bg-gray-50 dark:bg-gray-600 rounded-lg relative group">
                        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ notice.title }}</h4>
                        <p class="text-gray-600 dark:text-gray-300 mt-2">{{ notice.description }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Posted on: {{ notice.date }}</p>
                        <div
                            class="absolute top-2 right-2 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button v-if="is('Super Admin')" @click="() => editNotice(index)"
                                class="p-1 text-green-500 hover:text-green-600">
                                <PencilIcon class="w-5 h-5" />
                            </button>
                            <button v-if="is('Super Admin')" @click="() => deleteNotice(index)"
                                class="p-1 text-red-500 hover:text-red-600">
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events Section -->
            <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-sm mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Upcoming Events</h3>
                    <button v-if="is('Super Admin')" @click="() => openEventModal()"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                        Add Event
                    </button>
                </div>
                <div class="h-64 overflow-y-auto space-y-4">
                    <div v-for="(event, index) in events" :key="event.id"
                        class="p-4 bg-gray-50 dark:bg-gray-600 rounded-lg relative group">
                        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ event.title }}</h4>
                        <p class="text-gray-600 dark:text-gray-300 mt-2">{{ event.description }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Date: {{ event.date }}</p>
                        <div
                            class="absolute top-2 right-2 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button v-if="is('Super Admin')" @click="() => editEvent(index)"
                                class="p-1 text-green-500 hover:text-green-600">
                                <PencilIcon class="w-5 h-5" />
                            </button>
                            <button v-if="is('Super Admin')" @click="() => deleteEvent(index)"
                                class="p-1 text-red-500 hover:text-red-600">
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg mb-8">
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6">
                    Recent Employee Activity
                </h3>
                <!-- Wrapper with fixed height and scrollable overflow -->
                <div class="h-64 overflow-y-auto">
                    <ul class="space-y-5">
                        <!-- Loop through logs -->
                        <li v-for="log in logs" :key="log.id"
                            class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow-sm hover:bg-gray-100 dark:hover:bg-gray-600 transition duration-200">
                            <span class="text-gray-700 dark:text-gray-300 text-base">
                                <!-- Display user_name and action -->
                                <strong class="font-medium">{{ log.action }}:</strong>
                                {{ log.user_name }}
                                <span class="text-gray-600 dark:text-gray-400">{{ log.description }}</span>
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                <!-- Format the timestamp as needed -->
                                {{ formatDate(log.created_at) }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>


        <!-- Notice Modal -->
        <Modal :show="isNoticeModalOpen" @close="closeNoticeModal">
            <div class="p-6">
                <h3 class="text-xl font-semibold mb-4">{{ isEditingNotice ? 'Edit Notice' : 'Add Notice' }}</h3>
                <form @submit.prevent="saveNotice">
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2">Title</label>
                        <input v-model="noticeForm.title" type="text"
                            class="w-full px-4 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700" required />
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2">Description</label>
                        <textarea v-model="noticeForm.description"
                            class="w-full px-4 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700" rows="4"
                            required></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2">Date</label>
                        <input v-model="noticeForm.date" type="date"
                            class="w-full px-4 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700" required />
                    </div>
                    <div class="flex justify-end">
                        <button type="button" @click="closeNoticeModal"
                            class="mr-2 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                            {{ isEditingNotice ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Event Modal -->
        <Modal :show="isEventModalOpen" @close="closeEventModal">
            <div class="p-6">
                <h3 class="text-xl font-semibold mb-4">{{ isEditingEvent ? 'Edit Event' : 'Add Event' }}</h3>
                <form @submit.prevent="saveEvent">
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2">Title</label>
                        <input v-model="eventForm.title" type="text"
                            class="w-full px-4 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700" required />
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2">Description</label>
                        <textarea v-model="eventForm.description"
                            class="w-full px-4 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700" rows="4"
                            required></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2">Date</label>
                        <input v-model="eventForm.date" type="date"
                            class="w-full px-4 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700" required />
                    </div>
                    <div class="flex justify-end">
                        <button type="button" @click="closeEventModal"
                            class="mr-2 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                            {{ isEditingEvent ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, PropType } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { PencilIcon, TrashIcon } from '@heroicons/vue/16/solid';
import axios from 'axios';
// @ts-ignore
import { can, is } from 'laravel-permission-to-vuejs'

// Define types
interface Notice {
    id: number;
    title: string;
    description: string;
    date: string;
}

interface Event {
    id: number;
    title: string;
    description: string;
    date: string;
}
interface Log {
    id: number;
    action: string;
    description: string;
    created_at: string;
    user_name: string;

}

// Notice Board Data
const notices = ref<Notice[]>([]);
const isNoticeModalOpen = ref(false);
const isEditingNotice = ref(false);
const noticeForm = ref<Notice>({ id: 0, title: '', description: '', date: '' });
const currentNoticeIndex = ref<number | null>(null);

const isLoading = ref(false);
const error = ref<string | null>(null);

// Event Data
const events = ref<Event[]>([]);
const isEventModalOpen = ref(false);
const isEditingEvent = ref(false);
const eventForm = ref<Event>({ id: 0, title: '', description: '', date: '' });
const currentEventIndex = ref<number | null>(null);

// Fetch Notices
const fetchNotices = async () => {
    isLoading.value = true;
    error.value = null;
    try {
        const response = await axios.get<Notice[]>('/notices');
        notices.value = response.data;
    } catch (err) {
        error.value = 'Failed to fetch notices. Please try again later.';
    } finally {
        isLoading.value = false;
    }
};

// Fetch Events
const fetchEvents = async () => {
    isLoading.value = true;
    error.value = null;
    try {
        const response = await axios.get<Event[]>('/events');
        events.value = response.data;
    } catch (err) {
        error.value = 'Failed to fetch events. Please try again later.';
    } finally {
        isLoading.value = false;
    }
};

// Notice Modal
const openNoticeModal = (index?: number) => {
    if (index !== undefined) {
        noticeForm.value = { ...notices.value[index] };
        isEditingNotice.value = true;
        currentNoticeIndex.value = index;
    } else {
        noticeForm.value = { id: 0, title: '', description: '', date: '' };
        isEditingNotice.value = false;
    }
    isNoticeModalOpen.value = true;
};

const closeNoticeModal = () => {
    isNoticeModalOpen.value = false;
};

const saveNotice = async () => {
    isLoading.value = true;
    error.value = null;
    try {
        if (isEditingNotice.value && currentNoticeIndex.value !== null) {
            await axios.put(`/notices/${notices.value[currentNoticeIndex.value].id}`, noticeForm.value);
        } else {
            await axios.post('/notices', noticeForm.value);
        }
        fetchNotices();
        closeNoticeModal();
    } catch (err) {
        error.value = 'Failed to save notice. Please try again later.';
    } finally {
        isLoading.value = false;
    }
};

const editNotice = (index: number) => {
    openNoticeModal(index);
};

const deleteNotice = async (index: number) => {
    isLoading.value = true;
    error.value = null;
    try {
        await axios.delete(`/notices/${notices.value[index].id}`);
        fetchNotices();
    } catch (err) {
        error.value = 'Failed to delete notice. Please try again later.';
    } finally {
        isLoading.value = false;
    }
};

// Event Modal
const openEventModal = (index?: number) => {
    if (index !== undefined) {
        eventForm.value = { ...events.value[index] };
        isEditingEvent.value = true;
        currentEventIndex.value = index;
    } else {
        eventForm.value = { id: 0, title: '', description: '', date: '' };
        isEditingEvent.value = false;
    }
    isEventModalOpen.value = true;
};

const closeEventModal = () => {
    isEventModalOpen.value = false;
};

const saveEvent = async () => {
    isLoading.value = true;
    error.value = null;
    try {
        if (isEditingEvent.value && currentEventIndex.value !== null) {
            await axios.put(`/events/${events.value[currentEventIndex.value].id}`, eventForm.value);
        } else {
            await axios.post('/events', eventForm.value);
        }
        fetchEvents();
        closeEventModal();
    } catch (err) {
        error.value = 'Failed to save event. Please try again later.';
    } finally {
        isLoading.value = false;
    }
};

const editEvent = (index: number) => {
    openEventModal(index);
};

const deleteEvent = async (index: number) => {
    isLoading.value = true;
    error.value = null;
    try {
        await axios.delete(`/events/${events.value[index].id}`);
        fetchEvents();
    } catch (err) {
        error.value = 'Failed to delete event. Please try again later.';
    } finally {
        isLoading.value = false;
    }
};

// Fetch data on component mount
onMounted(() => {
    fetchNotices();
    fetchEvents();
});

defineProps({
    usersCount: {
        type: Number,
        default: 0
    },
    activeUsersCount: {
        type: Number,
        default: 0
    },
    newUsersCount: {
        type: Number,
        default: 0
    },
    logs: {
        type: Array as PropType<Log[]>,
    },
    totalRevenue: {
        type: String,
        default: '0'

    },
    totalProfit: {
        type: String,
        default: '0'
    },
    totalStock: {
        type: String,
        default: '0'
    },
    totalCost: {
        type: String,
        default: '0'
    },
});


const formatDate = (date: string) => {
    const now = new Date();
    const timestamp = new Date(date);
    const diffInSeconds = Math.floor((now.getTime() - timestamp.getTime()) / 1000);

    const minutes = Math.floor(diffInSeconds / 60);
    const hours = Math.floor(diffInSeconds / 3600);
    const days = Math.floor(diffInSeconds / 86400);
    const months = Math.floor(diffInSeconds / 2592000);
    const years = Math.floor(diffInSeconds / 31536000);

    if (years > 0) {
        return `${years} year${years > 1 ? 's' : ''} ago`;
    }
    if (months > 0) {
        return `${months} month${months > 1 ? 's' : ''} ago`;
    }
    if (days > 0) {
        return `${days} day${days > 1 ? 's' : ''} ago`;
    }
    if (hours > 0) {
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    }
    if (minutes > 0) {
        return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    }
    return 'Just now';
};



</script>
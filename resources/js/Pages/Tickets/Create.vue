<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    statuses: Array,
});

const form = useForm({
    title: '',
    description: '',
    status_id: '',
});

const submit = () => {
    form.post(route('tickets.store'));
};
</script>

<template>
    <Head title="Create Ticket" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Create Ticket
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Title
                                </label>

                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />

                                <div v-if="form.errors.title" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.title }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Description
                                </label>

                                <textarea
                                    v-model="form.description"
                                    rows="5"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                ></textarea>

                                <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Status
                                </label>

                                <select
                                    v-model="form.status_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option value="">Select status</option>
                                    <option
                                        v-for="status in statuses"
                                        :key="status.id"
                                        :value="status.id"
                                    >
                                        {{ status.name }}
                                    </option>
                                </select>

                                <div v-if="form.errors.status_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.status_id }}
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="rounded bg-gray-800 px-4 py-2 text-white disabled:opacity-50"
                                >
                                    Save
                                </button>

                                <Link
                                    :href="route('tickets.index')"
                                    class="text-sm text-gray-600 underline"
                                >
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
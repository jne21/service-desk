<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    ticket: Object,
    statuses: Array,
});

const form = useForm({
    title: props.ticket.title ?? '',
    description: props.ticket.description ?? '',
    status_id: props.ticket.status_id ?? '',
});

const submit = () => {
    form.patch(route('tickets.update', props.ticket.id));
};
</script>

<template>
    <Head :title="`Ticket #${ticket.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Ticket #{{ ticket.id }}
                </h2>
                <Link
                    :href="route('tickets.index')"
                    class="text-sm text-gray-600 underline"
                >
                    Back to list
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-6 text-sm text-gray-600">
                            Created by: {{ ticket.user?.name || '—' }} {{ ticket.user?.department?.name || '' }} <span v-if="ticket.user?.role?.name">({{ ticket.user.role.name }})</span>
                        </div>
                        <div class="mb-6 text-sm text-gray-600">
                            Department: {{ ticket.department?.name || '—' }}
                        </div>
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
                                    Status
                                </label>

                                <select
                                    v-model="form.status_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
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

                            <div class="flex items-center gap-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="rounded bg-gray-800 px-4 py-2 text-white disabled:opacity-50"
                                >
                                    Save changes
                                </button>

                                <Link
                                    :href="route('tickets.index')"
                                    class="text-sm text-gray-600 underline"
                                >
                                    Back to list
                                </Link>
                            </div>
                        </form>
                        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                            <div>
                                <div class="text-gray-500">Created</div>
                                <div>{{ ticket.created_at }}</div>
                            </div>

                            <div>
                                <div class="text-gray-500">Updated</div>
                                <div>{{ ticket.updated_at }}</div>
                            </div>
                        </div>

                        <div class="border-t pt-6">
                            <h3 class="font-semibold">
                                Next blocks
                            </h3>

                            <div class="mt-2 text-sm text-gray-600">
                                Тут пізніше будуть коментарі, файли, історія статусів і історія змін.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    tickets: Array,
});
</script>

<template>
    <Head title="Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Tickets
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-4">
                            <Link
                                :href="route('tickets.create')"
                                class="rounded bg-gray-800 px-4 py-2 text-white"
                            >
                                Create ticket
                            </Link>
                        </div>
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="text-left">ID</th>
                                    <th class="text-left">Title</th>
                                    <th class="text-left">Status</th>
                                    <th class="text-left">Created</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="ticket in tickets" :key="ticket.id">
                                    <td>{{ ticket.id }}</td>
                                    <td>{{ ticket.title }}</td>
                                    <td>{{ ticket.status?.name }}</td>
                                    <td>{{ ticket.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="tickets.length === 0" class="mt-4">
                            No tickets yet.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

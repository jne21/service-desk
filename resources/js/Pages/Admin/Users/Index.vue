<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    users: Array,
});
</script>

<template>
    <Head title="Users" />

    <AdmindLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Users
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-4">
                            <Link
                                :href="route('admin.users.create')"
                                class="rounded bg-gray-800 px-4 py-2 text-white"
                            >
                                Create user
                            </Link>
                        </div>
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="text-left">Id</th>
                                    <th class="text-left">Name</th>
                                    <th class="text-left">Role</th>
                                    <th class="text-left">Department</th>
                                    <th class="text-left">Created</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="user in users.data" :key="user.id">
                                    <td>{{ user.id }}</td>
                                    <td>
                                        <Link
                                            :href="route('admin.users.show', user.id)"
                                            class="text-blue-600 underline"
                                        >
                                            {{ user.name }}
                                        </Link>
                                    </td>
                                    <td>{{ user.role?.name || '—' }}</td>
                                    <td>{{ user.department?.name || '—' }}</td>
                                    <td class="table-date">{{ $formatDate(user.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="users.links.length > 3" class="mt-6 flex flex-wrap gap-2">
                            <Link
                                v-for="link in users.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                v-html="link.label"
                                class="rounded border px-3 py-1 text-sm"
                                :class="{
                                    'bg-gray-800 text-white': link.active,
                                    'text-gray-400 pointer-events-none': !link.url,
                                }"
                            />
                        </div>
                        <div v-if="users.data.length === 0" class="mt-4">
                            No users yet.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdmindLayout>
</template>

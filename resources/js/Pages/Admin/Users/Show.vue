<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    roles: Array,
    departments: Array,
});

const form = useForm({
    title: props.user.name ?? '',
    department_id: props.user.department_id ?? '',
    role_id: props.user.role_id ?? '',
});

const submit = () => {
    form.patch(route('admin.users.update', props.user.id));
};
</script>

<template>
    <Head :title="`User #${user.id}`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    User #{{ ticket.id }}
                </h2>
                <Link
                    :href="route('admin.users.index')"
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
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Name
                                </label>

                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />

                                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Role
                                </label>

                                <select
                                    v-model="form.role_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option
                                        v-for="role in roles"
                                        :key="role.id"
                                        :value="role.id"
                                    >
                                        {{ role.name }}
                                    </option>
                                </select>

                                <div v-if="form.errors.role_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.role_id }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Department
                                </label>

                                <select
                                    v-model="form.department_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option
                                        v-for="department in departments"
                                        :key="department.id"
                                        :value="department.id"
                                    >
                                        {{ department.name }}
                                    </option>
                                </select>

                                <div v-if="form.errors.department_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.department_id }}
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
                                <div>{{ $formatDate(user.created_at) }}</div>
                            </div>

                            <div>
                                <div class="text-gray-500">Updated</div>
                                <div>{{ $formatDate(user.updated_at) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

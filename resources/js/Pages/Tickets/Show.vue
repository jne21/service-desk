<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    ticket: Object,
});
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
                    <div class="p-6 text-gray-900 space-y-6">
                        <div>
                            <div class="text-sm text-gray-500">Title</div>
                            <div class="text-lg font-semibold">
                                {{ ticket.title }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500">Status</div>
                            <div>
                                {{ ticket.status?.name }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500">Description</div>
                            <div class="whitespace-pre-line">
                                {{ ticket.description || '—' }}
                            </div>
                        </div>

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

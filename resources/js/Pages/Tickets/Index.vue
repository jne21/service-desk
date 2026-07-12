<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

/** Real time notification support */
const props = defineProps({
    realtime: Object,
});

const notification = ref(null);

let notificationTimer = null;

const showNotification = (message) => {
    notification.value = message;

    if (notificationTimer) {
        clearTimeout(notificationTimer);
    }

    notificationTimer = setTimeout(() => {
        notification.value = null;
    }, 5000);
};

/** Real time syncronization support */

const highlightedTicketIds = ref([]);

const highlightTicket = (ticketId) => {
    highlightedTicketIds.value = [
        ...highlightedTicketIds.value.filter((id) => id !== ticketId),
        ticketId,
    ];

    setTimeout(() => {
        highlightedTicketIds.value = highlightedTicketIds.value.filter(
            (id) => id !== ticketId,
        );
    }, 5000);
};

const isHighlighted = (ticketId) => {
    return highlightedTicketIds.value.includes(ticketId);
};

const prependTicket = (ticket) => {
    tickets.value = [
        ticket,
        ...tickets.value.filter((item) => item.id !== ticket.id),
    ].slice(0, pagination.value.perPage);
};

const replaceTicket = (ticket) => {
    tickets.value = tickets.value.map((item) => {
        if (item.id !== ticket.id) {
            return item;
        }

        return ticket;
    });
};

const removeTicket = (ticketId) => {
    tickets.value = tickets.value.filter((item) => item.id !== ticketId);
};

const hasTicketInCurrentList = (ticketId) => {
    return tickets.value.some((item) => item.id === ticketId);
};

/** Ticket Changed handler */

const handleTicketChanged = (event) => {
    if (event.type === 'created' || event.type === 'restored') {
        showNotification('Є нова або відновлена заявка.');

        if (pagination.value.currentPage === 1 && event.ticket) {
            prependTicket(event.ticket);
            highlightTicket(event.ticket.id);
        }

        return;
    }

    if (event.type === 'updated') {
        showNotification('Заявку оновлено.');

        if (event.ticket && hasTicketInCurrentList(event.ticket.id)) {
            replaceTicket(event.ticket);
            highlightTicket(event.ticket.id);
        }

        return;
    }

    if (
        event.type === 'deleted'
        || event.type === 'removed_from_access'
    ) {
        showNotification('Заявка більше не доступна у цьому списку.');

        removeTicket(event.ticket_id);

        return;
    }
};

/** UX */

const tickets = ref([]);
const pagination = ref({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
});

const loading = ref(false);
const error = ref(null);

const hasTickets = computed(() => tickets.value.length > 0);

const canGoPrevious = computed(() => pagination.value.currentPage > 1);
const canGoNext = computed(
    () => pagination.value.currentPage < pagination.value.lastPage,
);

const loadTickets = async (page = 1) => {
    loading.value = true;
    error.value = null;

    try {
        const response = await window.axios.get('/api/user/tickets', {
            params: {
                page,
                per_page: pagination.value.perPage,
            },
        });

        tickets.value = response.data.tickets;
        pagination.value = response.data.pagination;
    } catch (e) {
        error.value = e.response?.data?.error
            ?? 'Не вдалося завантажити список заявок.';
    } finally {
        loading.value = false;
    }
};

const goToPage = (page) => {
    if (page < 1 || page > pagination.value.lastPage) {
        return;
    }

    loadTickets(page);
};

onMounted(() => {
    loadTickets();

    if (props.realtime?.enabled && props.realtime?.channel) {
        window.Echo
            .private(props.realtime.channel)
            .listen('.ticket.changed', handleTicketChanged);
    }
});

onUnmounted(() => {
    if (props.realtime?.enabled && props.realtime?.channel) {
        window.Echo.leave(`private-${props.realtime.channel}`);
    }

    if (notificationTimer) {
        clearTimeout(notificationTimer);
    }
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

                        <div
                            v-if="error"
                            class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700"
                        >
                            {{ error }}
                        </div>
                        <div
                            v-if="notification"
                            class="mb-4 rounded border border-blue-200 bg-blue-50 p-3 text-sm text-blue-700"
                        >
                            {{ notification }}
                        </div>

                        <div
                            v-if="loading"
                            class="mb-4 text-sm text-gray-500"
                        >
                            Loading tickets...
                        </div>

                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="text-left">ID</th>
                                    <th class="text-left">Title</th>
                                    <th class="text-left">Status</th>
                                    <th class="text-left">Created</th>
                                    <th class="text-left">Author</th>
                                    <th class="text-left">Department</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="ticket in tickets"
                                    :key="ticket.id"
                                    :class="{
                                        'bg-yellow-100 transition-colors duration-500': isHighlighted(ticket.id),
                                    }"
                                >
                                    <td>{{ ticket.id }}</td>

                                    <td>
                                        <Link
                                            :href="route('tickets.show', ticket.id)"
                                            class="text-blue-600 underline"
                                        >
                                            {{ ticket.title }}
                                        </Link>
                                    </td>

                                    <td>{{ ticket.status?.name || '—' }}</td>

                                    <td class="table-date">
                                        {{ $formatDate(ticket.created_at) }}
                                    </td>

                                    <td>{{ ticket.created_by?.name || '—' }}</td>

                                    <td>{{ ticket.department?.name || '—' }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div
                            v-if="!loading && !hasTickets"
                            class="mt-4"
                        >
                            No tickets yet.
                        </div>

                        <div
                            v-if="pagination.lastPage > 1"
                            class="mt-6 flex items-center gap-2"
                        >
                            <button
                                type="button"
                                class="rounded border px-3 py-1 text-sm"
                                :class="{
                                    'text-gray-400 pointer-events-none': !canGoPrevious,
                                }"
                                :disabled="!canGoPrevious || loading"
                                @click="goToPage(pagination.currentPage - 1)"
                            >
                                Previous
                            </button>

                            <span class="text-sm text-gray-600">
                                Page {{ pagination.currentPage }} of {{ pagination.lastPage }}
                            </span>

                            <button
                                type="button"
                                class="rounded border px-3 py-1 text-sm"
                                :class="{
                                    'text-gray-400 pointer-events-none': !canGoNext,
                                }"
                                :disabled="!canGoNext || loading"
                                @click="goToPage(pagination.currentPage + 1)"
                            >
                                Next
                            </button>
                        </div>

                        <div class="mt-4 text-sm text-gray-500">
                            Total: {{ pagination.total }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
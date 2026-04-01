<script setup>
import {
    createColumnHelper,
    FlexRender,
    getCoreRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import { computed, reactive } from 'vue';
import RoleDashboardLayout from '@/Layouts/RoleDashboardLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    reservations: {
        type: Object,
        default: () => ({
            data: [],
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            per_page: 10,
            search: '',
            status: 'all',
        }),
    },
});

const page = usePage();

const reservationRows = computed(() => props.reservations?.data ?? []);
const currentPage = computed(() => props.reservations?.current_page ?? 1);
const lastPage = computed(() => props.reservations?.last_page ?? 1);
const totalReservations = computed(() => props.reservations?.total ?? 0);
const perPage = computed(() => Number(props.reservations?.per_page ?? props.filters?.per_page ?? 10));
const filterState = reactive({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? 'all',
    per_page: Number(props.filters?.per_page ?? 10),
});

const paginationState = computed(() => ({
    pageIndex: Math.max(currentPage.value - 1, 0),
    pageSize: perPage.value,
}));

const columnHelper = createColumnHelper();

const formatPrice = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value ?? 0);
};

const columns = [
    columnHelper.accessor('id', {
        header: 'ID',
        cell: (info) => `#${info.getValue()}`,
    }),
    columnHelper.accessor('client_name', {
        header: 'Client',
        cell: (info) => info.getValue() ?? 'N/A',
    }),
    columnHelper.accessor('client_email', {
        header: 'Email',
        cell: (info) => info.getValue() ?? 'N/A',
    }),
    columnHelper.accessor('room_number', {
        header: 'Room',
        cell: (info) => info.getValue() ?? 'N/A',
    }),
    columnHelper.accessor('check_in_date', {
        header: 'Check-In',
        cell: (info) => info.getValue() ?? 'N/A',
    }),
    columnHelper.accessor('check_out_date', {
        header: 'Check-Out',
        cell: (info) => info.getValue() ?? 'N/A',
    }),
    columnHelper.accessor('accompany_number', {
        header: 'Accompany',
        cell: (info) => info.getValue() ?? 0,
    }),
    columnHelper.accessor('paid_price', {
        header: 'Paid Price',
        cell: (info) => formatPrice(info.getValue()),
    }),
    columnHelper.display({
        id: 'status',
        header: 'Status',
    }),
    columnHelper.display({
        id: 'actions',
        header: 'Actions',
    }),
    columnHelper.accessor('created_at', {
        header: 'Reserved At',
        cell: (info) => info.getValue() ?? 'N/A',
    }),
];

const table = useVueTable({
    get data() {
        return reservationRows.value;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    get pageCount() {
        return lastPage.value;
    },
    state: {
        get pagination() {
            return paginationState.value;
        },
    },
});

const loadPage = (nextPage, nextPerPage = perPage.value) => {
    router.get(
        route('reservations.clients.index'),
        {
            page: nextPage,
            per_page: nextPerPage,
            search: filterState.search,
            status: filterState.status,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const changePerPage = (event) => {
    filterState.per_page = Number(event.target.value);
    loadPage(1, filterState.per_page);
};

const applyFilters = () => {
    loadPage(1, filterState.per_page);
};

const resetFilters = () => {
    filterState.search = '';
    filterState.status = 'all';
    filterState.per_page = 10;
    loadPage(1, filterState.per_page);
};

const toggleStatus = (reservation) => {
    router.patch(
        route('reservations.clients.status', reservation.id),
        {
            is_active: !reservation.is_active,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};
</script>

<template>
    <Head title="Clients Reservations" />

    <RoleDashboardLayout>
        <div class="h-full overflow-y-auto bg-slate-100 p-6 md:p-10">
            <div class="mx-auto max-w-6xl">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-slate-900">Clients Reservations</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Global reservations view for receptionists, managers, and admins.
                    </p>
                </div>

                <div class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                        <input
                            v-model="filterState.search"
                            type="text"
                            class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                            placeholder="Reservation ID, client name/email, room number"
                        >
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                        <select
                            v-model="filterState.status"
                            class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                        >
                            <option value="all">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-slate-900 px-3 py-2 text-sm font-semibold text-white"
                            @click="applyFilters"
                        >
                            Apply
                        </button>
                        <button
                            type="button"
                            class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700"
                            @click="resetFilters"
                        >
                            Reset
                        </button>
                    </div>
                </div>

                <div
                    v-if="page.props.flash?.success"
                    class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                >
                    {{ page.props.flash.success }}
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                                <th
                                    v-for="header in headerGroup.headers"
                                    :key="header.id"
                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    <FlexRender
                                        v-if="!header.isPlaceholder"
                                        :render="header.column.columnDef.header"
                                        :props="header.getContext()"
                                    />
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200">
                            <tr
                                v-for="row in table.getRowModel().rows"
                                :key="row.id"
                                class="hover:bg-slate-50"
                            >
                                <td
                                    v-for="cell in row.getVisibleCells()"
                                    :key="cell.id"
                                    class="px-5 py-4 text-sm text-slate-600"
                                >
                                    <span
                                        v-if="cell.column.id === 'status'"
                                        class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="row.original.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700'"
                                    >
                                        {{ row.original.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <button
                                        v-else-if="cell.column.id === 'actions'"
                                        type="button"
                                        class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                        @click="toggleStatus(row.original)"
                                    >
                                        {{ row.original.is_active ? 'Mark Inactive' : 'Mark Active' }}
                                    </button>
                                    <FlexRender
                                        v-else
                                        :render="cell.column.columnDef.cell"
                                        :props="cell.getContext()"
                                    />
                                </td>
                            </tr>

                            <tr v-if="table.getRowModel().rows.length === 0">
                                <td
                                    colspan="11"
                                    class="px-5 py-8 text-center text-sm text-slate-500"
                                >
                                    No reservations found.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            Showing {{ reservationRows.length }} of {{ totalReservations }} reservations.
                        </div>

                        <div class="flex items-center gap-3">
                            <label class="text-slate-500" for="clients-reservations-per-page">Rows per page</label>
                            <select
                                id="clients-reservations-per-page"
                                class="rounded-md border border-slate-300 px-2 py-1"
                                :value="perPage"
                                @change="changePerPage"
                            >
                                <option :value="5">5</option>
                                <option :value="10">10</option>
                                <option :value="15">15</option>
                                <option :value="20">20</option>
                            </select>

                            <button
                                type="button"
                                class="rounded-md border border-slate-300 px-3 py-1 disabled:opacity-50"
                                :disabled="currentPage <= 1"
                                @click="loadPage(currentPage - 1)"
                            >
                                Previous
                            </button>
                            <span>Page {{ currentPage }} of {{ lastPage }}</span>
                            <button
                                type="button"
                                class="rounded-md border border-slate-300 px-3 py-1 disabled:opacity-50"
                                :disabled="currentPage >= lastPage"
                                @click="loadPage(currentPage + 1)"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </RoleDashboardLayout>
</template>

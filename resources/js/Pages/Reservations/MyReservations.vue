<script setup>
import {
    createColumnHelper,
    FlexRender,
    getCoreRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import { computed } from 'vue';
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
        }),
    },
});

const page = usePage();

const reservationRows = computed(() => props.reservations?.data ?? []);
const currentPage = computed(() => props.reservations?.current_page ?? 1);
const lastPage = computed(() => props.reservations?.last_page ?? 1);
const totalReservations = computed(() => props.reservations?.total ?? 0);
const perPage = computed(() => Number(props.reservations?.per_page ?? props.filters?.per_page ?? 10));

const paginationState = computed(() => ({
    pageIndex: Math.max(currentPage.value - 1, 0),
    pageSize: perPage.value,
}));

const columnHelper = createColumnHelper();

const formatPrice = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format((Number(value ?? 0)) / 100);
};

const columns = [
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
    columnHelper.accessor('nights', {
        header: 'Nights',
        cell: (info) => info.getValue() ?? 'N/A',
    }),
    columnHelper.accessor('capacity', {
        header: 'Capacity',
        cell: (info) => info.getValue() ?? 'N/A',
    }),
    columnHelper.accessor('accompany_number', {
        header: 'Accompany',
        cell: (info) => info.getValue() ?? 'N/A',
    }),
    columnHelper.accessor('paid_price', {
        header: 'Paid Price',
        cell: (info) => formatPrice(info.getValue()),
    }),
    columnHelper.display({
        id: 'status',
        header: 'Status',
    }),
    columnHelper.accessor('created_at', {
        header: 'Created At',
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
        route('reservations.index'),
        {
            page: nextPage,
            per_page: nextPerPage,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const changePerPage = (event) => {
    loadPage(1, Number(event.target.value));
};
</script>

<template>
    <Head title="My Reservations" />

    <RoleDashboardLayout>
        <div class="h-full overflow-y-auto bg-slate-100 p-6 md:p-10">
            <div class="mx-auto max-w-6xl">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-slate-900">My Reservations</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        A complete view of reservations created from your account.
                    </p>
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
                                        :class="row.original.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'"
                                    >
                                        {{ row.original.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <FlexRender
                                        v-else
                                        :render="cell.column.columnDef.cell"
                                        :props="cell.getContext()"
                                    />
                                </td>
                            </tr>

                            <tr v-if="table.getRowModel().rows.length === 0">
                                <td
                                    colspan="9"
                                    class="px-5 py-8 text-center text-sm text-slate-500"
                                >
                                    No reservations yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            Showing {{ reservationRows.length }} of {{ totalReservations }} reservations.
                        </div>

                        <div class="flex items-center gap-3">
                            <label class="text-slate-500" for="reservations-per-page">Rows per page</label>
                            <select
                                id="reservations-per-page"
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

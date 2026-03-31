<script setup>
import {
    createColumnHelper,
    FlexRender,
    getCoreRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import { computed } from 'vue';
import RoleDashboardLayout from '@/Layouts/RoleDashboardLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    rooms: {
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

const roomRows = computed(() => props.rooms?.data ?? []);
const currentPage = computed(() => props.rooms?.current_page ?? 1);
const lastPage = computed(() => props.rooms?.last_page ?? 1);
const totalRooms = computed(() => props.rooms?.total ?? 0);
const perPage = computed(() => Number(props.rooms?.per_page ?? props.filters?.per_page ?? 10));

const paginationState = computed(() => ({
    pageIndex: Math.max(currentPage.value - 1, 0),
    pageSize: perPage.value,
}));

const columnHelper = createColumnHelper();

const columns = [
    columnHelper.accessor('number', {
        header: 'Room Number',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('floor', {
        header: 'Floor',
        cell: (info) => info.getValue() ?? 'N/A',
    }),
    columnHelper.accessor('price', {
        header: 'Price',
        cell: (info) => formatPrice(info.getValue()),
    }),
    columnHelper.accessor('capacity', {
        header: 'Capacity',
        cell: (info) => info.getValue(),
    }),
    columnHelper.display({
        id: 'action',
        header: 'Action',
    }),
];

const table = useVueTable({
    get data() {
        return roomRows.value;
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
        route('reservations.create'),
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

const formatPrice = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format((Number(value ?? 0)) / 100);
};
</script>

<template>
    <Head title="Make Reservation" />

    <RoleDashboardLayout>
        <div class="h-full overflow-y-auto bg-slate-100 p-6 md:p-10">
            <div class="mx-auto max-w-6xl">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-slate-900">Make Reservation</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Available rooms only. Reserved rooms are automatically excluded.
                    </p>
                </div>

                <div
                    v-if="page.props.flash?.success"
                    class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                >
                    {{ page.props.flash.success }}
                </div>

                <div
                    v-if="page.props.flash?.error"
                    class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                >
                    {{ page.props.flash.error }}
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
                                    <Link
                                        v-if="cell.column.id === 'action'"
                                        :href="route('reservations.rooms.show', { roomId: row.original.id })"
                                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        Make Reservation
                                    </Link>
                                    <FlexRender
                                        v-else
                                        :render="cell.column.columnDef.cell"
                                        :props="cell.getContext()"
                                    />
                                </td>
                            </tr>

                            <tr v-if="table.getRowModel().rows.length === 0">
                                <td
                                    colspan="5"
                                    class="px-5 py-8 text-center text-sm text-slate-500"
                                >
                                    No available rooms at the moment.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            Showing {{ roomRows.length }} of {{ totalRooms }} rooms.
                        </div>

                        <div class="flex items-center gap-3">
                            <label class="text-slate-500" for="rooms-per-page">Rows per page</label>
                            <select
                                id="rooms-per-page"
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

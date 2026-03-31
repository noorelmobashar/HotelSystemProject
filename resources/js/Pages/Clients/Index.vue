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
    clients: {
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
            search: '',
            status: 'all',
            per_page: 10,
        }),
    },
    canApprove: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const clientsRows = computed(() => props.clients?.data ?? []);
const currentPage = computed(() => props.clients?.current_page ?? 1);
const lastPage = computed(() => props.clients?.last_page ?? 1);
const totalClients = computed(() => props.clients?.total ?? 0);
const perPage = computed(() => Number(props.clients?.per_page ?? props.filters?.per_page ?? 10));

const filterState = reactive({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? 'all',
});

const paginationState = computed(() => ({
    pageIndex: Math.max(currentPage.value - 1, 0),
    pageSize: perPage.value,
}));

const columnHelper = createColumnHelper();

const columns = [
    columnHelper.accessor('name', {
        header: 'Name',
    }),
    columnHelper.accessor('email', {
        header: 'Email',
    }),
    columnHelper.accessor('national_id', {
        header: 'National ID',
        cell: (info) => info.getValue() ?? 'N/A',
    }),
    columnHelper.accessor('gender', {
        header: 'Gender',
        cell: (info) => info.getValue() ?? 'N/A',
    }),
    columnHelper.accessor('approved_by_name', {
        header: 'Approved By',
        cell: (info) => info.getValue() ?? 'N/A',
    }),
    columnHelper.accessor('approved_at', {
        header: 'Approved At',
        cell: (info) => info.getValue() ?? 'Pending',
    }),
    columnHelper.accessor('created_at', {
        header: 'Created At',
        cell: (info) => info.getValue() ?? 'N/A',
    }),
    columnHelper.display({
        id: 'actions',
        header: 'Actions',
    }),
];

const table = useVueTable({
    get data() {
        return clientsRows.value;
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
        route('clients.index'),
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

const applyFilters = () => {
    loadPage(1);
};

const clearFilters = () => {
    filterState.search = '';
    filterState.status = 'all';
    loadPage(1);
};

const changePerPage = (event) => {
    loadPage(1, Number(event.target.value));
};

const approveClient = (clientId) => {
    router.put(
        route('clients.approve', { client: clientId }),
        {},
        {
            preserveScroll: true,
            onSuccess: () => loadPage(currentPage.value),
        },
    );
};
</script>

<template>
    <Head title="Manage Clients" />

    <RoleDashboardLayout>
        <div class="h-full overflow-y-auto bg-slate-100 p-6 md:p-10">
            <div class="mx-auto max-w-7xl">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-slate-900">Manage Clients</h1>
                    <p class="mt-1 text-sm text-slate-500">Review, filter, and approve client accounts.</p>
                </div>

                <div
                    v-if="page.props.flash?.success"
                    class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                >
                    {{ page.props.flash.success }}
                </div>

                <div class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-[2fr,1fr,auto,auto]">
                    <input
                        v-model="filterState.search"
                        type="text"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                        placeholder="Search by name, email, national id, or gender"
                    />

                    <select
                        v-model="filterState.status"
                        class="rounded-md border border-slate-300 px-3 py-2 text-sm"
                    >
                        <option value="all">All</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                    </select>

                    <button
                        type="button"
                        class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                        @click="applyFilters"
                    >
                        Apply
                    </button>

                    <button
                        type="button"
                        class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        @click="clearFilters"
                    >
                        Clear
                    </button>
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
                                    <button
                                        v-if="cell.column.id === 'actions'"
                                        type="button"
                                        class="rounded-md px-3 py-1.5 text-xs font-semibold"
                                        :class="(!props.canApprove || row.original.approved_at) ? 'cursor-not-allowed bg-slate-200 text-slate-500' : 'bg-emerald-600 text-white hover:bg-emerald-500'"
                                        :disabled="!props.canApprove || !!row.original.approved_at"
                                        @click="approveClient(row.original.id)"
                                    >
                                        {{ row.original.approved_at ? 'Approved' : 'Approve' }}
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
                                    colspan="8"
                                    class="px-5 py-8 text-center text-sm text-slate-500"
                                >
                                    No clients found.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            Showing {{ clientsRows.length }} of {{ totalClients }} clients.
                        </div>

                        <div class="flex items-center gap-3">
                            <label class="text-slate-500" for="clients-per-page">Rows per page</label>
                            <select
                                id="clients-per-page"
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

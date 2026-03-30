<script setup>
import RoleDashboardLayout from '@/Layouts/RoleDashboardLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import {
    FlexRender,
    getCoreRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import { computed, h, reactive, ref } from 'vue';

const props = defineProps({
    receptionists: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            per_page: 10,
            sort_by: 'created_at',
            sort_dir: 'desc',
        }),
    },
});

const filterState = reactive({
    search: props.filters.search ?? '',
    per_page: Number(props.filters.per_page ?? 10),
    sort_by: props.filters.sort_by ?? 'created_at',
    sort_dir: props.filters.sort_dir ?? 'desc',
});

const createForm = useForm({
    name: '',
    email: '',
    national_id: '',
    gender: '',
    password: '',
    password_confirmation: '',
});

const editForm = useForm({
    id: null,
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showCreateForm = ref(false);

const pagination = computed(() => props.receptionists ?? {
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    from: null,
    to: null,
});

const tableData = computed(() => pagination.value.data ?? []);

const formatDate = (value) => value || 'Never';

const columns = computed(() => [
    {
        header: 'Name',
        accessorKey: 'name',
        meta: { sortKey: 'name' },
        cell: ({ getValue }) => h('p', { class: 'min-w-[180px] text-sm font-semibold text-slate-700' }, getValue()),
    },
    {
        header: 'Email',
        accessorKey: 'email',
        meta: { sortKey: 'email' },
    },
    {
        header: 'National ID',
        accessorKey: 'national_id',
        meta: { sortKey: 'national_id' },
        cell: ({ getValue }) => getValue() || 'Not set',
    },
    {
        header: 'Gender',
        accessorKey: 'gender',
        meta: { sortKey: 'gender' },
        cell: ({ getValue }) => getValue() || 'Not set',
    },
    {
        header: 'Last Login',
        accessorKey: 'last_login_at',
        meta: { sortKey: 'last_login_at' },
        cell: ({ getValue }) => formatDate(getValue()),
    },
    {
        header: 'Created At',
        accessorKey: 'created_at',
        meta: { sortKey: 'created_at' },
        cell: ({ getValue }) => getValue(),
    },
]);

const table = useVueTable({
    get data() {
        return tableData.value;
    },
    get columns() {
        return columns.value;
    },
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
});

const reload = (page = 1) => {
    router.get(route('receptionists.index'), {
        search: filterState.search,
        per_page: filterState.per_page,
        sort_by: filterState.sort_by,
        sort_dir: filterState.sort_dir,
        page,
    }, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filterState.search = '';
    filterState.per_page = 10;
    filterState.sort_by = 'created_at';
    filterState.sort_dir = 'desc';
    reload(1);
};

const sortIndicator = (sortKey) => {
    if (filterState.sort_by !== sortKey) {
        return '↕';
    }

    return filterState.sort_dir === 'asc' ? '↑' : '↓';
};

const toggleSort = (sortKey) => {
    if (filterState.sort_by === sortKey) {
        filterState.sort_dir = filterState.sort_dir === 'asc' ? 'desc' : 'asc';
    } else {
        filterState.sort_by = sortKey;
        filterState.sort_dir = 'asc';
    }

    reload(1);
};

const submitCreate = () => {
    createForm.post(route('receptionists.store'), {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
            showCreateForm.value = false;
        },
    });
};

const startEdit = (row) => {
    editForm.id = row.id;
    editForm.name = row.name;
    editForm.email = row.email;
    editForm.password = '';
    editForm.password_confirmation = '';
};

const cancelEdit = () => {
    editForm.reset();
    editForm.clearErrors();
    editForm.id = null;
};

const submitEdit = () => {
    editForm.put(route('receptionists.update', editForm.id), {
        preserveScroll: true,
        onSuccess: () => {
            cancelEdit();
        },
    });
};

const deleteReceptionist = async (id) => {
    if (!window.confirm('Warning: this action permanently deletes the receptionist account. Continue?')) {
        return;
    }

    await axios.delete(route('receptionists.destroy', id), {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    const currentPage = Number(pagination.value.current_page ?? 1);
    reload(currentPage);
};
</script>

<template>
    <Head title="Manage Receptionists" />

    <RoleDashboardLayout>
        <div class="h-full overflow-y-auto bg-gradient-to-br from-slate-100 via-slate-100 to-slate-200/60 px-6 py-8 lg:px-10">
            <div class="mx-auto max-w-7xl space-y-6">
                <div class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.95)] md:flex-row md:items-end md:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900">Manage Receptionists</h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Create, update, and remove reception desk accounts.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700"
                        @click="showCreateForm = !showCreateForm"
                    >
                        {{ showCreateForm ? 'Close Create Form' : 'Add Receptionist' }}
                    </button>
                </div>

                <div
                    v-if="showCreateForm"
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.95)]"
                >
                    <h2 class="text-lg font-semibold text-slate-800">Create Receptionist</h2>

                    <form class="mt-5 grid gap-4 md:grid-cols-2" @submit.prevent="submitCreate">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Full Name</label>
                            <input v-model="createForm.name" type="text" class="mt-1 w-full rounded-xl border-slate-300 text-sm" required>
                            <p v-if="createForm.errors.name" class="mt-1 text-xs text-rose-600">{{ createForm.errors.name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Email</label>
                            <input v-model="createForm.email" type="email" class="mt-1 w-full rounded-xl border-slate-300 text-sm" required>
                            <p v-if="createForm.errors.email" class="mt-1 text-xs text-rose-600">{{ createForm.errors.email }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">National ID</label>
                            <input v-model="createForm.national_id" type="text" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                            <p v-if="createForm.errors.national_id" class="mt-1 text-xs text-rose-600">{{ createForm.errors.national_id }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Gender</label>
                            <select v-model="createForm.gender" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                            <p v-if="createForm.errors.gender" class="mt-1 text-xs text-rose-600">{{ createForm.errors.gender }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Password</label>
                            <input v-model="createForm.password" type="password" class="mt-1 w-full rounded-xl border-slate-300 text-sm" required>
                            <p v-if="createForm.errors.password" class="mt-1 text-xs text-rose-600">{{ createForm.errors.password }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Confirm Password</label>
                            <input v-model="createForm.password_confirmation" type="password" class="mt-1 w-full rounded-xl border-slate-300 text-sm" required>
                        </div>

                        <div class="md:col-span-2">
                            <button
                                type="submit"
                                class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700"
                                :disabled="createForm.processing"
                            >
                                Create Receptionist
                            </button>
                        </div>
                    </form>
                </div>

                <div
                    v-if="editForm.id"
                    class="rounded-3xl border border-teal-200 bg-white p-6 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.95)]"
                >
                    <h2 class="text-lg font-semibold text-slate-800">Edit Receptionist</h2>

                    <form class="mt-5 grid gap-4 md:grid-cols-2" @submit.prevent="submitEdit">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Full Name</label>
                            <input v-model="editForm.name" type="text" class="mt-1 w-full rounded-xl border-slate-300 text-sm" required>
                            <p v-if="editForm.errors.name" class="mt-1 text-xs text-rose-600">{{ editForm.errors.name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Email</label>
                            <input v-model="editForm.email" type="email" class="mt-1 w-full rounded-xl border-slate-300 text-sm" required>
                            <p v-if="editForm.errors.email" class="mt-1 text-xs text-rose-600">{{ editForm.errors.email }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">New Password (optional)</label>
                            <input v-model="editForm.password" type="password" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                            <p v-if="editForm.errors.password" class="mt-1 text-xs text-rose-600">{{ editForm.errors.password }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Confirm New Password</label>
                            <input v-model="editForm.password_confirmation" type="password" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                        </div>

                        <div class="flex gap-2 md:col-span-2">
                            <button
                                type="submit"
                                class="rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-500"
                                :disabled="editForm.processing"
                            >
                                Save Changes
                            </button>
                            <button
                                type="button"
                                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                                @click="cancelEdit"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.95)]">
                    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Search</label>
                                <input
                                    v-model="filterState.search"
                                    type="text"
                                    class="mt-1 w-full rounded-xl border-slate-300 text-sm"
                                    placeholder="Name, email, national ID"
                                >
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Per Page</label>
                                <select v-model.number="filterState.per_page" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                                    <option :value="10">10</option>
                                    <option :value="20">20</option>
                                    <option :value="50">50</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700"
                                @click="reload(1)"
                            >
                                Apply
                            </button>
                            <button
                                type="button"
                                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                                @click="resetFilters"
                            >
                                Reset
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr
                                    v-for="headerGroup in table.getHeaderGroups()"
                                    :key="headerGroup.id"
                                >
                                    <th
                                        v-for="header in headerGroup.headers"
                                        :key="header.id"
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                    >
                                        <button
                                            v-if="header.column.columnDef.meta?.sortKey"
                                            type="button"
                                            class="inline-flex items-center gap-1 hover:text-slate-700"
                                            @click="toggleSort(header.column.columnDef.meta.sortKey)"
                                        >
                                            <FlexRender
                                                :render="header.column.columnDef.header"
                                                :props="header.getContext()"
                                            />
                                            <span>{{ sortIndicator(header.column.columnDef.meta.sortKey) }}</span>
                                        </button>
                                        <FlexRender
                                            v-else
                                            :render="header.column.columnDef.header"
                                            :props="header.getContext()"
                                        />
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200 bg-white">
                                <tr
                                    v-for="row in table.getRowModel().rows"
                                    :key="row.id"
                                    class="hover:bg-slate-50"
                                >
                                    <td
                                        v-for="cell in row.getVisibleCells()"
                                        :key="cell.id"
                                        class="px-4 py-3 text-sm text-slate-600"
                                    >
                                        <FlexRender
                                            :render="cell.column.columnDef.cell"
                                            :props="cell.getContext()"
                                        />
                                    </td>

                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex gap-2">
                                            <button
                                                type="button"
                                                class="rounded-lg border border-teal-300 px-3 py-1.5 text-xs font-semibold text-teal-700 transition hover:bg-teal-50"
                                                @click="startEdit(row.original)"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                class="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50"
                                                @click="deleteReceptionist(row.original.id)"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="table.getRowModel().rows.length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">
                                        No receptionists found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex flex-col gap-3 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
                        <p>
                            Showing {{ pagination.from ?? 0 }} to {{ pagination.to ?? 0 }} of {{ pagination.total ?? 0 }} receptionists
                        </p>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="pagination.current_page <= 1"
                                @click="reload(pagination.current_page - 1)"
                            >
                                Previous
                            </button>
                            <span class="font-medium text-slate-700">Page {{ pagination.current_page }} / {{ pagination.last_page }}</span>
                            <button
                                type="button"
                                class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="pagination.current_page >= pagination.last_page"
                                @click="reload(pagination.current_page + 1)"
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

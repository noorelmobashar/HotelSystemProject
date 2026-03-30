<script setup>
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    FlexRender,
    createColumnHelper,
    getCoreRowModel,
    useVueTable,
} from "@tanstack/vue-table";
import RoleDashboardLayout from "@/Layouts/RoleDashboardLayout.vue";

const props = defineProps({
    rooms: {
        type: Object,
        default: () => ({
            data: [],
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
            from: 0,
            to: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: "",
            per_page: 10,
            sort_by: null,
            sort_dir: "asc",
        }),
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
    canCreate: {
        type: Boolean,
        default: false,
    },
});

const rows = computed(() =>
    Array.isArray(props.rooms?.data) ? props.rooms.data : [],
);

const meta = computed(() => ({
    currentPage: Number(props.rooms?.current_page ?? 1),
    lastPage: Number(props.rooms?.last_page ?? 1),
    perPage: Number(props.rooms?.per_page ?? 10),
    total: Number(props.rooms?.total ?? 0),
    from: Number(props.rooms?.from ?? 0),
    to: Number(props.rooms?.to ?? 0),
}));

const search = ref(String(props.filters?.search ?? ""));
const perPage = ref(Number(props.filters?.per_page ?? meta.value.perPage));
const sortBy = ref(String(props.filters?.sort_by ?? ""));
const sortDir = ref(String(props.filters?.sort_dir ?? "asc"));
const loading = ref(false);
const deletingRoomId = ref(null);
const deleteCandidate = ref(null);
const deleteModalOpen = ref(false);
const deleteMessage = ref("");
const deleteMessageTone = ref("error");
const isAdmin = computed(() => Boolean(props.isAdmin));
const canCreate = computed(() => Boolean(props.canCreate));
const hasManageableRows = computed(() =>
    rows.value.some((row) => Boolean(row.can_manage)),
);
const searchPlaceholder = computed(() =>
    isAdmin.value
        ? "Search by room number, floor, or manager"
        : "Search by room number, capacity, or floor",
);

const columnHelper = createColumnHelper();

const columns = computed(() => {
    const baseColumns = [
        columnHelper.accessor("number", {
            header: "Number",
            cell: (info) => info.getValue() ?? "-",
        }),
        columnHelper.accessor("capacity", {
            header: "Capacity",
            cell: (info) => info.getValue() ?? "-",
        }),
        columnHelper.accessor("price", {
            header: "Price",
            cell: (info) => formatPrice(info.getValue()),
        }),
        columnHelper.accessor("floor_name", {
            header: "Floor Name",
            cell: (info) => info.getValue() ?? "-",
        }),
    ];

    if (isAdmin.value) {
        baseColumns.push(
            columnHelper.accessor("manager_name", {
                header: "Manager Name",
                cell: (info) => info.getValue() ?? "-",
            }),
        );
    }

    if (hasManageableRows.value) {
        baseColumns.push(
            columnHelper.display({
                id: "actions",
                header: "Actions",
                cell: ({ row }) => row.original,
            }),
        );
    }

    return baseColumns;
});

const table = useVueTable({
    get data() {
        return rows.value;
    },
    get columns() {
        return columns.value;
    },
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    get pageCount() {
        return meta.value.lastPage;
    },
});

const queryParams = (page) => ({
    page,
    search: search.value || undefined,
    per_page: perPage.value,
    sort_by: sortBy.value || undefined,
    sort_dir: sortBy.value ? sortDir.value : undefined,
});

const loadPage = (page) => {
    loading.value = true;

    router.get(route("rooms.index"), queryParams(page), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => {
            loading.value = false;
        },
    });
};

const isSortableColumn = (column) => {
    if (column === "manager_name") {
        return isAdmin.value;
    }

    return ["number", "capacity", "price", "floor_name"].includes(column);
};

const sortIndicator = (column) => {
    if (sortBy.value !== column) {
        return "<>";
    }

    return sortDir.value === "asc" ? "^" : "v";
};

const toggleSort = (column) => {
    if (!isSortableColumn(column)) {
        return;
    }

    if (sortBy.value === column) {
        sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
    } else {
        sortBy.value = column;
        sortDir.value = "asc";
    }

    loadPage(1);
};

const formatPrice = (value) => {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(Number(value ?? 0));
};

const openDeleteModal = (room) => {
    if (!room?.id || !room?.can_manage) {
        return;
    }

    deleteCandidate.value = room;
    deleteModalOpen.value = true;
};

const closeDeleteModal = (force = false) => {
    if (deletingRoomId.value && !force) {
        return;
    }

    deleteModalOpen.value = false;
    deleteCandidate.value = null;
};

let deleteMessageTimer = null;

const showDeleteMessage = (message, tone = "error") => {
    deleteMessage.value = message;
    deleteMessageTone.value = tone;
    clearTimeout(deleteMessageTimer);

    deleteMessageTimer = setTimeout(() => {
        deleteMessage.value = "";
    }, 3200);
};

const confirmDeleteRoom = () => {
    const room = deleteCandidate.value;

    if (!room?.id || !room?.can_manage) {
        return;
    }

    deletingRoomId.value = room.id;

    router.delete(route("rooms.destroy", room.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal(true);
            showDeleteMessage("Room deleted successfully.", "success");
            loadPage(meta.value.currentPage);
        },
        onError: (errors) => {
            closeDeleteModal(true);
            showDeleteMessage(
                errors.room || "Unable to delete this room at the moment.",
            );
        },
        onFinish: () => {
            deletingRoomId.value = null;
        },
    });
};

watch(perPage, () => {
    loadPage(1);
});

let searchDebounce = null;

watch(search, () => {
    clearTimeout(searchDebounce);

    searchDebounce = setTimeout(() => {
        loadPage(1);
    }, 350);
});

onBeforeUnmount(() => {
    clearTimeout(searchDebounce);
    clearTimeout(deleteMessageTimer);
});
</script>

<template>
    <Head title="Manage Rooms" />

    <RoleDashboardLayout>
        <section class="relative h-full overflow-y-auto">
            <div class="pointer-events-none absolute inset-0">
                <div
                    class="absolute left-6 top-8 h-44 w-44 rounded-full bg-amber-200/40 blur-3xl"
                />
                <div
                    class="absolute bottom-2 right-12 h-40 w-40 rounded-full bg-cyan-200/40 blur-3xl"
                />
            </div>

            <div
                v-if="deleteModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <button
                    type="button"
                    class="absolute inset-0 bg-slate-900/45 backdrop-blur-[1px]"
                    @click="closeDeleteModal"
                    aria-label="Close delete dialog"
                />

                <div
                    class="relative w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_32px_80px_-40px_rgba(15,23,42,0.9)]"
                >
                    <p
                        class="text-[11px] font-semibold uppercase tracking-[0.32em] text-rose-500"
                    >
                        Confirm Delete
                    </p>

                    <h3 class="mt-3 text-xl font-semibold text-slate-900">
                        Delete this room?
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        You are about to delete room
                        <span class="font-semibold text-slate-700">
                            {{ deleteCandidate?.number || "this room" }}
                        </span>
                        . This action cannot be undone.
                    </p>

                    <div class="mt-6 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            :disabled="Boolean(deletingRoomId)"
                            @click="closeDeleteModal"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-colors duration-200 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            :disabled="Boolean(deletingRoomId)"
                            @click="confirmDeleteRoom"
                            class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition-colors duration-200 hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Delete Room
                        </button>
                    </div>
                </div>
            </div>

            <div class="relative mx-auto max-w-6xl px-6 py-8 md:py-10">
                <div
                    class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-[0_24px_80px_-48px_rgba(15,23,42,0.8)] backdrop-blur-sm"
                >
                    <p
                        class="text-[11px] font-semibold uppercase tracking-[0.35em] text-amber-600"
                    >
                        Room Operations
                    </p>
                    <div
                        class="mt-3 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
                    >
                        <div>
                            <h1 class="text-3xl font-semibold text-slate-900">
                                Room Management
                            </h1>
                            <p class="mt-1 text-sm text-slate-500">
                                Managers can create and manage only the rooms
                                they created. Admin can view all rooms.
                            </p>
                        </div>

                        <div
                            class="flex w-full flex-col gap-3 sm:flex-row md:w-auto"
                        >
                            <input
                                v-model="search"
                                type="text"
                                :placeholder="searchPlaceholder"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200/60 sm:w-64"
                            />

                            <select
                                v-model.number="perPage"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200/60"
                            >
                                <option :value="5">5 / page</option>
                                <option :value="10">10 / page</option>
                                <option :value="15">15 / page</option>
                            </select>

                            <Link
                                v-if="canCreate"
                                :href="route('rooms.create')"
                                class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700 transition-colors duration-200 hover:bg-amber-100"
                            >
                                Add Room
                            </Link>
                        </div>
                    </div>
                </div>

                <div
                    v-if="deleteMessage"
                    class="mt-4 rounded-2xl border px-4 py-3 text-sm"
                    :class="
                        deleteMessageTone === 'success'
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : 'border-rose-200 bg-rose-50 text-rose-700'
                    "
                >
                    <div class="flex items-center justify-between gap-3">
                        <p>{{ deleteMessage }}</p>

                        <button
                            type="button"
                            class="text-xs font-semibold uppercase tracking-[0.18em] opacity-70 transition-opacity duration-150 hover:opacity-100"
                            @click="deleteMessage = ''"
                        >
                            Dismiss
                        </button>
                    </div>
                </div>

                <div
                    class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr
                                    v-for="headerGroup in table.getHeaderGroups()"
                                    :key="headerGroup.id"
                                >
                                    <th
                                        v-for="header in headerGroup.headers"
                                        :key="header.id"
                                        class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500"
                                        :class="{
                                            'text-right':
                                                header.column.id === 'actions',
                                        }"
                                    >
                                        <template v-if="!header.isPlaceholder">
                                            <button
                                                v-if="
                                                    isSortableColumn(
                                                        header.column.id,
                                                    )
                                                "
                                                type="button"
                                                @click="
                                                    toggleSort(header.column.id)
                                                "
                                                class="inline-flex items-center gap-2 text-left hover:text-slate-700"
                                            >
                                                <FlexRender
                                                    :render="
                                                        header.column.columnDef
                                                            .header
                                                    "
                                                    :props="header.getContext()"
                                                />
                                                <span
                                                    class="text-[10px] text-slate-400"
                                                >
                                                    {{
                                                        sortIndicator(
                                                            header.column.id,
                                                        )
                                                    }}
                                                </span>
                                            </button>

                                            <FlexRender
                                                v-else
                                                :render="
                                                    header.column.columnDef
                                                        .header
                                                "
                                                :props="header.getContext()"
                                            />
                                        </template>
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr v-if="!table.getRowModel().rows.length">
                                    <td
                                        :colspan="
                                            table.getVisibleLeafColumns().length
                                        "
                                        class="px-5 py-10 text-center text-sm text-slate-500"
                                    >
                                        No rooms returned from server.
                                    </td>
                                </tr>

                                <tr
                                    v-for="row in table.getRowModel().rows"
                                    :key="row.id"
                                    class="hover:bg-slate-50/80"
                                >
                                    <td
                                        v-for="cell in row.getVisibleCells()"
                                        :key="cell.id"
                                        class="whitespace-nowrap px-5 py-4 text-sm text-slate-600"
                                        :class="{
                                            'text-right':
                                                cell.column.id === 'actions',
                                            'font-medium text-slate-700':
                                                cell.column.id === 'number',
                                        }"
                                    >
                                        <template
                                            v-if="cell.column.id === 'actions'"
                                        >
                                            <div class="inline-flex gap-2">
                                                <template
                                                    v-if="
                                                        cell.row.original
                                                            ?.can_manage
                                                    "
                                                >
                                                    <Link
                                                        :href="
                                                            route(
                                                                'rooms.edit',
                                                                cell.row
                                                                    .original
                                                                    .id,
                                                            )
                                                        "
                                                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors duration-200 hover:bg-slate-100"
                                                    >
                                                        Edit
                                                    </Link>
                                                    <button
                                                        type="button"
                                                        :disabled="
                                                            deletingRoomId ===
                                                                cell.row
                                                                    .original
                                                                    .id || loading
                                                        "
                                                        @click="
                                                            openDeleteModal(
                                                                cell.row
                                                                    .original,
                                                            )
                                                        "
                                                        class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-600 transition-colors duration-200 hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-60"
                                                    >
                                                        Delete
                                                    </button>
                                                </template>
                                            </div>
                                        </template>

                                        <template v-else>
                                            <FlexRender
                                                :render="
                                                    cell.column.columnDef.cell
                                                "
                                                :props="cell.getContext()"
                                            />
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/80 px-5 py-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p class="text-sm text-slate-500">
                            Showing {{ meta.from }} to {{ meta.to }} of
                            {{ meta.total }} rooms.
                        </p>

                        <div class="inline-flex items-center gap-2">
                            <button
                                type="button"
                                :disabled="meta.currentPage <= 1 || loading"
                                @click="loadPage(meta.currentPage - 1)"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 transition-colors duration-200 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Previous
                            </button>

                            <span class="text-sm font-medium text-slate-600">
                                Page {{ meta.currentPage }} /
                                {{ meta.lastPage }}
                            </span>

                            <button
                                type="button"
                                :disabled="
                                    meta.currentPage >= meta.lastPage || loading
                                "
                                @click="loadPage(meta.currentPage + 1)"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 transition-colors duration-200 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </RoleDashboardLayout>
</template>

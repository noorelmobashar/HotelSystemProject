<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import RoleDashboardLayout from "@/Layouts/RoleDashboardLayout.vue";

const props = defineProps({
    room: {
        type: Object,
        required: true,
    },
    floors: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    number: props.room?.number ?? "",
    capacity: props.room?.capacity ?? 1,
    price: props.room?.price ?? "",
    floor_id: props.room?.floor_id ?? "",
});

const submit = () => {
    form.put(route("rooms.update", props.room.id));
};
</script>

<template>
    <Head title="Edit Room" />

    <RoleDashboardLayout>
        <section
            class="h-full overflow-y-auto bg-gradient-to-br from-slate-100 via-slate-100 to-cyan-50/40"
        >
            <div class="mx-auto max-w-3xl px-6 py-8 md:py-10">
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-[11px] font-semibold uppercase tracking-[0.35em] text-cyan-600"
                            >
                                Room Setup
                            </p>
                            <h1
                                class="mt-2 text-3xl font-semibold text-slate-900"
                            >
                                Edit Room
                            </h1>
                        </div>

                        <Link
                            :href="route('rooms.index')"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-colors duration-200 hover:bg-slate-50"
                        >
                            Back to Rooms
                        </Link>
                    </div>

                    <form class="mt-8 space-y-6" @submit.prevent="submit">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label
                                    for="number"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Room Number
                                </label>
                                <input
                                    id="number"
                                    v-model="form.number"
                                    type="text"
                                    inputmode="numeric"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-cyan-300 focus:outline-none focus:ring-2 focus:ring-cyan-200/60"
                                />
                                <p
                                    v-if="form.errors.number"
                                    class="mt-2 text-sm text-rose-500"
                                >
                                    {{ form.errors.number }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="capacity"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Capacity
                                </label>
                                <input
                                    id="capacity"
                                    v-model.number="form.capacity"
                                    type="number"
                                    min="1"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-cyan-300 focus:outline-none focus:ring-2 focus:ring-cyan-200/60"
                                />
                                <p
                                    v-if="form.errors.capacity"
                                    class="mt-2 text-sm text-rose-500"
                                >
                                    {{ form.errors.capacity }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label
                                    for="price"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Price (USD)
                                </label>
                                <input
                                    id="price"
                                    v-model.number="form.price"
                                    type="number"
                                    min="1"
                                    step="1"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-cyan-300 focus:outline-none focus:ring-2 focus:ring-cyan-200/60"
                                />
                                <p
                                    v-if="form.errors.price"
                                    class="mt-2 text-sm text-rose-500"
                                >
                                    {{ form.errors.price }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="floor_id"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Floor
                                </label>
                                <select
                                    id="floor_id"
                                    v-model="form.floor_id"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-cyan-300 focus:outline-none focus:ring-2 focus:ring-cyan-200/60"
                                >
                                    <option value="" disabled>
                                        Select a floor
                                    </option>
                                    <option
                                        v-for="floor in floors"
                                        :key="floor.id"
                                        :value="floor.id"
                                    >
                                        {{ floor.name }} ({{ floor.number }})
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.floor_id"
                                    class="mt-2 text-sm text-rose-500"
                                >
                                    {{ form.errors.floor_id }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600"
                        >
                            Price is entered in dollars and saved internally as
                            cents to keep reservation totals consistent.
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <Link
                                :href="route('rooms.index')"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition-colors duration-200 hover:bg-slate-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition-colors duration-200 hover:bg-cyan-100 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Update Room
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </RoleDashboardLayout>
</template>

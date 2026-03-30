<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import RoleDashboardLayout from "@/Layouts/RoleDashboardLayout.vue";

const props = defineProps({
    floor: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.floor?.name ?? "",
});

const submit = () => {
    form.put(route("floors.update", props.floor.id));
};
</script>

<template>
    <Head title="Edit Floor" />

    <RoleDashboardLayout>
        <section
            class="h-full overflow-y-auto bg-gradient-to-br from-slate-100 via-slate-100 to-emerald-50/40"
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
                                class="text-[11px] font-semibold uppercase tracking-[0.35em] text-emerald-600"
                            >
                                Floor Setup
                            </p>
                            <h1
                                class="mt-2 text-3xl font-semibold text-slate-900"
                            >
                                Edit Floor
                            </h1>
                        </div>

                        <Link
                            :href="route('floors.index')"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-colors duration-200 hover:bg-slate-50"
                        >
                            Back to Floors
                        </Link>
                    </div>

                    <form class="mt-8 space-y-6" @submit.prevent="submit">
                        <div>
                            <label
                                for="name"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Floor Name
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-200/60"
                            />
                            <p
                                v-if="form.errors.name"
                                class="mt-2 text-sm text-rose-500"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="number"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Floor Number
                            </label>
                            <input
                                id="number"
                                type="text"
                                :value="props.floor?.number"
                                readonly
                                class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm text-slate-600"
                            />
                            <p class="mt-2 text-xs text-slate-500">
                                Floor number is system-generated and cannot be
                                edited.
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <Link
                                :href="route('floors.index')"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition-colors duration-200 hover:bg-slate-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition-colors duration-200 hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Update Floor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </RoleDashboardLayout>
</template>

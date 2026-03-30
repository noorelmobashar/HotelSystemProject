<script setup>
import RoleDashboardLayout from '@/Layouts/RoleDashboardLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    rooms: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const formatPrice = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value ?? 0);
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
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Room Number
                                </th>
                                <!-- Floor -->
                                 <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Floor   
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Price
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Capacity
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200">
                            <tr
                                v-for="room in props.rooms"
                                :key="room.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-5 py-4 text-sm font-medium text-slate-700">
                                    {{ room.number }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ room.floor ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ formatPrice(room.price) }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ room.capacity }}
                                </td>
                                <td class="px-5 py-4">
                                    <Link
                                        :href="route('reservations.rooms.show', { roomId: room.id })"
                                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        Make Reservation
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="props.rooms.length === 0">
                                <td
                                    colspan="4"
                                    class="px-5 py-8 text-center text-sm text-slate-500"
                                >
                                    No available rooms at the moment.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </RoleDashboardLayout>
</template>

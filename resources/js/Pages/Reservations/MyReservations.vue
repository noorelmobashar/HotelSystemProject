<script setup>
import { computed } from 'vue';
import RoleDashboardLayout from '@/Layouts/RoleDashboardLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const props = defineProps({
    reservations: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const status = computed(() => {
    const url = String(page.url ?? '');
    const query = url.includes('?') ? url.split('?')[1] : '';

    return new URLSearchParams(query).get('status');
});

const statusMessage = computed(() => {
    if (status.value === 'success') {
        return 'Payment completed successfully and reservation has been added.';
    }

    if (status.value === 'already_confirmed') {
        return 'Reservation was already confirmed for this room.';
    }

    return '';
});

const formatPrice = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value ?? 0);
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
                    v-if="statusMessage"
                    class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                >
                    {{ statusMessage }}
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
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Room
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Capacity
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Accompany
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Paid Price
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Status
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Created At
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200">
                            <tr
                                v-for="reservation in props.reservations"
                                :key="reservation.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-5 py-4 text-sm font-medium text-slate-700">
                                    {{ reservation.room_number }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ reservation.capacity }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ reservation.accompany_number }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ formatPrice(reservation.paid_price) }}
                                </td>
                                <td class="px-5 py-4 text-sm">
                                    <span
                                        class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="reservation.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'"
                                    >
                                        {{ reservation.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ reservation.created_at }}
                                </td>
                            </tr>

                            <tr v-if="props.reservations.length === 0">
                                <td
                                    colspan="6"
                                    class="px-5 py-8 text-center text-sm text-slate-500"
                                >
                                    No reservations yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </RoleDashboardLayout>
</template>

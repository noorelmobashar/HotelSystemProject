<script setup>
import InputError from '@/Components/InputError.vue';
import RoleDashboardLayout from '@/Layouts/RoleDashboardLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    room: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const form = useForm({
    accompany_number: 0,
});

const submit = () => {
    form.post(route('reservations.rooms.checkout', { roomId: props.room.id }));
};

const formatPrice = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value ?? 0);
};
</script>

<template>
    <Head :title="`Reserve Room ${props.room.number}`" />

    <RoleDashboardLayout>
        <div class="h-full overflow-y-auto bg-slate-100 p-6 md:p-10">
            <div class="mx-auto max-w-3xl">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900">
                            Room {{ props.room.number }} Reservation
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Complete details, enter accompany number, then pay the exact room price using Stripe.
                        </p>
                    </div>

                    <Link
                        :href="route('reservations.create')"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Back
                    </Link>
                </div>

                <div
                    v-if="page.props.flash?.error"
                    class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                >
                    {{ page.props.flash.error }}
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Price</p>
                            <p class="mt-1 text-lg font-semibold text-slate-900">{{ formatPrice(props.room.price) }}</p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Capacity</p>
                            <p class="mt-1 text-lg font-semibold text-slate-900">{{ props.room.capacity }}</p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Floor</p>
                            <p class="mt-1 text-lg font-semibold text-slate-900">{{ props.room.floor ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <form class="mt-6 space-y-4" @submit.prevent="submit">
                        <div>
                            <label
                                for="accompany_number"
                                class="mb-1 block text-sm font-medium text-slate-700"
                            >
                                Number of accompany
                            </label>
                            <input
                                id="accompany_number"
                                v-model.number="form.accompany_number"
                                type="number"
                                min="0"
                                :max="props.room.capacity"
                                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                            >
                            <p class="mt-1 text-xs text-slate-500">
                                Maximum allowed for this room: {{ props.room.capacity }}.
                            </p>
                            <InputError class="mt-2" :message="form.errors.accompany_number" />
                            <InputError class="mt-2" :message="form.errors.payment" />
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            Pay {{ formatPrice(props.room.price) }} with Stripe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </RoleDashboardLayout>
</template>

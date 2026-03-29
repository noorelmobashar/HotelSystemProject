<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
        default: false,
    },
    canRegister: {
        type: Boolean,
        default: false,
    },
});

const hotelStats = [
    {
        label: 'Role-aware dashboards',
        value: '4',
        tone: 'text-sky-600',
    },
    {
        label: 'Shared reservation workflow',
        value: '1',
        tone: 'text-emerald-600',
    },
    {
        label: 'Front-desk ready workspace',
        value: '24/7',
        tone: 'text-amber-600',
    },
];

const roleCards = [
    {
        title: 'Admin',
        description: 'Control property operations, team structure, and platform-wide activity.',
        accent: 'from-cyan-500 to-blue-500',
    },
    {
        title: 'Manager',
        description: 'Coordinate floors, rooms, staff workflows, and operational oversight.',
        accent: 'from-emerald-500 to-teal-500',
    },
    {
        title: 'Receptionist',
        description: 'Handle guest approvals, desk operations, and reservation follow-through.',
        accent: 'from-amber-500 to-orange-500',
    },
    {
        title: 'Client',
        description: 'Browse room availability and manage personal reservations in one place.',
        accent: 'from-fuchsia-500 to-violet-500',
    },
];

const operationalCards = [
    {
        eyebrow: 'Reservations',
        title: 'Track room availability and guest bookings.',
        description: 'Clients reserve available rooms while staff keep visibility on status changes and activity.',
    },
    {
        eyebrow: 'Approvals',
        title: 'Move guest workflows through clear role handoffs.',
        description: 'Reception and management flows stay aligned instead of being spread across disconnected screens.',
    },
    {
        eyebrow: 'Workspace',
        title: 'Keep the interface consistent from auth to dashboard.',
        description: 'The landing page, login, registration, and internal panels now belong to the same product.',
    },
];

const quickMetrics = [
    {
        value: 'Admin',
        label: 'Property-wide control',
    },
    {
        value: 'Manager',
        label: 'Operational oversight',
    },
    {
        value: 'Reception',
        label: 'Front desk execution',
    },
    {
        value: 'Client',
        label: 'Reservation self-service',
    },
];
</script>

<template>
    <Head title="Aurora Grand Hotel" />

    <div class="min-h-screen overflow-hidden bg-slate-100 text-slate-900">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.12),_transparent_28%),radial-gradient(circle_at_top_right,_rgba(16,185,129,0.1),_transparent_24%),linear-gradient(to_bottom,_#f8fafc,_#f1f5f9)]" />
        <div class="absolute left-1/2 top-24 -z-10 hidden h-72 w-72 -translate-x-1/2 rounded-full bg-sky-200/30 blur-3xl lg:block" />

        <div class="mx-auto max-w-7xl px-5 py-6 sm:px-8 lg:px-10 lg:py-8">
            <header class="flex flex-col gap-5 rounded-[2rem] border border-slate-200 bg-white/90 px-5 py-5 shadow-[0_24px_80px_-50px_rgba(15,23,42,0.45)] backdrop-blur sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                <Link href="/" class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-slate-900 text-white">
                        <ApplicationLogo class="h-9 w-9 fill-current" />
                    </div>

                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.35em] text-sky-500">
                            Hotel Workspace
                        </p>
                        <h1 class="text-xl font-semibold text-slate-900">
                            Aurora Grand Hotel
                        </h1>
                    </div>
                </Link>

                <nav class="flex flex-wrap items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        Open Dashboard
                    </Link>

                    <template v-else-if="canLogin">
                        <Link
                            :href="route('login')"
                            class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Log In
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Create Account
                        </Link>
                    </template>
                </nav>
            </header>

            <section class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_390px]">
                <div class="overflow-hidden rounded-[2.25rem] border border-slate-200 bg-[#f8fafc] px-6 py-8 shadow-[0_32px_90px_-58px_rgba(15,23,42,0.55)] sm:px-8 sm:py-10">
                    <div class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-sky-600">
                        Hotel Management System
                    </div>

                    <h2 class="mt-6 max-w-3xl text-4xl font-semibold leading-tight text-slate-900 sm:text-5xl">
                        One workspace for hotel operations, guest access, and reservations.
                    </h2>

                    <p class="mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                        This project is built around role-based hotel operations. Admins, managers, receptionists, and clients each get focused tools while still working inside one consistent system.
                    </p>

                    <div class="mt-8 grid gap-4 md:grid-cols-[minmax(0,1fr)_280px]">
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-slate-400">
                                Production-Ready Flow
                            </p>
                            <p class="mt-3 text-xl font-semibold leading-8 text-slate-900">
                                A cleaner public experience for guests and hotel staff.
                            </p>
                            <p class="mt-3 text-sm leading-7 text-slate-500">
                                The landing page, authentication pages, and internal workspace now share the same visual language, interaction patterns, and product story.
                            </p>
                        </div>

                        <div class="rounded-[1.75rem] border border-slate-200 bg-slate-900 p-5 text-white">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-sky-300/90">
                                Guest Experience
                            </p>
                            <p class="mt-3 text-xl font-semibold leading-8">
                                Reserve with clarity, not confusion.
                            </p>
                            <p class="mt-3 text-sm leading-7 text-slate-300">
                                Clients move from registration to login to reservation tools without falling into a generic starter UI.
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="inline-flex items-center rounded-2xl bg-slate-900 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Continue to dashboard
                        </Link>

                        <template v-else-if="canLogin">
                            <Link
                                :href="route('login')"
                                class="inline-flex items-center rounded-2xl bg-slate-900 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                            >
                                Access workspace
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-6 py-3.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                Register as client
                            </Link>
                        </template>
                    </div>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3">
                        <div
                            v-for="stat in hotelStats"
                            :key="stat.label"
                            class="rounded-3xl border border-slate-200 bg-white p-5"
                        >
                            <p :class="['text-3xl font-semibold', stat.tone]">
                                {{ stat.value }}
                            </p>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                {{ stat.label }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[2.25rem] border border-slate-200 bg-white p-6 shadow-[0_32px_90px_-58px_rgba(15,23,42,0.55)] sm:p-7">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.35em] text-slate-400">
                                Workspace Roles
                            </p>
                            <h3 class="mt-2 text-2xl font-semibold text-slate-900">
                                Built around real hotel teams
                            </h3>
                        </div>

                        <div class="rounded-2xl bg-slate-100 px-3 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                            Live
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div
                            v-for="role in roleCards"
                            :key="role.title"
                            class="flex items-start gap-4 rounded-3xl border border-slate-200 bg-slate-50 p-4"
                        >
                            <div
                                :class="[
                                    'mt-0.5 flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br text-sm font-semibold text-white',
                                    role.accent,
                                ]"
                            >
                                {{ role.title.charAt(0) }}
                            </div>

                            <div>
                                <p class="text-base font-semibold text-slate-900">
                                    {{ role.title }}
                                </p>
                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    {{ role.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-8 rounded-[2.25rem] border border-slate-200 bg-white p-6 shadow-[0_24px_80px_-56px_rgba(15,23,42,0.5)] sm:p-8">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.35em] text-slate-400">
                            Role Access
                        </p>
                        <h3 class="mt-3 text-2xl font-semibold text-slate-900">
                            Every role enters the same system with a different focus.
                        </h3>
                    </div>

                    <p class="max-w-2xl text-sm leading-7 text-slate-500">
                        Public-facing pages should explain the product quickly. This section makes the hotel system feel intentional before users even authenticate.
                    </p>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        v-for="metric in quickMetrics"
                        :key="metric.value"
                        class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-5 py-5"
                    >
                        <p class="text-lg font-semibold text-slate-900">
                            {{ metric.value }}
                        </p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            {{ metric.label }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="mt-8 grid gap-5 lg:grid-cols-3">
                <article
                    v-for="card in operationalCards"
                    :key="card.title"
                    class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_24px_80px_-56px_rgba(15,23,42,0.5)]"
                >
                    <p class="text-[11px] font-semibold uppercase tracking-[0.35em] text-slate-400">
                        {{ card.eyebrow }}
                    </p>
                    <h3 class="mt-4 text-xl font-semibold leading-8 text-slate-900">
                        {{ card.title }}
                    </h3>
                    <p class="mt-3 text-sm leading-7 text-slate-500">
                        {{ card.description }}
                    </p>
                </article>
            </section>
        </div>
    </div>
</template>

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

const features = [
    'Easy booking',
    'Real-time availability',
    'Secure reservations',
];
</script>

<template>
    <Head title="Aurora Grand Hotel" />

    <div class="min-h-screen bg-slate-100 text-slate-900">
        <div class="absolute inset-0 -z-10 bg-[linear-gradient(to_bottom,_#f8fafc,_#eef2f7)]" />

        <div class="mx-auto flex min-h-screen max-w-6xl flex-col px-5 py-6 sm:px-8 lg:px-10">
            <header class="flex flex-col gap-4 rounded-[2rem] border border-slate-200 bg-white px-5 py-4 shadow-[0_24px_80px_-50px_rgba(15,23,42,0.24)] sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <Link href="/" class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-slate-900 text-white">
                        <ApplicationLogo class="h-9 w-9 fill-current" />
                    </div>

                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.35em] text-sky-500">
                            Guest Portal
                        </p>
                        <h1 class="text-xl font-semibold text-slate-900">
                            Aurora Grand Hotel
                        </h1>
                    </div>
                </Link>

                <nav
                    v-if="canLogin"
                    class="flex flex-wrap items-center gap-3"
                >
                    <Link
                        :href="route('login')"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Login
                    </Link>

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        Register
                    </Link>
                </nav>
            </header>

            <main class="flex flex-1 items-center py-12 sm:py-16">
                <section class="w-full rounded-[2.5rem] border border-slate-200 bg-white px-6 py-12 shadow-[0_32px_90px_-58px_rgba(15,23,42,0.28)] sm:px-10 lg:px-14 lg:py-16">
                    <div class="mx-auto max-w-3xl text-center">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.35em] text-sky-500">
                            Welcome
                        </p>

                        <h2 class="mt-5 text-4xl font-semibold leading-tight text-slate-900 sm:text-5xl">
                            Book your stay easily and quickly
                        </h2>

                        <p class="mt-5 text-base leading-8 text-slate-600 sm:text-lg">
                            Reserve your room with a simple, secure, and comfortable booking experience.
                        </p>

                        <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                            >
                                Browse Rooms
                            </Link>

                            <Link
                                v-if="canLogin"
                                :href="route('login')"
                                class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-7 py-3.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                            >
                                Login
                            </Link>
                        </div>

                        <div class="mt-14 grid gap-3 sm:grid-cols-3">
                            <div
                                v-for="feature in features"
                                :key="feature"
                                class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-5 py-5 text-sm font-medium text-slate-700"
                            >
                                {{ feature }}
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>

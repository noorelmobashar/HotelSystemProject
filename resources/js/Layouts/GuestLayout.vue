<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
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
                        <p class="text-xl font-semibold text-slate-900">
                            Aurora Grand Hotel
                        </p>
                    </div>
                </Link>

                <Link
                    href="/"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Back to Welcome
                </Link>
            </header>

            <main class="flex flex-1 items-center justify-center py-12 sm:py-16">
                <section class="w-full max-w-3xl rounded-[2.5rem] border border-slate-200 bg-white px-6 py-10 shadow-[0_32px_90px_-58px_rgba(15,23,42,0.28)] sm:px-10 sm:py-14">
                    <slot />
                </section>
            </main>
        </div>
    </div>
</template>

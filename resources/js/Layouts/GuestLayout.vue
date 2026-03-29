<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-slate-100 text-slate-900">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.15),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(16,185,129,0.12),_transparent_28%),linear-gradient(to_bottom,_#f8fafc,_#eef2f7)]" />

        <div class="grid min-h-screen lg:grid-cols-[1.05fr_minmax(0,580px)]">
            <section
                class="relative hidden overflow-hidden border-r border-slate-200 bg-[#f8fafc]/90 px-10 py-12 lg:flex lg:flex-col"
            >
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.14),_transparent_35%),radial-gradient(circle_at_bottom_right,_rgba(16,185,129,0.12),_transparent_32%)]" />

                <div class="relative flex items-center gap-3">
                    <Link href="/" class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-500 text-sm font-semibold text-white">
                            HC
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.35em] text-sky-500">
                                Hotel Workspace
                            </p>
                            <p class="text-xl font-semibold text-slate-900">
                                Aurora Grand Hotel
                            </p>
                        </div>
                    </Link>
                </div>

                <div class="relative mt-16 max-w-xl">
                    <div class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-sky-600">
                        Guest Access
                    </div>

                    <h1 class="mt-6 text-5xl font-semibold leading-tight text-slate-900">
                        Sign in to the same workspace your team already uses.
                    </h1>

                    <p class="mt-5 max-w-lg text-base leading-7 text-slate-600">
                        Reservations, approvals, room management, and front-desk activity all live in one place. Your account entry points should look like they belong to that system.
                    </p>
                </div>

                <div class="relative mt-10 grid max-w-2xl gap-4 sm:grid-cols-2">
                    <div class="rounded-[1.75rem] border border-slate-200 bg-white/90 p-5 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.85)]">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-slate-400">
                            Unified Flow
                        </p>
                        <p class="mt-3 text-lg font-semibold text-slate-900">
                            Guest onboarding connects directly to reservations.
                        </p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            No disconnected screens or generic starter templates. Public entry pages and internal workspace now read as one product.
                        </p>
                    </div>

                    <div class="rounded-[1.75rem] border border-slate-200 bg-slate-900 p-5 text-white shadow-[0_18px_40px_-34px_rgba(15,23,42,0.85)]">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-sky-300/90">
                            Operational Focus
                        </p>
                        <p class="mt-3 text-lg font-semibold">
                            Built for real hotel roles, not generic dashboard placeholders.
                        </p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">
                            The public UI sets expectations for admin, manager, receptionist, and client workflows before the user even signs in.
                        </p>
                    </div>
                </div>

                <div class="relative mt-auto grid gap-4 md:grid-cols-3">
                    <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.85)]">
                        <p class="text-2xl font-semibold text-slate-900">24/7</p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Front desk visibility across shifts and guest activity.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.85)]">
                        <p class="text-2xl font-semibold text-slate-900">Roles</p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Admin, manager, receptionist, and client views stay aligned.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.85)]">
                        <p class="text-2xl font-semibold text-slate-900">Live</p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Booking changes and approvals move through one shared workflow.
                        </p>
                    </div>
                </div>
            </section>

            <section class="flex items-center justify-center px-5 py-8 sm:px-8 lg:px-10">
                <div class="w-full max-w-xl">
                    <div class="mb-8 flex items-center justify-center lg:hidden">
                        <Link href="/" class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-500 text-sm font-semibold text-white">
                                HC
                            </div>

                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.35em] text-sky-500">
                                    Hotel Workspace
                                </p>
                                <p class="text-lg font-semibold text-slate-900">
                                    Aurora Grand Hotel
                                </p>
                            </div>
                        </Link>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.45)] backdrop-blur sm:p-8">
                        <div class="mb-8 flex items-center gap-4 rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-900 text-white">
                                <ApplicationLogo class="h-8 w-8 fill-current" />
                            </div>

                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">
                                    Secure Access
                                </p>
                                <p class="text-base font-semibold text-slate-800">
                                    Account portal
                                </p>
                            </div>
                        </div>

                        <slot />
                    </div>

                    <div class="mt-5 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 px-2 text-center text-xs font-medium uppercase tracking-[0.22em] text-slate-400">
                        <span>Role-Based Access</span>
                        <span class="hidden h-1 w-1 rounded-full bg-slate-300 sm:block" />
                        <span>Reservation Ready</span>
                        <span class="hidden h-1 w-1 rounded-full bg-slate-300 sm:block" />
                        <span>Unified Guest Experience</span>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

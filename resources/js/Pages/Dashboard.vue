<script setup>
import RoleDashboardLayout from '@/Layouts/RoleDashboardLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    role: {
        type: String,
        default: 'client',
    },
    overview: {
        type: Object,
        default: () => ({
            eyebrow: '',
            title: '',
            description: '',
            stats: [],
            highlights: [],
            actions: [],
            panels: [],
        }),
    },
});

const roleThemes = {
    admin: {
        shell: 'from-sky-200 via-cyan-100 to-slate-100',
        hero: 'from-sky-600 via-cyan-500 to-blue-600',
        ring: 'ring-sky-100',
        badge: 'bg-sky-100 text-sky-700',
        soft: 'bg-sky-50 text-sky-700',
        primaryButton: 'bg-slate-950 text-white hover:bg-slate-800',
        secondaryButton: 'border-sky-200 text-sky-700 hover:bg-sky-50',
    },
    manager: {
        shell: 'from-emerald-200 via-teal-100 to-slate-100',
        hero: 'from-emerald-600 via-teal-500 to-cyan-500',
        ring: 'ring-emerald-100',
        badge: 'bg-emerald-100 text-emerald-700',
        soft: 'bg-emerald-50 text-emerald-700',
        primaryButton: 'bg-slate-950 text-white hover:bg-slate-800',
        secondaryButton: 'border-emerald-200 text-emerald-700 hover:bg-emerald-50',
    },
    receptionist: {
        shell: 'from-amber-200 via-orange-100 to-slate-100',
        hero: 'from-amber-500 via-orange-500 to-rose-500',
        ring: 'ring-amber-100',
        badge: 'bg-amber-100 text-amber-700',
        soft: 'bg-amber-50 text-amber-700',
        primaryButton: 'bg-slate-950 text-white hover:bg-slate-800',
        secondaryButton: 'border-amber-200 text-amber-700 hover:bg-amber-50',
    },
    client: {
        shell: 'from-fuchsia-200 via-violet-100 to-slate-100',
        hero: 'from-fuchsia-500 via-violet-500 to-indigo-500',
        ring: 'ring-violet-100',
        badge: 'bg-violet-100 text-violet-700',
        soft: 'bg-violet-50 text-violet-700',
        primaryButton: 'bg-slate-950 text-white hover:bg-slate-800',
        secondaryButton: 'border-violet-200 text-violet-700 hover:bg-violet-50',
    },
};

const theme = computed(() => roleThemes[props.role] ?? roleThemes.client);

const highlightToneClass = (tone) => {
    if (tone === 'positive') {
        return 'bg-emerald-50 text-emerald-700';
    }

    if (tone === 'warning') {
        return 'bg-amber-50 text-amber-700';
    }

    return 'bg-slate-100 text-slate-700';
};

const formatValue = (value) => {
    if (typeof value === 'number') {
        return new Intl.NumberFormat('en-US').format(value);
    }

    return value;
};
</script>

<template>
    <Head title="Overview" />

    <RoleDashboardLayout>
        <div :class="['h-full overflow-y-auto bg-gradient-to-br px-6 py-8 lg:px-10', theme.shell]">
            <div class="mx-auto max-w-7xl space-y-6">
                <section :class="['overflow-hidden rounded-[32px] bg-gradient-to-br p-6 text-white shadow-[0_24px_70px_-36px_rgba(15,23,42,0.8)] lg:p-8', theme.hero]">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-3xl">
                            <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-white/90">
                                {{ overview.eyebrow }}
                            </span>
                            <h1 class="mt-4 max-w-2xl text-3xl font-semibold leading-tight lg:text-[2.6rem]">
                                {{ overview.title }}
                            </h1>
                            <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80 lg:text-base">
                                {{ overview.description }}
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <Link
                                v-for="action in overview.actions"
                                :key="action.label"
                                :href="action.href"
                                :class="[
                                    'inline-flex items-center justify-center rounded-2xl border px-5 py-3 text-sm font-semibold transition',
                                    action.style === 'primary'
                                        ? 'border-white/10 bg-white text-slate-900 hover:bg-slate-100'
                                        : 'border-white/25 bg-white/10 text-white hover:bg-white/15',
                                ]"
                            >
                                {{ action.label }}
                            </Link>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 xl:grid-cols-4">
                    <article
                        v-for="stat in overview.stats"
                        :key="stat.label"
                        :class="['rounded-3xl border border-white/70 bg-white/90 p-5 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.95)] ring-1 backdrop-blur', theme.ring]"
                    >
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">
                            {{ stat.label }}
                        </p>
                        <p class="mt-4 text-3xl font-semibold text-slate-900">
                            {{ formatValue(stat.value) }}
                        </p>
                        <p class="mt-2 text-sm text-slate-500">
                            {{ stat.help }}
                        </p>
                    </article>
                </section>

                <section class="grid gap-6 lg:grid-cols-[1.7fr,1fr]">
                    <div class="grid gap-6">
                        <article
                            v-for="panel in overview.panels"
                            :key="panel.title"
                            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.95)]"
                        >
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="max-w-2xl">
                                    <h2 class="text-xl font-semibold text-slate-900">
                                        {{ panel.title }}
                                    </h2>
                                    <p class="mt-2 text-sm leading-6 text-slate-500">
                                        {{ panel.body }}
                                    </p>
                                </div>

                                <span :class="['inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em]', theme.badge]">
                                    Focus
                                </span>
                            </div>

                            <div class="mt-5 grid gap-3 md:grid-cols-3">
                                <div
                                    v-for="item in panel.items"
                                    :key="item"
                                    class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium leading-6 text-slate-700"
                                >
                                    {{ item }}
                                </div>
                            </div>
                        </article>
                    </div>

                    <aside class="space-y-6">
                        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.95)]">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-slate-900">Quick highlights</h2>
                                <span :class="['rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em]', theme.soft]">
                                    Live
                                </span>
                            </div>

                            <div class="mt-5 space-y-3">
                                <div
                                    v-for="highlight in overview.highlights"
                                    :key="highlight.label"
                                    class="rounded-2xl border border-slate-200 p-4"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-medium text-slate-500">
                                                {{ highlight.label }}
                                            </p>
                                            <p class="mt-2 text-xl font-semibold text-slate-900">
                                                {{ formatValue(highlight.value) }}
                                            </p>
                                        </div>

                                        <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', highlightToneClass(highlight.tone)]">
                                            {{ highlight.tone === 'positive' ? 'Good' : highlight.tone === 'warning' ? 'Watch' : 'Info' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.95)]">
                            <h2 class="text-lg font-semibold text-slate-900">Workspace shortcuts</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Use the overview as the starting point, then move into the detailed modules from the sidebar or the actions below.
                            </p>

                            <div class="mt-5 space-y-3">
                                <Link
                                    v-for="action in overview.actions"
                                    :key="`${action.label}-shortcut`"
                                    :href="action.href"
                                    :class="[
                                        'flex items-center justify-between rounded-2xl border px-4 py-3 text-sm font-semibold transition',
                                        action.style === 'primary'
                                            ? theme.primaryButton
                                            : `bg-white ${theme.secondaryButton}`,
                                    ]"
                                >
                                    <span>{{ action.label }}</span>
                                    <span class="text-base leading-none">+</span>
                                </Link>
                            </div>
                        </section>
                    </aside>
                </section>
            </div>
        </div>
    </RoleDashboardLayout>
</template>

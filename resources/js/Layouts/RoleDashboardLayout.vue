<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import RoleSidebarIcon from "@/Components/RoleSidebarIcon.vue";

const props = defineProps({
    hideMain: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const hotelName = "Aurora Grand Hotel";

const roleLayouts = {
    admin: {
        roleLabel: "Hotel Administrator",
        brandCaptionClass: "text-sky-500",
        badgeGradient: "from-cyan-500 to-blue-500",
        activeItemClass: "border-sky-200 bg-sky-50/90",
        activeIconClass: "bg-sky-100 text-sky-700",
        activeTitleClass: "text-sky-700",
        activeSubtitleClass: "text-sky-400",
        menu: [
            {
                label: "Overview",
                subtitle: "Portfolio health",
                icon: "overview",
                routeName: "dashboard",
            },
            {
<<<<<<< HEAD
                label: 'Manage Managers',
                subtitle: 'Leadership accounts',
                icon: 'managers',
                routeName: 'managers.index',
=======
                label: "Manage Managers",
                subtitle: "Leadership accounts",
                icon: "managers",
                routeName: null,
>>>>>>> 5b13735357bcb58abbb9d87ab730d608b1cd554c
            },
            {
                label: "Manage Receptionists",
                subtitle: "Desk staffing",
                icon: "receptionists",
                routeName: "receptionists.index",
            },
            {
                label: "Manage Clients",
                subtitle: "Guest approvals",
                icon: "clients",
                routeName: null,
            },
            {
                label: "Manage Floors",
                subtitle: "Hotel structure",
                icon: "floors",
                routeName: "floors.index",
            },
            {
                label: "Manage Rooms",
                subtitle: "Rates and capacity",
                icon: "rooms",
                routeName: null,
            },
            {
                label: "Approved Clients",
                subtitle: "Reception handoff",
                icon: "approved",
                routeName: null,
            },
            {
                label: "Client Reservations",
                subtitle: "Paid stays",
                icon: "reservations",
                routeName: null,
            },
            {
                label: "Statistics",
                subtitle: "Business intelligence",
                icon: "stats",
                routeName: null,
            },
        ],
    },
    manager: {
        roleLabel: "Property Manager",
        brandCaptionClass: "text-teal-500",
        badgeGradient: "from-emerald-500 to-cyan-500",
        activeItemClass: "border-teal-200 bg-teal-50/90",
        activeIconClass: "bg-teal-100 text-teal-700",
        activeTitleClass: "text-teal-700",
        activeSubtitleClass: "text-teal-400",
        menu: [
            {
                label: "Overview",
                subtitle: "Property command",
                icon: "overview",
                routeName: "dashboard",
            },
            {
                label: "Manage Receptionists",
                subtitle: "Desk staffing",
                icon: "receptionists",
                routeName: "receptionists.index",
            },
            {
                label: "Manage Clients",
                subtitle: "Guest records",
                icon: "clients",
                routeName: null,
            },
            {
                label: "Manage Floors",
                subtitle: "Structure and numbering",
                icon: "floors",
                routeName: "floors.index",
            },
            {
                label: "Manage Rooms",
                subtitle: "Rates and capacity",
                icon: "rooms",
                routeName: null,
            },
            {
                label: "Statistics",
                subtitle: "Business intelligence",
                icon: "stats",
                routeName: null,
            },
        ],
    },
    receptionist: {
        roleLabel: "Front Desk Receptionist",
        brandCaptionClass: "text-orange-500",
        badgeGradient: "from-amber-500 to-orange-500",
        activeItemClass: "border-amber-200 bg-amber-50/90",
        activeIconClass: "bg-amber-100 text-amber-700",
        activeTitleClass: "text-amber-700",
        activeSubtitleClass: "text-amber-400",
        menu: [
            {
                label: "Overview",
                subtitle: "Desk command",
                icon: "overview",
                routeName: "dashboard",
            },
            {
                label: "Manage Clients",
                subtitle: "Pending approvals",
                icon: "clients",
                routeName: null,
            },
            {
                label: "My Approved Clients",
                subtitle: "Approved guests",
                icon: "approved",
                routeName: null,
            },
            {
                label: "Clients Reservations",
                subtitle: "Paid stays",
                icon: "reservations",
                routeName: "reservations.index",
            },
        ],
    },
    client: {
        roleLabel: "Approved Client",
        brandCaptionClass: "text-violet-500",
        badgeGradient: "from-fuchsia-500 to-violet-500",
        activeItemClass: "border-violet-200 bg-violet-50/90",
        activeIconClass: "bg-violet-100 text-violet-700",
        activeTitleClass: "text-violet-700",
        activeSubtitleClass: "text-violet-400",
        menu: [
            {
                label: "Overview",
                subtitle: "Guest workspace",
                icon: "overview",
                routeName: "dashboard",
            },
            {
                label: "Make Reservation",
                subtitle: "Available rooms",
                icon: "reservation",
                routeName: "reservations.create",
            },
            {
                label: "My Reservations",
                subtitle: "Current and past stays",
                icon: "reservations",
                routeName: "reservations.index",
            },
        ],
    },
};

const currentRole = computed(() => {
    const role = String(page.props.auth?.role ?? "").toLowerCase();

    return roleLayouts[role] ? role : "admin";
});

const roleLayout = computed(() => roleLayouts[currentRole.value]);

const user = computed(() => page.props.auth?.user ?? {});

const userEmail = computed(() => user.value?.email || "user@hotel.com");

const userAvatarUrl = computed(() => {
    const avatar = user.value?.avatar_image;

    if (!avatar) {
        return '/images/default-avatar.svg';
    }

    if (String(avatar).startsWith("http")) {
        return avatar;
    }

    if (String(avatar).startsWith('/')) {
        return avatar;
    }

    return `/storage/${avatar}`;
});

const roleInitials = computed(() => {
    const roleLabel = String(roleLayout.value.roleLabel ?? "").trim();

    if (!roleLabel) {
        return "HC";
    }

    const pieces = roleLabel.split(/\s+/).filter(Boolean);

    return pieces
        .slice(0, 2)
        .map((piece) => piece.charAt(0).toUpperCase())
        .join("");
});

const hasRoute = (routeName) => Boolean(routeName) && route().has(routeName);

const isItemActive = (item) => {
    if (!hasRoute(item.routeName)) {
        return false;
    }

    if (item.routeName === "floors.index") {
        return route().current("floors.*");
    }
    return route().current(item.routeName);
};

const itemHref = (item) => {
    if (!hasRoute(item.routeName)) {
        return null;
    }

    return route(item.routeName);
};

const sidebarItems = computed(() =>
    roleLayout.value.menu.map((item) => {
        const href = itemHref(item);

        return {
            ...item,
            href,
            active: isItemActive(item),
            disabled: !href,
        };
    }),
);
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <div class="flex min-h-screen">
            <aside
                class="flex w-full flex-col border-r border-slate-200 bg-[#f8fafc] px-4 py-5 md:w-[323px] md:min-w-[323px]"
            >
                <div class="flex items-center gap-3 px-1">
                    <div
                        :class="[
                            'flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br text-sm font-semibold text-white',
                            roleLayout.badgeGradient,
                        ]"
                    >
                        HC
                    </div>

                    <div class="min-w-0">
                        <p
                            :class="[
                                'text-[11px] font-semibold uppercase tracking-[0.35em]',
                                roleLayout.brandCaptionClass,
                            ]"
                        >
                            Hotel Workspace
                        </p>
                        <p
                            class="text-xl font-semibold leading-tight text-slate-900"
                        >
                            {{ hotelName }}
                        </p>
                    </div>
                </div>

                <div
                    class="mt-6 rounded-3xl border border-slate-200 bg-white p-4 shadow-[0_16px_40px_-34px_rgba(15,23,42,0.9)]"
                >
                    <div class="flex items-center gap-3">
                        <div
                            :class="[
                                'flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br text-base font-semibold text-white',
                                roleLayout.badgeGradient,
                            ]"
                        >
                            <img
                                v-if="userAvatarUrl"
                                :src="userAvatarUrl"
                                :alt="`${roleLayout.roleLabel} avatar`"
                                class="h-full w-full object-cover"
                            />
                            <span v-else>{{ roleInitials }}</span>
                        </div>

                        <div class="min-w-0">
                            <p
                                class="truncate text-[17px] font-semibold leading-tight text-slate-700"
                            >
                                {{ roleLayout.roleLabel }}
                            </p>
                            <p class="truncate text-sm text-slate-500">
                                {{ userEmail }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <p
                        class="px-2 text-[11px] uppercase tracking-[0.35em] text-slate-400"
                    >
                        Workspace
                    </p>

                    <nav class="mt-2 space-y-1.5">
                        <component
                            v-for="item in sidebarItems"
                            :key="item.label"
                            :is="item.href ? Link : 'button'"
                            :href="item.href || undefined"
                            :type="item.href ? undefined : 'button'"
                            :class="[
                                'group flex w-full items-center gap-3 rounded-2xl border px-3 py-3 text-left transition-all duration-200',
                                item.active
                                    ? roleLayout.activeItemClass
                                    : item.disabled
                                      ? 'cursor-not-allowed border-transparent bg-slate-50/70 opacity-75'
                                      : 'border-transparent hover:border-slate-200 hover:bg-white/80',
                            ]"
                        >
                            <span
                                :class="[
                                    'flex h-8 w-8 items-center justify-center rounded-xl transition-colors duration-200',
                                    item.active
                                        ? roleLayout.activeIconClass
                                        : item.disabled
                                          ? 'bg-slate-100 text-slate-300'
                                          : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200 group-hover:text-slate-500',
                                ]"
                            >
                                <RoleSidebarIcon :name="item.icon" />
                            </span>

                            <span class="min-w-0">
                                <span
                                    :class="[
                                        'block truncate text-[15px] font-medium leading-tight',
                                        item.active
                                            ? roleLayout.activeTitleClass
                                            : item.disabled
                                              ? 'text-slate-400'
                                              : 'text-slate-600',
                                    ]"
                                >
                                    {{ item.label }}
                                </span>
                                <span
                                    :class="[
                                        'block truncate text-xs',
                                        item.active
                                            ? roleLayout.activeSubtitleClass
                                            : item.disabled
                                              ? 'text-slate-300'
                                              : 'text-slate-400',
                                    ]"
                                >
                                    {{ item.subtitle }}
                                </span>
                            </span>
                        </component>
                    </nav>
                </div>

                <div class="mt-auto space-y-2 pt-8">
                    <Link
                        :href="route('profile.edit')"
                        class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors duration-200 hover:bg-slate-50"
                    >
                        <RoleSidebarIcon name="profile" />
                        Profile
                    </Link>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors duration-200 hover:bg-slate-50"
                    >
                        <RoleSidebarIcon name="logout" />
                        Log Out
                    </Link>
                </div>
            </aside>

            <main v-if="!props.hideMain" class="hidden flex-1 md:block">
                <slot>
                    <div
                        class="h-full bg-gradient-to-br from-slate-100 via-slate-100 to-slate-200/60"
                    />
                </slot>
            </main>
        </div>
    </div>
</template>

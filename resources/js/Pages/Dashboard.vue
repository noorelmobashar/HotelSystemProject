<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import RoleDashboardLayout from '@/Layouts/RoleDashboardLayout.vue';

const page = usePage();

const dashboardTitle = computed(() => {
    const role = String(page.props.auth?.role ?? '').toLowerCase();

    if (role === 'receptionist') {
        return 'Guest approval command';
    }

    if (role === 'client') {
        return 'Reservation home';
    }

    return 'Hotel operations overview';
});
</script>

<template>
    <Head :title="dashboardTitle" />

    <RoleDashboardLayout />
</template>

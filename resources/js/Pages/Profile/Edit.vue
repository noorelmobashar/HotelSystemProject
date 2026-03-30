<script setup>
import RoleDashboardLayout from '@/Layouts/RoleDashboardLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <RoleDashboardLayout>
        <div class="h-full overflow-y-auto bg-gradient-to-br from-slate-100 via-slate-100 to-slate-200/60">
            <div class="mx-auto max-w-6xl space-y-6 px-6 py-8 lg:px-10">
                <header class="space-y-1">
                    <h1 class="text-2xl font-semibold text-slate-900">Profile Workspace</h1>
                    <p class="text-sm text-slate-500">
                        Manage your account identity, password, and security controls.
                    </p>
                </header>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.95)]">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.95)]">
                    <UpdatePasswordForm />
                </div>

                <div class="rounded-3xl border border-rose-200 bg-white p-6 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.95)]">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </RoleDashboardLayout>
</template>

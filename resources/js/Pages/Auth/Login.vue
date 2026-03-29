<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-sky-500">
                Welcome Back
            </p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">
                Log in to your hotel workspace
            </h1>
            <p class="mt-3 text-sm leading-6 text-slate-500">
                Use your account credentials to access reservations, guest workflows, and room operations.
            </p>
        </div>

        <div class="mb-6 grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                    Access
                </p>
                <p class="mt-2 text-sm font-medium text-slate-700">
                    Continue to your role-specific dashboard and current tasks.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                    Security
                </p>
                <p class="mt-2 text-sm font-medium text-slate-700">
                    Session access stays tied to your hotel workspace account.
                </p>
            </div>
        </div>

        <div
            v-if="status"
            class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
        >
            {{ status }}
        </div>

        <form
            class="space-y-5"
            @submit.prevent="submit"
        >
            <div>
                <InputLabel for="email" value="Work Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-sky-500 focus:bg-white focus:ring-sky-500"
                    v-model="form.email"
                    required
                    autofocus
                    placeholder="name@hotel.com"
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-sky-500 focus:bg-white focus:ring-sky-500"
                    v-model="form.password"
                    required
                    placeholder="Enter your password"
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <label class="flex items-center text-sm text-slate-600">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm font-medium text-slate-500 transition hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                >
                    Forgot your password?
                </Link>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">
                    Need an account?
                    <Link
                        :href="route('register')"
                        class="font-semibold text-slate-900 transition hover:text-sky-600"
                    >
                        Register here
                    </Link>
                </p>
                <PrimaryButton
                    class="justify-center rounded-2xl bg-slate-900 px-6 py-3 text-sm font-semibold normal-case tracking-normal hover:bg-slate-800 focus:ring-sky-500 sm:min-w-[140px]"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Sign In
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

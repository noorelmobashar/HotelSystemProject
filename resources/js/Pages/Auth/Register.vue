<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-500">
                Create Account
            </p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">
                Register for the hotel workspace
            </h1>
            <p class="mt-3 text-sm leading-6 text-slate-500">
                Create your account to access reservations, profile tools, and your role-based dashboard.
            </p>
        </div>

        <div class="mb-6 grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                    Fast Setup
                </p>
                <p class="mt-2 text-sm font-medium text-slate-700">
                    Create your account and move directly into the guest reservation flow.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                    Unified Account
                </p>
                <p class="mt-2 text-sm font-medium text-slate-700">
                    Your profile, reservations, and dashboard all connect through one account.
                </p>
            </div>
        </div>

        <form
            class="space-y-5"
            @submit.prevent="submit"
        >
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:ring-emerald-500"
                    v-model="form.name"
                    required
                    autofocus
                    placeholder="Your full name"
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Work Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:ring-emerald-500"
                    v-model="form.email"
                    required
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
                    class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:ring-emerald-500"
                    v-model="form.password"
                    required
                    placeholder="Create a password"
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:ring-emerald-500"
                    v-model="form.password_confirmation"
                    required
                    placeholder="Repeat your password"
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">
                    Already registered?
                    <Link
                        :href="route('login')"
                        class="font-semibold text-slate-900 transition hover:text-emerald-600"
                    >
                        Log in here
                    </Link>
                </p>
                <PrimaryButton
                    class="justify-center rounded-2xl bg-slate-900 px-6 py-3 text-sm font-semibold normal-case tracking-normal hover:bg-slate-800 focus:ring-emerald-500 sm:min-w-[140px]"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Create Account
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

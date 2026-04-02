<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    countries: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: '',
    email: '',
    country: '',
    avatar_image: null,
    password: '',
    password_confirmation: '',
});

const localImagePreview = ref(null);

const avatarPreview = computed(() => localImagePreview.value || '/images/default-avatar.svg');

const onAvatarChange = (event) => {
    const [file] = event.target.files ?? [];

    if (!file) {
        form.avatar_image = null;
        localImagePreview.value = null;
        return;
    }

    form.avatar_image = file;
    localImagePreview.value = URL.createObjectURL(file);
};

const submit = () => {
    form.post(route('register'), {
        forceFormData: true,
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
            <h1 class="mt-3 text-center text-3xl font-semibold text-slate-900 sm:text-4xl">
                Register for guest access
            </h1>
            <p class="mt-3 text-center text-sm leading-6 text-slate-500 sm:text-base">
                Create your account to manage reservations and access the hotel system.
            </p>
        </div>

        <div class="mb-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-center text-sm text-slate-600">
            Your account will be used for reservations, profile details, and access to the guest workspace.
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
                <InputLabel for="email" value="Email Address" />

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
                <InputLabel for="country" value="Country" />

                <select
                    id="country"
                    v-model="form.country"
                    required
                    class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-emerald-500 focus:bg-white focus:ring-emerald-500"
                >
                    <option disabled value="">Select your country</option>
                    <option
                        v-for="country in props.countries"
                        :key="country"
                        :value="country"
                    >
                        {{ country }}
                    </option>
                </select>

                <InputError class="mt-2" :message="form.errors.country" />
            </div>

            <div>
                <InputLabel for="avatar_image" value="Profile Image" />

                <div class="mt-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center gap-4">
                        <img
                            :src="avatarPreview"
                            alt="Profile avatar preview"
                            class="h-16 w-16 rounded-2xl border border-slate-200 object-cover"
                        >

                        <div class="min-w-0">
                            <input
                                id="avatar_image"
                                type="file"
                                accept="image/png,image/jpeg,image/jpg,image/webp"
                                class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200"
                                @change="onAvatarChange"
                            >
                            <p class="mt-2 text-sm text-slate-500">
                                Upload a profile image now, or continue and we will assign a default avatar.
                            </p>
                        </div>
                    </div>
                </div>

                <InputError class="mt-2" :message="form.errors.avatar_image" />
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

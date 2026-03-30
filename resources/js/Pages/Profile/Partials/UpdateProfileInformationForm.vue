<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;
const userRole = usePage().props.auth.role;

const form = useForm({
    name: user.name,
    email: user.email,
    gender: user.gender ?? '',
    avatar_image: null,
});

const localImagePreview = ref(null);

const avatarPreview = computed(() => {
    if (localImagePreview.value) {
        return localImagePreview.value;
    }

    if (!user.avatar_image) {
        return '/images/default-avatar.svg';
    }

    if (String(user.avatar_image).startsWith('http')) {
        return user.avatar_image;
    }

    if (String(user.avatar_image).startsWith('/')) {
        return user.avatar_image;
    }

    return `/storage/${user.avatar_image}`;
});

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
    form.patch(route('profile.update'), {
        forceFormData: true,
    });
};

const formatDate = (value) => {
    if (!value) {
        return 'Not available';
    }

    return new Date(value).toLocaleString();
};

const formatLabel = (value) => {
    if (!value) {
        return 'Not set';
    }

    return value
        .split('_')
        .map((chunk) => chunk.charAt(0).toUpperCase() + chunk.slice(1))
        .join(' ');
};
</script>

<template>
    <section class="space-y-6">
        <header class="space-y-2">
            <h2 class="text-xl font-semibold text-slate-900">
                Profile Information
            </h2>

            <p class="text-sm text-slate-500">
                Keep your account details accurate for reservations, approvals, and operational access.
            </p>
        </header>

        <div class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">Role</p>
                <p class="mt-1 text-sm font-semibold text-slate-700">{{ formatLabel(userRole) }}</p>
            </div>

            <div>
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">Email Status</p>
                <p class="mt-1 text-sm font-semibold text-slate-700">
                    {{(userRole == 'admin' || userRole == 'manager' || userRole == 'receptionist') ? 'Verified' : (user.email_verified_at ? 'Verified' : 'Pending verification') }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">Approval Status</p>
                <p class="mt-1 text-sm font-semibold text-slate-700">
                    {{(userRole == 'admin' || userRole == 'manager' || userRole == 'receptionist') ? 'Approved' : (user.approved_at ? 'Approved' : 'Pending approval') }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">Last Login</p>
                <p class="mt-1 text-sm font-semibold text-slate-700">{{ formatDate(user.last_login_at) }}</p>
            </div>
        </div>

        <form
            @submit.prevent="submit"
            class="grid gap-5"
        >
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <InputLabel for="name" value="Full Name" />

                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                    />

                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="gender" value="Gender" />

                    <select
                        id="gender"
                        v-model="form.gender"
                        class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        disabled
                    >
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>

                    <InputError class="mt-2" :message="form.errors.gender" />
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <InputLabel for="email" value="Work Email" />

                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autocomplete="username"
                    />

                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="avatar_image" value="Profile Image" />

                    <input
                        id="avatar_image"
                        type="file"
                        accept="image/png,image/jpeg,image/jpg,image/webp"
                        class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200"
                        @change="onAvatarChange"
                    />

                    <InputError class="mt-2" :message="form.errors.avatar_image" />
                </div>
            </div>

            <div v-if="avatarPreview" class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">Avatar Preview</p>
                <div class="mt-3 flex items-center gap-4">
                    <img
                        :src="avatarPreview"
                        alt="Profile avatar preview"
                        class="h-14 w-14 rounded-2xl border border-slate-200 object-cover"
                    >
                    <p class="truncate text-sm text-slate-500">
                        {{ form.avatar_image ? form.avatar_image.name : 'Current profile image' }}
                    </p>
                </div>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm text-slate-700">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-indigo-600 underline hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-emerald-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-slate-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>

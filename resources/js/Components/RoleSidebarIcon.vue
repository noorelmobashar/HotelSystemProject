<script setup>
defineProps({
    name: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <svg
        v-if="name === 'overview'"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        class="h-4 w-4"
    >
        <path
            d="M4.75 4.75h6.5v6.5h-6.5zM12.75 4.75h6.5v6.5h-6.5zM4.75 12.75h6.5v6.5h-6.5zM12.75 12.75h6.5v6.5h-6.5z"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
        />
    </svg>

    <svg
        v-else-if="name === 'managers'"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        class="h-4 w-4"
    >
        <path
            d="M15 11a3 3 0 100-6 3 3 0 000 6zM7.5 13a3 3 0 100-6 3 3 0 000 6z"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
        />
        <path
            d="M2.5 19a5 5 0 0110 0M12 19a4.5 4.5 0 019 0"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
        />
    </svg>

    <svg
        v-else-if="name === 'receptionists'"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        class="h-4 w-4"
    >
        <path
            d="M3 5.5h18v10H3zM8 18h8M10 15.5h4"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
        />
    </svg>

    <svg
        v-else-if="name === 'clients'"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        class="h-4 w-4"
    >
        <path
            d="M12 11a3.5 3.5 0 100-7 3.5 3.5 0 000 7zM4 19a8 8 0 0116 0"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
        />
    </svg>

    <svg
        v-else-if="name === 'floors'"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        class="h-4 w-4"
    >
        <path
            d="M7 3.5h10v17H7zM10 7h.01M14 7h.01M10 11h.01M14 11h.01M10 15h.01M14 15h.01M11 20.5h2"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
        />
    </svg>

    <svg
        v-else-if="name === 'rooms'"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        class="h-4 w-4"
    >
        <path
            d="M3 12h18M4 12V9a2 2 0 012-2h4a2 2 0 012 2v3M12 12V10a2 2 0 012-2h4a2 2 0 012 2v2M4 15v-3M20 15v-3"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
        />
    </svg>

    <svg
        v-else-if="name === 'approved'"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        class="h-4 w-4"
    >
        <path
            d="M12 21a9 9 0 100-18 9 9 0 000 18zM8.75 12.25l2.25 2.25 4.5-4.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
        />
    </svg>

    <svg
        v-else-if="name === 'reservations'"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        class="h-4 w-4"
    >
        <path
            d="M7 3.5v2M17 3.5v2M4 7h16"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
        />
        <path
            d="M5 5.5h14A1.5 1.5 0 0120.5 7v11a1.5 1.5 0 01-1.5 1.5H5A1.5 1.5 0 013.5 18V7A1.5 1.5 0 015 5.5z"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
        />
    </svg>

    <svg
        v-else-if="name === 'stats'"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        class="h-4 w-4"
    >
        <path
            d="M4 20h16M7 17v-5M12 17V9M17 17v-8"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
        />
    </svg>

    <svg
        v-else-if="name === 'reservation'"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        class="h-4 w-4"
    >
        <path
            d="M4 7.5h16v9H4zM8 10.5h8M8 13.5h5"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
        />
    </svg>

    <svg
        v-else-if="name === 'profile'"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        class="h-4 w-4"
    >
        <path
            d="M12 11a3.5 3.5 0 100-7 3.5 3.5 0 000 7zM4 19a8 8 0 0116 0"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
        />
    </svg>

    <svg
        v-else-if="name === 'logout'"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        class="h-4 w-4"
    >
        <path
            d="M13 8l4 4-4 4M17 12H9M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h5"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
        />
    </svg>

    <svg
        v-else
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        class="h-4 w-4"
    >
        <path
            d="M12 12h.01"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
        />
    </svg>
</template>

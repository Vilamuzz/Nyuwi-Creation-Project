<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    items: {
        type: Array,
        required: true,
        // Each item: { label: string, href?: string }
    },
    separator: {
        type: String,
        default: "/",
    },
});
</script>

<template>
    <nav class="flex items-center space-x-2 text-sm sm:text-base text-gray-600 flex-wrap" aria-label="Breadcrumb">
        <template v-for="(item, index) in items" :key="index">
            <Link v-if="item.href && index < items.length - 1" :href="item.href"
                class="hover:text-orange-500 underline transition-colors">
                {{ item.label }}
            </Link>
            <span v-else class="text-gray-900 font-medium">
                {{ item.label }}
            </span>

            <span v-if="index < items.length - 1" class="text-gray-400 select-none">
                {{ separator }}
            </span>
        </template>
    </nav>
</template>

<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
    src: {
        type: String,
        default: null,
    },
    alt: {
        type: String,
        default: null,
    },
});

const page = usePage();

const logoSrc = computed(() => {
    if (props.src) return props.src;
    return page.props.storeLogo || "/img/Logo/Logo.svg";
});

const logoAlt = computed(() => {
    if (props.alt) return props.alt;
    return page.props.storeName ? `Logo ${page.props.storeName}` : "Logo Perusahaan";
});
</script>

<template>
    <img :src="logoSrc" :alt="logoAlt" class="h-12" />
</template>


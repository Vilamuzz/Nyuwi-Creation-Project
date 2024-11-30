<script setup>
defineProps({
    title: {
        type: String,
        default: "Shop",
    },
    breadcrumb: {
        type: String,
        default: "Home > Shop",
    },
});
</script>

<template>
    <div
        class="relative flex flex-col items-center justify-center text-center space-y-2 h-[300px] bg-cover bg-center"
        :style="{ backgroundImage: `url('/img/background/hero.svg')` }"
    >
        <div class="absolute inset-0 bg-white/30 backdrop-blur-sm"></div>
        <h1 class="text-4xl font-bold z-10">{{ title }}</h1>
        <h2 class="text-lg font-bold z-10">{{ breadcrumb }}</h2>
    </div>
</template>

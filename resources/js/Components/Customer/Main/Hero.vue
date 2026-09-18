<script setup>
defineProps({
    title: {
        type: String,
        default: "",
    },
    subtitle: {
        type: String,
        default: "",
    }
});
</script>

<template>
    <div
        class="relative flex flex-col items-center justify-center text-center space-y-2 h-36 bg-cover bg-center bg-[#fdf7f2]">
        <h1 class="text-4xl font-bold underline">{{ title }}</h1>
        <p class="text-lg font-bold uppercase">{{ subtitle }}</p>
    </div>
</template>

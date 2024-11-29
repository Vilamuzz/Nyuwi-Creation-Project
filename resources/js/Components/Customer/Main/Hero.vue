<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        required: false,
        default: '',
    },
    backgroundImage: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <div
        class="relative flex flex-col items-center justify-center text-center space-y-2 h-[300px] bg-cover bg-center"
        :style="{ backgroundImage: `url(${backgroundImage})` }"
    >
        <!-- Overlay -->
        <div class="absolute inset-0 bg-white/30 backdrop-blur-sm"></div>

        <!-- Content -->
        <h1 class="text-4xl font-bold z-10">{{ title }}</h1>
        <h2 class="text-lg z-10">{{ subtitle }}</h2>
    </div>
</template>





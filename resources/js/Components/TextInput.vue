<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-xl border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm px-3.5 py-2.5 transition duration-150 ease-in-out"
        v-model="model"
        ref="input"
    />
</template>

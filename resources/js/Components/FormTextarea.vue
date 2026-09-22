<script setup>
import { onMounted, ref } from 'vue';
import InputError from '@/Components/InputError.vue';

const model = defineModel({
    type: String,
    required: true,
});

const props = defineProps({
    error: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: '',
    },
    rows: {
        type: [String, Number],
        default: 3,
    },
});

const textarea = ref(null);

onMounted(() => {
    if (textarea.value.hasAttribute('autofocus')) {
        textarea.value.focus();
    }
});

defineExpose({ focus: () => textarea.value.focus() });
</script>

<template>
    <div class="flex flex-col space-y-2">
        <textarea
            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            v-model="model"
            ref="textarea"
            :placeholder="placeholder"
            :required="required"
            :rows="rows"
            v-bind="$attrs"
        ></textarea>
        <InputError :message="error" />
    </div>
</template>
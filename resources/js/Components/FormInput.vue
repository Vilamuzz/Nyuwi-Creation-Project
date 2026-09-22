<script setup>
import { onMounted, ref } from 'vue';
import InputError from '@/Components/InputError.vue';

const model = defineModel({
    type: [String, Number],
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
    type: {
        type: String,
        default: 'text',
    },
    label: {
        type: String,
        default: '',
    },
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
    <div class="flex flex-col space-y-2">
        <label v-if="label" class="text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-500"> *</span>
        </label>
        <input
            class="rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
            v-model="model"
            ref="input"
            :placeholder="placeholder"
            :type="type"
            :required="required"
            v-bind="$attrs"
        />
        <InputError :message="error" />
    </div>
</template>
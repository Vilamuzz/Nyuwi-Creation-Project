<script setup>
import { ref } from 'vue';
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
    options: {
        type: Array,
        default: () => [],
    },
    optionLabel: {
        type: String,
        default: 'label',
    },
    optionValue: {
        type: String,
        default: 'value',
    },
    placeholder: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
});

const select = ref(null);

defineExpose({ focus: () => select.value.focus() });
</script>

<template>
    <div class="flex flex-col space-y-2">
        <label v-if="label" class="text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-500"> *</span>
        </label>
        <select
            class="rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
            v-model="model"
            ref="select"
            :required="required"
            v-bind="$attrs"
        >
            <option v-if="placeholder" value="">
                {{ placeholder }}
            </option>
            <option
                v-for="(option, index) in options"
                :key="index"
                :value="typeof option === 'object' ? option[optionValue] : option"
            >
                {{ typeof option === 'object' ? option[optionLabel] : option }}
            </option>
        </select>
        <InputError :message="error" />
    </div>
</template>
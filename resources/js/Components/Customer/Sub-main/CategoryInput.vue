<script setup>
import { ref, watch } from "vue";

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: [String, Number],
        default: "",
    },
});

const emit = defineEmits(["update:modelValue", "categoryChange"]);

const selectedCategory = ref(props.modelValue);

// Watch for changes and emit to parent
watch(selectedCategory, (newValue) => {
    emit("update:modelValue", newValue);
    emit("categoryChange", newValue);
});
</script>

<template>
    <div class="mb-4">
        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">
            <span class="font-semibold">Kategori*</span>
        </label>
        <select
            v-model="selectedCategory"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500"
        >
            <option disabled value="">Pilih Kategori</option>
            <option
                v-for="category in categories"
                :key="category.id"
                :value="category.id"
            >
                {{ category.name }}
            </option>
        </select>
    </div>
</template>
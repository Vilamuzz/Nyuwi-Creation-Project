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
    newCategory: {
        type: String,
        default: "",
    },
});

const emit = defineEmits([
    "update:modelValue",
    "update:newCategory",
    "categoryChange",
]);

const selectedCategory = ref(props.modelValue);

// Watch for changes and emit to parent
watch(selectedCategory, (newValue) => {
    emit("update:modelValue", newValue);
    emit("categoryChange", newValue);
});
</script>

<template>
    <div class="mb-4">
        <label class="mb-2 block text-sm font-medium text-gray-700">
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
            <option value="new">Tambah Kategori Baru</option>
        </select>
        <div v-if="selectedCategory === 'new'" class="mt-2">
            <input
                :value="newCategory"
                @input="$emit('update:newCategory', $event.target.value)"
                placeholder="Nama Kategori Baru"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500"
            />
        </div>
    </div>
</template>

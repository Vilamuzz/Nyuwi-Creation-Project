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
    <div class="form-control mb-4">
        <label class="label">
            <span class="label-text font-semibold">Kategori*</span>
        </label>
        <select
            v-model="selectedCategory"
            class="select select-bordered w-full"
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
                class="input input-bordered w-full"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({
    sizes: {
        type: Array,
        default: () => [],
    },
    selectedCategory: {
        type: [String, Number],
        default: "",
    },
    categorySizes: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(["update:sizes", "notification"]);

const sizeList = ref([...props.sizes]);
const newSize = ref("");

const addSize = () => {
    if (newSize.value.trim()) {
        sizeList.value.push(newSize.value.trim());
        emit("update:sizes", [...sizeList.value]);
        newSize.value = ""; // Clear input after adding
        emit("notification", "Ukuran berhasil ditambahkan", "success");
    }
};

const removeSize = (index) => {
    sizeList.value.splice(index, 1);
    emit("update:sizes", [...sizeList.value]);
    emit("notification", "Ukuran berhasil dihapus", "info");
};
</script>

<template>
    <div class="mb-4">
        <label class="mb-2 block text-sm font-medium text-gray-700">
            <span class="font-semibold">Ukuran</span>
        </label>
        <div class="flex">
            <input
                type="text"
                v-model="newSize"
                placeholder="Masukkan ukuran"
                class="min-w-0 flex-grow rounded-l-lg border border-gray-300 px-3 py-2 focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500"
                @keyup.enter="addSize"
            />
            <button
                type="button"
                @click="addSize"
                class="rounded-r-lg bg-orange-500 px-4 py-2 font-semibold text-white hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500"
            >
                +
            </button>
        </div>

        <!-- Display sizes -->
        <div class="flex flex-wrap gap-2 mt-2">
            <div
                v-for="(size, index) in sizeList"
                :key="index"
                class="inline-flex items-center gap-2 rounded-full bg-gray-800 px-3 py-1 text-sm text-white"
            >
                <span>{{ size }}</span>
                <button
                    @click="removeSize(index)"
                    type="button"
                    class="text-red-300 hover:text-red-100"
                >
                    ✕
                </button>
            </div>
        </div>
    </div>
</template>

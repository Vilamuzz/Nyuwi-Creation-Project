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
    <div class="form-control mb-4">
        <label class="label">
            <span class="label-text font-semibold">Ukuran</span>
        </label>
        <div class="join">
            <input
                type="text"
                v-model="newSize"
                placeholder="Masukkan ukuran"
                class="input input-bordered join-item flex-grow"
                @keyup.enter="addSize"
            />
            <button
                type="button"
                @click="addSize"
                class="btn btn-primary join-item"
            >
                +
            </button>
        </div>

        <!-- Display sizes -->
        <div class="flex flex-wrap gap-2 mt-2">
            <div
                v-for="(size, index) in sizeList"
                :key="index"
                class="badge badge-neutral gap-2"
            >
                <span>{{ size }}</span>
                <button
                    @click="removeSize(index)"
                    type="button"
                    class="text-error hover:text-error-focus"
                >
                    ✕
                </button>
            </div>
        </div>
    </div>
</template>

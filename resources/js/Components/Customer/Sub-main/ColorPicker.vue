<script setup>
import { ref } from "vue";

const props = defineProps({
    colors: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["update:colors", "notification"]);

const colorButtons = ref([]);
const currentColor = ref("#000000");

// Initialize colorButtons from props
if (props.colors.length > 0) {
    colorButtons.value = props.colors.map((color) => ({ hex: color }));
}

const addNewColor = () => {
    colorButtons.value.push({
        hex: currentColor.value,
    });
    const updatedColors = [...props.colors, currentColor.value];
    emit("update:colors", updatedColors);
    emit("notification", "Warna berhasil ditambahkan", "success");
};

const removeColorButton = (index) => {
    colorButtons.value.splice(index, 1);
    const updatedColors = [...props.colors];
    updatedColors.splice(index, 1);
    emit("update:colors", updatedColors);
    emit("notification", "Warna berhasil dihapus", "info");
};
</script>

<template>
    <div class="form-control mb-4">
        <label class="label">
            <span class="label-text font-semibold">Warna</span>
        </label>
        <div class="flex flex-wrap gap-2">
            <!-- Color Buttons -->
            <div
                v-for="(button, index) in colorButtons"
                :key="index"
                class="flex items-center gap-2"
            >
                <div
                    class="w-10 h-10 rounded-full border-2 border-base-300"
                    :style="{ backgroundColor: button.hex }"
                ></div>
                <button
                    @click="removeColorButton(index)"
                    type="button"
                    class="btn btn-circle btn-xs btn-error"
                >
                    ✕
                </button>
            </div>

            <!-- Color Input Button -->
            <div class="relative">
                <input
                    type="color"
                    v-model="currentColor"
                    @change="addNewColor"
                    class="absolute inset-0 opacity-0 w-10 h-10 cursor-pointer"
                />
                <div
                    class="w-10 h-10 rounded-full border-2 border-dashed border-base-300 flex items-center justify-center hover:border-primary cursor-pointer"
                >
                    +
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
input[type="color"]::-webkit-color-swatch-wrapper {
    padding: 0;
}
input[type="color"]::-webkit-color-swatch {
    border: none;
    border-radius: 50%;
}
</style>

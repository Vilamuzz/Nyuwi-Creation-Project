resources\js\Components\Customer\Sub-main\ImageInput.vue
<script setup>
import { ref, onUnmounted, onMounted } from "vue";
import { ImageUp } from "lucide-vue-next";

const props = defineProps({
    modelValue: {
        type: [File, null],
        default: null,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    currentImage: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["update:modelValue", "notification"]);

const imagePreview = ref(null);
const isDragging = ref(false);
const fileInput = ref(null);

// Set initial preview from current image
onMounted(() => {
    if (props.currentImage) {
        imagePreview.value = `/storage/products/${props.currentImage}`;
    }
});

const processFile = (file) => {
    if (!file) return;

    // Validate file type
    const validTypes = ["image/jpeg", "image/jpg", "image/png"];
    if (!validTypes.includes(file.type)) {
        emit(
            "notification",
            "File harus berupa gambar (JPG, JPEG, PNG).",
            "error"
        );
        if (fileInput.value) fileInput.value.value = "";
        return;
    }

    // Validate file size (max 10MB)
    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
    if (file.size > maxSize) {
        emit(
            "notification",
            "Ukuran file terlalu besar. Maksimal 10MB.",
            "error"
        );
        if (fileInput.value) fileInput.value.value = "";
        return;
    }

    emit("update:modelValue", file);

    // Clean up previous blob URL if it exists
    if (imagePreview.value && imagePreview.value.startsWith("blob:")) {
        URL.revokeObjectURL(imagePreview.value);
    }

    imagePreview.value = URL.createObjectURL(file);
    emit("notification", "Gambar berhasil dipilih", "success");
};

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    processFile(file);
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleDragOver = (e) => {
    e.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = (e) => {
    e.preventDefault();
    isDragging.value = false;
};

const handleDrop = (e) => {
    e.preventDefault();
    isDragging.value = false;
    const file = e.dataTransfer.files[0];
    processFile(file);

    // Update the file input for form submission
    if (file) {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.value.files = dataTransfer.files;
    }
};

const deleteImage = () => {
    // If there was a blob URL, revoke it to prevent memory leaks
    if (imagePreview.value && imagePreview.value.startsWith("blob:")) {
        URL.revokeObjectURL(imagePreview.value);
    }

    // Clear the preview
    imagePreview.value = null;

    // Clear the form image
    emit("update:modelValue", null);

    // Reset the file input value
    if (fileInput.value) {
        fileInput.value.value = "";
    }

    emit("notification", "Gambar berhasil dihapus", "info");
};

// Cleanup preview URL when component unmounts
onUnmounted(() => {
    if (imagePreview.value && imagePreview.value.startsWith("blob:")) {
        URL.revokeObjectURL(imagePreview.value);
    }
});
</script>

<template>
    <div class="form-control mb-4">
        <label class="label">
            <span class="label-text font-semibold">Gambar Produk*</span>
        </label>

        <!-- Show image preview when available -->
        <div v-if="imagePreview" class="flex justify-start items-center">
            <div class="relative inline-block">
                <img
                    :src="imagePreview"
                    class="max-h-[155px] object-cover rounded-lg shadow-sm"
                    alt="Preview"
                />
                <button
                    type="button"
                    @click="deleteImage"
                    class="absolute -top-1.5 -right-1.5 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center cursor-pointer shadow-sm transition-opacity hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-red-400"
                    aria-label="Delete image"
                >
                    ✕
                </button>
            </div>
        </div>

        <!-- Upload box - only visible when no image -->
        <div v-else class="w-full">
            <input
                ref="fileInput"
                type="file"
                accept="image/png, image/jpeg, image/jpg"
                @change="handleImageUpload"
                class="hidden"
            />

            <div
                class="w-full bg-base-100 border-2 border-dashed border-base-300 hover:border-primary rounded-lg flex flex-col items-center justify-center p-8 cursor-pointer transition-all duration-200 min-h-[155px]"
                :class="{
                    'bg-blue-50 border-blue-400 border-2 border-dashed shadow-lg':
                        isDragging,
                }"
                @click="triggerFileInput"
                @dragover.prevent="handleDragOver"
                @dragleave.prevent="handleDragLeave"
                @drop.prevent="handleDrop"
            >
                <div class="flex flex-col items-center gap-4">
                    <!-- Upload Icon -->
                    <ImageUp />
                    <span class="text-base-300">
                        Seret dan jatuhkan gambar di sini
                    </span>
                </div>
            </div>
        </div>

        <div v-if="errors.image" class="label">
            <span class="label-text-alt text-error">{{ errors.image }}</span>
        </div>
    </div>
</template>

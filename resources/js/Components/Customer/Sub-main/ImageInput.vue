resources\js\Components\Customer\Sub-main\ImageInput.vue
<script setup>
import { ref, onUnmounted, onMounted, watch } from "vue";
import { ImageUp } from "lucide-vue-next";

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    currentImages: {
        type: Array,
        default: () => [],
    },
    maxImages: {
        type: Number,
        default: 10,
    },
});

const emit = defineEmits(["update:modelValue", "notification"]);

const imagePreviews = ref([]);
const isDragging = ref(false);
const fileInput = ref(null);

// Set initial previews from current images
onMounted(() => {
    if (props.currentImages && props.currentImages.length > 0) {
        imagePreviews.value = props.currentImages.map((image) => ({
            url: `/storage/products/${image}`,
            isExisting: true,
            fileName: image,
        }));
    }
});

const processFiles = (files) => {
    if (!files || files.length === 0) return;

    const fileArray = Array.from(files);
    const currentCount = imagePreviews.value.length;

    // Check if adding these files would exceed the limit
    if (currentCount + fileArray.length > props.maxImages) {
        emit(
            "notification",
            `Maksimal ${props.maxImages} gambar yang dapat dipilih. Saat ini ada ${currentCount} gambar.`,
            "error"
        );
        return;
    }

    const validFiles = [];

    for (let file of fileArray) {
        // Validate file type
        const validTypes = ["image/jpeg", "image/jpg", "image/png"];
        if (!validTypes.includes(file.type)) {
            emit(
                "notification",
                `File ${file.name} harus berupa gambar (JPG, JPEG, PNG).`,
                "error"
            );
            continue;
        }

        // Validate file size (max 2MB)
        const maxSize = 2 * 1024 * 1024; // 2MB in bytes
        if (file.size > maxSize) {
            emit(
                "notification",
                `File ${file.name} terlalu besar. Maksimal 2MB.`,
                "error"
            );
            continue;
        }

        validFiles.push(file);
    }

    if (validFiles.length === 0) {
        if (fileInput.value) fileInput.value.value = "";
        return;
    }

    // Add valid files to previews and model
    validFiles.forEach((file) => {
        imagePreviews.value.push({
            url: URL.createObjectURL(file),
            isExisting: false,
            file: file,
            fileName: file.name,
        });
    });

    // Update model value with all files (existing + new)
    const allFiles = [...props.modelValue, ...validFiles];
    emit("update:modelValue", allFiles);

    emit(
        "notification",
        `${validFiles.length} gambar berhasil ditambahkan`,
        "success"
    );

    // Clear file input
    if (fileInput.value) fileInput.value.value = "";
};

const handleImageUpload = (event) => {
    const files = event.target.files;
    processFiles(files);
};

const triggerFileInput = () => {
    if (imagePreviews.value.length >= props.maxImages) {
        emit(
            "notification",
            `Maksimal ${props.maxImages} gambar yang dapat dipilih.`,
            "error"
        );
        return;
    }
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
    const files = e.dataTransfer.files;
    processFiles(files);
};

const deleteImage = (index) => {
    const imageToDelete = imagePreviews.value[index];

    // If it's a blob URL, revoke it to prevent memory leaks
    if (!imageToDelete.isExisting && imageToDelete.url.startsWith("blob:")) {
        URL.revokeObjectURL(imageToDelete.url);
    }

    // Remove from previews
    imagePreviews.value.splice(index, 1);

    // Update model value - only include non-existing files
    const newFiles = [];
    let fileIndex = 0;

    imagePreviews.value.forEach((preview) => {
        if (!preview.isExisting) {
            newFiles.push(props.modelValue[fileIndex]);
            fileIndex++;
        }
    });

    emit("update:modelValue", newFiles);
    emit("notification", "Gambar berhasil dihapus", "info");
};

// Cleanup preview URLs when component unmounts
onUnmounted(() => {
    imagePreviews.value.forEach((preview) => {
        if (!preview.isExisting && preview.url.startsWith("blob:")) {
            URL.revokeObjectURL(preview.url);
        }
    });
});
</script>

<template>
    <div class="form-control mb-4">
        <label class="label">
            <span class="label-text font-semibold">
                Gambar Produk* ({{ imagePreviews.length }}/{{ maxImages }})
            </span>
        </label>

        <!-- Image Grid Preview -->
        <div v-if="imagePreviews.length > 0" class="mb-4">
            <div
                class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto border rounded-lg p-2"
            >
                <div
                    v-for="(preview, index) in imagePreviews"
                    :key="index"
                    class="relative group"
                >
                    <img
                        :src="preview.url"
                        :alt="`Preview ${index + 1}`"
                        class="w-full h-24 object-cover rounded border"
                    />
                    <button
                        type="button"
                        @click="deleteImage(index)"
                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
                        aria-label="Delete image"
                    >
                        ×
                    </button>
                    <!-- Indicator for existing vs new images -->
                    <div
                        v-if="preview.isExisting"
                        class="absolute bottom-1 left-1 bg-blue-500 text-white text-xs px-1 rounded"
                    >
                        Existing
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload box -->
        <div class="w-full">
            <input
                ref="fileInput"
                type="file"
                multiple
                accept="image/png, image/jpeg, image/jpg"
                @change="handleImageUpload"
                class="hidden"
            />

            <div
                class="w-full bg-base-100 border-2 border-dashed border-base-300 hover:border-primary rounded-lg flex flex-col items-center justify-center p-8 cursor-pointer transition-all duration-200 min-h-[120px]"
                :class="{
                    'bg-blue-50 border-blue-400 border-2 border-dashed shadow-lg':
                        isDragging,
                    'border-gray-300 cursor-not-allowed opacity-50':
                        imagePreviews.length >= maxImages,
                }"
                @click="triggerFileInput"
                @dragover.prevent="handleDragOver"
                @dragleave.prevent="handleDragLeave"
                @drop.prevent="handleDrop"
            >
                <div class="flex flex-col items-center gap-4">
                    <!-- Upload Icon -->
                    <ImageUp
                        :class="{
                            'text-gray-400': imagePreviews.length >= maxImages,
                        }"
                    />
                    <div class="text-center">
                        <span
                            class="text-base-content"
                            :class="{
                                'text-gray-400':
                                    imagePreviews.length >= maxImages,
                            }"
                        >
                            {{
                                imagePreviews.length >= maxImages
                                    ? `Maksimal ${maxImages} gambar tercapai`
                                    : "Seret dan jatuhkan gambar di sini atau klik untuk memilih"
                            }}
                        </span>
                        <div class="text-xs text-base-content/60 mt-1">
                            Format: JPEG, JPG, PNG. Maksimal 2MB per file.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="errors.images" class="label">
            <span class="label-text-alt text-error">{{ errors.images }}</span>
        </div>
    </div>
</template>

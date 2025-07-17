<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed, watch, onUnmounted } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import ToastNotification from "@/Components/Customer/Sub-main/ToastNotification.vue";
import { ImageUp } from "lucide-vue-next";

// Add image preview ref
const imagePreview = ref(null);
const isDragging = ref(false);
const fileInput = ref(null);

// Toast notification state
const toastMessage = ref("");
const toastType = ref("info");
const showToast = ref(false);

defineProps({ errors: Object, categories: Array });

// Preset sizes for each category
const categorySizes = {
    1: ["14cm", "16cm", "18cm", "20cm"], // Gelang sizes
    2: ["40cm", "45cm", "50cm", "55cm"], // Kalung sizes
    3: ["5", "6", "7", "8", "9"], // Cincin sizes
    4: ["Small", "Medium", "Large"], // Bunga sizes
    5: ["Small", "Medium", "Large"], // Anting sizes
};

const form = useForm({
    name: "",
    stock: "",
    price: "",
    category_id: "",
    new_category: "",
    description: "",
    image: null, // Change to null instead of empty string
    colors: [],
    sizes: [],
});

const selectedCategory = ref("");
const sizeList = ref([]);
const newSize = ref("");

// Watch for category changes
watch(selectedCategory, (newCategoryId) => {
    if (newCategoryId && newCategoryId !== "new") {
        // Clear existing sizes
        sizeList.value = [];
        form.sizes = [];

        // Add preset sizes for the selected category
        const presetSizes = categorySizes[newCategoryId];
        if (presetSizes) {
            sizeList.value = [...presetSizes];
            form.sizes = [...presetSizes];
        }
    }
});

// Function to show toast notifications
const showNotification = (message, type = "info") => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
};

// Function to close toast
const closeToast = () => {
    showToast.value = false;
};

const saveProduct = () => {
    if (selectedCategory.value === "new") {
        form.new_category = form.new_category;
    } else {
        form.category_id = selectedCategory.value;
    }

    form.post(route("products.store"), {
        onSuccess: () => {
            form.reset();
            showNotification("Produk berhasil ditambahkan!", "success");
        },
        onError: (errors) => {
            const errorMessages = Object.values(errors).flat();
            showNotification(
                errorMessages[0] || "Terjadi kesalahan saat menyimpan produk",
                "error"
            );
        },
    });
};

const newColor = ref("");

const addColor = () => {
    if (newColor.value.trim()) {
        form.colors.push(newColor.value.trim());
        newColor.value = "";
        showNotification("Warna berhasil ditambahkan", "success");
    }
};

const removeColor = (index) => {
    form.colors.splice(index, 1);
    showNotification("Warna berhasil dihapus", "info");
};

const addSize = () => {
    if (newSize.value.trim()) {
        sizeList.value.push(newSize.value.trim());
        form.sizes.push(newSize.value.trim());
        newSize.value = ""; // Clear input after adding
        showNotification("Ukuran berhasil ditambahkan", "success");
    }
};

const removeSize = (index) => {
    sizeList.value.splice(index, 1);
    form.sizes.splice(index, 1);
    showNotification("Ukuran berhasil dihapus", "info");
};

const colorButtons = ref([]);
const currentColor = ref("#000000");

const addNewColor = () => {
    colorButtons.value.push({
        hex: currentColor.value,
    });
    form.colors.push(currentColor.value);
    showNotification("Warna berhasil ditambahkan", "success");
};

const removeColorButton = (index) => {
    colorButtons.value.splice(index, 1);
    form.colors.splice(index, 1);
    showNotification("Warna berhasil dihapus", "info");
};

const editColor = (index) => {
    colorButtons.value[index].isEditing = true;
    currentColor.value = colorButtons.value[index].hex;
    showColorPicker.value = true;
};

const updateColor = (index) => {
    colorButtons.value[index].hex = currentColor.value;
    form.colors[index] = currentColor.value;
    colorButtons.value[index].isEditing = false;
    showColorPicker.value = false;
    showNotification("Warna berhasil diperbarui", "success");
};

// Updated image handling function
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    processFile(file);
};

const processFile = (file) => {
    if (!file) return;

    // Validate file type
    const validTypes = ["image/jpeg", "image/jpg", "image/png"];
    if (!validTypes.includes(file.type)) {
        showNotification("File harus berupa gambar (JPG, JPEG, PNG).", "error");
        if (fileInput.value) fileInput.value.value = "";
        return;
    }

    // Validate file size (max 10MB)
    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
    if (file.size > maxSize) {
        showNotification("Ukuran file terlalu besar. Maksimal 10MB.", "error");
        if (fileInput.value) fileInput.value.value = "";
        return;
    }

    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
    showNotification("Gambar berhasil dipilih", "success");
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
    form.image = null;

    // Reset the file input value
    if (fileInput.value) {
        fileInput.value.value = "";
    }

    showNotification("Gambar berhasil dihapus", "info");
};

// Cleanup preview URL when component unmounts
onUnmounted(() => {
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
    }
});
</script>

<template>
    <Head title="Create Products" />

    <AdminLayout pageTitle="Create Product">
        <!-- Toast Notification Component -->
        <ToastNotification
            :message="toastMessage"
            :type="toastType"
            :show="showToast"
            @close="closeToast"
        />

        <div class="mx-12 my-10">
            <form @submit.prevent="saveProduct">
                <div class="flex flex-row space-x-6 w-full">
                    <div class="flex flex-col w-1/2">
                        <!-- Nama Produk -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold"
                                    >Nama Produk*</span
                                >
                            </label>
                            <input
                                type="text"
                                v-model="form.name"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': errors.name }"
                                required
                            />
                            <div v-if="errors.name" class="label">
                                <span class="label-text-alt text-error">{{
                                    errors.name
                                }}</span>
                            </div>
                        </div>

                        <!-- Jumlah Stok -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold"
                                    >Jumlah Stok*</span
                                >
                            </label>
                            <input
                                type="number"
                                v-model="form.stock"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': errors.stock }"
                                required
                                min="1"
                            />
                            <div v-if="errors.stock" class="label">
                                <span class="label-text-alt text-error">{{
                                    errors.stock
                                }}</span>
                            </div>
                        </div>

                        <!-- Harga -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold"
                                    >Harga*</span
                                >
                            </label>
                            <input
                                type="number"
                                v-model="form.price"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': errors.price }"
                                required
                                min="1"
                            />
                            <div v-if="errors.price" class="label">
                                <span class="label-text-alt text-error">{{
                                    errors.price
                                }}</span>
                            </div>
                        </div>

                        <!-- Kategori -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold"
                                    >Kategori*</span
                                >
                            </label>
                            <select
                                v-model="selectedCategory"
                                class="select select-bordered w-full"
                            >
                                <option disabled value="">
                                    Pilih Kategori
                                </option>
                                <option
                                    v-for="category in categories"
                                    :key="category.id"
                                    :value="category.id"
                                >
                                    {{ category.name }}
                                </option>
                                <option value="new">
                                    Tambah Kategori Baru
                                </option>
                            </select>
                            <div v-if="selectedCategory === 'new'" class="mt-2">
                                <input
                                    v-model="form.new_category"
                                    placeholder="Nama Kategori Baru"
                                    class="input input-bordered w-full"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col w-1/2">
                        <!-- Gambar -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold"
                                    >Gambar Produk</span
                                >
                            </label>

                            <!-- Show image preview when available -->
                            <div
                                v-if="imagePreview"
                                class="flex justify-start items-center"
                            >
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
                                    <div
                                        class="flex flex-col items-center gap-4"
                                    >
                                        <!-- Upload Icon -->
                                        <ImageUp />

                                        <span class="text-base-300">
                                            Seret dan jatuhkan gambar di sini
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="form.errors.image" class="label">
                                <span class="label-text-alt text-error">{{
                                    form.errors.image
                                }}</span>
                            </div>
                        </div>

                        <!-- Color Picker Section -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold"
                                    >Warna (Optional)</span
                                >
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

                        <!-- Ukuran (Optional) -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold"
                                    >Ukuran (Optional)</span
                                >
                            </label>
                            <!-- Only show custom size input if category is selected -->
                            <div class="join" v-if="selectedCategory">
                                <input
                                    type="text"
                                    v-model="newSize"
                                    placeholder="Masukkan ukuran"
                                    class="input input-bordered join-item flex-grow"
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
                    </div>
                </div>

                <div>
                    <!-- Deskripsi -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-semibold"
                                >Deskripsi</span
                            >
                        </label>
                        <textarea
                            v-model="form.description"
                            class="textarea textarea-bordered w-full"
                            :class="{
                                'textarea-error': form.errors.description,
                            }"
                            rows="4"
                            placeholder="Deskripsi produk..."
                        ></textarea>
                        <div v-if="form.errors.description" class="label">
                            <span class="label-text-alt text-error">{{
                                form.errors.description
                            }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4 mt-6">
                    <Link
                        :href="route('products.index')"
                        class="btn btn-neutral flex-1"
                    >
                        Kembali
                    </Link>
                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="btn btn-primary flex-1"
                        :class="{ loading: form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? "Menyimpan..." : "Tambah Produk" }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
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

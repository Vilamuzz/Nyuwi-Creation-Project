<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";

// Add new refs
const imagePreview = ref(null);
const colorButtons = ref([]);
const currentColor = ref("#000000");
const selectedCategory = ref("");
const sizeList = ref([]);
const newSize = ref("");

// Preset sizes for each category
const categorySizes = {
    1: ["14cm", "16cm", "18cm", "20cm"],
    2: ["40cm", "45cm", "50cm", "55cm"],
    3: ["5", "6", "7", "8", "9"],
    4: ["Small", "Medium", "Large"],
    5: ["Small", "Medium", "Large"],
};

const props = defineProps({
    errors: Object,
    product: Object,
    categories: Array,
});

// Update form structure
const form = useForm({
    name: props.product.name,
    stock: props.product.stock,
    price: props.product.price,
    category_id: props.product.category_id,
    new_category: "",
    description: props.product.description,
    image: null, // Only send when changed
    colors: props.product.colors?.map((c) => c.color) || [],
    sizes: props.product.sizes?.map((s) => s.size) || [],
    _method: "PUT", // Add method explicitly
});

// Initialize existing colors
if (props.product.colors) {
    colorButtons.value = props.product.colors.map((c) => ({
        hex: c.color,
        isEditing: false,
    }));
}

// Initialize existing sizes
if (props.product.sizes) {
    sizeList.value = props.product.sizes.map((s) => s.size);
}

// Initialize selected category
selectedCategory.value = props.product.category_id;

// Watch for category changes
watch(selectedCategory, (newCategoryId) => {
    if (newCategoryId && newCategoryId !== "new") {
        sizeList.value = [];
        form.sizes = [];
        const presetSizes = categorySizes[newCategoryId];
        if (presetSizes) {
            sizeList.value = [...presetSizes];
            form.sizes = [...presetSizes];
        }
    }
});

// Add color management functions
const addNewColor = () => {
    colorButtons.value.push({
        hex: currentColor.value,
    });
    form.colors.push(currentColor.value);
};

const removeColorButton = (index) => {
    colorButtons.value.splice(index, 1);
    form.colors.splice(index, 1);
};

// Add size management functions
const addSize = () => {
    if (newSize.value.trim()) {
        sizeList.value.push(newSize.value.trim());
        form.sizes.push(newSize.value.trim());
        newSize.value = "";
    }
};

const removeSize = (index) => {
    sizeList.value.splice(index, 1);
    form.sizes.splice(index, 1);
};

// Add image handling
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

// Update the updateProduct function
const updateProduct = () => {
    // Form validation
    if (!form.name || !form.price || !form.stock) {
        return;
    }

    // Handle category selection
    if (form.category_id === "new" && form.new_category) {
        form.category_id = null; // Clear category_id if creating new category
    }

    // Submit form
    form.put(route("products.update", props.product.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect to index page on success
            window.location = route("products.index");
        },
        onError: (errors) => {
            console.error("Form submission errors:", errors);
        },
        onFinish: () => {
            // Clean up
            if (imagePreview.value) {
                URL.revokeObjectURL(imagePreview.value);
            }
        },
    });
};
</script>

<template>
    <Head title="Edit Products" />

    <div class="bg-orange-200 px-8 py-4">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Edit Product
        </h2>
    </div>

    <div v-if="$page.props.flash.message" class="alert">
        {{ $page.props.flash.message }}
    </div>

    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md m-16">
        <form @submit.prevent="updateProduct">
            <!-- Name Input -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2"
                    >Nama Produk</label
                >
                <input
                    type="text"
                    v-model="form.name"
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                    :class="{ 'border-red-500': errors.name }"
                    required
                />
                <div v-if="errors.name" class="text-red-500 text-sm mt-1">
                    {{ errors.name }}
                </div>
            </div>

            <!-- Stock Input -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2"
                    >Stok</label
                >
                <input
                    type="number"
                    v-model="form.stock"
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                    :class="{ 'border-red-500': errors.stock }"
                    required
                />
                <div v-if="errors.stock" class="text-red-500 text-sm mt-1">
                    {{ errors.stock }}
                </div>
            </div>

            <!-- Price Input -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2"
                    >Harga</label
                >
                <input
                    type="number"
                    v-model="form.price"
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                    :class="{ 'border-red-500': errors.price }"
                    placeholder="Rp10,000"
                    required
                />
                <div v-if="errors.price" class="text-red-500 text-sm mt-1">
                    {{ errors.price }}
                </div>
            </div>

            <!-- Category Input -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2"
                    >Kategori</label
                >
                <select
                    v-model="form.category_id"
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                    :class="{ 'border-red-500': errors.category_id }"
                    required
                >
                    <option value="">Pilih Kategori</option>
                    <option
                        v-for="category in categories"
                        :key="category.id"
                        :value="category.id"
                    >
                        {{ category.name }}
                    </option>
                    <option value="new">+ Tambah Kategori Baru</option>
                </select>
                <div
                    v-if="errors.category_id"
                    class="text-red-500 text-sm mt-1"
                >
                    {{ errors.category_id }}
                </div>
            </div>

            <!-- New Category Input (if needed) -->
            <div v-if="form.category_id === 'new'" class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2"
                    >Kategori Baru</label
                >
                <input
                    type="text"
                    v-model="form.new_category"
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                    :class="{ 'border-red-500': errors.new_category }"
                    required
                />
                <div
                    v-if="errors.new_category"
                    class="text-red-500 text-sm mt-1"
                >
                    {{ errors.new_category }}
                </div>
            </div>

            <!-- Description Input -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2"
                    >Deskripsi</label
                >
                <textarea
                    v-model="form.description"
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                    :class="{ 'border-red-500': errors.description }"
                    rows="4"
                ></textarea>
                <div
                    v-if="errors.description"
                    class="text-red-500 text-sm mt-1"
                >
                    {{ errors.description }}
                </div>
            </div>

            <!-- Image Input -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2"
                    >Gambar</label
                >
                <input
                    type="file"
                    @input="handleImageUpload"
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                    :class="{ 'border-red-500': errors.image }"
                    accept="image/*"
                />
                <div v-if="errors.image" class="text-red-500 text-sm mt-1">
                    {{ errors.image }}
                </div>
                <div v-if="imagePreview || product.image" class="mt-2">
                    <img
                        :src="
                            imagePreview || `/storage/products/${product.image}`
                        "
                        class="w-32 h-32 object-cover rounded-md"
                        alt="Preview"
                    />
                </div>
            </div>

            <!-- Add Color Picker Section -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    Warna (Optional)
                </label>
                <div class="flex flex-wrap gap-2">
                    <div
                        v-for="(button, index) in colorButtons"
                        :key="index"
                        class="flex items-center gap-2"
                    >
                        <div
                            class="w-10 h-10 rounded-full border border-gray-300"
                            :style="{ backgroundColor: button.hex }"
                        ></div>
                        <button
                            @click="removeColorButton(index)"
                            type="button"
                            class="text-red-500 hover:text-red-700"
                        >
                            &times;
                        </button>
                    </div>
                    <div class="relative">
                        <input
                            type="color"
                            v-model="currentColor"
                            @change="addNewColor"
                            class="absolute inset-0 opacity-0 w-10 h-10 cursor-pointer"
                        />
                        <div
                            class="w-10 h-10 rounded-full border border-dashed border-gray-300 flex items-center justify-center hover:border-gray-400"
                        >
                            +
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Size Section -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    Ukuran (Optional)
                </label>
                <div class="flex gap-2" v-if="selectedCategory">
                    <input
                        type="text"
                        v-model="newSize"
                        placeholder="Masukkan ukuran"
                        class="flex-grow px-4 py-2 border rounded-md"
                    />
                    <button
                        type="button"
                        @click="addSize"
                        class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
                    >
                        +
                    </button>
                </div>
                <div class="flex flex-wrap gap-2 mt-2">
                    <div
                        v-for="(size, index) in sizeList"
                        :key="index"
                        class="flex items-center gap-2 bg-gray-100 px-3 py-1 rounded-md"
                    >
                        <span>{{ size }}</span>
                        <button
                            @click="removeSize(index)"
                            type="button"
                            class="text-red-500 hover:text-red-700"
                        >
                            &times;
                        </button>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full bg-blue-500 text-white py-2 rounded-md font-semibold hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400"
            >
                Simpan
            </button>
            <Link
                :href="route('products.index')"
                class="block w-full mt-4 bg-gray-300 text-center text-gray-800 py-2 rounded-md font-semibold hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400"
            >
                Kembali
            </Link>
        </form>
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

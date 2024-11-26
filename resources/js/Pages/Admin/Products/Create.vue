<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";

// Add image preview ref
const imagePreview = ref(null);

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

const saveProduct = () => {
    if (selectedCategory.value === "new") {
        form.new_category = form.new_category;
    } else {
        form.category_id = selectedCategory.value;
    }

    form.post(route("products.store"), {
        onSuccess: () => form.reset(),
    });
};
const newColor = ref("");

const addColor = () => {
    if (newColor.value.trim()) {
        form.colors.push(newColor.value.trim());
        newColor.value = "";
    }
};

const removeColor = (index) => {
    form.colors.splice(index, 1);
};

const addSize = () => {
    if (newSize.value.trim()) {
        sizeList.value.push(newSize.value.trim());
        form.sizes.push(newSize.value.trim());
        newSize.value = ""; // Clear input after adding
    }
};

const removeSize = (index) => {
    sizeList.value.splice(index, 1);
    form.sizes.splice(index, 1);
};

const colorButtons = ref([]);
const currentColor = ref("#000000");

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
};

// Add image handling function
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
        // Create preview URL
        imagePreview.value = URL.createObjectURL(file);
    }
};
</script>

<template>
    <Head title="Create Products" />

    <div class="bg-orange-200 px-8 py-4">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Create Product
        </h2>
    </div>

    <div v-if="$page.props.flash.message" class="alert">
        {{ $page.props.flash.message }}
    </div>

    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md m-16">
        <form @submit.prevent="saveProduct">
            <!-- Nama Produk -->
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-semibold mb-2"
                    >Nama Produk</label
                >
                <input
                    type="text"
                    v-model="form.name"
                    class="w-full px-4 py-2 border rounded-md"
                    required
                />
                <div v-if="errors.name" class="text-red-500">
                    {{ errors.name }}
                </div>
            </div>

            <!-- Jumlah Stok -->
            <div class="mb-4">
                <label
                    for="stock"
                    class="block text-gray-700 font-semibold mb-2"
                    >Jumlah Stok</label
                >
                <input
                    type="number"
                    v-model="form.stock"
                    class="w-full px-4 py-2 border rounded-md"
                    required
                    min="1"
                />
                <div v-if="errors.stock" class="text-red-500">
                    {{ errors.stock }}
                </div>
            </div>

            <!-- Harga -->
            <div class="mb-4">
                <label
                    for="price"
                    class="block text-gray-700 font-semibold mb-2"
                    >Harga</label
                >
                <input
                    type="number"
                    v-model="form.price"
                    class="w-full px-4 py-2 border rounded-md"
                    required
                />
                <div v-if="errors.price" class="text-red-500">
                    {{ errors.price }}
                </div>
            </div>

            <!-- Kategori -->
            <div class="mb-4">
                <label
                    for="category"
                    class="block text-gray-700 font-semibold mb-2"
                    >Kategori</label
                >
                <select
                    v-model="selectedCategory"
                    class="w-full px-4 py-2 border rounded-md"
                >
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
                        v-model="form.new_category"
                        placeholder="Nama Kategori Baru"
                        class="w-full px-4 py-2 border rounded-md"
                    />
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2"
                    >Deskripsi</label
                >
                <textarea
                    v-model="form.description"
                    class="w-full px-4 py-2 border rounded-md"
                    rows="4"
                ></textarea>
                <div v-if="form.errors.description" class="text-red-500">
                    {{ form.errors.description }}
                </div>
            </div>

            <!-- Gambar -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    Gambar Produk
                </label>

                <input
                    type="file"
                    @input="handleImageUpload"
                    accept="image/*"
                    class="w-full px-4 py-2 border rounded-md"
                />

                <!-- Add image preview -->
                <div v-if="imagePreview" class="mt-2">
                    <img
                        :src="imagePreview"
                        class="w-32 h-32 object-cover rounded-md"
                        alt="Preview"
                    />
                </div>

                <div v-if="form.errors.image" class="text-red-500 text-sm mt-1">
                    {{ form.errors.image }}
                </div>
            </div>

            <!-- Color Picker Section -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2"
                    >Warna (Optional)</label
                >

                <div class="flex flex-wrap gap-2">
                    <!-- Color Buttons -->
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

                    <!-- Color Input Button -->
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

            <!-- Ukuran (Optional) -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    Ukuran (Optional)
                </label>

                <!-- Only show custom size input if category is selected -->
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

                <!-- Display sizes -->
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
                class="w-full bg-blue-500 text-white py-2 rounded-md font-semibold"
            >
                Tambah Produk
            </button>
            <Link
                :href="route('products.index')"
                class="block w-full mt-4 bg-gray-300 text-center text-gray-800 py-2 rounded-md font-semibold"
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

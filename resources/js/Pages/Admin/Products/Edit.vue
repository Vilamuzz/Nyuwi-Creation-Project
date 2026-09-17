<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import ToastNotification from "@/Components/Customer/Sub-main/ToastNotification.vue";
import ColorPicker from "@/Components/Customer/Sub-main/ColorPicker.vue";
import ImageInput from "@/Components/Customer/Sub-main/ImageInput.vue";
import CategoryInput from "@/Components/Customer/Sub-main/CategoryInput.vue";
import SizeInput from "@/Components/Customer/Sub-main/SizeInput.vue";

// Toast notification state
const toastMessage = ref("");
const toastType = ref("info");
const showToast = ref(false);

const props = defineProps({
    errors: Object,
    product: Object,
    categories: Array,
});

const form = useForm({
    name: props.product.name,
    stock: props.product.stock,
    price: props.product.price,
    weight: props.product.weight, // Add this line for the weight
    category_id: props.product.category_id,
    new_category: "",
    description: props.product.description,
    images: [], // Changed from 'image' to 'images' array to match controller
    colors: props.product.colors || [],
    sizes: props.product.sizes || [],
    _method: "PUT",
});

const selectedCategory = ref(props.product.category_id);

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

const updateProduct = () => {
    if (selectedCategory.value === "new") {
        form.new_category = form.new_category;
    } else {
        form.category_id = selectedCategory.value;
    }

    form.post(route("products.update", props.product.id), {
        onSuccess: () => {
            showNotification("Produk berhasil diperbarui!", "success");
        },
        onError: (errors) => {
            const errorMessages = Object.values(errors).flat();
            showNotification(
                errorMessages[0] || "Terjadi kesalahan saat memperbarui produk",
                "error"
            );
        },
    });
};

const handleCategoryChange = (categoryId) => {
    selectedCategory.value = categoryId;
};
</script>

<template>
    <Head title="Edit Products" />

    <AdminLayout pageTitle="Edit Product">
        <!-- Toast Notification Component -->
        <ToastNotification
            :message="toastMessage"
            :type="toastType"
            :show="showToast"
            @close="closeToast"
        />

        <div class="mx-12 my-10">
            <form @submit.prevent="updateProduct">
                <div class="flex flex-row space-x-6 w-full">
                    <div class="flex flex-col w-1/2">
                        <!-- Nama Produk -->
                        <div class="mb-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700">
                                <span class="font-semibold"
                                    >Nama Produk*</span
                                >
                            </label>
                            <input
                                type="text"
                                v-model="form.name"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500"
                                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': errors.name }"
                                required
                            />
                            <div v-if="errors.name" class="mb-2 block text-sm font-medium text-gray-700">
                                <span class="mt-1 text-sm text-red-600">{{
                                    errors.name
                                }}</span>
                            </div>
                        </div>

                        <!-- Jumlah Stok -->
                        <div class="mb-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700">
                                <span class="font-semibold"
                                    >Jumlah Stok*</span
                                >
                            </label>
                            <input
                                type="number"
                                v-model="form.stock"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500"
                                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': errors.stock }"
                                required
                                min="1"
                            />
                            <div v-if="errors.stock" class="mb-2 block text-sm font-medium text-gray-700">
                                <span class="mt-1 text-sm text-red-600">{{
                                    errors.stock
                                }}</span>
                            </div>
                        </div>

                        <!-- Harga -->
                        <div class="mb-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700">
                                <span class="font-semibold"
                                    >Harga*</span
                                >
                            </label>
                            <input
                                type="number"
                                v-model="form.price"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500"
                                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': errors.price }"
                                required
                                min="1"
                            />
                            <div v-if="errors.price" class="mb-2 block text-sm font-medium text-gray-700">
                                <span class="mt-1 text-sm text-red-600">{{
                                    errors.price
                                }}</span>
                            </div>
                        </div>

                        <!-- Berat -->
                        <div class="mb-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700">
                                <span class="font-semibold"
                                    >Berat*</span
                                >
                            </label>
                            <input
                                type="number"
                                v-model="form.weight"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500"
                                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': errors.weight }"
                                required
                                min="1"
                            />
                            <div v-if="errors.weight" class="mb-2 block text-sm font-medium text-gray-700">
                                <span class="mt-1 text-sm text-red-600">{{
                                    errors.weight
                                }}</span>
                            </div>
                        </div>

                        <!-- Kategori Component -->
                        <CategoryInput
                            :categories="categories"
                            v-model="selectedCategory"
                            v-model:new-category="form.new_category"
                            @category-change="handleCategoryChange"
                        />
                    </div>

                    <div class="flex flex-col w-1/2">
                        <!-- Image Input Component -->
                        <ImageInput
                            v-model="form.images"
                            :errors="errors"
                            :current-images="product.images"
                            @notification="showNotification"
                        />

                        <!-- Color Picker Component -->
                        <ColorPicker
                            v-model:colors="form.colors"
                            @notification="showNotification"
                        />

                        <!-- Size Input Component -->
                        <SizeInput
                            v-model:sizes="form.sizes"
                            @notification="showNotification"
                        />
                    </div>
                </div>

                <div>
                    <!-- Deskripsi -->
                    <div class="mb-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            <span class="font-semibold"
                                >Deskripsi*</span
                            >
                        </label>
                        <textarea
                            v-model="form.description"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500"
                            :class="{
                                'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.description,
                            }"
                            rows="4"
                            placeholder="Deskripsi produk..."
                        ></textarea>
                        <div v-if="form.errors.description" class="mb-2 block text-sm font-medium text-gray-700">
                            <span class="mt-1 text-sm text-red-600">{{
                                form.errors.description
                            }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4 mt-6">
                    <Link
                        :href="route('products.index')"
                        class="flex-1 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-center font-semibold text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500"
                    >
                        Kembali
                    </Link>
                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="flex-1 rounded-lg bg-orange-500 px-4 py-2.5 font-semibold text-white hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? "Memperbarui..."
                                : "Perbarui Produk"
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

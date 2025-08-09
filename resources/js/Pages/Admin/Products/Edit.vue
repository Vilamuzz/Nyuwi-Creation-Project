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

                        <!-- Berat -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold"
                                    >Berat*</span
                                >
                            </label>
                            <input
                                type="number"
                                v-model="form.weight"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': errors.weight }"
                                required
                                min="1"
                            />
                            <div v-if="errors.weight" class="label">
                                <span class="label-text-alt text-error">{{
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
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-semibold"
                                >Deskripsi*</span
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

<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
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
    image: null,
    colors: [],
    sizes: [],
});

const selectedCategory = ref("");

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

const handleCategoryChange = (categoryId) => {
    selectedCategory.value = categoryId;
};
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
                            v-model="form.image"
                            :errors="form.errors"
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
                            :selected-category="selectedCategory"
                            :category-sizes="categorySizes"
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
                        {{ form.processing ? "Menyimpan..." : "Tambah Produk" }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

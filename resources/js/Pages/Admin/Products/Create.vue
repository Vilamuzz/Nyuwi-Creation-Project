<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref } from "vue";

defineProps({ errors: Object, categories: Array });

const form = useForm({
    name: "",
    stock: "",
    price: "",
    category_id: null,
    new_category: "",
});

const selectedCategory = ref("");

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
</script>

<template>
    <Head title="Create Products" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Create Product
            </h2>
        </template>

        <div v-if="$page.props.flash.message" class="alert">
            {{ $page.props.flash.message }}
        </div>

        <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md m-16">
            <form @submit.prevent="saveProduct">
                <!-- Nama Produk -->
                <div class="mb-4">
                    <label
                        for="name"
                        class="block text-gray-700 font-semibold mb-2"
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
                        type="text"
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
    </AuthenticatedLayout>
</template>

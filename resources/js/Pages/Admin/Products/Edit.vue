<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
// import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

// Mengambil props `errors`, `product`, dan `categories`
const props = defineProps({
    errors: Object,
    product: Object,
    categories: Array,
});

const form = useForm({
    name: props.product.name,
    stock: props.product.stock,
    price: props.product.price,
    category_id: props.product.category_id, // Menambahkan category_id
    new_category: "", // Untuk kategori baru jika user memilih menambahkan kategori
});

// Fungsi untuk mengirim permintaan update produk
const updateProduct = () => {
    // Jika pengguna memilih untuk menambahkan kategori baru
    if (form.new_category) {
        // Kirim data form dengan kategori baru
        form.put(route("products.update", props.product.id), {
            preserveScroll: true,
            onSuccess: () => form.reset("new_category"),
        });
    } else {
        // Kirim data form tanpa kategori baru (gunakan category_id yang dipilih)
        form.put(route("products.update", props.product.id), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    }
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
        <form @submit.prevent="updateProduct()">
            <!-- Nama Produk -->
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-semibold mb-2"
                    >Nama Produk</label
                >
                <input
                    type="text"
                    v-model="form.name"
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Nama Produk"
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
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Jumlah Stok"
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
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Rp10,000"
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
                    v-model="form.category_id"
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
                <div v-if="form.category_id === 'new'" class="mt-2">
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

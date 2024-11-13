<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

defineProps({ errors: Object });

const form = useForm({
    name: "",
    stock: "",
    price: "",
});

const saveProduct = () => {
    const res = form.post(route("products.store"));
    if (res) {
        form.reset();
    }
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
            <form @submit.prevent="saveProduct()">
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
                        type="text"
                        v-model="form.price"
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                        placeholder="Rp10,000"
                        required
                    />
                    <div v-if="errors.price" class="text-red-500">
                        {{ errors.price }}
                    </div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full bg-blue-500 text-white py-2 rounded-md font-semibold hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                    Tambah Produk
                </button>
                <Link
                    :href="route('products.index')"
                    class="block w-full mt-4 bg-gray-300 text-center text-gray-800 py-2 rounded-md font-semibold hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400"
                >
                    Kembali
                </Link>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

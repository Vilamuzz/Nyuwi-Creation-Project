<script setup>
import { Head } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

// Add props to receive data from controller
defineProps({
    topSelling: Array,
    mostRated: Array,
    lowStock: Array,
});

// Add function to format price
const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
};
</script>

<template>
    <Head title="Dashboard" />
    <AdminLayout pageTitle="Dashboard">
        <div class="p-6">
            <h1 class="text-2xl font-semibold text-gray-800 mb-6">
                Selamat Datang, {{ $page.props.auth.user.name }}
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Top Selling Products -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">
                        Produk Terlaris
                    </h2>
                    <div class="space-y-4">
                        <div
                            v-for="product in topSelling"
                            :key="product.id"
                            class="flex items-center space-x-4"
                        >
                            <img
                                :src="`/storage/products/${product.image}`"
                                :alt="product.name"
                                class="w-16 h-16 object-cover rounded"
                            />
                            <div>
                                <h3 class="font-medium">{{ product.name }}</h3>
                                <p class="text-sm text-gray-500">
                                    Terjual: {{ product.total_sold }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ formatPrice(product.price) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Most Rated Products -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">
                        Produk Rating Tertinggi
                    </h2>
                    <div class="space-y-4">
                        <div
                            v-for="product in mostRated"
                            :key="product.id"
                            class="flex items-center space-x-4"
                        >
                            <img
                                :src="`/storage/products/${product.image}`"
                                :alt="product.name"
                                class="w-16 h-16 object-cover rounded"
                            />
                            <div>
                                <h3 class="font-medium">{{ product.name }}</h3>
                                <div class="flex items-center">
                                    <div class="flex">
                                        <div v-for="star in 5" :key="star">
                                            <svg
                                                :class="[
                                                    'w-4 h-4',
                                                    star <=
                                                    Math.round(
                                                        product.average_rating
                                                    )
                                                        ? 'text-yellow-400'
                                                        : 'text-gray-300',
                                                ]"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-500 ml-1"
                                        >({{ product.total_reviews }})</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Products -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">
                        Stok Menipis
                    </h2>
                    <div class="space-y-4">
                        <div
                            v-for="product in lowStock"
                            :key="product.id"
                            class="flex items-center space-x-4"
                        >
                            <img
                                :src="`/storage/products/${product.image}`"
                                :alt="product.name"
                                class="w-16 h-16 object-cover rounded"
                            />
                            <div>
                                <h3 class="font-medium">{{ product.name }}</h3>
                                <p class="text-sm text-red-500">
                                    Sisa stok: {{ product.stock }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ formatPrice(product.price) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Product from "@/Components/Customer/Sub-main/Product.vue";

const props = defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
});

// Add reactive data
const products = ref([]);
const categories = ref([]);
const isLoading = ref(true);
const error = ref(null);

// Fetch data from API
const fetchHomeData = async () => {
    try {
        isLoading.value = true;
        const response = await axios.get("/api/home");

        if (response.data.success) {
            products.value = response.data.data.products || [];
            categories.value = response.data.data.categories || [];
        } else {
            error.value = "Failed to load data";
        }
    } catch (err) {
        console.error("Error fetching home data:", err);
        error.value = "Failed to load data";
    } finally {
        isLoading.value = false;
    }
};

// Fetch data on component mount
onMounted(() => {
    fetchHomeData();
});

const getCategoryName = (categoryId) => {
    const category = categories.value.find((cat) => cat.id === categoryId);
    return category ? category.name : "Tidak ada kategori";
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
};
</script>

<template>
    <Head title="Landing Page" />
    <CustomersLayout>
        <!-- Hero section remains the same -->
        <section
            class="bg-cover bg-center h-screen flex items-center justify-center"
            :style="{
                backgroundImage:
                    'linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url(/img/background/hero.svg)',
            }"
        >
            <div class="text-center max-w-2xl mx-auto p-6 relative z-10">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">
                    Welcome to Our Store
                </h1>
                <p class="text-lg md:text-xl text-white mb-6">
                    Discover the best products at amazing prices. We offer a
                    wide selection of items tailored just for you.
                </p>
                <Link
                    href="/shop"
                    class="inline-block bg-orange-500 text-white text-lg font-semibold py-3 px-8 rounded-full hover:bg-orange-600 transition duration-300"
                >
                    Shop Now
                </Link>
            </div>
        </section>

        <section class="my-10">
            <div class="mx-20 p-8 flex flex-col items-center space-y-6">
                <div class="text-center">
                    <h1 class="text-3xl font-bold">Category</h1>
                    <p class="mt-2">Find out the beauty of your idea</p>
                </div>

                <!-- Loading state for categories -->
                <div v-if="isLoading" class="text-center py-8">
                    <div
                        class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"
                    ></div>
                    <p class="mt-4 text-gray-600">Loading categories...</p>
                </div>

                <!-- Error state -->
                <div v-else-if="error" class="text-center py-8 text-red-500">
                    <p>{{ error }}</p>
                    <button
                        @click="fetchHomeData"
                        class="mt-4 px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600"
                    >
                        Retry
                    </button>
                </div>

                <!-- Category Items -->
                <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Category Card (Dynamic from API using product images) -->
                    <div
                        v-for="category in categories.slice(0, 3)"
                        :key="category.id"
                        class="flex flex-col items-center space-y-4 w-[300px]"
                    >
                        <img
                            :src="
                                category.image
                                    ? `/storage/products/${category.image}`
                                    : '/img/products/default.jpg'
                            "
                            :alt="category.name"
                            class="rounded-lg w-full h-64 object-cover"
                            @error="
                                $event.target.src = '/img/products/default.jpg'
                            "
                        />
                        <h2 class="text-2xl font-bold">{{ category.name }}</h2>
                    </div>

                    <!-- Fallback static categories if no categories from API -->
                    <template v-if="categories.length === 0">
                        <div
                            class="flex flex-col items-center space-y-4 w-[300px]"
                        >
                            <img
                                :src="'/img/products/acc.jpg'"
                                alt="Category Image"
                                class="rounded-lg w-full h-64 object-cover"
                            />
                            <h2 class="text-2xl font-bold">Accessories</h2>
                        </div>
                        <div
                            class="flex flex-col items-center space-y-4 w-[300px]"
                        >
                            <img
                                :src="'/img/products/deco.jpg'"
                                alt="Category Image"
                                class="rounded-lg w-full h-64 object-cover"
                            />
                            <h2 class="text-2xl font-bold">Decoration</h2>
                        </div>
                        <div
                            class="flex flex-col items-center space-y-4 w-[300px]"
                        >
                            <img
                                :src="'/img/products/buck.jpg'"
                                alt="Category Image"
                                class="rounded-lg w-full h-64 object-cover"
                            />
                            <h2 class="text-2xl font-bold">Bucket</h2>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <section>
            <div class="flex flex-col items-center space-y-8 mb-10">
                <h1 class="text-3xl font-bold">Our Products</h1>

                <div v-if="isLoading" class="text-center py-8">
                    <div
                        class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"
                    ></div>
                    <p class="mt-4 text-gray-600">Loading products...</p>
                </div>

                <div v-else-if="error" class="text-center py-8 text-red-500">
                    <p>{{ error }}</p>
                </div>

                <div
                    v-else-if="products.length > 0"
                    class="grid grid-cols-1 md:grid-cols-4 gap-8"
                >
                    <Product
                        v-for="(item, index) in products"
                        :key="index"
                        :slug="item.slug"
                        :name="item.name"
                        :price="formatPrice(item.price)"
                        :category="getCategoryName(item.category_id)"
                        :image="item.image"
                        :rating="item.average_rating || 0"
                        :total-reviews="item.total_reviews || 0"
                    />
                </div>

                <div v-else class="text-center py-8 text-gray-500">
                    <p>No products available at the moment.</p>
                </div>

                <Link
                    href="/shop"
                    class="border-orange-500 border text-md font-bold text-orange-500 py-2 px-12 hover:text-white hover:bg-orange-500 duration-300"
                >
                    Show More
                </Link>
            </div>
        </section>

        <section class="bg-gray-600 py-20">
            <div class="flex justify-center items-center mx-20 text-white">
                <div class="flex flex-col space-y-4 ml-5 text-center">
                    <h1 class="text-3xl font-bold">50+ Beautiful Products</h1>
                    <p>We offer a wide selection of items</p>
                    <Link
                        href="/shop"
                        class="bg-orange-500 py-2 w-1/2 ml-20 text-center text-white text-md font-bold hover:bg-orange-700 duration-300"
                    >
                        Explore More
                    </Link>
                </div>
            </div>
        </section>
    </CustomersLayout>
</template>

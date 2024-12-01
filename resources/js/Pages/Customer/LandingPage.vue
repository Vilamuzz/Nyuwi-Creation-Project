<script setup>
import { Head, Link } from "@inertiajs/vue3";
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
    products: Array,
    categories: Array,
});
const getCategoryName = (categoryId) => {
    const category = props.categories.find((cat) => cat.id === categoryId);
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
        <section
            class="bg-cover bg-center h-screen flex items-center justify-center"
            :style="{
                backgroundImage:
                    'linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url(/img/background/hero.svg)',
            }"
        >
            <div class="text-center max-w-2xl mx-auto p-6 relative z-10">
                <!-- Hero Title -->
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">
                    Welcome to Our Store
                </h1>

                <!-- Hero Subtitle -->
                <p class="text-lg md:text-xl text-white mb-6">
                    Discover the best products at amazing prices. We offer a
                    wide selection of items tailored just for you.
                </p>

                <!-- Call-to-Action Button -->
                <a
                    href="#shop"
                    class="inline-block bg-orange-500 text-white text-lg font-semibold py-3 px-8 rounded-full hover:bg-orange-600 transition duration-300"
                >
                    Shop Now
                </a>
            </div>
        </section>
        <section class="my-10">
            <div class="mx-20 p-8 flex flex-col items-center space-y-6">
                <!-- Section Title -->
                <div class="text-center">
                    <h1 class="text-3xl font-bold">Category</h1>
                    <p class="mt-2">Find out the beauty of your idea</p>
                </div>

                <!-- Category Items -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Category Card (Reusable) -->
                    <div class="flex flex-col items-center space-y-4 w-[300px]">
                        <img
                            :src="'/img/background/category1.svg'"
                            alt="Category Image"
                            class="rounded-lg w-full object-cover"
                        />
                        <h2 class="text-2xl font-bold">Accessories</h2>
                    </div>
                    <div class="flex flex-col items-center space-y-4 w-[300px]">
                        <img
                            :src="'/img/background/category1.svg'"
                            alt="Category Image"
                            class="rounded-lg w-full object-cover"
                        />
                        <h2 class="text-2xl font-bold">Clothing</h2>
                    </div>
                    <div class="flex flex-col items-center space-y-4 w-[300px]">
                        <img
                            :src="'/img/background/category1.svg'"
                            alt="Category Image"
                            class="rounded-lg w-full object-cover"
                        />
                        <h2 class="text-2xl font-bold">Footwear</h2>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="flex flex-col items-center space-y-8 mb-10">
                <h1 class="text-3xl font-bold">Our Products</h1>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <Product
                        v-for="(item, index) in products"
                        :key="index"
                        :id="item.id"
                        :name="item.name"
                        :price="formatPrice(item.price)"
                        :category="getCategoryName(item.category_id)"
                        :image="item.image"
                        :rating="item.average_rating || 0"
                        :total-reviews="item.total_reviews || 0"
                    />
                </div>
                <Link
                    href="/shop"
                    class="border-orange-500 border text-md font-bold text-orange-500 py-2 px-12 hover:text-white hover:bg-orange-500 duration-300"
                >
                    Show More
                </Link>
            </div>
        </section>
        <section class="bg-orange-100 py-20">
            <div class="flex flex-row mx-20">
                <div class="flex flex-col space-y-4">
                    <h1 class="text-3xl font-bold">50+ Beautiful Products</h1>
                    <p>We offer a wide selection of items</p>
                    <button
                        class="bg-orange-500 py-2 px-4 w-1/2 text-center text-white text-md font-bold hover:bg-orange-700 duration-300"
                    >
                        Explore More
                    </button>
                </div>
                <div></div>
            </div>
        </section>
        <section></section>
    </CustomersLayout>
</template>

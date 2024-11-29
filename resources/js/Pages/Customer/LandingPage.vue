<script setup>
import { Head, Link } from "@inertiajs/vue3";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Product from "@/Components/Sub-main/ProductCard.vue";

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
            class="bg-cover bg-center h-screen flex items-center justify-start"
            :style="{
                backgroundImage:
                    'linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url(/img/background/hero.svg)',
            }"
        >
            <div class="bg-orange-100 max-w-2xl mx-auto p-10 rounded-lg relative z-10 text-center mr-10">
                <h1 class="text-1xl md:text-1xl font-bold text-black mb-2 text-left tracking-widest">
                    Nyuwi Creation
                </h1>

                <!-- Hero Title -->
                <h2 class="text-4xl md:text-5xl mb-3 font-bold text-left text-[#B8860B]">
                    Kerajinan tangan yang unik dan estetik
                </h2>
                

                <!-- Hero Subtitle -->
                <p class="text-1xl md:text-1xl text-left text-black mb-6">
                    Temukan produk terbaik dengan harga yang ramah di kantong dan berbagai pilihan barang yang dirancang khusus untuk kamu!
                </p>

                <!-- Call-to-Action Button -->
                <a
                    href="#shop"
                    class="inline-block text-white text-lg font-semibold py-3 px-8 rounded-full hover:bg-orange-600 transition duration-300"
                    style="background-color: #B8860B;"
                >
                    Beli Sekarang
                </a>
            </div>
        </section>
        <section class="my-10">
            <div class="mx-20 p-1 flex flex-col items-center space-y-6">
                <!-- Section Title -->
                <div class="text-center">
                    <h1 class="text-2xl font-bold text-black">Browse The Range</h1>
                    <p class="mt-1">Find out the beauty of your idea</p>
                </div>

                <!-- Category Items -->
                <div class="grid grid-cols-3 md:grid-cols-3 gap-6">
                    <!-- Category Card (Reusable) -->
                    <div class="flex flex-col items-center space-y-4 w-[300px]">
                        <img
                            :src="'/img/product/aksesoris1.jpg'"
                            alt="Category Image"
                            class="rounded-lg w-full object-cover"
                        />
                        <h2 class="text-2xl font-bold">Aksesoris</h2>
                    </div>
                    <div class="flex flex-col items-center space-y-4 w-[300px]">
                        <img
                            :src="'/img/product/buket1.jpg'"
                            alt="Category Image"
                            class="rounded-lg w-full object-cover"
                        />
                        <h2 class="text-2xl font-bold">Buket</h2>
                    </div>
                    <div class="flex flex-col items-center space-y-4 w-[300px]">
                        <img
                            :src="'/img/product/hiasan1.jpg'"
                            alt="Category Image"
                            class="rounded-lg w-full object-cover"
                        />
                        <h2 class="text-2xl font-bold">Dekorasi</h2>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="flex flex-col items-center space-y-8 mb-10">
                <h1 class="text-3xl font-bold text-black">Produk Kami</h1>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <Product
                        v-for="(item, index) in products"
                        :key="index"
                        :id="item.id"
                        :name="item.name"
                        :price="formatPrice(item.price)"
                        :category="getCategoryName(item.category_id)"
                        :image="item.image"
                    />
                </div>
                <Link
                    href="/shop"
                    class="border-[#b5853b] border text-md font-bold text-[#b5853b] py-2 px-12 hover:text-white hover:bg-[#b5853b] duration-300"
                >
                    Show More
                </Link>
            </div>
        </section>
        <section class="bg-[#fdf7f2]">
            <div class="p-8" id="app">
             <div class="flex">
                <div class="w-1/3 py-20">
                <h1 class="text-3xl font-bold">50+ Beautiful handicrafts inspiration</h1>
                <p class="text-gray-600 mt-4 mb-4 w-1/1">Our artisan already made a lot of beautiful handicrafts that will inspire you</p>
                <button class="bg-[#b5853b] text-white py-2 px-4 rounded">Explore More</button>
                </div>
             </div>
                <div></div>
            </div>
        </section>
        <section class="my-10">
            <div class="mx-20 p-1 flex flex-col items-center space-y-6">
                <!-- Section Title -->
                <div class="text-center">
                    <h1 class="text-1xl text-gray-500 font-bold">Share your setup with</h1>
                    <p class="mt-1 font-extrabold text-4xl">#NyuwiCreation</p>
                </div>

                <!-- Category Items -->
                <div class="grid grid-cols-3 md:grid-cols-3 gap-6">
                    <!-- Category Card (Reusable) -->
                    <div class="flex flex-col items-center space-y-4 w-[300px]">
                        <img
                            :src="'/img/product/aksesoris2.jpg'"
                            alt="Category Image"
                            class="rounded-lg w-full object-cover"
                        />
                    </div>
                    <div class="flex flex-col items-center space-y-4 w-[300px]">
                        <img
                            :src="'/img/product/aksesoris3.jpg'"
                            alt="Category Image"
                            class="rounded-lg w-full object-cover"
                        />
                    </div>
                    <div class="flex flex-col items-center space-y-4 w-[300px]">
                        <img
                            :src="'/img/product/aksesoris4.jpg'"
                            alt="Category Image"
                            class="rounded-lg w-full object-cover"
                        />
                    </div>
                </div>
            </div>
        </section>
    </CustomersLayout>
</template>

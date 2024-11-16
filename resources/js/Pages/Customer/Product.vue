<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
const sizes = ref(["S", "M", "L", "XL"]);
const colors = ref(["bg-yellow-300", "bg-red-300", "bg-blue-300"]);
const props = defineProps({
    errors: Object,
    product: Object,
    categories: Array,
});
</script>
<template>
    <Head title="Product" />
    <CustomersLayout>
        <section class="bg-orange-100 px-24 py-6 flex flex-row">
            <div class="text-gray-400 mr-2">Home > Shop ></div>
            <div>Product</div>
        </section>
        <section class="mx-24 my-6 flex flex-row gap-4">
            <!-- Image Section -->
            <div class="w-1/12 flex flex-col">
                <div class="p-8 bg-black rounded-md"></div>
            </div>

            <!-- Product Description Section -->
            <div class="w-1/3 bg-black rounded-md"></div>

            <!-- Product Details Section -->
            <div class="w-1/2 flex flex-col ml-16">
                <h1 class="font-bold text-3xl">{{ product.name }}</h1>
                <h2 class="font-thin text-gray-400 text-2xl">Product Price</h2>
                <div>Star | Review</div>
                <p>Deskripsi</p>

                <!-- Size Selection -->
                <h3>Size</h3>
                <div class="flex flex-row gap-4 mb-4">
                    <div
                        v-for="(size, index) in sizes"
                        :key="index"
                        class="bg-orange-100 w-10 h-10 flex items-center justify-center rounded-md hover:bg-orange-500 hover:text-white duration-300"
                    >
                        {{ size }}
                    </div>
                </div>

                <!-- Color Selection -->
                <h3>Color</h3>
                <div class="flex flex-row gap-4 mb-4">
                    <div
                        v-for="(color, index) in colors"
                        :key="index"
                        :class="`p-4 rounded-full ${color}`"
                    ></div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-row gap-x-4">
                    <div
                        class="rounded-md border border-gray-300 flex flex-row items-center p-0"
                    >
                        <div
                            class="p-4 hover:bg-orange-500 rounded-l-md hover:text-white duration-300"
                        >
                            -
                        </div>
                        <div
                            class="p-4 hover:bg-orange-500 hover:text-white duration-300"
                        >
                            1
                        </div>
                        <div
                            class="p-4 hover:bg-orange-500 rounded-r-md hover:text-white duration-300"
                        >
                            +
                        </div>
                    </div>
                    <Link
                        class="rounded-md border border-gray-300 px-10 py-4 hover:bg-orange-500 hover:text-white duration-300"
                    >
                        Add To Cart
                    </Link>
                    <div class="rounded-md border border-gray-300 px-10 py-4">
                        + Compare ????
                    </div>
                </div>
            </div>
        </section>
    </CustomersLayout>
</template>

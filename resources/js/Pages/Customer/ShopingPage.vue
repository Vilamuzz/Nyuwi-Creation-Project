<script setup>
import { Head } from "@inertiajs/vue3";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Hero from "@/Components/Main/Hero.vue";
import Product from "@/Components/Sub-main/Product.vue";

defineProps({
    products: Array,
});
</script>

<template>
    <Head title="Shoping Page" />
    <CustomersLayout>
        <Hero />
        <section class="bg-orange-200">
            <div class="mx-24 flex flex-row justify-between py-8">
                <div class="flex flex-row space-x-10">
                    <div><h1 class="font-bold">Filter</h1></div>
                    <div class="font-bold">Showing 1-16 of 32 Results</div>
                </div>
                <div class="flex flex-row space-x-10">
                    <div>Show</div>
                    <div>Short By</div>
                </div>
            </div>
        </section>
        <section>
            <div class="flex flex-col items-center space-y-8 my-14">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <Product
                        v-for="(item, index) in products"
                        :key="index"
                        :name="item.name"
                        :price="item.price"
                    />
                </div>
                <nav class="space-x-4">
                    <a
                        href=""
                        class="bg-orange-100 py-3 px-4 rounded-md hover:bg-orange-500 hover:text-white duration-300"
                        >1</a
                    >
                    <a
                        href=""
                        class="bg-orange-100 py-3 px-4 rounded-md hover:bg-orange-500 hover:text-white duration-300"
                        >2</a
                    >
                    <a
                        href=""
                        class="bg-orange-100 py-3 px-4 rounded-md hover:bg-orange-500 hover:text-white duration-300"
                        >3</a
                    >
                    <a
                        href=""
                        class="bg-orange-100 py-3 px-4 rounded-md hover:bg-orange-500 hover:text-white duration-300"
                        >Next</a
                    >
                </nav>
            </div>
        </section>
    </CustomersLayout>
</template>

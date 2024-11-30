<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    id: {
        type: Number,
        required: true,
    },
    name: {
        type: String,
        required: true,
    },
    price: {
        type: String,
        required: true,
    },
    category: {
        type: String,
        required: true,
    },
    image: {
        type: String,
        default: null,
    },
});

const form = useForm({
    product_id: props.id,
});

const addToWishlist = (e) => {
    e.preventDefault();
    form.post(route("wishlist.store"));
};
</script>

<template>
    <Link
        :href="route('product', { id: id })"
        class="relative flex flex-col items-start w-[200px] bg-gray-200 rounded-md shadow-md"
    >
        <!-- Container Gambar dan Overlay -->
        <div class="relative w-full h-48">
            <img
                :src="
                    image
                        ? `/storage/products/${image}`
                        : '/img/background/category1.svg'
                "
                :alt="name"
                class="w-full h-full object-cover rounded-t-md"
            />
            <!-- Overlay untuk tambah ke keranjang -->
            <div
                class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-50 text-white text-lg font-semibold rounded-t-md opacity-0 hover:opacity-100 transition-opacity duration-300"
            >
                <button
                    @click.stop="addToCart"
                    class="p-2 px-8 bg-white text-orange-500 text-sm mb-2"
                >
                    Add To Cart
                </button>
                <button
                    @click.stop="addToWishlist"
                    class="p-2 px-8 bg-white text-orange-500 text-sm"
                >
                    Add To Wishlist
                </button>
            </div>
        </div>

        <div class="p-4">
            <h2 class="text-lg font-bold">{{ name }}</h2>
            <p class="text-gray-500">{{ category }}</p>
            <h2 class="text-lg font-bold">{{ price }}</h2>
        </div>
    </Link>
</template>

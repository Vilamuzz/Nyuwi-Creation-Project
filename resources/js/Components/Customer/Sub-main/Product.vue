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
    image: {
        type: String,
        default: null,
    },
    rating: {
        type: Number,
        default: 0,
    },
    totalReviews: {
        type: Number,
        default: 0,
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
        class="relative flex flex-col items-start w-[200px] bg-gray-100 rounded-md hover:shadow-lg hover:scale-105 duration-300"
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
                    @click.stop="addToWishlist"
                    class="p-2 px-8 bg-white text-orange-500 text-sm"
                >
                    Add To Wishlist
                </button>
            </div>
        </div>

        <div class="p-4">
            <h2 class="text-lg font-bold">{{ name }}</h2>

            <!-- Add rating display -->
            <div class="flex items-center mt-1">
                <div class="flex">
                    <div v-for="star in 5" :key="star">
                        <svg
                            :class="[
                                'w-4 h-4',
                                star <= Math.round(rating)
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
                <span class="text-sm text-gray-600 ml-1"
                    >({{ totalReviews }})</span
                >
            </div>

            <h2 class="text-lg font-bold mt-2">{{ price }}</h2>
        </div>
    </Link>
</template>

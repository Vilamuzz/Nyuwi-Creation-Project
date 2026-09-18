<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    slug: {
        type: String,
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
    category: {
        type: String,
        default: "",
    },
    stock: {
        type: Number,
        default: null,
    },
    colors: {
        type: Array,
        default: () => [],
    },
    sizes: {
        type: Array,
        default: () => [],
    },
});

</script>

<template>
    <Link :href="route('product', { slug: slug })"
        class="relative flex w-full flex-col items-start overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200 transition duration-300 hover:-translate-y-1 hover:shadow-lg">
        <!-- Container Gambar dan Overlay -->
        <div class="relative h-52 sm:h-72 lg:h-96 w-full">
            <img :src="image ? `/storage/products/${image}` : '/img/products/default.jpg'" :alt="name"
                class="h-full w-full rounded-t-2xl object-cover"
                @error="$event.target.src = '/img/products/default.jpg'" />

        </div>

        <div class="p-4">
            <p v-if="category" class="text-xs font-semibold uppercase tracking-wider text-orange-600">{{ category }}</p>
            <h2 class="mt-1 line-clamp-2 text-lg font-bold text-stone-900">{{ name }}</h2>

            <div v-if="totalReviews > 0" class="mt-2 flex items-center">
                <div class="flex">
                    <div v-for="star in 5" :key="star">
                        <svg :class="[
                            'w-4 h-4',
                            star <= Math.round(rating)
                                ? 'text-yellow-400'
                                : 'text-gray-300',
                        ]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z" />
                        </svg>
                    </div>
                </div>
                <span class="text-sm text-gray-600 ml-1">({{ totalReviews }})</span>
            </div>

            <p class="mt-3 text-xl font-bold text-stone-900">{{ price }}</p>
            <div class="mt-3 flex flex-wrap gap-2 text-xs text-stone-600">
                <span v-if="stock !== null" :class="stock > 0 ? 'text-green-700' : 'text-red-600'">
                    {{ stock > 0 ? `${stock} tersedia` : 'Habis' }}
                </span>
                <span v-if="colors?.length">{{ colors.length }} warna</span>
                <span v-if="sizes?.length">{{ sizes.length }} ukuran</span>
            </div>
        </div>
    </Link>
</template>

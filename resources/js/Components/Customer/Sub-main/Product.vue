<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

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

// Add a success message state
const successMessage = ref("");
const errorMessage = ref("");
const showMessage = ref(false);

const form = useForm({});
const addToWishlist = (e) => {
    e.preventDefault();
    form.post(route("wishlist.store"), {
        slug: props.slug,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = "Product added to wishlist";
            errorMessage.value = "";
            showMessage.value = true;
            setTimeout(() => { showMessage.value = false; }, 3000);
        },
        onError: () => {
            errorMessage.value = "Failed to add to wishlist";
            successMessage.value = "";
            showMessage.value = true;
            setTimeout(() => { showMessage.value = false; }, 3000);
        },
    });
};
</script>

<template>
    <Link
        :href="route('product', { slug: slug })"
        class="relative flex w-full flex-col items-start overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200 transition duration-300 hover:-translate-y-1 hover:shadow-lg"
    >
        <!-- Container Gambar dan Overlay -->
        <div class="relative h-72 w-full">
            <img
                :src="image ? `/storage/products/${image}` : '/img/products/default.jpg'"
                :alt="name"
                class="h-full w-full rounded-t-2xl object-cover"
                @error="$event.target.src = '/img/products/default.jpg'"
            />
            <!-- Overlay untuk tambah ke keranjang -->
            <div
                class="absolute inset-0 flex flex-col items-center justify-center rounded-t-2xl bg-black/50 text-lg font-semibold text-white opacity-0 transition-opacity duration-300 hover:opacity-100 focus-within:opacity-100"
            >
                <button
                    @click.stop="addToWishlist"
                    class="rounded-full bg-white px-5 py-2 text-sm font-semibold text-orange-600 hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-white"
                >
                    Add To Wishlist
                </button>
            </div>

            <!-- Success/Error notification -->
            <div
                v-if="showMessage"
                class="absolute bottom-0 left-0 right-0 p-2 text-center text-sm"
                :class="[
                    successMessage
                        ? 'bg-green-500 text-white'
                        : 'bg-red-500 text-white',
                ]"
            >
                {{ successMessage || errorMessage }}
            </div>
        </div>

        <div class="p-4">
            <p v-if="category" class="text-xs font-semibold uppercase tracking-wider text-orange-600">{{ category }}</p>
            <h2 class="mt-1 line-clamp-2 text-lg font-bold text-stone-900">{{ name }}</h2>

            <div v-if="totalReviews > 0" class="mt-2 flex items-center">
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

            <p class="mt-3 text-xl font-bold text-stone-900">{{ price }}</p>
            <div class="mt-3 flex flex-wrap gap-2 text-xs text-stone-600">
                <span v-if="stock !== null" :class="stock > 0 ? 'text-green-700' : 'text-red-600'">
                    {{ stock > 0 ? `${stock} tersedia` : 'Habis' }}
                </span>
                <span v-if="colors.length">{{ colors.length }} warna</span>
                <span v-if="sizes.length">{{ sizes.length }} ukuran</span>
            </div>
        </div>
    </Link>
</template>

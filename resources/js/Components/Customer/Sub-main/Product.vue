<script setup>
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { ShoppingCart, Loader2, X, Plus, Minus } from "lucide-vue-next";

const props = defineProps({
    id: {
        type: [Number, String],
        default: null,
    },
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
        type: [Array, String],
        default: () => [],
    },
    sizes: {
        type: [Array, String],
        default: () => [],
    },
});

const isAdding = ref(false);
const showOptionModal = ref(false);
const selectedColor = ref("");
const selectedSize = ref("");
const quantity = ref(1);

const parsedColors = computed(() => {
    if (!props.colors) return [];
    if (Array.isArray(props.colors)) return props.colors;
    if (typeof props.colors === "string") {
        try {
            const parsed = JSON.parse(props.colors);
            return Array.isArray(parsed) ? parsed : [props.colors];
        } catch {
            return [props.colors];
        }
    }
    return [];
});

const parsedSizes = computed(() => {
    if (!props.sizes) return [];
    if (Array.isArray(props.sizes)) return props.sizes;
    if (typeof props.sizes === "string") {
        try {
            const parsed = JSON.parse(props.sizes);
            return Array.isArray(parsed) ? parsed : [props.sizes];
        } catch {
            return [props.sizes];
        }
    }
    return [];
});

const openOptionModal = () => {
    if (!props.id || (props.stock !== null && props.stock <= 0)) {
        return;
    }
    selectedColor.value = parsedColors.value.length > 0 ? parsedColors.value[0] : "";
    selectedSize.value = parsedSizes.value.length > 0 ? parsedSizes.value[0] : "";
    quantity.value = 1;
    showOptionModal.value = true;
};

const closeOptionModal = () => {
    showOptionModal.value = false;
};

const confirmAddToCart = () => {
    if (!props.id || isAdding.value) return;

    isAdding.value = true;

    router.post(
        route("cart.store"),
        {
            product_id: props.id,
            quantity: quantity.value,
            color: selectedColor.value || null,
            size: selectedSize.value || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showOptionModal.value = false;
            },
            onFinish: () => {
                isAdding.value = false;
            },
        }
    );
};
</script>

<template>
    <Link v-bind="$attrs" :href="route('product', { slug: slug })"
        class="group relative flex w-full flex-col items-start overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200 transition duration-300 hover:-translate-y-1 hover:shadow-lg">
        <!-- Container Gambar dan Overlay -->
        <div class="relative h-52 sm:h-72 lg:h-96 w-full overflow-hidden">
            <img :src="image ? `/storage/products/${image}` : '/img/products/default.jpg'" :alt="name"
                class="h-full w-full rounded-t-2xl object-cover transition-transform duration-500 group-hover:scale-105"
                @error="$event.target.src = '/img/products/default.jpg'" />

            <!-- Overlay + Add to Cart Button on Hover -->
            <div
                class="absolute inset-0 opacity-0 transition-all duration-300 group-hover:opacity-100 flex items-end justify-center p-3 sm:p-4 bg-gradient-to-t from-black/60 via-black/20 to-transparent">
                <button type="button" :disabled="stock !== null && stock <= 0" @click.prevent.stop="openOptionModal"
                    class="w-full translate-y-4 transition-all duration-300 group-hover:translate-y-0 flex items-center justify-center gap-2 rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-orange-700 active:scale-95 disabled:bg-stone-400 disabled:cursor-not-allowed">
                    <ShoppingCart class="h-4 w-4" />
                    <span>{{ stock !== null && stock <= 0 ? 'Stok Habis' : 'Tambah ke Keranjang' }}</span>
                </button>
            </div>
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
                <span v-if="parsedColors?.length">{{ parsedColors.length }} warna</span>
                <span v-if="parsedSizes?.length">{{ parsedSizes.length }} ukuran</span>
            </div>
        </div>
    </Link>

    <!-- Bottom Right Option Selection Confirmation Window -->
    <Teleport to="body">
        <!-- Backdrop Overlay -->
        <Transition appear enter-active-class="transition-opacity duration-300 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-opacity duration-300 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showOptionModal"
                class="fixed inset-0 z-[9998] bg-black/30 transition-opacity duration-300 ease-out"
                @click="closeOptionModal" />
        </Transition>

        <!-- Pop-up Window -->
        <Transition appear enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-6 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-6 scale-95">
            <div v-if="showOptionModal"
                class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-[9999] w-[calc(100vw-2rem)] sm:w-96 rounded-2xl bg-white p-5 shadow-2xl ring-1 ring-stone-900/10 border border-stone-200 flex flex-col gap-4 text-stone-900 font-sans"
                @click.stop>
                <!-- Header -->
                <div class="flex items-start justify-between gap-3 border-b border-stone-100 pb-3">
                    <div class="flex items-center gap-3">
                        <img :src="image ? `/storage/products/${image}` : '/img/products/default.jpg'" :alt="name"
                            class="h-12 w-12 rounded-xl object-cover ring-1 ring-stone-200 shrink-0"
                            @error="$event.target.src = '/img/products/default.jpg'" />
                        <div>
                            <h4 class="font-bold text-sm text-stone-900 line-clamp-1">{{ name }}</h4>
                            <p class="text-sm font-semibold text-orange-600">{{ price }}</p>
                        </div>
                    </div>
                    <button type="button"
                        class="rounded-full p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-700 transition"
                        @click="closeOptionModal">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Options Section -->
                <div class="space-y-4 max-h-[50vh] overflow-y-auto pr-1">
                    <!-- Color Options -->
                    <div v-if="parsedColors && parsedColors.length > 0">
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">
                            Pilih Warna: <span class="text-stone-900 font-semibold">{{ selectedColor }}</span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="color in parsedColors" :key="color" type="button"
                                @click="selectedColor = color" :class="[
                                    'px-3 py-1.5 text-xs font-medium rounded-lg border transition',
                                    selectedColor === color
                                        ? 'border-orange-600 bg-orange-50 text-orange-700 font-semibold shadow-sm'
                                        : 'border-stone-200 bg-stone-50 text-stone-700 hover:border-stone-300 hover:bg-white'
                                ]">
                                {{ color }}
                            </button>
                        </div>
                    </div>

                    <!-- Size Options -->
                    <div v-if="parsedSizes && parsedSizes.length > 0">
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">
                            Pilih Ukuran: <span class="text-stone-900 font-semibold">{{ selectedSize }}</span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="size in parsedSizes" :key="size" type="button" @click="selectedSize = size"
                                :class="[
                                    'px-3 py-1.5 text-xs font-medium rounded-lg border transition',
                                    selectedSize === size
                                        ? 'border-orange-600 bg-orange-50 text-orange-700 font-semibold shadow-sm'
                                        : 'border-stone-200 bg-stone-50 text-stone-700 hover:border-stone-300 hover:bg-white'
                                ]">
                                {{ size }}
                            </button>
                        </div>
                    </div>

                    <!-- Quantity Option -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">
                            Jumlah
                        </label>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center rounded-xl border border-stone-200 bg-stone-50">
                                <button type="button"
                                    class="px-3 py-1.5 text-stone-600 hover:text-stone-900 disabled:opacity-40"
                                    :disabled="quantity <= 1" @click="quantity > 1 && quantity--">
                                    <Minus class="h-4 w-4" />
                                </button>
                                <span class="px-3 py-1 text-sm font-bold text-stone-900 min-w-[2rem] text-center">
                                    {{ quantity }}
                                </span>
                                <button type="button"
                                    class="px-3 py-1.5 text-stone-600 hover:text-stone-900 disabled:opacity-40"
                                    :disabled="stock !== null && quantity >= stock"
                                    @click="stock === null || quantity < stock ? quantity++ : null">
                                    <Plus class="h-4 w-4" />
                                </button>
                            </div>
                            <span v-if="stock !== null" class="text-xs text-stone-500">
                                (Stok: {{ stock }})
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Footer Confirm Button -->
                <div class="pt-2 border-t border-stone-100 flex items-center gap-2">
                    <button type="button"
                        class="flex-1 rounded-xl bg-orange-600 py-2.5 px-4 text-center text-sm font-semibold text-white shadow-md hover:bg-orange-700 active:scale-98 transition disabled:opacity-50 flex items-center justify-center gap-2"
                        :disabled="isAdding" @click="confirmAddToCart">
                        <Loader2 v-if="isAdding" class="h-4 w-4 animate-spin" />
                        <ShoppingCart v-else class="h-4 w-4" />
                        <span>{{ isAdding ? 'Menambahkan...' : 'Konfirmasi & Tambah' }}</span>
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

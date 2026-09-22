<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, watch, onMounted, computed, onUnmounted } from "vue";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import ToastNotification from "@/Components/Customer/Sub-main/ToastNotification.vue";
import Product from "@/Components/Customer/Sub-main/Product.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {

    ShoppingCart,
    ChevronLeft,
    ChevronRight,
    X,
} from "lucide-vue-next";

const props = defineProps({
    product: { type: Object, default: () => ({}) },
    categories: { type: Array, default: () => [] },
    productRating: { type: Object, default: () => ({}) },
    relatedProducts: { type: Array, default: () => [] },
});

const product = computed(() => props.product);
const categories = computed(() => props.categories);
const productRating = computed(() => props.productRating);
const relatedProducts = computed(() => props.relatedProducts);
const isLoading = computed(() => false);
const error = computed(() => null);

// Image gallery state
const selectedImageIndex = ref(0);
const showImageModal = ref(false);

// Form data
const form = useForm({
    product_id: null,
    quantity: 1,
    price: 0,
    size: "",
    color: "",
});


const quantity = ref(1);
const selectedSize = ref("");
const selectedColor = ref("");

const parsedColors = computed(() => {
    const raw = product.value?.colors;
    if (!raw) return [];
    if (Array.isArray(raw)) return raw;
    if (typeof raw === "string") {
        try {
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [raw];
        } catch {
            return [raw];
        }
    }
    return [];
});

const parsedSizes = computed(() => {
    const raw = product.value?.sizes;
    if (!raw) return [];
    if (Array.isArray(raw)) return raw;
    if (typeof raw === "string") {
        try {
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [raw];
        } catch {
            return [raw];
        }
    }
    return [];
});

const setDefaultOptions = () => {
    if (parsedColors.value.length > 0) {
        selectedColor.value = parsedColors.value[0];
    } else {
        selectedColor.value = "";
    }
    if (parsedSizes.value.length > 0) {
        selectedSize.value = parsedSizes.value[0];
    } else {
        selectedSize.value = "";
    }
};

watch(
    () => product.value,
    () => {
        setDefaultOptions();
    },
    { immediate: true, deep: true }
);

// Toast notification states
const toast = ref({
    show: false,
    message: "",
    type: "info",
});

// Computed property for product images array
const productImages = computed(() => {
    if (
        product.value.images &&
        Array.isArray(product.value.images) &&
        product.value.images.length > 0
    ) {
        return product.value.images;
    }
    if (product.value.image) {
        return [product.value.image];
    }
    return [];
});

// Computed property for current selected image
const currentImage = computed(() => {
    return productImages.value[selectedImageIndex.value] || null;
});

// Computed property for formatted price
const formattedPrice = computed(() => {
    if (!product.value.price) return "Rp 0";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(product.value.price);
});

// Image gallery functions
const selectImage = (index) => {
    selectedImageIndex.value = index;
};

const previousImage = () => {
    if (selectedImageIndex.value > 0) {
        selectedImageIndex.value--;
    } else {
        selectedImageIndex.value = productImages.value.length - 1;
    }
};

const nextImage = () => {
    if (selectedImageIndex.value < productImages.value.length - 1) {
        selectedImageIndex.value++;
    } else {
        selectedImageIndex.value = 0;
    }
};

const openImageModal = () => {
    showImageModal.value = true;
};

const closeImageModal = () => {
    showImageModal.value = false;
};

const fetchProductData = () => { };

// Add formatPrice function for related products
const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
};

// Watch for quantity changes
watch(quantity, (newValue) => {
    form.quantity = newValue;
});

// Show toast function
const showToast = (message, type = "info", duration = 3000) => {
    toast.value = {
        show: true,
        message,
        type,
    };
};

// Hide toast function
const hideToast = () => {
    toast.value.show = false;
};

// Add to cart handler
const addToCart = () => {
    form.product_id = product.value.id;
    form.price = product.value.price;
    form.quantity = quantity.value;
    form.size = selectedSize.value;
    form.color = selectedColor.value;

    form.post(route("cart.store"), {
        preserveScroll: true,
        onSuccess: () => {
            quantity.value = 1;
            setDefaultOptions();
            showToast("Produk berhasil ditambahkan ke keranjang!", "success");
        },
        onError: (errors) => {
            // Show validation errors
            if (errors.available_stock) {
                showToast(`Jumlah melebihi stok yang tersedia. Stok tersedia: ${errors.available_stock}`, "error");
            } else {
                showToast("Gagal menambahkan produk ke keranjang", "error");
            }
        },
    });
};


const getCategoryName = (categoryId) => {
    const category = categories.value.find((cat) => cat.id === categoryId);
    return category ? category.name : "Tidak ada kategori";
};

// Keyboard navigation for image gallery
const handleKeydown = (event) => {
    if (showImageModal.value) {
        if (event.key === "ArrowLeft") {
            previousImage();
        } else if (event.key === "ArrowRight") {
            nextImage();
        } else if (event.key === "Escape") {
            closeImageModal();
        }
    }
};

// Add keyboard event listener
onMounted(() => {
    document.addEventListener("keydown", handleKeydown);
});

// Remove keyboard event listener
onUnmounted(() => {
    document.removeEventListener("keydown", handleKeydown);
});
</script>

<template>

    <Head title="Product" />
    <CustomersLayout>
        <!-- Loading state -->
        <div v-if="isLoading" class="text-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
            <p class="mt-4 text-gray-600">Loading product...</p>
        </div>

        <!-- Error state -->
        <div v-else-if="error" class="text-center py-20 text-red-500">
            <p>{{ error }}</p>
            <button @click="fetchProductData"
                class="mt-4 px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                Retry
            </button>
        </div>

        <!-- Product content -->
        <template v-else-if="product && product.id">
            <section class="px-4 sm:px-12 md:px-24 py-6">
                <Breadcrumb
                    :items="[
                        { label: 'Home', href: '/' },
                        { label: 'Sale', href: '/sale' },
                        { label: product.name || 'Product' }
                    ]"
                    separator=">"
                />
            </section>

            <section
                class="mx-4 sm:mx-12 md:mx-24 my-6 flex flex-col md:flex-row gap-8 justify-center items-center md:items-start">
                <!-- Image Gallery Section -->
                <div class="w-full max-w-md md:max-w-96 flex flex-col space-y-4">
                    <!-- Main Image Display -->
                    <div class="relative">
                        <div v-if="currentImage" class="bg-gray-200 rounded-2xl overflow-hidden">
                            <img :src="`/storage/products/${currentImage}`" :alt="product.name || 'Product image'"
                                class="w-full h-96 object-cover cursor-pointer hover:scale-105 transition-transform duration-300"
                                @click="openImageModal" @error="
                                    $event.target.src = '/img/placeholder.png'
                                    " />

                            <!-- Navigation arrows for main image -->
                            <template v-if="productImages.length > 1">
                                <button @click="previousImage"
                                    class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition-all">
                                    <ChevronLeft class="w-5 h-5" />
                                </button>
                                <button @click="nextImage"
                                    class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition-all">
                                    <ChevronRight class="w-5 h-5" />
                                </button>
                            </template>
                        </div>
                        <div v-else class="bg-gray-200 rounded-lg h-96 flex items-center justify-center">
                            <span class="text-gray-500">No image available</span>
                        </div>
                    </div>

                    <!-- Thumbnail Images -->
                    <div v-if="productImages.length > 1" class="flex space-x-2 overflow-x-auto">
                        <button v-for="(image, index) in productImages" :key="index" @click="selectImage(index)" :class="[
                            'flex-shrink-0 w-20 h-20 rounded-md overflow-hidden border-2 transition-all',
                            selectedImageIndex === index
                                ? 'border-orange-500 shadow-lg'
                                : 'border-gray-300 hover:border-orange-300',
                        ]">
                            <img :src="`/storage/products/${image}`" :alt="`Product image ${index + 1}`"
                                class="w-full h-full object-cover" @error="
                                    $event.target.src = '/img/placeholder.png'
                                    " />
                        </button>
                    </div>

                    <!-- Image counter -->
                    <div v-if="productImages.length > 1" class="text-center text-sm text-gray-600">
                        {{ selectedImageIndex + 1 }} of
                        {{ productImages.length }}
                    </div>
                </div>

                <!-- Product Details Section -->
                <div class="w-full md:w-1/2 flex flex-col md:ml-8">
                    <div class="flex justify-between">
                        <h1 class="font-bold text-3xl">
                            {{ product.name || "Product Name" }}
                        </h1>
                        <div v-if="productRating.totalReviews < 0" class="flex items-center mt-2 mb-2">
                            <div class="flex items-center">
                                <div v-for="star in 5" :key="star">
                                    <svg :class="[
                                        'w-5 h-5',
                                        star <=
                                            Math.round(
                                                productRating.average_rating || 0
                                            )
                                            ? 'text-yellow-400'
                                            : 'text-gray-300',
                                    ]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z" />
                                    </svg>
                                </div>
                            </div>
                            <span class="ml-2 text-sm text-gray-600">
                                ({{
                                    productRating.total_reviews || 0
                                }}
                                reviews)
                            </span>
                        </div>

                    </div>
                    <h2 class="font-thin text-2xl">
                        {{ formattedPrice }}
                    </h2>

                    <!-- Colors -->
                    <div v-if="product.colors && product.colors.length" class="mt-4">
                        <h3 class="font-semibold mb-2">Colors</h3>
                        <div class="flex gap-2">
                            <button v-for="color in product.colors" :key="color" @click="selectedColor = color"
                                class="w-8 h-8 rounded-full border-2" :class="{
                                    'border-orange-500':
                                        selectedColor === color,
                                    'border-gray-300': selectedColor !== color,
                                }" :style="{ backgroundColor: color }" :title="color"></button>
                        </div>
                    </div>

                    <!-- Sizes -->
                    <div v-if="product.sizes && product.sizes.length" class="mt-2">
                        <h3 class="font-semibold mb-2">Sizes</h3>
                        <div class="flex gap-2 flex-wrap">
                            <button v-for="size in product.sizes" :key="size" @click="selectedSize = size" :class="[
                                'px-4 py-2 border rounded-md hover:bg-orange-500 hover:text-white duration-300',
                                selectedSize === size
                                    ? 'bg-orange-500 text-white border-orange-500'
                                    : 'border-gray-300',
                            ]">
                                {{ size }}
                            </button>
                        </div>
                    </div>

                    <!-- Add to Cart -->
                    <div class="flex flex-row gap-x-4 mt-8">
                        <button @click="addToCart"
                            class="w-96 font-bold flex items-center justify-center gap-2 text-lg px-6 py-3 bg-orange-500 text-white rounded-md hover:bg-white hover:text-orange-500 hover:ring-orange-500 hover:ring-2 transition-colors ring-inset"
                            :disabled="!product.stock ||
                                product.stock < 1 ||
                                (!selectedSize && product.sizes?.length) ||
                                (!selectedColor && product.colors?.length) ||
                                quantity > product.stock
                                ">
                            <ShoppingCart />
                            <span>{{
                                !product.stock || product.stock < 1 ? "Out of Stock" : !selectedSize &&
                                    product.sizes?.length ? "Please select size" : !selectedColor &&
                                        product.colors?.length ? "Please select color" : quantity > product.stock
                                    ? "Not enough stock"
                                    : "Add To Cart"
                            }}</span>
                        </button>
                    </div>

                    <hr class="mt-16 mb-10">

                    <div>
                        <p class="">
                            {{ product.description || "No description available" }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Related Products Section -->
            <section v-if="relatedProducts.length > 0" class="mx-4 sm:mx-12 md:mx-24 my-8 sm:my-16">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">
                        Related Products
                    </h2>
                    <p class="text-gray-600 mt-2">
                        You might also like these products
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <Product v-for="item in relatedProducts" :key="item.id" :id="item.id" :slug="item.slug"
                        :name="item.name" :price="formatPrice(item.price)" :category="getCategoryName(item.category_id)"
                        :image="item.images &&
                            Array.isArray(item.images) &&
                            item.images.length > 0
                            ? item.images[0]
                            : item.image || null
                            " :rating="Number(item.average_rating) || 0"
                        :total-reviews="Number(item.total_reviews) || 0" :stock="item.stock" :colors="item.colors"
                        :sizes="item.sizes" />
                </div>

                <!-- View More Button -->
                <div class="text-center mt-8">
                    <Link :href="route('sale', { category: product.category_id })"
                        class="inline-block px-8 py-3 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition duration-300">
                        View More
                        {{ getCategoryName(product.category_id) }} Products
                    </Link>
                </div>
            </section>
        </template>

        <!-- No product found -->
        <div v-else class="text-center py-20">
            <p class="text-gray-600">Product not found</p>
            <Link href="/sales" class="mt-4 px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                Back to Sale
            </Link>
        </div>

        <!-- Image Modal -->
        <div v-if="showImageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
            @click="closeImageModal">
            <div class="relative max-w-4xl max-h-full p-4">
                <img :src="`/storage/products/${currentImage}`" :alt="product.name || 'Product image'"
                    class="max-w-full max-h-full object-contain" @click.stop />

                <!-- Close button -->
                <button @click="closeImageModal"
                    class="absolute top-6 right-6 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-70">
                    <X :size="15" />
                </button>

                <!-- Navigation arrows in modal -->
                <template v-if="productImages.length > 1">
                    <button @click.stop="previousImage"
                        class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white bg-black bg-opacity-50 p-3 rounded-full hover:bg-opacity-70">
                        <ChevronLeft class="w-6 h-6" />
                    </button>
                    <button @click.stop="nextImage"
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white bg-black bg-opacity-50 p-3 rounded-full hover:bg-opacity-70">
                        <ChevronRight class="w-6 h-6" />
                    </button>
                </template>

                <!-- Image counter in modal -->
                <div
                    class="absolute bottom-6 left-1/2 transform -translate-x-1/2 text-white bg-black bg-opacity-50 px-3 py-1 rounded">
                    {{ selectedImageIndex + 1 }} / {{ productImages.length }}
                </div>
            </div>
        </div>

        <!-- Toast Notification Component -->
        <ToastNotification :show="toast.show" :message="toast.message" :type="toast.type" @close="hideToast" />
    </CustomersLayout>
</template>

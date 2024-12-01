<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";

// Update form to handle product data
const form = useForm({
    product_id: null,
    quantity: 1,
    price: 0,
    size: "",
    color: "",
});

const wishlistForm = useForm({
    product_id: null,
});

const quantity = ref(1);
const selectedSize = ref("");
const selectedColor = ref("");
const errorMessage = ref("");
const successMessage = ref("");
const wishlistSuccessMessage = ref("");
const wishlistErrorMessage = ref("");

// Watch for quantity changes
watch(quantity, (newValue) => {
    form.quantity = newValue;
});

// Add to cart handler
const addToCart = () => {
    form.product_id = props.product.id;
    form.price = props.product.price;
    form.quantity = quantity.value;
    form.size = selectedSize.value;
    form.color = selectedColor.value;

    form.post(route("cart.add"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            quantity.value = 1;
            selectedSize.value = "";
            selectedColor.value = "";
            errorMessage.value = "";
            // Set success message
            successMessage.value = "Produk berhasil ditambahkan ke keranjang!";
            // Clear success message after 3 seconds
            setTimeout(() => {
                successMessage.value = "";
            }, 3000);
        },
        onError: (errors) => {
            if (errors.quantity) {
                errorMessage.value = errors.quantity;
            }
        },
    });
};

const addToWishlist = (e) => {
    e.preventDefault();
    wishlistForm.product_id = props.product.id;

    wishlistForm.post(route("wishlist.store"), {
        preserveScroll: true,
        onSuccess: () => {
            wishlistForm.reset();
            wishlistSuccessMessage.value =
                "Produk berhasil ditambahkan ke wishlist!";
            setTimeout(() => {
                wishlistSuccessMessage.value = "";
            }, 3000);
        },
        onError: (errors) => {
            if (errors.message) {
                wishlistErrorMessage.value = errors.message;
                setTimeout(() => {
                    wishlistErrorMessage.value = "";
                }, 3000);
            }
        },
    });
};

const props = defineProps({
    product: Object,
    categories: Array,
    productRating: Object,
});

const getCategoryName = (categoryId) => {
    const category = props.categories.find((cat) => cat.id === categoryId);
    return category ? category.name : "Tidak ada kategori";
};

// Remove fetchProductReviews function and onMounted hook since we now get data directly
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
            <div class="w-1/4 flex flex-col">
                <div v-if="product.image" class="bg-gray-200 rounded-md">
                    <img
                        :src="`/storage/products/${product.image}`"
                        :alt="product.name"
                        class="w-full h-auto object-cover"
                    />
                </div>
            </div>

            <!-- Product Details Section -->
            <div class="w-1/2 flex flex-col ml-16">
                <h1 class="font-bold text-3xl">{{ product.name }}</h1>
                <div class="flex items-center mt-2 mb-2">
                    <div class="flex items-center">
                        <div v-for="star in 5" :key="star">
                            <svg
                                :class="[
                                    'w-5 h-5',
                                    star <=
                                    Math.round(
                                        productRating.average_rating || 0
                                    )
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
                    <span class="ml-2 text-sm text-gray-600">
                        {{ productRating.average_rating }}/5 ({{
                            productRating.total_reviews
                        }}
                        reviews)
                    </span>
                </div>
                <h2 class="font-thin text-gray-400 text-2xl">
                    Rp {{ product.price }}
                </h2>
                <p class="mt-4">{{ product.description }}</p>

                <!-- Category -->
                <div class="mt-4">
                    <span class="font-semibold">Category: </span>
                    <span>{{ getCategoryName(product.category_id) }}</span>
                </div>

                <!-- Stock -->
                <div class="mt-2">
                    <span class="font-semibold">Stock: </span>
                    <span>{{ product.stock }}</span>
                </div>

                <!-- Colors -->
                <div
                    v-if="product.colors && product.colors.length"
                    class="mt-4"
                >
                    <h3 class="font-semibold mb-2">Colors</h3>
                    <div class="flex gap-2">
                        <button
                            v-for="color in product.colors"
                            :key="color.id"
                            @click="selectedColor = color.color"
                            class="w-8 h-8 rounded-full border-2"
                            :class="{
                                'border-orange-500':
                                    selectedColor === color.color,
                            }"
                            :style="{ backgroundColor: color.color }"
                        ></button>
                    </div>
                </div>

                <!-- Sizes -->
                <div v-if="product.sizes && product.sizes.length" class="mt-4">
                    <h3 class="font-semibold mb-2">Sizes</h3>
                    <div class="flex gap-2">
                        <button
                            v-for="size in product.sizes"
                            :key="size.id"
                            @click="selectedSize = size.size"
                            :class="[
                                'px-4 py-2 border rounded-md hover:bg-orange-500 hover:text-white duration-300',
                                selectedSize === size.size
                                    ? 'bg-orange-500 text-white'
                                    : '',
                            ]"
                        >
                            {{ size.size }}
                        </button>
                    </div>
                </div>

                <!-- Quantity and Add to Cart -->
                <div class="flex flex-row gap-x-4 mt-8">
                    <div class="flex flex-row items-center p-0">
                        <button
                            type="button"
                            @click="quantity > 1 ? quantity-- : null"
                            class="p-4 border-l border-y border-gray-300 hover:bg-orange-500 rounded-l-md hover:text-white duration-300"
                        >
                            -
                        </button>
                        <input
                            type="number"
                            v-model="quantity"
                            class="border-x-0 border-y border-gray-300 text-center p-4 hover:bg-orange-500 hover:text-white duration-300 w-16 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                            min="1"
                            :max="product.stock"
                        />
                        <button
                            type="button"
                            @click="
                                quantity < product.stock ? quantity++ : null
                            "
                            class="p-4 border-r border-y border-gray-300 hover:bg-orange-500 rounded-r-md hover:text-white duration-300"
                        >
                            +
                        </button>
                    </div>

                    <button
                        @click="addToCart"
                        class="rounded-md border border-gray-300 px-10 py-4 hover:bg-orange-500 hover:text-white duration-300"
                        :disabled="
                            product.stock < 1 ||
                            (!selectedSize && product.sizes?.length) ||
                            (!selectedColor && product.colors?.length) ||
                            quantity > product.stock
                        "
                    >
                        {{
                            product.stock < 1
                                ? "Out of Stock"
                                : !selectedSize && product.sizes?.length
                                ? "Please select size"
                                : !selectedColor && product.colors?.length
                                ? "Please select color"
                                : quantity > product.stock
                                ? "Not enough stock"
                                : "Add To Cart"
                        }}
                    </button>

                    <button
                        @click.prevent="addToWishlist"
                        class="rounded-md border border-gray-300 px-10 py-4 hover:bg-orange-500 hover:text-white duration-300"
                    >
                        Add To Wishlist
                    </button>

                    <!-- Toast notification with close button -->
                    <div
                        v-if="errorMessage"
                        class="fixed top-24 right-24 z-50 rounded-md bg-red-50 p-4 shadow-lg"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-5 w-5 text-red-400"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800">
                                        {{ errorMessage }}
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="errorMessage = ''"
                                class="ml-4 text-red-400 hover:text-red-500"
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <!-- Success notification -->
                    <div
                        v-if="successMessage"
                        class="fixed top-24 right-24 z-50 rounded-md bg-green-50 p-4 shadow-lg"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-5 w-5 text-green-400"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p
                                        class="text-sm font-medium text-green-800"
                                    >
                                        {{ successMessage }}
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="successMessage = ''"
                                class="ml-4 text-green-400 hover:text-green-500"
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div
                        v-if="wishlistSuccessMessage"
                        class="fixed top-24 right-24 z-50 rounded-md bg-green-50 p-4 shadow-lg"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-5 w-5 text-green-400"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p
                                        class="text-sm font-medium text-green-800"
                                    >
                                        {{ wishlistSuccessMessage }}
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="wishlistSuccessMessage = ''"
                                class="ml-4 text-green-400 hover:text-green-500"
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Add error message toast -->
                    <div
                        v-if="wishlistErrorMessage"
                        class="fixed top-24 right-24 z-50 rounded-md bg-red-50 p-4 shadow-lg"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-5 w-5 text-red-400"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800">
                                        {{ wishlistErrorMessage }}
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="wishlistErrorMessage = ''"
                                class="ml-4 text-red-400 hover:text-red-500"
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </CustomersLayout>
</template>

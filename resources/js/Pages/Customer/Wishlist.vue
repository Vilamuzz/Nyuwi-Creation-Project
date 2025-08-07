<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Hero from "@/Components/Customer/Main/Hero.vue";

// Reactive state
const wishlistItems = ref([]);
const isLoading = ref(true);
const error = ref(null);
const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
});

// Toast notification state
const message = ref("");
const messageType = ref("");
const showMessage = ref(false);

// Fetch wishlist from API
const fetchWishlist = async (page = 1) => {
    try {
        isLoading.value = true;
        const response = await axios.get(`/api/wishlist?page=${page}`);

        if (response.data.success) {
            wishlistItems.value = response.data.data.data;
            pagination.value = {
                current_page: response.data.data.current_page,
                last_page: response.data.data.last_page,
                total: response.data.data.total,
            };
        } else {
            error.value = "Failed to load wishlist items";
        }
    } catch (err) {
        console.error("Error fetching wishlist data:", err);
        error.value = "Failed to load wishlist items";
    } finally {
        isLoading.value = false;
    }
};

// Remove item from wishlist
const removeFromWishlist = async (itemId) => {
    if (confirm("Are you sure you want to remove this item from wishlist?")) {
        try {
            const response = await axios.delete(`/api/wishlist/${itemId}`);

            if (response.data.success) {
                // Show success message
                message.value =
                    response.data.message || "Item removed from wishlist";
                messageType.value = "success";
                showMessage.value = true;

                // Refresh the wishlist data
                fetchWishlist(pagination.value.current_page);

                // Hide message after 3 seconds
                setTimeout(() => {
                    showMessage.value = false;
                }, 3000);
            } else {
                console.error("Failed to remove item:", response.data.message);
            }
        } catch (error) {
            console.error("Error removing item:", error);

            // Show error message
            message.value =
                error.response?.data?.message ||
                "Failed to remove item from wishlist";
            messageType.value = "error";
            showMessage.value = true;

            // Hide message after 3 seconds
            setTimeout(() => {
                showMessage.value = false;
            }, 3000);
        }
    }
};

// Change page
const changePage = (page) => {
    fetchWishlist(page);
};

// Fetch wishlist on component mount
onMounted(() => {
    fetchWishlist();
});
</script>

<template>
    <CustomersLayout>
        <Head title="My Wishlist" />
        <Hero title="Wishlist" breadcrumb="Home > Wishlist" />

        <!-- DaisyUI Toast Notification -->
        <div v-if="showMessage" class="toast toast-top toast-end z-50">
            <div
                class="alert"
                :class="
                    messageType === 'success' ? 'alert-success' : 'alert-error'
                "
            >
                <span
                    v-if="messageType === 'success'"
                    class="flex items-center"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="stroke-current shrink-0 h-6 w-6 mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    {{ message }}
                </span>
                <span v-else class="flex items-center">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="stroke-current shrink-0 h-6 w-6 mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    {{ message }}
                </span>
            </div>
        </div>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold mb-6">My Wishlist</h1>

            <!-- Loading state -->
            <div v-if="isLoading" class="text-center py-20">
                <div
                    class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"
                ></div>
                <p class="mt-4 text-gray-600">Loading wishlist...</p>
            </div>

            <!-- Error state -->
            <div v-else-if="error" class="text-center py-20 text-red-500">
                <p>{{ error }}</p>
                <button
                    @click="fetchWishlist"
                    class="mt-4 px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600"
                >
                    Retry
                </button>
            </div>

            <!-- Wishlist content -->
            <div v-else-if="wishlistItems.length" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="item in wishlistItems"
                        :key="item.id"
                        class="border rounded-lg overflow-hidden shadow-sm"
                    >
                        <img
                            :src="`/storage/products/${item.product.images[0]}`"
                            :alt="item.product.name"
                            class="w-full h-48 object-cover"
                        />

                        <div class="p-4">
                            <h2 class="text-lg font-semibold">
                                {{ item.product.name }}
                            </h2>
                            <p class="text-gray-600">
                                {{
                                    new Intl.NumberFormat("id-ID", {
                                        style: "currency",
                                        currency: "IDR",
                                        minimumFractionDigits: 0,
                                    }).format(item.product.price)
                                }}
                            </p>

                            <div class="mt-4 flex justify-between">
                                <Link
                                    :href="
                                        route('product', {
                                            slug: item.product.slug,
                                        })
                                    "
                                    class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600"
                                >
                                    View Product
                                </Link>
                                <button
                                    @click="removeFromWishlist(item.id)"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="pagination.last_page > 1"
                    class="flex justify-center gap-2 mt-8"
                >
                    <button
                        v-for="page in pagination.last_page"
                        :key="page"
                        @click="changePage(page)"
                        :class="[
                            'py-2 px-4 rounded-md border',
                            page === pagination.current_page
                                ? 'bg-orange-500 text-white border-orange-500'
                                : 'bg-orange-100 hover:bg-orange-500 hover:text-white duration-300',
                        ]"
                    >
                        {{ page }}
                    </button>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center text-gray-500 py-8">
                Your wishlist is empty
            </div>
        </div>
    </CustomersLayout>
</template>

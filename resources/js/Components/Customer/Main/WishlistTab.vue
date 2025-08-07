<script setup>
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";
import axios from "axios";
import { CircleCheck, AlertCircle } from "lucide-vue-next"; // Optional: import icons

const props = defineProps({
    wishlistItems: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["refresh"]);

// Format price for displaying
const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
};

// Success/error messages
const message = ref("");
const messageType = ref("");
const showMessage = ref(false);

// Add function to remove from wishlist using API
const removeFromWishlist = async (id) => {
    if (
        confirm("Are you sure you want to remove this item from your wishlist?")
    ) {
        try {
            const response = await axios.delete(`/api/wishlist/${id}`);

            if (response.data.success) {
                message.value =
                    response.data.message || "Item removed from wishlist";
                messageType.value = "success";
                showMessage.value = true;

                // Emit event to parent to refresh wishlist data
                emit("refresh");

                // Hide message after 3 seconds
                setTimeout(() => {
                    showMessage.value = false;
                }, 3000);
            }
        } catch (error) {
            console.error("Error removing from wishlist:", error);
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
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-xl font-bold mb-4">Wishlist</h2>

        <!-- DaisyUI Alert Component -->
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

        <div
            v-if="wishlistItems?.length"
            class="grid grid-cols-1 md:grid-cols-3 gap-6"
        >
            <div
                v-for="item in wishlistItems"
                :key="item.id"
                class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow"
            >
                <img
                    :src="`/storage/products/${item.product.images[0]}`"
                    :alt="item.product.name"
                    class="w-full h-48 object-cover"
                />

                <div class="p-4">
                    <h3 class="text-lg font-semibold">
                        {{ item.product.name }}
                    </h3>
                    <p class="text-gray-600 mt-2">
                        {{ formatPrice(item.product.price) }}
                    </p>

                    <div class="mt-4 flex justify-between items-center">
                        <Link
                            :href="
                                route('product', { slug: item.product.slug })
                            "
                            class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600"
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
        <div v-else class="text-gray-500 text-center py-8">
            Your wishlist is empty
        </div>
    </div>
</template>

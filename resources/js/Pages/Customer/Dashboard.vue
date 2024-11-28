<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import { ref, computed, onMounted } from "vue";
import axios from "axios";

const props = defineProps({
    orders: Array,
});

// Create a ref from props.orders
const orders = ref(props.orders);

const selectedAction = ref("history");
const showOrderModal = ref(false);
const selectedOrder = ref(null);
const trackingInfo = ref(null);
const trackingError = ref(null);
const showTrackingInfo = ref(false);
const expandedOrders = ref(new Set());

const reviewForm = useForm({
    order_id: null,
    reviews: [], // Will contain {product_id, rating} objects
});

const openOrderModal = async (order) => {
    selectedOrder.value = order;
    showOrderModal.value = true;
    trackingInfo.value = null;
    trackingError.value = null;
    showTrackingInfo.value = false;

    // Initialize empty reviews array for each product in order
    reviewForm.reviews = selectedOrder.value.order_items.map((item) => ({
        product_id: item.product_id,
        rating: 0,
    }));

    // Immediately fetch tracking info if available
    if (order.tracking_number && order.shipping_method) {
        try {
            const response = await axios.get(
                "https://api.binderbyte.com/v1/track",
                {
                    params: {
                        api_key:
                            "151a782863970433251abbcbf51fe253f4625de1eda00f7a49ba55d90e7419a5",
                        courier: order.shipping_method.toLowerCase(),
                        awb: order.tracking_number,
                    },
                }
            );
            if (response.data.status === 200) {
                trackingInfo.value = response.data.data;
                showTrackingInfo.value = true;
            } else {
                trackingError.value = response.data.message;
            }
        } catch (error) {
            trackingError.value = "Failed to fetch tracking information";
            console.error("Tracking error:", error);
        }
    }
};

const closeOrderModal = () => {
    showOrderModal.value = false;
    selectedOrder.value = null;
};

const actions = [
    { id: "history", label: "Riwayat Belanja" },
    { id: "wishlist", label: "Wishlist" },
    { id: "reviews", label: "Ulasan Belanja" },
];

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
};

const getStatusClass = (status) => {
    return {
        "px-2 py-1 text-xs font-semibold rounded-full": true,
        "bg-yellow-100 text-yellow-800": status === "awaiting",
        "bg-blue-100 text-blue-800": status === "processing",
        "bg-green-100 text-green-800": status === "completed",
        "bg-red-100 text-red-800": status === "cancelled",
    };
};

const toggleOrder = (orderId) => {
    if (expandedOrders.value.has(orderId)) {
        expandedOrders.value.delete(orderId);
    } else {
        expandedOrders.value.add(orderId);
    }
};

// Add new computed property
const isDelivered = computed(() => {
    return trackingInfo.value?.summary?.status?.toLowerCase() === "delivered";
});

// Track if all products are reviewed
const allProductsReviewed = computed(() => {
    if (!selectedOrder.value || !reviewForm.reviews.length) return false;
    return selectedOrder.value.order_items.every((item) =>
        reviewForm.reviews.some(
            (review) =>
                review.product_id === item.product_id && review.rating > 0
        )
    );
});

// Handle rating selection
const setRating = (productId, rating) => {
    if (!selectedOrder.value) return; // Guard clause

    const existingReview = reviewForm.reviews.find(
        (r) => r.product_id === productId
    );
    if (existingReview) {
        existingReview.rating = rating;
    }
};

// Submit reviews and complete order
const completeOrder = () => {
    if (!allProductsReviewed.value) {
        alert("Please rate all products before completing the order");
        return;
    }

    reviewForm.order_id = selectedOrder.value.id; // Fix: Remove .value after reviewForm

    reviewForm.post("/orders/complete", {
        // Use direct URL path instead of route name
        preserveScroll: true,
        onSuccess: () => {
            closeOrderModal();
            // Optionally refresh the page or update the order status locally
            window.location.reload();
        },
    });
};

// Add new data for reviews section
const reviewedOrders = computed(() => {
    return orders.value?.filter((order) => order.status === "completed");
});

// Add function to open review modal
const showReviewModal = ref(false);
const selectedReviewOrder = ref(null);

const openReviewModal = (order) => {
    selectedReviewOrder.value = order;
    showReviewModal.value = true;
};

const closeReviewModal = () => {
    showReviewModal.value = false;
    selectedReviewOrder.value = null;
};

// Add to existing refs
const userReviews = ref([]);

// Add this function
const fetchUserReviews = async () => {
    try {
        const response = await axios.get("/user/reviews");
        userReviews.value = response.data;
    } catch (error) {
        console.error("Error fetching reviews:", error);
    }
};

// Call this in the mounted hook
onMounted(() => {
    fetchUserReviews();
});
</script>

<template>
    <Head title="Dashboard" />
    <CustomersLayout>
        <section class="mx-16 flex flex-row my-8 gap-x-8">
            <!-- Sidebar -->
            <div class="w-1/4">
                <div class="border rounded-md p-8 shadow-md">
                    <h1 class="text-xl font-bold mb-4">Profile</h1>
                    <h1 class="mb-2">{{ $page.props.auth.user.name }}</h1>
                    <h1 class="mb-4">{{ $page.props.auth.user.email }}</h1>

                    <h1 class="font-semibold mb-3">Aksi</h1>
                    <div class="space-y-2">
                        <button
                            v-for="action in actions"
                            :key="action.id"
                            @click="selectedAction = action.id"
                            class="block w-full text-left px-3 py-2 rounded-md transition-colors"
                            :class="{
                                'bg-orange-500 text-white':
                                    selectedAction === action.id,
                                'hover:bg-orange-100':
                                    selectedAction !== action.id,
                            }"
                        >
                            {{ action.label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="w-3/4 border rounded-md p-8">
                <!-- Shopping History -->
                <div v-if="selectedAction === 'history'" class="space-y-4">
                    <h2 class="text-xl font-bold mb-4">Riwayat Belanja</h2>
                    <div v-if="orders?.length" class="grid gap-4">
                        <!-- Order Cards -->
                        <div
                            v-for="order in orders"
                            :key="order.id"
                            class="border rounded-lg p-4 hover:shadow-md transition-shadow"
                        >
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-semibold">
                                        Order #{{ order.id }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{
                                            new Date(
                                                order.created_at
                                            ).toLocaleDateString()
                                        }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <p class="font-bold">
                                        {{ formatPrice(order.total_price) }}
                                    </p>
                                    <span :class="getStatusClass(order.status)">
                                        {{ order.status }}
                                    </span>
                                    <button
                                        @click="openOrderModal(order)"
                                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                                    >
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-gray-500">No orders found</div>
                </div>

                <!-- Order Details Modal -->
                <div
                    v-if="showOrderModal"
                    class="fixed inset-0 z-50 overflow-y-auto"
                >
                    <div
                        class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                    ></div>
                    <div
                        class="flex min-h-full items-center justify-center p-4"
                    >
                        <div
                            class="relative bg-white rounded-lg max-w-4xl w-full shadow-xl"
                        >
                            <!-- Modal Header -->
                            <div class="px-6 py-4 border-b">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-xl font-semibold">
                                        Order Details #{{ selectedOrder?.id }}
                                    </h3>
                                    <button
                                        @click="closeOrderModal"
                                        class="text-gray-400 hover:text-gray-500"
                                    >
                                        ×
                                    </button>
                                </div>
                            </div>

                            <!-- Modal Content -->
                            <div class="p-6">
                                <!-- Order Status and Details -->
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p>
                                            <span class="font-semibold"
                                                >Status:</span
                                            >
                                            <span
                                                :class="
                                                    getStatusClass(
                                                        selectedOrder?.status
                                                    )
                                                "
                                            >
                                                {{ selectedOrder?.status }}
                                            </span>
                                        </p>
                                        <p>
                                            <span class="font-semibold"
                                                >Date:</span
                                            >
                                            {{
                                                new Date(
                                                    selectedOrder?.created_at
                                                ).toLocaleDateString()
                                            }}
                                        </p>
                                        <p>
                                            <span class="font-semibold"
                                                >Shipping Method:</span
                                            >
                                            {{ selectedOrder?.shipping_method }}
                                        </p>
                                    </div>
                                    <div>
                                        <p>
                                            <span class="font-semibold"
                                                >Address:</span
                                            >
                                            {{ selectedOrder?.address }}
                                        </p>
                                        <p>
                                            <span class="font-semibold"
                                                >Phone:</span
                                            >
                                            {{ selectedOrder?.phone }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Order Items -->
                                <div class="mt-4">
                                    <h4 class="font-semibold mb-2">
                                        Order Items
                                    </h4>
                                    <div class="space-y-2">
                                        <div
                                            v-for="item in selectedOrder?.order_items"
                                            :key="item.id"
                                            class="flex items-center justify-between p-2 bg-gray-50 rounded"
                                        >
                                            <div
                                                class="flex items-center space-x-4"
                                            >
                                                <img
                                                    :src="`/storage/products/${item.product.image}`"
                                                    class="w-16 h-16 object-cover rounded"
                                                    :alt="item.product.name"
                                                />
                                                <div>
                                                    <p class="font-medium">
                                                        {{ item.product.name }}
                                                    </p>
                                                    <div
                                                        class="text-sm text-gray-500"
                                                    >
                                                        <span v-if="item.size"
                                                            >Size:
                                                            {{
                                                                item.size
                                                            }}</span
                                                        >
                                                        <span
                                                            v-if="item.color"
                                                            class="ml-2"
                                                        >
                                                            Color:
                                                            <span
                                                                class="inline-block w-4 h-4 rounded-full"
                                                                :style="{
                                                                    backgroundColor:
                                                                        item.color,
                                                                }"
                                                            >
                                                            </span>
                                                        </span>
                                                    </div>
                                                    <p
                                                        class="text-sm text-gray-500"
                                                    >
                                                        Qty: {{ item.quantity }}
                                                    </p>
                                                </div>
                                            </div>
                                            <p class="font-medium">
                                                {{
                                                    formatPrice(
                                                        item.total_price
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tracking Information -->
                                <div
                                    v-if="selectedOrder?.tracking_number"
                                    class="mt-6"
                                >
                                    <div
                                        class="flex justify-between items-center mb-4"
                                    >
                                        <h4 class="font-semibold">
                                            Tracking Information
                                        </h4>
                                    </div>

                                    <p class="mb-4">
                                        <span class="font-semibold"
                                            >Tracking Number:</span
                                        >
                                        {{ selectedOrder.tracking_number }}
                                    </p>

                                    <!-- Tracking Results -->
                                    <div
                                        v-if="showTrackingInfo && trackingInfo"
                                        class="border rounded-lg p-4"
                                    >
                                        <div class="mb-4">
                                            <p>
                                                <span class="font-semibold"
                                                    >Status:</span
                                                >
                                                {{
                                                    trackingInfo.summary.status
                                                }}
                                            </p>
                                            <p>
                                                <span class="font-semibold"
                                                    >Service:</span
                                                >
                                                {{
                                                    trackingInfo.summary.service
                                                }}
                                            </p>
                                        </div>

                                        <!-- Tracking History -->
                                        <div class="space-y-4">
                                            <h5 class="font-semibold">
                                                Tracking History
                                            </h5>
                                            <div
                                                v-for="(
                                                    history, index
                                                ) in trackingInfo.history"
                                                :key="index"
                                                class="border-l-2 border-gray-200 pl-4 pb-4 relative"
                                            >
                                                <div
                                                    class="absolute w-3 h-3 bg-blue-500 rounded-full -left-[7px]"
                                                ></div>
                                                <p class="font-semibold">
                                                    {{ history.date }}
                                                </p>
                                                <p>{{ history.desc }}</p>
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{ history.location }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="trackingError"
                                        class="text-red-500 mt-2"
                                    >
                                        {{ trackingError }}
                                    </div>
                                </div>

                                <!-- Order items with reviews -->
                                <div
                                    v-if="
                                        isDelivered &&
                                        selectedOrder.status !== 'completed'
                                    "
                                    class="space-y-4"
                                >
                                    <h3 class="font-semibold text-lg mb-4">
                                        Rate Your Products
                                    </h3>
                                    <div
                                        v-for="item in selectedOrder.order_items"
                                        :key="item.id"
                                        class="border rounded p-4"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <div
                                                class="flex items-center space-x-4"
                                            >
                                                <img
                                                    :src="`/storage/products/${item.product.image}`"
                                                    class="w-16 h-16 object-cover rounded"
                                                    :alt="item.product.name"
                                                />
                                                <div>
                                                    <p class="font-medium">
                                                        {{ item.product.name }}
                                                    </p>
                                                    <div
                                                        class="flex items-center space-x-1"
                                                    >
                                                        <!-- Star rating system -->
                                                        <button
                                                            v-for="star in 5"
                                                            :key="star"
                                                            @click="
                                                                setRating(
                                                                    item.product_id,
                                                                    star
                                                                )
                                                            "
                                                            class="focus:outline-none"
                                                            type="button"
                                                        >
                                                            <svg
                                                                :class="[
                                                                    'w-5 h-5',
                                                                    reviewForm.reviews.find(
                                                                        (r) =>
                                                                            r.product_id ===
                                                                            item.product_id
                                                                    )?.rating >=
                                                                    star
                                                                        ? 'text-yellow-400'
                                                                        : 'text-gray-300',
                                                                ]"
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                viewBox="0 0 20 20"
                                                                fill="currentColor"
                                                            >
                                                                <path
                                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                                                />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Complete Order Button -->
                                    <button
                                        @click="completeOrder"
                                        :disabled="!allProductsReviewed"
                                        class="w-full mt-4 py-2 px-4 rounded-lg transition"
                                        :class="[
                                            allProductsReviewed
                                                ? 'bg-green-600 hover:bg-green-700 text-white'
                                                : 'bg-gray-300 text-gray-500 cursor-not-allowed',
                                        ]"
                                    >
                                        {{
                                            allProductsReviewed
                                                ? "Complete Order"
                                                : "Please Rate All Products"
                                        }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wishlist -->
                <div
                    v-else-if="selectedAction === 'wishlist'"
                    class="space-y-4"
                >
                    <h2 class="text-xl font-bold mb-4">Wishlist</h2>
                    <div v-if="wishlist?.length" class="grid grid-cols-3 gap-4">
                        <!-- Add wishlist items here -->
                        <p>Your wishlist items will appear here</p>
                    </div>
                    <div v-else class="text-gray-500">
                        Your wishlist is empty
                    </div>
                </div>

                <!-- Reviews -->
                <div v-else-if="selectedAction === 'reviews'" class="space-y-4">
                    <h2 class="text-xl font-bold mb-4">Ulasan Belanja</h2>
                    <div v-if="reviewedOrders?.length" class="space-y-4">
                        <!-- List of completed orders with reviews -->
                        <div
                            v-for="order in reviewedOrders"
                            :key="order.id"
                            class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                            @click="openReviewModal(order)"
                        >
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-semibold">
                                        Order #{{ order.id }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{
                                            new Date(
                                                order.created_at
                                            ).toLocaleDateString()
                                        }}
                                    </p>
                                </div>
                                <button
                                    @click="openReviewModal(order)"
                                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                                >
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-gray-500">
                        No completed orders found
                    </div>

                    <!-- Review Modal -->
                    <div
                        v-if="showReviewModal && selectedReviewOrder"
                        class="fixed inset-0 z-50 overflow-y-auto"
                    >
                        <div
                            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                        ></div>
                        <div
                            class="flex min-h-full items-center justify-center p-4"
                        >
                            <div
                                class="relative bg-white rounded-lg max-w-4xl w-full shadow-xl"
                            >
                                <!-- Modal Header -->
                                <div class="px-6 py-4 border-b">
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <h3 class="text-xl font-semibold">
                                            Order Reviews #{{
                                                selectedReviewOrder.id
                                            }}
                                        </h3>
                                        <button
                                            @click="closeReviewModal"
                                            class="text-gray-400 hover:text-gray-500"
                                        >
                                            ×
                                        </button>
                                    </div>
                                </div>

                                <!-- Modal Content -->
                                <div class="p-6">
                                    <div class="space-y-4">
                                        <div
                                            v-for="review in userReviews"
                                            :key="review.id"
                                            class="border rounded-lg p-4 flex items-center justify-between"
                                        >
                                            <div
                                                class="flex items-center space-x-4"
                                            >
                                                <img
                                                    :src="`/storage/products/${review.product.image}`"
                                                    :alt="review.product.name"
                                                    class="w-16 h-16 object-cover rounded"
                                                />
                                                <div>
                                                    <h3 class="font-semibold">
                                                        {{
                                                            review.product.name
                                                        }}
                                                    </h3>
                                                    <div
                                                        class="flex items-center space-x-1"
                                                    >
                                                        <div
                                                            v-for="star in 5"
                                                            :key="star"
                                                        >
                                                            <svg
                                                                :class="[
                                                                    'w-5 h-5',
                                                                    review.rating >=
                                                                    star
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
                                                    <p
                                                        class="text-sm text-gray-500"
                                                    >
                                                        Order #{{
                                                            review.order_id
                                                        }}
                                                        •
                                                        {{
                                                            new Date(
                                                                review.created_at
                                                            ).toLocaleDateString()
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Reviews -->
                <div v-else-if="selectedAction === 'reviews'" class="space-y-4">
                    <h2 class="text-xl font-bold mb-4">My Reviews</h2>
                    <div v-if="userReviews.length" class="grid gap-4">
                        <div
                            v-for="review in userReviews"
                            :key="review.id"
                            class="border rounded-lg p-4 flex items-center justify-between"
                        >
                            <div class="flex items-center space-x-4">
                                <img
                                    :src="`/storage/products/${review.product.image}`"
                                    :alt="review.product.name"
                                    class="w-16 h-16 object-cover rounded"
                                />
                                <div>
                                    <h3 class="font-semibold">
                                        {{ review.product.name }}
                                    </h3>
                                    <div class="flex items-center space-x-1">
                                        <div v-for="star in 5" :key="star">
                                            <svg
                                                :class="[
                                                    'w-5 h-5',
                                                    review.rating >= star
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
                                    <p class="text-sm text-gray-500">
                                        Order #{{ review.order_id }} •
                                        {{
                                            new Date(
                                                review.created_at
                                            ).toLocaleDateString()
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-gray-500">
                        You haven't reviewed any products yet.
                    </div>
                </div>
            </div>
        </section>
    </CustomersLayout>
</template>

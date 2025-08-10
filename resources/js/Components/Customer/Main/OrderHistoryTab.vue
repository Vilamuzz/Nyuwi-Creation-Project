<script setup>
import { ref, computed } from "vue";
import axios from "axios";

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["viewOrder", "uploadPayment"]);

// Format price for displaying
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
        "bg-yellow-100 text-yellow-800": status === "waiting",
        "bg-purple-100 text-purple-800": status === "checking",
        "bg-purple-100 text-cyan-800": status === "shiping",
        "bg-orange-100 text-orange-800": status === "pending",
        "bg-blue-100 text-blue-800": status === "processing",
        "bg-green-100 text-green-800": status === "completed",
        "bg-red-100 text-red-800": status === "cancelled",
    };
};

// New state variables for order details modal
const showOrderModal = ref(false);
const selectedOrder = ref(null);
const isLoadingTracking = ref(false);
const trackingInfo = ref(null);
const trackingError = ref(null);

// Open order details modal
const openOrderModal = (order) => {
    selectedOrder.value = order;
    showOrderModal.value = true;

    // Reset review form for this order
    reviewForm.value = {
        order_id: order.id,
        reviews: [],
    };

    // Fetch tracking information if available
    if (order.tracking_number && order.shipping_method !== "GoSend") {
        fetchTrackingInfo(order.tracking_number);
    }
};

// Close order details modal
const closeOrderModal = () => {
    showOrderModal.value = false;
    selectedOrder.value = null;
    trackingInfo.value = null;
    trackingError.value = null;
    reviewForm.value = { order_id: null, reviews: [] };
};

// Fetch tracking information from the server
const fetchTrackingInfo = async (trackingNumber) => {
    isLoadingTracking.value = true;
    trackingError.value = null;

    try {
        const response = await fetch(`/api/tracking/${trackingNumber}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
            },
        });

        const data = await response.json();

        if (data.success) {
            trackingInfo.value = data.data;
        } else {
            trackingError.value =
                data.message || "Failed to fetch tracking information";
        }
    } catch (error) {
        trackingError.value =
            "An error occurred while fetching tracking information";
        console.error("Tracking API error:", error);
    } finally {
        isLoadingTracking.value = false;
    }
};

// Review form state
const reviewForm = ref({
    order_id: null,
    reviews: [],
});

// Add computed property to check if order is delivered
const isDelivered = computed(() => {
    if (!selectedOrder.value) return false;

    // If using GoSend and status is shipping, can be reviewed
    if (
        selectedOrder.value.shipping_method === "GoSend" &&
        selectedOrder.value.status === "shiping"
    ) {
        return true;
    }

    // For other couriers, check tracking status
    return trackingInfo.value?.summary?.status?.toLowerCase() === "delivered";
});

// Add computed property to check if all products are reviewed
const allProductsReviewed = computed(() => {
    if (!selectedOrder.value || !reviewForm.value.reviews.length) return false;

    return selectedOrder.value.order_items.every((item) =>
        reviewForm.value.reviews.some(
            (review) =>
                review.product_id === item.product_id && review.rating > 0
        )
    );
});

// Set rating for a product
const setRating = (product_id, rating) => {
    const existingReview = reviewForm.value.reviews.find(
        (review) => review.product_id === product_id
    );

    if (existingReview) {
        existingReview.rating = rating;
    } else {
        reviewForm.value.reviews.push({ product_id, rating });
    }
};

// Complete order after reviewing products
const completeOrder = async () => {
    if (!allProductsReviewed.value) {
        alert("Please rate all products before completing the order");
        return;
    }

    try {
        const response = await axios.post("/api/orders/complete", {
            order_id: selectedOrder.value.id,
            reviews: reviewForm.value.reviews,
        });

        if (response.data.success) {
            closeOrderModal();
            alert(response.data.message || "Order completed successfully!");
            window.location.reload();
        } else {
            alert(
                response.data.message ||
                    "Failed to complete order. Please try again."
            );
        }
    } catch (error) {
        console.error("Error completing order:", error);
        const message =
            error.response?.data?.message ||
            "An error occurred. Please try again.";
        alert(message);
    }
};
</script>

<template>
    <div class="space-y-4">
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
                        <p class="font-semibold">Order #{{ order.id }}</p>
                        <p class="text-sm text-gray-500">
                            {{
                                new Date(order.created_at).toLocaleDateString()
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

                        <!-- Add payment proof button for both digital wallet and QRIS orders -->
                        <button
                            v-if="
                                (order.payment_method === 'digital_wallet' ||
                                    order.payment_method === 'qris') &&
                                order.status === 'waiting'
                            "
                            @click="emit('uploadPayment', order)"
                            class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600"
                        >
                            Upload Bukti Pembayaran
                        </button>

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
    <div v-if="showOrderModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
        ></div>
        <div class="flex min-h-full items-center justify-center p-4">
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
                            class="text-gray-400 hover:text-gray-500 text-2xl"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-6">
                    <div v-if="selectedOrder" class="space-y-6">
                        <!-- Order Information -->
                        <div>
                            <h4 class="text-lg font-semibold mb-3">
                                Order Information
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p>
                                        <span class="font-medium">Status:</span>
                                        <span
                                            :class="
                                                getStatusClass(
                                                    selectedOrder.status
                                                )
                                            "
                                        >
                                            {{ selectedOrder.status }}
                                        </span>
                                    </p>
                                    <p>
                                        <span class="font-medium"
                                            >Payment Method:</span
                                        >
                                        {{ selectedOrder.payment_method }}
                                    </p>
                                    <p>
                                        <span class="font-medium"
                                            >Shipping Method:</span
                                        >
                                        {{ selectedOrder.shipping_method }}
                                    </p>
                                </div>
                                <div>
                                    <p>
                                        <span class="font-medium"
                                            >Order Date:</span
                                        >
                                        {{
                                            new Date(
                                                selectedOrder.created_at
                                            ).toLocaleDateString()
                                        }}
                                    </p>
                                    <p>
                                        <span class="font-medium">Total:</span>
                                        {{
                                            formatPrice(
                                                selectedOrder.total_price
                                            )
                                        }}
                                    </p>
                                    <p v-if="selectedOrder.tracking_number">
                                        <span class="font-medium"
                                            >Tracking Number:</span
                                        >
                                        {{ selectedOrder.tracking_number }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div>
                            <h4 class="text-lg font-semibold mb-3">
                                Shipping Address
                            </h4>
                            <p>{{ selectedOrder.name }}</p>
                            <p>{{ selectedOrder.address }}</p>
                            <p>
                                {{ selectedOrder.district }},
                                {{ selectedOrder.city }}
                            </p>
                            <p>{{ selectedOrder.province }}</p>
                            <p>Phone: {{ selectedOrder.phone }}</p>
                        </div>

                        <!-- Order Items -->
                        <div>
                            <h4 class="text-lg font-semibold mb-3">
                                Order Items
                            </h4>
                            <div class="space-y-3">
                                <div
                                    v-for="item in selectedOrder.order_items"
                                    :key="item.id"
                                    class="flex items-center space-x-4 border rounded-lg p-3"
                                >
                                    <img
                                        :src="`/storage/products/${item.product.images[0]}`"
                                        :alt="item.product.name"
                                        class="w-16 h-16 object-cover rounded"
                                    />
                                    <div class="flex-1">
                                        <h5 class="font-medium">
                                            {{ item.product.name }}
                                        </h5>
                                        <p class="text-sm text-gray-500">
                                            <span v-if="item.size"
                                                >Size: {{ item.size }}</span
                                            >
                                            <span
                                                v-if="item.color"
                                                class="ml-2"
                                            >
                                                Color:
                                                <span
                                                    class="inline-block w-4 h-4 rounded-full ml-1"
                                                    :style="{
                                                        backgroundColor:
                                                            item.color,
                                                    }"
                                                ></span>
                                            </span>
                                        </p>
                                        <p class="text-sm">
                                            Quantity: {{ item.quantity }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium">
                                            {{ formatPrice(item.price) }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Total:
                                            {{ formatPrice(item.total_price) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tracking Information -->
                        <div
                            v-if="
                                selectedOrder.tracking_number &&
                                selectedOrder.shipping_method !== 'GoSend'
                            "
                        >
                            <h4 class="text-lg font-semibold mb-3">
                                Tracking Information
                            </h4>

                            <!-- Loading State -->
                            <div
                                v-if="isLoadingTracking"
                                class="text-center py-4"
                            >
                                <div
                                    class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500 mx-auto"
                                ></div>
                                <p class="mt-2 text-gray-600">
                                    Loading tracking information...
                                </p>
                            </div>

                            <!-- Tracking Info -->
                            <div
                                v-else-if="trackingInfo"
                                class="border rounded-lg p-4"
                            >
                                <div class="mb-4">
                                    <p>
                                        <span class="font-medium">Status:</span>
                                        {{ trackingInfo.summary.status }}
                                    </p>
                                    <p>
                                        <span class="font-medium"
                                            >Service:</span
                                        >
                                        {{ trackingInfo.summary.service }}
                                    </p>
                                </div>

                                <!-- Tracking History -->
                                <div class="space-y-3">
                                    <h5 class="font-medium">
                                        Tracking History
                                    </h5>
                                    <div
                                        v-for="(
                                            history, index
                                        ) in trackingInfo.history"
                                        :key="index"
                                        class="border-l-2 border-gray-200 pl-4 pb-3 relative"
                                    >
                                        <div
                                            class="absolute w-3 h-3 bg-blue-500 rounded-full -left-[7px]"
                                        ></div>
                                        <p class="font-medium">
                                            {{ history.date }}
                                        </p>
                                        <p>{{ history.desc }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ history.location }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Error Message -->
                            <div v-else-if="trackingError" class="text-red-500">
                                {{ trackingError }}
                            </div>
                        </div>

                        <!-- Review Section for Delivered Orders -->
                        <div
                            v-if="
                                isDelivered &&
                                selectedOrder.status !== 'completed'
                            "
                        >
                            <h4 class="text-lg font-semibold mb-3">
                                Rate Products
                            </h4>
                            <div class="space-y-4">
                                <div
                                    v-for="item in selectedOrder.order_items"
                                    :key="item.id"
                                    class="border rounded-lg p-4"
                                >
                                    <div
                                        class="flex items-center space-x-4 mb-3"
                                    >
                                        <img
                                            :src="`/storage/products/${item.product.images[0]}`"
                                            :alt="item.product.name"
                                            class="w-12 h-12 object-cover rounded"
                                        />
                                        <h5 class="font-medium">
                                            {{ item.product.name }}
                                        </h5>
                                    </div>

                                    <!-- Star Rating -->
                                    <div class="flex items-center space-x-1">
                                        <span class="text-sm font-medium mr-2"
                                            >Rating:</span
                                        >
                                        <button
                                            v-for="star in 5"
                                            :key="star"
                                            @click="
                                                setRating(item.product_id, star)
                                            "
                                            class="text-2xl focus:outline-none"
                                            :class="[
                                                reviewForm.reviews.find(
                                                    (r) =>
                                                        r.product_id ===
                                                        item.product_id
                                                )?.rating >= star
                                                    ? 'text-yellow-400'
                                                    : 'text-gray-300',
                                            ]"
                                        >
                                            ★
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Complete Order Button -->
                            <div class="mt-6 flex justify-end">
                                <button
                                    @click="completeOrder"
                                    :disabled="!allProductsReviewed"
                                    class="px-6 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Complete Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Your existing Payment Proof Modal -->
    <!-- ... -->
</template>

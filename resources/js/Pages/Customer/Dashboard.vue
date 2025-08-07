<script setup>
import { Head, useForm, Link } from "@inertiajs/vue3";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import Hero from "@/Components/Customer/Main/Hero.vue";
import UpdateProfileInformationForm from "@/Pages/Admin/Profile/Partials/UpdateProfileInformationForm.vue";
import UpdatePasswordForm from "@/Pages/Admin/Profile/Partials/UpdatePasswordForm.vue";
import DeleteUserForm from "@/Pages/Admin/Profile/Partials/DeleteUserForm.vue";
import OrderHistoryTab from "@/Components/Customer/Main/OrderHistoryTab.vue";
import WishlistTab from "@/Components/Customer/Main/WishlistTab.vue";
import ReviewsTab from "@/Components/Customer/Main/ReviewsTab.vue";
import SettingsTab from "@/Components/Customer/Main/SettingsTab.vue";

const props = defineProps({
    orders: Array,
    reviews: Array, // Tambahkan props reviews
    wishlistItems: Array, // Add this prop
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

// Create refs from props
const orders = ref(props.orders);
const userReviews = ref(props.reviews); // Gunakan data reviews dari props

const selectedAction = ref("history");
const showOrderModal = ref(false);
const selectedOrder = ref(null);
const trackingInfo = ref(null);
const trackingError = ref(null);
const showTrackingInfo = ref(false);
const expandedOrders = ref(new Set());
const isLoadingTracking = ref(false); // Tambahkan state untuk loading

const reviewForm = useForm({
    order_id: null,
    reviews: [], // Will contain {product_id, rating} objects
});

// Add new form for payment proof
const paymentProofForm = useForm({
    order_id: null,
    payment_proof: null,
});

// Add new state for payment proof modal
const showPaymentProofModal = ref(false);
const selectedPaymentOrder = ref(null);

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
        isLoadingTracking.value = true; // Set loading true
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
        } finally {
            isLoadingTracking.value = false; // Set loading false
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
    { id: "settings", label: "Pengaturan" }, // Add settings option
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
        "bg-yellow-100 text-yellow-800": status === "waiting",
        "bg-purple-100 text-purple-800": status === "checking",
        "bg-purple-100 text-cyan-800": status === "shiping",
        "bg-orange-100 text-orange-800": status === "pending",
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

// Modifikasi computed property isDelivered
const isDelivered = computed(() => {
    if (!selectedOrder.value) return false;

    // Jika menggunakan GoSend dan status shiping, langsung bisa review
    if (
        selectedOrder.value.shipping_method === "GoSend" &&
        selectedOrder.value.status === "shiping"
    ) {
        return true;
    }

    // Untuk kurir lain, cek status tracking
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
    // Filter reviews for this specific order
    const orderReviews = userReviews.value.filter(
        (review) => review.order_id === order.id
    );
    showReviewModal.value = true;
};

const closeReviewModal = () => {
    showReviewModal.value = false;
    selectedReviewOrder.value = null;
};

// Add methods for payment proof handling
const openPaymentProofModal = (order) => {
    selectedPaymentOrder.value = order;
    showPaymentProofModal.value = true;
};

const closePaymentProofModal = () => {
    showPaymentProofModal.value = false;
    selectedPaymentOrder.value = null;
    paymentProofForm.reset();
};

const handlePaymentProofUpload = (e) => {
    paymentProofForm.payment_proof = e.target.files[0];
};

const submitPaymentProof = () => {
    paymentProofForm.order_id = selectedPaymentOrder.value.id;
    paymentProofForm.post(route("orders.upload-proof"), {
        preserveScroll: true,
        onSuccess: () => {
            closePaymentProofModal();
            // Optionally refresh orders
            window.location.reload();
        },
    });
};

// Add form for wishlist operations
const wishlistForm = useForm({});

// Add function to remove from wishlist
const removeFromWishlist = (id) => {
    if (
        confirm("Are you sure you want to remove this item from your wishlist?")
    ) {
        wishlistForm.delete(route("wishlist.destroy", id), {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: Add success notification
            },
        });
    }
};
</script>

<template>
    <Head title="Profile" />

    <CustomersLayout
        ><Hero title="Profile" breadcrumb="Home > Profile" />
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
                <!-- Use tab components -->
                <OrderHistoryTab
                    v-if="selectedAction === 'history'"
                    :orders="orders"
                    @view-order="openOrderModal"
                    @upload-payment="openPaymentProofModal"
                />

                <WishlistTab
                    v-else-if="selectedAction === 'wishlist'"
                    :wishlist-items="wishlistItems"
                    @remove-from-wishlist="removeFromWishlist"
                />

                <ReviewsTab
                    v-else-if="selectedAction === 'reviews'"
                    :orders="reviewedOrders"
                    :reviews="userReviews"
                    @view-reviews="openReviewModal"
                />

                <SettingsTab
                    v-else-if="selectedAction === 'settings'"
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
            </div>
        </section>
    </CustomersLayout>

    <!-- Payment Proof Modal -->
    <div
        v-if="showPaymentProofModal"
        class="fixed inset-0 z-50 overflow-y-auto"
    >
        <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
        ></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-lg max-w-md w-full shadow-xl">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold">
                            Upload Bukti Pembayaran
                        </h3>
                        <button
                            @click="closePaymentProofModal"
                            class="text-gray-400 hover:text-gray-500"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <form @submit.prevent="submitPaymentProof" class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Order #{{ selectedPaymentOrder?.id }}
                            </label>
                            <div class="mt-2">
                                <input
                                    type="file"
                                    accept="image/*"
                                    @input="handlePaymentProofUpload"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"
                                    required
                                />
                            </div>
                            <div
                                v-if="paymentProofForm.errors.payment_proof"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ paymentProofForm.errors.payment_proof }}
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button
                                type="button"
                                @click="closePaymentProofModal"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600"
                                :disabled="paymentProofForm.processing"
                            >
                                {{
                                    paymentProofForm.processing
                                        ? "Uploading..."
                                        : "Upload"
                                }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

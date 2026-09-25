<script setup>
import { ref, computed } from "vue";
import { router, useForm, usePage, Link } from "@inertiajs/vue3";
import { triggerSnapPayment } from "@/Utils/midtrans";
import axios from "axios";
import {
    Package,
    ShoppingBag,
    Search,
    Truck,
    Clock,
    CreditCard,
    ChevronDown,
    ChevronUp,
    ExternalLink,
    MapPin,
    AlertCircle,
    CheckCircle2,
    X,
    Star,
    Loader2
} from "lucide-vue-next";

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const searchQuery = ref("");
const selectedFilter = ref("all"); // 'all' | 'pending' | 'processing' | 'shiping' | 'completed' | 'cancelled'
const payingOrderId = ref(null);
const expandedOrders = ref(new Set());

// Order Details Modal state
const showOrderModal = ref(false);
const selectedOrder = ref(null);
const isLoadingTracking = ref(false);
const trackingInfo = ref(null);
const trackingError = ref(null);

const reviewForm = ref({
    order_id: null,
    reviews: [],
});

const completeForm = useForm({
    order_id: null,
    reviews: [],
});

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price || 0);
};

const formatDate = (dateStr) => {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit"
    });
};

const getProductImage = (product) => {
    if (product?.images && Array.isArray(product.images) && product.images.length > 0) {
        return `/storage/products/${product.images[0]}`;
    }
    if (typeof product?.images === "string") {
        try {
            const parsed = JSON.parse(product.images);
            if (Array.isArray(parsed) && parsed.length > 0) {
                return `/storage/products/${parsed[0]}`;
            }
        } catch {
            return `/storage/products/${product.images}`;
        }
    }
    return "/img/products/default.jpg";
};

// Status labels & badges
const getStatusBadge = (status) => {
    switch (status) {
        case "processing":
            return {
                label: "Sedang Diproses",
                class: "bg-blue-50 text-blue-700 border-blue-200"
            };
        case "shiping":
            return {
                label: "Dalam Pengiriman",
                class: "bg-purple-50 text-purple-700 border-purple-200"
            };
        case "completed":
            return {
                label: "Selesai",
                class: "bg-emerald-50 text-emerald-700 border-emerald-200"
            };
        case "cancelled":
            return {
                label: "Dibatalkan",
                class: "bg-red-50 text-red-700 border-red-200"
            };
        default:
            return {
                label: status,
                class: "bg-gray-50 text-gray-700 border-gray-200"
            };
    }
};

const getPaymentStatusBadge = (status) => {
    switch (status) {
        case "pending":
            return {
                label: "Menunggu Pembayaran",
                class: "bg-amber-50 text-amber-800 border-amber-200"
            };
        case "paid":
            return {
                label: "Sudah Dibayar",
                class: "bg-emerald-50 text-emerald-700 border-emerald-200"
            };
        case "failed":
            return {
                label: "Pembayaran Gagal",
                class: "bg-rose-50 text-rose-700 border-rose-200"
            };
        case "expired":
            return {
                label: "Kedaluwarsa",
                class: "bg-stone-100 text-stone-700 border-stone-200"
            };
        case "refunded":
            return {
                label: "Dikembalikan",
                class: "bg-blue-50 text-blue-700 border-blue-200"
            };
        default:
            return {
                label: status,
                class: "bg-gray-50 text-gray-700 border-gray-200"
            };
    }
};

// Filter tab counts
const filterCounts = computed(() => {
    const list = props.orders || [];
    return {
        all: list.length,
        pending: list.filter((o) => o.payment_status === "pending").length,
        processing: list.filter((o) => o.status === "processing" && o.payment_status !== "pending").length,
        shiping: list.filter((o) => o.status === "shiping").length,
        completed: list.filter((o) => o.status === "completed").length,
        cancelled: list.filter((o) => o.status === "cancelled").length,
    };
});

// Filtered and searched orders
const filteredOrders = computed(() => {
    let list = props.orders || [];

    // Filter by tab
    if (selectedFilter.value === "pending") {
        list = list.filter((o) => o.payment_status === "pending");
    } else if (selectedFilter.value === "processing") {
        list = list.filter((o) => o.status === "processing" && o.payment_status !== "pending");
    } else if (selectedFilter.value === "shiping") {
        list = list.filter((o) => o.status === "shiping");
    } else if (selectedFilter.value === "completed") {
        list = list.filter((o) => o.status === "completed");
    } else if (selectedFilter.value === "cancelled") {
        list = list.filter((o) => o.status === "cancelled");
    }

    // Filter by search query (Order ID or Product Name)
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        list = list.filter((o) => {
            const matchesId = String(o.id).includes(query);
            const matchesProduct = o.order_items?.some((item) =>
                item.product?.name?.toLowerCase().includes(query)
            );
            return matchesId || matchesProduct;
        });
    }

    return list;
});

const toggleOrderExpand = (orderId) => {
    if (expandedOrders.value.has(orderId)) {
        expandedOrders.value.delete(orderId);
    } else {
        expandedOrders.value.add(orderId);
    }
};

// Midtrans payment
const payOrder = async (order) => {
    payingOrderId.value = order.id;

    try {
        const response = await axios.get(route("customer.orders.payment-token", order.id));
        const snapToken = response.data.snap_token;
        const midtrans = page.props.midtrans || {};

        triggerSnapPayment({
            snapToken,
            snapJsUrl: midtrans.snapJsUrl,
            clientKey: midtrans.clientKey,
            onSuccess: () => {
                router.reload({ preserveScroll: true });
            },
            onPending: () => {
                router.reload({ preserveScroll: true });
            },
            onError: () => {
                alert("Pembayaran gagal atau dibatalkan.");
            },
            onClose: () => {
                payingOrderId.value = null;
            },
        });
    } catch (err) {
        console.error("Gagal mendapatkan token pembayaran:", err);
        alert(err.response?.data?.message || "Gagal memproses pembayaran. Silakan coba lagi.");
    } finally {
        payingOrderId.value = null;
    }
};

// Open order details modal
const openOrderModal = (order) => {
    selectedOrder.value = order;
    showOrderModal.value = true;
    trackingInfo.value = null;
    trackingError.value = null;

    // Initialize reviews for modal
    reviewForm.value = {
        order_id: order.id,
        reviews: (order.order_items || []).map((item) => ({
            product_id: item.product_id,
            rating: 0,
        })),
    };

    // Auto fetch tracking if available
    if (order.tracking_number && order.shipping_method !== "GoSend") {
        fetchTrackingInfo(order.tracking_number);
    }
};

const closeOrderModal = () => {
    showOrderModal.value = false;
    selectedOrder.value = null;
    trackingInfo.value = null;
    trackingError.value = null;
    reviewForm.value = { order_id: null, reviews: [] };
};

const fetchTrackingInfo = (trackingNumber) => {
    isLoadingTracking.value = true;
    trackingError.value = null;

    router.get(
        route("customer.orders.tracking", trackingNumber),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            only: ["trackingData"],
            onSuccess: (page) => {
                const data = page.props.trackingData;
                if (data) {
                    if (data.status === 200 || data.data) {
                        trackingInfo.value = data.data || data;
                    } else {
                        trackingError.value = data.message || "Gagal memuat status pengiriman";
                    }
                } else {
                    trackingError.value = "Informasi pengiriman tidak tersedia";
                }
                isLoadingTracking.value = false;
            },
            onError: () => {
                trackingError.value = "Terjadi kesalahan saat memuat pelacakan paket";
                isLoadingTracking.value = false;
            },
        }
    );
};

const isDelivered = computed(() => {
    if (!selectedOrder.value) return false;
    if (
        selectedOrder.value.shipping_method === "GoSend" &&
        selectedOrder.value.status === "shiping"
    ) {
        return true;
    }
    if (selectedOrder.value.status === "completed") {
        return true;
    }
    return trackingInfo.value?.summary?.status?.toLowerCase() === "delivered";
});

const allProductsReviewed = computed(() => {
    if (!selectedOrder.value || !reviewForm.value.reviews?.length) return false;
    return selectedOrder.value.order_items.every((item) =>
        reviewForm.value.reviews.some(
            (review) => review.product_id === item.product_id && review.rating > 0
        )
    );
});

const setRating = (productId, rating) => {
    const existing = reviewForm.value.reviews.find((r) => r.product_id === productId);
    if (existing) {
        existing.rating = rating;
    } else {
        reviewForm.value.reviews.push({ product_id: productId, rating });
    }
};

const completeOrder = () => {
    if (!allProductsReviewed.value) {
        alert("Mohon berikan rating bintang untuk seluruh produk sebelum menyelesaikan pesanan.");
        return;
    }

    completeForm.order_id = selectedOrder.value.id;
    completeForm.reviews = reviewForm.value.reviews;

    completeForm.post(route("customer.orders.complete"), {
        preserveScroll: true,
        onSuccess: () => {
            closeOrderModal();
            router.reload({ preserveScroll: true });
        },
        onError: () => {
            alert("Gagal menyelesaikan pesanan. Silakan coba lagi.");
        },
    });
};

defineExpose({
    openOrderModal,
});
</script>

<template>
    <div class="space-y-6">
        <!-- Header & Search -->
        <div class="border-b border-gray-100 pb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Riwayat Belanja</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Pantau status pesanan, pembayaran, dan informasi pengiriman paket Anda.
                </p>
            </div>

            <!-- Search box -->
            <div class="relative w-full md:w-64">
                <Search :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Cari ID atau nama produk..."
                    class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-gray-200 bg-white placeholder-gray-400 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition"
                />
            </div>
        </div>

        <!-- Filter tabs bar -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
            <button
                type="button"
                @click="selectedFilter = 'all'"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5"
                :class="[
                    selectedFilter === 'all'
                        ? 'bg-orange-500 text-white shadow-sm'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                ]"
            >
                Semua
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="selectedFilter === 'all' ? 'bg-white/20 text-white' : 'bg-white text-gray-600'">
                    {{ filterCounts.all }}
                </span>
            </button>

            <button
                type="button"
                @click="selectedFilter = 'pending'"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5"
                :class="[
                    selectedFilter === 'pending'
                        ? 'bg-orange-500 text-white shadow-sm'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                ]"
            >
                Menunggu Pembayaran
                <span v-if="filterCounts.pending > 0" class="px-1.5 py-0.2 rounded-full text-[10px]" :class="selectedFilter === 'pending' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">
                    {{ filterCounts.pending }}
                </span>
            </button>

            <button
                type="button"
                @click="selectedFilter = 'processing'"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5"
                :class="[
                    selectedFilter === 'processing'
                        ? 'bg-orange-500 text-white shadow-sm'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                ]"
            >
                Diproses
                <span v-if="filterCounts.processing > 0" class="px-1.5 py-0.2 rounded-full text-[10px]" :class="selectedFilter === 'processing' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800'">
                    {{ filterCounts.processing }}
                </span>
            </button>

            <button
                type="button"
                @click="selectedFilter = 'shiping'"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5"
                :class="[
                    selectedFilter === 'shiping'
                        ? 'bg-orange-500 text-white shadow-sm'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                ]"
            >
                Dikirim
                <span v-if="filterCounts.shiping > 0" class="px-1.5 py-0.2 rounded-full text-[10px]" :class="selectedFilter === 'shiping' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-800'">
                    {{ filterCounts.shiping }}
                </span>
            </button>

            <button
                type="button"
                @click="selectedFilter = 'completed'"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5"
                :class="[
                    selectedFilter === 'completed'
                        ? 'bg-orange-500 text-white shadow-sm'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                ]"
            >
                Selesai
                <span v-if="filterCounts.completed > 0" class="px-1.5 py-0.2 rounded-full text-[10px]" :class="selectedFilter === 'completed' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800'">
                    {{ filterCounts.completed }}
                </span>
            </button>

            <button
                type="button"
                @click="selectedFilter = 'cancelled'"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5"
                :class="[
                    selectedFilter === 'cancelled'
                        ? 'bg-orange-500 text-white shadow-sm'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                ]"
            >
                Dibatalkan
                <span v-if="filterCounts.cancelled > 0" class="px-1.5 py-0.2 rounded-full text-[10px]" :class="selectedFilter === 'cancelled' ? 'bg-white/20 text-white' : 'bg-red-100 text-red-800'">
                    {{ filterCounts.cancelled }}
                </span>
            </button>
        </div>

        <!-- Orders list -->
        <div v-if="filteredOrders.length > 0" class="space-y-4">
            <div
                v-for="order in filteredOrders"
                :key="order.id"
                class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6 shadow-sm hover:shadow-md transition-shadow"
            >
                <!-- Card Header -->
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="text-sm font-bold text-gray-900">Pesanan #{{ order.id }}</span>
                        <span class="text-gray-300">•</span>
                        <span class="text-xs text-gray-500">{{ formatDate(order.created_at) }}</span>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <span
                            class="px-2.5 py-1 text-xs font-medium rounded-full border"
                            :class="getPaymentStatusBadge(order.payment_status).class"
                        >
                            {{ getPaymentStatusBadge(order.payment_status).label }}
                        </span>
                        <span
                            class="px-2.5 py-1 text-xs font-medium rounded-full border"
                            :class="getStatusBadge(order.status).class"
                        >
                            {{ getStatusBadge(order.status).label }}
                        </span>
                    </div>
                </div>

                <!-- Items Preview -->
                <div class="py-4 space-y-3">
                    <template v-for="(item, idx) in (expandedOrders.has(order.id) ? order.order_items : (order.order_items || []).slice(0, 1))" :key="item.id || idx">
                        <div class="flex items-center gap-4">
                            <img
                                :src="getProductImage(item.product)"
                                :alt="item.product?.name || 'Produk'"
                                class="w-16 h-16 object-cover rounded-xl border border-gray-100 bg-gray-50 shrink-0"
                            />
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-900 text-sm truncate">
                                    {{ item.product?.name || "Produk" }}
                                </h4>
                                <div class="flex items-center gap-2 text-xs text-gray-500 mt-1 flex-wrap">
                                    <span v-if="item.size" class="px-2 py-0.5 bg-gray-100 rounded text-gray-700">
                                        Ukuran: {{ item.size }}
                                    </span>
                                    <span v-if="item.color" class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 rounded text-gray-700">
                                        Warna:
                                        <span class="w-2.5 h-2.5 rounded-full border border-gray-300 inline-block" :style="{ backgroundColor: item.color }"></span>
                                    </span>
                                    <span>x{{ item.quantity }}</span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ formatPrice(item.total_price || (item.price * item.quantity)) }}
                                </p>
                            </div>
                        </div>
                    </template>

                    <!-- Expand/Collapse items toggle if more than 1 item -->
                    <button
                        v-if="(order.order_items?.length || 0) > 1"
                        type="button"
                        @click="toggleOrderExpand(order.id)"
                        class="text-xs font-semibold text-orange-600 hover:text-orange-700 inline-flex items-center gap-1 pt-1"
                    >
                        <template v-if="expandedOrders.has(order.id)">
                            <span>Sembunyikan produk lainnya</span>
                            <ChevronUp :size="14" />
                        </template>
                        <template v-else>
                            <span>+ {{ order.order_items.length - 1 }} produk lainnya</span>
                            <ChevronDown :size="14" />
                        </template>
                    </button>
                </div>

                <!-- Card Footer with Total & CTAs -->
                <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="text-xs text-gray-500 block">Total Pembayaran</span>
                        <span class="text-base font-bold text-gray-900">{{ formatPrice(order.total_price) }}</span>
                    </div>

                    <div class="flex items-center gap-2.5 flex-wrap">
                        <!-- Pay Now button if pending -->
                        <button
                            v-if="order.payment_status === 'pending'"
                            type="button"
                            @click="payOrder(order)"
                            :disabled="payingOrderId === order.id"
                            class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-xs font-semibold shadow-sm transition disabled:opacity-50 inline-flex items-center gap-1.5"
                        >
                            <Loader2 v-if="payingOrderId === order.id" :size="14" class="animate-spin" />
                            <CreditCard v-else :size="14" />
                            <span>{{ payingOrderId === order.id ? 'Memuat...' : 'Bayar Sekarang' }}</span>
                        </button>

                        <!-- Tracking shortcut -->
                        <button
                            v-if="order.tracking_number && order.shipping_method !== 'GoSend'"
                            type="button"
                            @click="openOrderModal(order)"
                            class="px-3.5 py-2 border border-gray-200 text-gray-700 hover:border-orange-500 hover:text-orange-600 rounded-xl text-xs font-medium transition inline-flex items-center gap-1.5"
                        >
                            <Truck :size="14" />
                            <span>Lacak Paket</span>
                        </button>

                        <!-- View Details button -->
                        <button
                            type="button"
                            @click="openOrderModal(order)"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl text-xs font-semibold transition"
                        >
                            Lihat Detail
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else
            class="bg-white rounded-2xl border border-gray-100 p-12 text-center shadow-sm"
        >
            <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <ShoppingBag :size="32" />
            </div>
            <h3 class="text-base font-semibold text-gray-900">Tidak Ada Pesanan Ditemukan</h3>
            <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">
                <template v-if="searchQuery">
                    Tidak ada pesanan yang sesuai dengan kata kunci "{{ searchQuery }}". Coba gunakan kata kunci lain.
                </template>
                <template v-else-if="selectedFilter !== 'all'">
                    Tidak ada pesanan dengan status yang dipilih saat ini.
                </template>
                <template v-else>
                    Anda belum memiliki riwayat pesanan. Mulai jelajahi katalog kami dan temukan produk favorit Anda!
                </template>
            </p>
            <div class="mt-6">
                <Link
                    href="/boquets"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-xl transition shadow-sm"
                >
                    <ShoppingBag :size="15" />
                    <span>Mulai Belanja</span>
                </Link>
            </div>
        </div>

        <!-- Order Details Modal -->
        <div v-if="showOrderModal && selectedOrder" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="closeOrderModal"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden border border-gray-100 animate-in fade-in zoom-in-95 duration-200">
                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">
                                Detail Pesanan #{{ selectedOrder.id }}
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ formatDate(selectedOrder.created_at) }}
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="closeOrderModal"
                            class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition"
                            aria-label="Tutup"
                        >
                            <X :size="20" />
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 max-h-[75vh] overflow-y-auto space-y-6">
                        <!-- Status row -->
                        <div class="flex items-center justify-between p-4 bg-orange-50/60 rounded-xl border border-orange-100">
                            <div>
                                <span class="text-xs font-medium text-orange-900 block">Status Pesanan:</span>
                                <span class="text-sm font-bold text-orange-950 mt-0.5 block">
                                    {{ getStatusBadge(selectedOrder.status).label }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-medium text-orange-900 block">Status Pembayaran:</span>
                                <span
                                    class="inline-block mt-0.5 px-2.5 py-0.5 text-xs font-semibold rounded-full border"
                                    :class="getPaymentStatusBadge(selectedOrder.payment_status).class"
                                >
                                    {{ getPaymentStatusBadge(selectedOrder.payment_status).label }}
                                </span>
                            </div>
                        </div>

                        <!-- Pay now CTA inside modal if pending -->
                        <div v-if="selectedOrder.payment_status === 'pending'" class="p-4 bg-amber-50 rounded-xl border border-amber-200 flex items-center justify-between gap-3">
                            <div class="text-xs text-amber-900">
                                <p class="font-bold">Pembayaran Belum Diselesaikan</p>
                                <p class="mt-0.5 text-amber-800">Selesaikan pembayaran agar pesanan dapat segera diproses.</p>
                            </div>
                            <button
                                type="button"
                                @click="payOrder(selectedOrder)"
                                :disabled="payingOrderId === selectedOrder.id"
                                class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-xs font-semibold shadow-sm transition shrink-0"
                            >
                                {{ payingOrderId === selectedOrder.id ? 'Memuat...' : 'Bayar via Midtrans' }}
                            </button>
                        </div>

                        <!-- Shipping & Courier Info -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">Pengiriman</span>
                                <p class="text-sm font-bold text-gray-900">{{ selectedOrder.shipping_method || "Kurir Reguler" }}</p>
                                <p v-if="selectedOrder.tracking_number" class="text-xs text-gray-600 mt-1">
                                    No. Resi: <span class="font-mono font-medium text-gray-900">{{ selectedOrder.tracking_number }}</span>
                                </p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">Metode Pembayaran</span>
                                <p class="text-sm font-bold text-gray-900">{{ selectedOrder.payment_method || "Midtrans" }}</p>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                <MapPin :size="14" />
                                <span>Alamat Penerima</span>
                            </div>
                            <p class="text-sm font-bold text-gray-900">{{ selectedOrder.name }}</p>
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                {{ selectedOrder.address }}, {{ selectedOrder.village ? selectedOrder.village + ', ' : '' }}{{ selectedOrder.district }}, {{ selectedOrder.city }}, {{ selectedOrder.province }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Telepon: {{ selectedOrder.phone }}</p>
                        </div>

                        <!-- Order Items List -->
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Daftar Produk</h4>
                            <div class="space-y-3">
                                <div
                                    v-for="item in selectedOrder.order_items"
                                    :key="item.id"
                                    class="flex items-center gap-4 p-3 bg-white rounded-xl border border-gray-100"
                                >
                                    <img
                                        :src="getProductImage(item.product)"
                                        :alt="item.product?.name || 'Produk'"
                                        class="w-14 h-14 object-cover rounded-lg border border-gray-100 bg-gray-50 shrink-0"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <h5 class="font-semibold text-gray-900 text-sm truncate">
                                            {{ item.product?.name || "Produk" }}
                                        </h5>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            <span v-if="item.size">Ukuran: {{ item.size }}</span>
                                            <span v-if="item.color" class="ml-2">Warna: {{ item.color }}</span>
                                            <span class="ml-2">{{ item.quantity }} pcs</span>
                                        </p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-xs text-gray-500">{{ formatPrice(item.price) }}</p>
                                        <p class="text-sm font-bold text-gray-900">{{ formatPrice(item.total_price) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Live Tracking Info (if applicable) -->
                        <div v-if="selectedOrder.tracking_number && selectedOrder.shipping_method !== 'GoSend'" class="border border-gray-100 rounded-xl p-4 bg-gray-50/50">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                                    <Truck :size="14" />
                                    <span>Status Pengiriman Kurir</span>
                                </h4>
                                <button
                                    type="button"
                                    @click="fetchTrackingInfo(selectedOrder.tracking_number)"
                                    class="text-xs font-medium text-orange-600 hover:text-orange-700"
                                >
                                    Perbarui
                                </button>
                            </div>

                            <div v-if="isLoadingTracking" class="py-6 text-center text-gray-500 text-xs flex items-center justify-center gap-2">
                                <Loader2 :size="16" class="animate-spin text-orange-500" />
                                <span>Memuat informasi pelacakan...</span>
                            </div>

                            <div v-else-if="trackingInfo" class="space-y-3">
                                <div class="p-3 bg-white rounded-lg border border-gray-100 flex items-center justify-between text-xs">
                                    <span class="text-gray-500">Status Terakhir:</span>
                                    <span class="font-bold text-gray-900">{{ trackingInfo.summary?.status || "Dalam Perjalanan" }}</span>
                                </div>

                                <div v-if="trackingInfo.history?.length" class="space-y-3 pt-2">
                                    <div
                                        v-for="(history, index) in trackingInfo.history"
                                        :key="index"
                                        class="border-l-2 border-orange-200 pl-4 pb-3 relative"
                                    >
                                        <div class="absolute w-2.5 h-2.5 bg-orange-500 rounded-full -left-[6px] top-1"></div>
                                        <p class="text-xs font-bold text-gray-900">{{ history.date }}</p>
                                        <p class="text-xs text-gray-700 mt-0.5">{{ history.desc }}</p>
                                        <p v-if="history.location" class="text-[11px] text-gray-400 mt-0.5">{{ history.location }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="trackingError" class="text-xs text-amber-700 bg-amber-50 p-3 rounded-lg border border-amber-200">
                                {{ trackingError }}
                            </div>
                        </div>

                        <!-- Review & Complete Order Section (If delivered or completed) -->
                        <div v-if="isDelivered && selectedOrder.status !== 'completed'" class="p-5 bg-emerald-50/50 rounded-xl border border-emerald-100 space-y-4">
                            <div>
                                <h4 class="text-sm font-bold text-emerald-950 flex items-center gap-1.5">
                                    <Star :size="16" class="fill-amber-400 text-amber-400" />
                                    <span>Beri Ulasan & Selesaikan Pesanan</span>
                                </h4>
                                <p class="text-xs text-emerald-800 mt-0.5">
                                    Paket telah terkirim! Silakan berikan penilaian bintang untuk produk yang Anda beli.
                                </p>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="item in selectedOrder.order_items"
                                    :key="item.id"
                                    class="bg-white p-3.5 rounded-xl border border-emerald-100 flex items-center justify-between gap-3 flex-wrap"
                                >
                                    <div class="flex items-center gap-3">
                                        <img
                                            :src="getProductImage(item.product)"
                                            :alt="item.product?.name || 'Produk'"
                                            class="w-10 h-10 object-cover rounded-lg border border-gray-100"
                                        />
                                        <span class="text-xs font-semibold text-gray-900 truncate max-w-[180px] sm:max-w-xs">
                                            {{ item.product?.name }}
                                        </span>
                                    </div>

                                    <!-- Star rating input -->
                                    <div class="flex items-center gap-1">
                                        <button
                                            v-for="star in 5"
                                            :key="star"
                                            type="button"
                                            @click="setRating(item.product_id, star)"
                                            class="p-1 focus:outline-none transition-transform hover:scale-110"
                                        >
                                            <Star
                                                :size="20"
                                                :class="[
                                                    (reviewForm.reviews.find((r) => r.product_id === item.product_id)?.rating || 0) >= star
                                                        ? 'fill-amber-400 text-amber-400'
                                                        : 'text-gray-300 fill-gray-100',
                                                ]"
                                            />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button
                                    type="button"
                                    @click="completeOrder"
                                    :disabled="!allProductsReviewed || completeForm.processing"
                                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                >
                                    <Loader2 v-if="completeForm.processing" :size="14" class="animate-spin" />
                                    <span>Selesaikan Pesanan & Kirim Ulasan</span>
                                </button>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="border-t border-gray-100 pt-4 space-y-2">
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>Total Pembayaran</span>
                                <span class="text-sm font-bold text-gray-900">{{ formatPrice(selectedOrder.total_price) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

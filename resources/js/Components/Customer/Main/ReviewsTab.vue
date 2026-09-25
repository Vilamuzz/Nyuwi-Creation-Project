<script setup>
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";
import { Star, MessageSquareQuote, CheckCircle2, PackageCheck, ShoppingBag, ArrowRight } from "lucide-vue-next";

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
    reviews: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["viewReviews", "reviewOrder"]);

const activeSubTab = ref("reviews"); // 'reviews' | 'pending'

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

const formatDate = (dateStr) => {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price || 0);
};

// Check reviewed order items
const completedOrders = computed(() => {
    return props.orders?.filter((order) => order.status === "completed") || [];
});

// Completed orders that have items not yet reviewed
const ordersAwaitingReview = computed(() => {
    return completedOrders.value.filter((order) => {
        const orderReviewProductIds = new Set(
            props.reviews
                .filter((r) => r.order_id === order.id)
                .map((r) => r.product_id)
        );
        // If any item hasn't been reviewed
        return order.order_items?.some(
            (item) => !orderReviewProductIds.has(item.product_id)
        );
    });
});
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="border-b border-gray-100 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Ulasan Belanja</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Lihat ulasan produk yang telah Anda berikan atau berikan ulasan pesanan selesai.
                </p>
            </div>

            <!-- Subtab pills -->
            <div class="flex items-center gap-1.5 p-1 bg-gray-100/80 rounded-xl self-start sm:self-auto">
                <button
                    type="button"
                    @click="activeSubTab = 'reviews'"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150"
                    :class="[
                        activeSubTab === 'reviews'
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-500 hover:text-gray-900',
                    ]"
                >
                    Ulasan Saya ({{ reviews.length }})
                </button>
                <button
                    type="button"
                    @click="activeSubTab = 'pending'"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150 flex items-center gap-1"
                    :class="[
                        activeSubTab === 'pending'
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-500 hover:text-gray-900',
                    ]"
                >
                    Menunggu Diulas
                    <span
                        v-if="ordersAwaitingReview.length > 0"
                        class="px-1.5 py-0.2 bg-orange-500 text-white text-[10px] rounded-full"
                    >
                        {{ ordersAwaitingReview.length }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Tab 1: Submitted Reviews -->
        <div v-if="activeSubTab === 'reviews'">
            <div v-if="reviews.length > 0" class="grid gap-4 sm:grid-cols-2">
                <div
                    v-for="review in reviews"
                    :key="review.id"
                    class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between"
                >
                    <div>
                        <!-- Product Info -->
                        <div class="flex items-start gap-4">
                            <img
                                :src="getProductImage(review.product)"
                                :alt="review.product?.name || 'Produk'"
                                class="w-16 h-16 object-cover rounded-xl border border-gray-100 shrink-0 bg-gray-50"
                            />
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-900 text-sm truncate">
                                    {{ review.product?.name || "Produk Tidak Ditemukan" }}
                                </h4>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Diulas pada {{ formatDate(review.created_at) }}
                                </p>

                                <!-- Star rating -->
                                <div class="flex items-center gap-1 mt-2">
                                    <template v-for="star in 5" :key="star">
                                        <Star
                                            :size="16"
                                            :class="[
                                                star <= review.rating
                                                    ? 'fill-amber-400 text-amber-400'
                                                    : 'text-gray-200 fill-gray-200',
                                            ]"
                                        />
                                    </template>
                                    <span class="text-xs font-bold text-gray-700 ml-1.5">
                                        {{ review.rating }} / 5
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Feedback Note or Order Reference -->
                        <div v-if="review.order_id" class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                            <span>Pesanan #{{ review.order_id }}</span>
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-medium">
                                <CheckCircle2 :size="13" /> Terverifikasi Pembeli
                            </span>
                        </div>
                    </div>

                    <div v-if="review.product?.slug" class="mt-4 pt-3">
                        <Link
                            :href="`/product/${review.product.slug}`"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-orange-600 hover:text-orange-700 transition"
                        >
                            Lihat Halaman Produk <ArrowRight :size="13" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State for Reviews -->
            <div
                v-else
                class="bg-white rounded-2xl border border-gray-100 p-12 text-center shadow-sm"
            >
                <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <MessageSquareQuote :size="32" />
                </div>
                <h3 class="text-base font-semibold text-gray-900">Belum Ada Ulasan</h3>
                <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">
                    Anda belum memberikan ulasan untuk pesanan apapun. Selesaikan pesanan untuk memberikan rating produk!
                </p>
                <button
                    v-if="ordersAwaitingReview.length > 0"
                    @click="activeSubTab = 'pending'"
                    class="mt-5 inline-flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-xl transition shadow-sm"
                >
                    Lihat Pesanan Siap Diulas
                </button>
            </div>
        </div>

        <!-- Tab 2: Orders Awaiting Review -->
        <div v-else-if="activeSubTab === 'pending'">
            <div v-if="ordersAwaitingReview.length > 0" class="space-y-4">
                <div
                    v-for="order in ordersAwaitingReview"
                    :key="order.id"
                    class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                >
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold text-gray-900">Pesanan #{{ order.id }}</span>
                            <span class="text-xs text-gray-400">•</span>
                            <span class="text-xs text-gray-500">{{ formatDate(order.created_at) }}</span>
                            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Selesai
                            </span>
                        </div>

                        <!-- Item thumbnails -->
                        <div class="flex items-center gap-2 pt-1">
                            <template
                                v-for="(item, idx) in (order.order_items || []).slice(0, 3)"
                                :key="item.id || idx"
                            >
                                <img
                                    :src="getProductImage(item.product)"
                                    :alt="item.product?.name || 'Produk'"
                                    class="w-12 h-12 object-cover rounded-lg border border-gray-100 bg-gray-50"
                                />
                            </template>
                            <span
                                v-if="(order.order_items?.length || 0) > 3"
                                class="text-xs text-gray-500 pl-1 font-medium"
                            >
                                +{{ order.order_items.length - 3 }} produk
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center sm:flex-col sm:items-end justify-between gap-2 shrink-0">
                        <p class="text-xs text-gray-500">
                            Total: <span class="font-bold text-gray-900">{{ formatPrice(order.total_price) }}</span>
                        </p>
                        <button
                            type="button"
                            @click="emit('reviewOrder', order)"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-xl transition shadow-sm"
                        >
                            <Star :size="14" class="fill-white" />
                            Beri Ulasan Sekarang
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State for Pending Reviews -->
            <div
                v-else
                class="bg-white rounded-2xl border border-gray-100 p-12 text-center shadow-sm"
            >
                <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <PackageCheck :size="32" />
                </div>
                <h3 class="text-base font-semibold text-gray-900">Semua Pesanan Selesai Sudah Diulas</h3>
                <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">
                    Terima kasih atas ulasan berharga Anda! Ulasan Anda membantu kami meningkatkan kualitas produk dan layanan.
                </p>
            </div>
        </div>
    </div>
</template>

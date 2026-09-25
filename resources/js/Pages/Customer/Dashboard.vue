<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import { ref, computed, nextTick } from "vue";
import OrderHistoryTab from "@/Components/Customer/Main/OrderHistoryTab.vue";
import ReviewsTab from "@/Components/Customer/Main/ReviewsTab.vue";
import SettingsTab from "@/Components/Customer/Main/SettingsTab.vue";
import ToastNotification from "@/Components/Customer/Sub-main/ToastNotification.vue";
import {
    ShoppingBag,
    Star,
    Settings,
    CreditCard,
    Truck,
    CheckCircle,
    User as UserIcon,
    Calendar,
    ChevronRight,
    LogOut
} from "lucide-vue-next";

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
    reviews: {
        type: Array,
        default: () => [],
    },
    mustVerifyEmail: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: "",
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const selectedAction = ref("history");
const orderHistoryRef = ref(null);

// Flash message toast
const toastMessage = ref("");
const toastType = ref("info");
const showToast = ref(false);

if (page.props.flash?.success) {
    toastMessage.value = page.props.flash.success;
    toastType.value = "success";
    showToast.value = true;
} else if (page.props.flash?.error) {
    toastMessage.value = page.props.flash.error;
    toastType.value = "error";
    showToast.value = true;
}

// User initials
const userInitials = computed(() => {
    if (!user.value?.name) return "U";
    return user.value.name
        .split(" ")
        .map((n) => n[0])
        .slice(0, 2)
        .join("")
        .toUpperCase();
});

// Member join date
const joinDate = computed(() => {
    if (!user.value?.created_at) return "-";
    return new Date(user.value.created_at).toLocaleDateString("id-ID", {
        month: "long",
        year: "numeric",
    });
});

// Quick Metrics
const stats = computed(() => {
    const list = props.orders || [];
    return {
        totalOrders: list.length,
        pendingPayment: list.filter((o) => o.payment_status === "pending").length,
        inDelivery: list.filter((o) => ["processing", "shiping"].includes(o.status)).length,
        reviewsCount: props.reviews?.length || 0,
    };
});

const navActions = [
    {
        id: "history",
        label: "Riwayat Belanja",
        description: "Status dan riwayat pesanan Anda",
        icon: ShoppingBag,
        badge: computed(() => props.orders?.length || 0),
    },
    {
        id: "reviews",
        label: "Ulasan Belanja",
        description: "Penilaian produk yang Anda beli",
        icon: Star,
        badge: computed(() => props.reviews?.length || 0),
    },
    {
        id: "settings",
        label: "Pengaturan Akun",
        description: "Kelola profil dan keamanan kata sandi",
        icon: Settings,
        badge: null,
    },
];

// Open order review directly from review tab
const handleReviewOrder = (order) => {
    selectedAction.value = "history";
    nextTick(() => {
        orderHistoryRef.value?.openOrderModal(order);
    });
};
</script>

<template>
    <Head title="Dashboard Pelanggan" />

    <CustomersLayout>
        <!-- Toast Notification -->
        <ToastNotification
            :show="showToast"
            :message="toastMessage"
            :type="toastType"
            @close="showToast = false"
        />

        <main class="min-h-screen bg-stone-50/60 pb-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
                <!-- User Profile Banner -->
                <div class="bg-gradient-to-br from-amber-50/80 via-orange-50/60 to-rose-50/40 rounded-3xl p-6 sm:p-8 border border-orange-100 shadow-xs mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                        <div class="flex items-center gap-4 sm:gap-6">
                            <!-- Avatar with Initials -->
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-orange-500 text-white font-bold text-xl sm:text-2xl flex items-center justify-center shadow-lg shadow-orange-500/25 shrink-0">
                                {{ userInitials }}
                            </div>

                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 truncate">
                                        {{ user?.name || "Pelanggan" }}
                                    </h1>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-800 border border-orange-200">
                                        <CheckCircle :size="12" /> Member
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-600 truncate">
                                    {{ user?.email }}
                                </p>
                                <div class="flex items-center gap-1.5 text-xs text-gray-500 pt-1">
                                    <Calendar :size="13" class="text-orange-500" />
                                    <span>Bergabung sejak {{ joinDate }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick action links -->
                        <div class="flex items-center gap-2 self-start sm:self-auto">
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-rose-600 bg-white hover:bg-rose-50 border border-rose-100 rounded-xl transition shadow-xs"
                            >
                                <LogOut :size="14" />
                                <span>Keluar</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Quick KPI Cards Grid -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-6 pt-6 border-t border-orange-100/80">
                        <!-- Total Orders -->
                        <div
                            @click="selectedAction = 'history'"
                            class="bg-white/80 backdrop-blur-xs rounded-2xl p-4 border border-orange-100/60 shadow-xs cursor-pointer hover:bg-white hover:shadow-sm transition"
                        >
                            <div class="flex items-center justify-between text-gray-500 mb-2">
                                <span class="text-xs font-medium">Total Pesanan</span>
                                <div class="p-2 bg-orange-50 text-orange-600 rounded-xl">
                                    <ShoppingBag :size="16" />
                                </div>
                            </div>
                            <span class="text-xl sm:text-2xl font-bold text-gray-900">{{ stats.totalOrders }}</span>
                        </div>

                        <!-- Pending Payment -->
                        <div
                            @click="selectedAction = 'history'"
                            class="bg-white/80 backdrop-blur-xs rounded-2xl p-4 border border-orange-100/60 shadow-xs cursor-pointer hover:bg-white hover:shadow-sm transition"
                        >
                            <div class="flex items-center justify-between text-gray-500 mb-2">
                                <span class="text-xs font-medium">Belum Dibayar</span>
                                <div class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                                    <CreditCard :size="16" />
                                </div>
                            </div>
                            <span class="text-xl sm:text-2xl font-bold text-amber-600">{{ stats.pendingPayment }}</span>
                        </div>

                        <!-- Active in Progress / Shipping -->
                        <div
                            @click="selectedAction = 'history'"
                            class="bg-white/80 backdrop-blur-xs rounded-2xl p-4 border border-orange-100/60 shadow-xs cursor-pointer hover:bg-white hover:shadow-sm transition"
                        >
                            <div class="flex items-center justify-between text-gray-500 mb-2">
                                <span class="text-xs font-medium">Dalam Pengiriman</span>
                                <div class="p-2 bg-purple-50 text-purple-600 rounded-xl">
                                    <Truck :size="16" />
                                </div>
                            </div>
                            <span class="text-xl sm:text-2xl font-bold text-purple-600">{{ stats.inDelivery }}</span>
                        </div>

                        <!-- Reviews Given -->
                        <div
                            @click="selectedAction = 'reviews'"
                            class="bg-white/80 backdrop-blur-xs rounded-2xl p-4 border border-orange-100/60 shadow-xs cursor-pointer hover:bg-white hover:shadow-sm transition"
                        >
                            <div class="flex items-center justify-between text-gray-500 mb-2">
                                <span class="text-xs font-medium">Ulasan Diberikan</span>
                                <div class="p-2 bg-yellow-50 text-yellow-600 rounded-xl">
                                    <Star :size="16" />
                                </div>
                            </div>
                            <span class="text-xl sm:text-2xl font-bold text-gray-900">{{ stats.reviewsCount }}</span>
                        </div>
                    </div>
                </div>

                <!-- Main Layout (Sidebar + Tab Content) -->
                <div class="flex flex-col lg:flex-row gap-8 items-start">
                    <!-- Navigation (Mobile Pills & Desktop Sidebar) -->
                    <div class="w-full lg:w-72 shrink-0">
                        <!-- Desktop Sidebar Menu -->
                        <div class="hidden lg:block bg-white rounded-3xl border border-gray-100 p-3 shadow-xs space-y-1 sticky top-24">
                            <button
                                v-for="action in navActions"
                                :key="action.id"
                                type="button"
                                @click="selectedAction = action.id"
                                class="w-full text-left p-3.5 rounded-2xl transition-all duration-150 flex items-center justify-between group"
                                :class="[
                                    selectedAction === action.id
                                        ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20'
                                        : 'text-gray-700 hover:bg-orange-50/70',
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="p-2 rounded-xl transition"
                                        :class="[
                                            selectedAction === action.id
                                                ? 'bg-white/20 text-white'
                                                : 'bg-gray-100 text-gray-600 group-hover:bg-orange-100 group-hover:text-orange-600',
                                        ]"
                                    >
                                        <component :is="action.icon" :size="18" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold">{{ action.label }}</p>
                                        <p
                                            class="text-[11px] truncate max-w-[130px]"
                                            :class="selectedAction === action.id ? 'text-white/80' : 'text-gray-400'"
                                        >
                                            {{ action.description }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <span
                                        v-if="action.badge?.value !== undefined && action.badge !== null"
                                        class="px-2 py-0.5 text-xs font-bold rounded-full"
                                        :class="[
                                            selectedAction === action.id
                                                ? 'bg-white/25 text-white'
                                                : 'bg-gray-100 text-gray-600',
                                        ]"
                                    >
                                        {{ action.badge.value }}
                                    </span>
                                    <ChevronRight
                                        :size="16"
                                        :class="selectedAction === action.id ? 'text-white' : 'text-gray-400 group-hover:text-gray-600'"
                                    />
                                </div>
                            </button>
                        </div>

                        <!-- Mobile Horizontal Scroll Tabs -->
                        <div class="lg:hidden flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none w-full">
                            <button
                                v-for="action in navActions"
                                :key="action.id"
                                type="button"
                                @click="selectedAction = action.id"
                                class="px-4 py-2.5 rounded-2xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 shrink-0 border"
                                :class="[
                                    selectedAction === action.id
                                        ? 'bg-orange-500 text-white border-orange-500 shadow-sm'
                                        : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50',
                                ]"
                            >
                                <component :is="action.icon" :size="15" />
                                <span>{{ action.label }}</span>
                                <span
                                    v-if="action.badge?.value !== undefined && action.badge !== null"
                                    class="px-1.5 py-0.2 rounded-full text-[10px]"
                                    :class="selectedAction === action.id ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-700'"
                                >
                                    {{ action.badge.value }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Main Tab Content Area -->
                    <div class="flex-1 w-full min-w-0">
                        <OrderHistoryTab
                            v-if="selectedAction === 'history'"
                            ref="orderHistoryRef"
                            :orders="orders"
                        />

                        <ReviewsTab
                            v-else-if="selectedAction === 'reviews'"
                            :orders="orders"
                            :reviews="reviews"
                            @review-order="handleReviewOrder"
                        />

                        <SettingsTab
                            v-else-if="selectedAction === 'settings'"
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                        />
                    </div>
                </div>
            </div>
        </main>
    </CustomersLayout>
</template>

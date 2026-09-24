<script setup>
import { ref, computed, watch } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Modal from "@/Components/Modal.vue";
import {
    Search,
    Filter,
    ArrowUpDown,
    SlidersHorizontal,
    Eye,
    ShoppingCart,
    Clock,
    AlertTriangle,
    CheckCircle2,
    XCircle,
    Truck,
    CreditCard,
    Wallet,
    Calendar,
    MapPin,
    User,
    ArrowRight,
    ChevronLeft,
    ChevronRight,
    RotateCcw,
    Receipt,
    X,
    Banknote,
    Package,
    FileText,
} from "lucide-vue-next";

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
});

// Search and filter states
const search = ref("");
const selectedStatus = ref("all");
const selectedPayment = ref("all");
const selectedSort = ref("newest");
const currentPage = ref(1);
const perPage = ref(10);

// Reset pagination when filters change
watch([search, selectedStatus, selectedPayment, selectedSort, perPage], () => {
    currentPage.value = 1;
});

// Format currency
const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price || 0);
};

// Format date & time
const formatDate = (dateString) => {
    if (!dateString) return "-";
    try {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat("id-ID", {
            day: "numeric",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        }).format(date);
    } catch {
        return dateString;
    }
};

// Customer initials
const getInitials = (name) => {
    if (!name) return "U";
    return name
        .split(" ")
        .filter(Boolean)
        .map((n) => n[0])
        .slice(0, 2)
        .join("")
        .toUpperCase();
};

// Order status configurations
const getStatusConfig = (status) => {
    switch (status) {
        case "waiting":
            return {
                label: "Menunggu Bayar",
                badgeClass: "bg-amber-50 text-amber-800 border-amber-200",
                dotClass: "bg-amber-500",
                icon: Clock,
            };
        case "checking":
            return {
                label: "Verifikasi Bukti",
                badgeClass: "bg-orange-50 text-orange-800 border-orange-200",
                dotClass: "bg-orange-500",
                icon: AlertTriangle,
            };
        case "processing":
            return {
                label: "Diproses",
                badgeClass: "bg-blue-50 text-blue-800 border-blue-200",
                dotClass: "bg-blue-500",
                icon: Package,
            };
        case "shiping":
            return {
                label: "Dikirim",
                badgeClass: "bg-purple-50 text-purple-800 border-purple-200",
                dotClass: "bg-purple-500",
                icon: Truck,
            };
        case "completed":
            return {
                label: "Selesai",
                badgeClass: "bg-emerald-50 text-emerald-800 border-emerald-200",
                dotClass: "bg-emerald-500",
                icon: CheckCircle2,
            };
        case "cancelled":
            return {
                label: "Dibatalkan",
                badgeClass: "bg-rose-50 text-rose-800 border-rose-200",
                dotClass: "bg-rose-500",
                icon: XCircle,
            };
        default:
            return {
                label: status || "Tidak Diketahui",
                badgeClass: "bg-stone-50 text-stone-700 border-stone-200",
                dotClass: "bg-stone-400",
                icon: Receipt,
            };
    }
};

// Payment method display
const getPaymentMethodDisplay = (method) => {
    if (method === "digital_wallet") {
        return {
            label: "Transfer / E-Wallet",
            icon: Wallet,
            class: "bg-indigo-50 text-indigo-700 border-indigo-200",
        };
    }
    if (method === "cod") {
        return {
            label: "Cash On Delivery (COD)",
            icon: Banknote,
            class: "bg-stone-100 text-stone-700 border-stone-200",
        };
    }
    return {
        label: method || "Lainnya",
        icon: CreditCard,
        class: "bg-stone-100 text-stone-700 border-stone-200",
    };
};

// Payment proof full URL
const getPaymentProofUrl = (proof) => {
    if (!proof) return null;
    return proof.startsWith("http") || proof.startsWith("/")
        ? proof
        : `/storage/payment_proofs/${proof}`;
};

// Top KPI metrics
const stats = computed(() => {
    const list = props.orders || [];
    const total = list.length;
    const needsAction = list.filter((o) =>
        ["waiting", "checking"].includes(o.status)
    ).length;
    const inProgress = list.filter((o) =>
        ["processing", "shiping"].includes(o.status)
    ).length;
    const completed = list.filter((o) => o.status === "completed").length;

    return { total, needsAction, inProgress, completed };
});

// Toggle quick filter via KPI cards
const toggleKpiFilter = (statusKey) => {
    if (selectedStatus.value === statusKey) {
        selectedStatus.value = "all";
    } else {
        selectedStatus.value = statusKey;
    }
};

// Filtered and sorted orders
const filteredOrders = computed(() => {
    let list = [...(props.orders || [])];

    // Search query
    if (search.value.trim()) {
        const q = search.value.toLowerCase().trim();
        list = list.filter((order) => {
            const idMatch =
                String(order.id).toLowerCase().includes(q) ||
                `#${order.id}`.toLowerCase().includes(q);
            const nameMatch = order.name?.toLowerCase().includes(q);
            const emailMatch = order.email?.toLowerCase().includes(q);
            const phoneMatch = order.phone?.toLowerCase().includes(q);
            const cityMatch = order.city?.toLowerCase().includes(q);
            const trackingMatch = order.tracking_number?.toLowerCase().includes(q);

            return (
                idMatch ||
                nameMatch ||
                emailMatch ||
                phoneMatch ||
                cityMatch ||
                trackingMatch
            );
        });
    }

    // Status filter
    if (selectedStatus.value !== "all") {
        if (selectedStatus.value === "needs_action") {
            list = list.filter((o) => ["waiting", "checking"].includes(o.status));
        } else if (selectedStatus.value === "in_progress") {
            list = list.filter((o) => ["processing", "shiping"].includes(o.status));
        } else {
            list = list.filter((o) => o.status === selectedStatus.value);
        }
    }

    // Payment method filter
    if (selectedPayment.value !== "all") {
        list = list.filter((o) => o.payment_method === selectedPayment.value);
    }

    // Sorting
    list.sort((a, b) => {
        if (selectedSort.value === "newest") {
            return new Date(b.created_at) - new Date(a.created_at);
        }
        if (selectedSort.value === "oldest") {
            return new Date(a.created_at) - new Date(b.created_at);
        }
        if (selectedSort.value === "highest_amount") {
            return Number(b.total_price) - Number(a.total_price);
        }
        if (selectedSort.value === "lowest_amount") {
            return Number(a.total_price) - Number(b.total_price);
        }
        return 0;
    });

    return list;
});

// Total pages
const totalPages = computed(() => {
    return Math.ceil(filteredOrders.value.length / perPage.value) || 1;
});

// Slice orders for current page
const paginatedOrders = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredOrders.value.slice(start, start + perPage.value);
});

// Pagination numbers with windowing
const displayedPages = computed(() => {
    const current = currentPage.value;
    const last = totalPages.value;
    if (last <= 7) {
        return Array.from({ length: last }, (_, i) => i + 1);
    }
    const pages = [];
    if (current <= 4) {
        for (let i = 1; i <= 5; i++) pages.push(i);
        pages.push("...");
        pages.push(last);
    } else if (current >= last - 3) {
        pages.push(1);
        pages.push("...");
        for (let i = last - 4; i <= last; i++) pages.push(i);
    } else {
        pages.push(1);
        pages.push("...");
        pages.push(current - 1);
        pages.push(current);
        pages.push(current + 1);
        pages.push("...");
        pages.push(last);
    }
    return pages;
});

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return (
        Boolean(search.value) ||
        selectedStatus.value !== "all" ||
        selectedPayment.value !== "all" ||
        selectedSort.value !== "newest"
    );
});

// Reset all filters
const resetAllFilters = () => {
    search.value = "";
    selectedStatus.value = "all";
    selectedPayment.value = "all";
    selectedSort.value = "newest";
    currentPage.value = 1;
};

// Quick Preview Modal state
const showPreviewModal = ref(false);
const previewOrder = ref(null);

const openPreviewModal = (order) => {
    previewOrder.value = order;
    showPreviewModal.value = true;
};

const closePreviewModal = () => {
    showPreviewModal.value = false;
    previewOrder.value = null;
};
</script>

<template>

    <Head title="Manajemen Pesanan" />

    <AdminLayout pageTitle="Manajemen Pesanan">
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2">
                <div>
                    <h1 class="text-2xl font-bold text-stone-900 tracking-tight flex items-center gap-2.5">
                        <ShoppingCart class="w-7 h-7 text-orange-600" />
                        Daftar Pesanan Pelanggan
                    </h1>
                    <p class="text-sm text-stone-500 mt-1">
                        Pantau status transaksi, verifikasi bukti pembayaran, dan lacak pengiriman pesanan.
                    </p>
                </div>
            </div>

            <!-- KPI Metric Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- Total Orders -->
                <div @click="toggleKpiFilter('all')" :class="[
                    'p-4 rounded-2xl border transition-all duration-200 cursor-pointer relative overflow-hidden',
                    selectedStatus === 'all'
                        ? 'bg-orange-50/70 border-orange-300 ring-2 ring-orange-500/20 shadow-sm'
                        : 'bg-white border-stone-200/80 hover:border-orange-200 hover:shadow-sm',
                ]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">
                            Total Pesanan
                        </span>
                        <div class="p-2 rounded-xl bg-orange-100 text-orange-700">
                            <Receipt class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-stone-900">
                            {{ stats.total }}
                        </span>
                        <span class="text-xs text-stone-400">transaksi</span>
                    </div>
                </div>

                <!-- Needs Action (Waiting / Checking) -->
                <div @click="toggleKpiFilter('needs_action')" :class="[
                    'p-4 rounded-2xl border transition-all duration-200 cursor-pointer relative overflow-hidden',
                    selectedStatus === 'needs_action'
                        ? 'bg-amber-50/70 border-amber-300 ring-2 ring-amber-500/20 shadow-sm'
                        : 'bg-white border-stone-200/80 hover:border-amber-200 hover:shadow-sm',
                ]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">
                            Perlu Tindakan
                        </span>
                        <div class="p-2 rounded-xl bg-amber-100 text-amber-700">
                            <Clock class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-amber-700">
                            {{ stats.needsAction }}
                        </span>
                        <span class="text-xs text-stone-400">menunggu/cek</span>
                    </div>
                </div>

                <!-- In Progress (Processing / Shipping) -->
                <div @click="toggleKpiFilter('in_progress')" :class="[
                    'p-4 rounded-2xl border transition-all duration-200 cursor-pointer relative overflow-hidden',
                    selectedStatus === 'in_progress'
                        ? 'bg-blue-50/70 border-blue-300 ring-2 ring-blue-500/20 shadow-sm'
                        : 'bg-white border-stone-200/80 hover:border-blue-200 hover:shadow-sm',
                ]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">
                            Sedang Diproses/Kirim
                        </span>
                        <div class="p-2 rounded-xl bg-blue-100 text-blue-700">
                            <Truck class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-blue-700">
                            {{ stats.inProgress }}
                        </span>
                        <span class="text-xs text-stone-400">berjalan</span>
                    </div>
                </div>

                <!-- Completed -->
                <div @click="toggleKpiFilter('completed')" :class="[
                    'p-4 rounded-2xl border transition-all duration-200 cursor-pointer relative overflow-hidden',
                    selectedStatus === 'completed'
                        ? 'bg-emerald-50/70 border-emerald-300 ring-2 ring-emerald-500/20 shadow-sm'
                        : 'bg-white border-stone-200/80 hover:border-emerald-200 hover:shadow-sm',
                ]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">
                            Pesanan Selesai
                        </span>
                        <div class="p-2 rounded-xl bg-emerald-100 text-emerald-700">
                            <CheckCircle2 class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-emerald-700">
                            {{ stats.completed }}
                        </span>
                        <span class="text-xs text-stone-400">sukses</span>
                    </div>
                </div>
            </div>

            <!-- Main Order Table Card -->
            <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
                <!-- Toolbar: Search, Filters, Sorters -->
                <div class="p-4 sm:p-5 border-b border-stone-100 bg-stone-50/50 space-y-3">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                        <!-- Live Search Input -->
                        <div class="relative flex-1 min-w-[260px]">
                            <Search
                                class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" />
                            <input type="text" v-model="search"
                                placeholder="Cari ID #, nama pelanggan, email, telepon, kota..."
                                class="w-full pl-10 pr-9 py-2.5 text-sm bg-white border border-stone-200 rounded-xl placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors" />
                            <button v-if="search" @click="search = ''" type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-0.5 rounded-full text-stone-400 hover:text-stone-600 hover:bg-stone-100"
                                title="Hapus pencarian">
                                <X class="w-3.5 h-3.5" />
                            </button>
                        </div>

                        <!-- Dropdowns: Status, Payment Method, Sort -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <!-- Status Filter Dropdown -->
                            <div class="relative min-w-[160px]">
                                <select v-model="selectedStatus"
                                    class="w-full appearance-none bg-none pl-3.5 pr-8 py-2.5 text-sm bg-white border border-stone-200 rounded-xl text-stone-700 font-medium focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors cursor-pointer">
                                    <option value="all">Semua Status</option>
                                    <option value="needs_action">Perlu Tindakan</option>
                                    <option value="waiting">Menunggu Pembayaran</option>
                                    <option value="checking">Verifikasi Bukti</option>
                                    <option value="processing">Sedang Diproses</option>
                                    <option value="shiping">Sedang Dikirim</option>
                                    <option value="completed">Selesai</option>
                                    <option value="cancelled">Dibatalkan</option>
                                </select>
                                <SlidersHorizontal
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" />
                            </div>

                            <!-- Payment Method Filter -->
                            <div class="relative min-w-[150px]">
                                <select v-model="selectedPayment"
                                    class="w-full appearance-none bg-none pl-3.5 pr-8 py-2.5 text-sm bg-white border border-stone-200 rounded-xl text-stone-700 font-medium focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors cursor-pointer">
                                    <option value="all">Semua Pembayaran</option>
                                    <option value="digital_wallet">Transfer / E-Wallet</option>
                                    <option value="cod">COD (Bayar di Tempat)</option>
                                </select>
                                <CreditCard
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" />
                            </div>

                            <!-- Sort Selector -->
                            <div class="relative min-w-[160px]">
                                <select v-model="selectedSort"
                                    class="w-full appearance-none bg-none pl-3.5 pr-8 py-2.5 text-sm bg-white border border-stone-200 rounded-xl text-stone-700 font-medium focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors cursor-pointer">
                                    <option value="newest">Urut: Terbaru</option>
                                    <option value="oldest">Urut: Terlama</option>
                                    <option value="highest_amount">Total: Tertinggi</option>
                                    <option value="lowest_amount">Total: Terendah</option>
                                </select>
                                <ArrowUpDown
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" />
                            </div>
                        </div>
                    </div>

                    <!-- Active Filter Chips -->
                    <div v-if="hasActiveFilters"
                        class="flex flex-wrap items-center gap-2 pt-2 border-t border-stone-200/60">
                        <span class="text-xs font-medium text-stone-500 flex items-center gap-1">
                            <Filter class="w-3.5 h-3.5 text-stone-400" />
                            Filter Aktif:
                        </span>

                        <span v-if="search"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-orange-50 text-orange-800 border border-orange-200">
                            Cari: "{{ search }}"
                            <button @click="search = ''" class="hover:text-orange-950">
                                <X class="w-3 h-3" />
                            </button>
                        </span>

                        <span v-if="selectedStatus !== 'all'"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-orange-50 text-orange-800 border border-orange-200">
                            Status: {{ getStatusConfig(selectedStatus).label }}
                            <button @click="selectedStatus = 'all'" class="hover:text-orange-950">
                                <X class="w-3 h-3" />
                            </button>
                        </span>

                        <span v-if="selectedPayment !== 'all'"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-orange-50 text-orange-800 border border-orange-200">
                            Metode: {{ getPaymentMethodDisplay(selectedPayment).label }}
                            <button @click="selectedPayment = 'all'" class="hover:text-orange-950">
                                <X class="w-3 h-3" />
                            </button>
                        </span>

                        <button @click="resetAllFilters"
                            class="text-xs font-semibold text-orange-600 hover:text-orange-700 hover:underline ml-1 inline-flex items-center gap-1">
                            <RotateCcw class="w-3 h-3" />
                            Reset Semua Filter
                        </button>
                    </div>
                </div>

                <!-- ORDERS TABLE -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-stone-50/75 border-b border-stone-200 text-stone-500 text-xs uppercase tracking-wider font-semibold">
                                <th class="py-3.5 px-4 min-w-[140px]">No. Pesanan</th>
                                <th class="py-3.5 px-4 min-w-[240px]">Pelanggan</th>
                                <th class="py-3.5 px-4 min-w-[160px]">Pembayaran</th>
                                <th class="py-3.5 px-4 min-w-[140px]">Total Tagihan</th>
                                <th class="py-3.5 px-4 min-w-[150px]">Status Pesanan</th>
                                <th class="py-3.5 px-4 min-w-[130px]">Pengiriman</th>
                                <th class="py-3.5 px-4 text-center min-w-[140px]">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-stone-100 text-sm">
                            <tr v-for="order in paginatedOrders" :key="order.id"
                                class="hover:bg-stone-50/80 transition-colors group">
                                <!-- Order ID & Date -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <Link :href="route('admin.orders.show', order.id)"
                                            class="font-bold text-stone-900 group-hover:text-orange-600 transition-colors inline-flex items-center gap-1">
                                            #{{ order.id }}
                                        </Link>
                                        <span class="text-xs text-stone-400 mt-0.5 flex items-center gap-1">
                                            <Calendar class="w-3 h-3 text-stone-300" />
                                            {{ formatDate(order.created_at) }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Customer Details -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <!-- Avatar Initials -->
                                        <div
                                            class="w-9 h-9 rounded-xl bg-orange-100 text-orange-700 font-bold text-xs flex items-center justify-center shrink-0 border border-orange-200/60">
                                            {{ getInitials(order.name) }}
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p class="font-semibold text-stone-800 truncate">
                                                {{ order.name }}
                                            </p>
                                            <div class="flex items-center gap-2 text-xs text-stone-400 mt-0.5">
                                                <span class="truncate max-w-[130px]" :title="order.email">
                                                    {{ order.email || order.phone || '-' }}
                                                </span>
                                                <span v-if="order.city"
                                                    class="inline-flex items-center gap-0.5 text-stone-500 font-medium">
                                                    •
                                                    <MapPin class="w-3 h-3 text-stone-400 inline" /> {{ order.city }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Payment Details & Proof Status -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <span :class="[
                                            'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-medium border',
                                            getPaymentMethodDisplay(order.payment_method).class,
                                        ]">
                                            <component :is="getPaymentMethodDisplay(order.payment_method).icon"
                                                class="w-3.5 h-3.5" />
                                            {{ getPaymentMethodDisplay(order.payment_method).label }}
                                        </span>

                                        <div v-if="order.payment_method === 'digital_wallet'" class="text-[11px]">
                                            <span v-if="order.payment_proof"
                                                class="text-emerald-700 font-medium inline-flex items-center gap-1">
                                                <CheckCircle2 class="w-3 h-3 text-emerald-600" />
                                                Bukti Diunggah
                                            </span>
                                            <span v-else
                                                class="text-amber-700 font-medium inline-flex items-center gap-1">
                                                <Clock class="w-3 h-3 text-amber-600" />
                                                Belum Ada Bukti
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Total Price -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-bold text-stone-900 text-sm">
                                        {{ formatPrice(order.total_price) }}
                                    </span>
                                </td>

                                <!-- Order Status -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span :class="[
                                        'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border',
                                        getStatusConfig(order.status).badgeClass,
                                    ]">
                                        <span :class="[
                                            'w-1.5 h-1.5 rounded-full',
                                            getStatusConfig(order.status).dotClass,
                                        ]" />
                                        {{ getStatusConfig(order.status).label }}
                                    </span>
                                </td>

                                <!-- Shipping -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-xs text-stone-600">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-medium text-stone-800">
                                            {{ order.shipping_method || 'Kurir Reguler' }}
                                        </span>
                                        <span v-if="order.tracking_number" class="text-[11px] text-stone-400 font-mono"
                                            :title="order.tracking_number">
                                            Resi: {{ order.tracking_number }}
                                        </span>
                                        <span v-else class="text-[11px] text-stone-400 italic">
                                            Belum ada resi
                                        </span>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                    <div class="inline-flex items-center justify-center gap-1.5">
                                        <!-- Quick Preview -->
                                        <button type="button" @click="openPreviewModal(order)"
                                            class="p-2 rounded-lg text-stone-500 hover:text-stone-800 hover:bg-stone-100 transition-colors"
                                            title="Pratinjau Cepat">
                                            <Eye class="w-4 h-4" />
                                        </button>

                                        <!-- Full Details Link -->
                                        <Link :href="route('admin.orders.show', order.id)"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-orange-600 hover:bg-orange-700 text-white shadow-xs transition-colors">
                                            <span>Kelola</span>
                                            <ArrowRight class="w-3.5 h-3.5" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- EMPTY STATE -->
                <div v-if="filteredOrders.length === 0"
                    class="py-16 px-4 text-center flex flex-col items-center justify-center">
                    <div
                        class="w-16 h-16 rounded-2xl bg-stone-100 text-stone-400 flex items-center justify-center mb-4">
                        <ShoppingCart class="w-8 h-8 text-stone-400" />
                    </div>

                    <h3 class="text-base font-bold text-stone-800">
                        {{
                            hasActiveFilters
                                ? "Tidak ada pesanan yang sesuai filter"
                                : "Belum ada pesanan masuk"
                        }}
                    </h3>

                    <p class="text-sm text-stone-500 max-w-sm mt-1 mb-5">
                        {{
                            hasActiveFilters
                                ? "Cobalah sesuaikan kata kunci pencarian atau ubah filter status transaksi."
                                : "Pesanan baru dari pelanggan di toko online Anda akan muncul otomatis di sini."
                        }}
                    </p>

                    <button v-if="hasActiveFilters" @click="resetAllFilters"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 transition-colors">
                        <RotateCcw class="w-4 h-4" />
                        Reset Semua Filter
                    </button>
                </div>

                <!-- PAGINATION CONTROLS -->
                <div v-if="filteredOrders.length > 0"
                    class="p-4 sm:p-5 border-t border-stone-100 bg-stone-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <!-- Info & Per-Page Selector -->
                    <div class="flex items-center gap-3 text-xs sm:text-sm text-stone-500">
                        <span>
                            Menampilkan
                            <span class="font-semibold text-stone-800">
                                {{ (currentPage - 1) * perPage + 1 }}
                            </span>
                            sampai
                            <span class="font-semibold text-stone-800">
                                {{ Math.min(currentPage * perPage, filteredOrders.length) }}
                            </span>
                            dari
                            <span class="font-semibold text-stone-800">
                                {{ filteredOrders.length }}
                            </span>
                            pesanan
                        </span>

                        <span class="text-stone-300">|</span>

                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-stone-400">Tampilkan:</span>
                            <select v-model="perPage"
                                class="text-xs bg-white border border-stone-200 rounded-lg px-2 py-1 font-semibold text-stone-700 focus:outline-none focus:ring-1 focus:ring-orange-500">
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                            </select>
                        </div>
                    </div>

                    <!-- Page Navigation -->
                    <div v-if="totalPages > 1" class="flex items-center gap-1.5 self-center sm:self-auto">
                        <!-- Prev -->
                        <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage <= 1"
                            class="p-2 rounded-xl border border-stone-200 bg-white text-stone-600 hover:bg-stone-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                            title="Halaman Sebelumnya">
                            <ChevronLeft class="w-4 h-4" />
                        </button>

                        <!-- Page Numbers -->
                        <template v-for="(pageNum, idx) in displayedPages" :key="idx">
                            <span v-if="pageNum === '...'" class="px-2 py-1 text-xs text-stone-400 font-bold">
                                ...
                            </span>
                            <button v-else @click="currentPage = pageNum" :class="[
                                'min-w-[36px] h-9 px-2.5 rounded-xl text-xs font-bold transition-all duration-150',
                                pageNum === currentPage
                                    ? 'bg-orange-600 text-white shadow-xs'
                                    : 'bg-white border border-stone-200 text-stone-700 hover:bg-stone-50',
                            ]">
                                {{ pageNum }}
                            </button>
                        </template>

                        <!-- Next -->
                        <button @click="currentPage = Math.min(totalPages, currentPage + 1)"
                            :disabled="currentPage >= totalPages"
                            class="p-2 rounded-xl border border-stone-200 bg-white text-stone-600 hover:bg-stone-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                            title="Halaman Selanjutnya">
                            <ChevronRight class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- QUICK ORDER PREVIEW MODAL -->
        <Modal :show="showPreviewModal" @close="closePreviewModal" maxWidth="lg">
            <div v-if="previewOrder" class="p-6">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                    <div class="flex items-center gap-2">
                        <Receipt class="w-5 h-5 text-orange-600" />
                        <div>
                            <h3 class="text-base font-bold text-stone-900 leading-tight">
                                Ringkasan Pesanan #{{ previewOrder.id }}
                            </h3>
                            <span class="text-xs text-stone-400">
                                {{ formatDate(previewOrder.created_at) }}
                            </span>
                        </div>
                    </div>

                    <button type="button" @click="closePreviewModal"
                        class="p-1.5 rounded-lg text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="mt-4 space-y-4 text-xs">
                    <!-- Status & Payment Top Card -->
                    <div
                        class="p-3.5 rounded-xl bg-stone-50 border border-stone-200/80 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-stone-400 block mb-1">
                                Status Pesanan
                            </span>
                            <span :class="[
                                'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-semibold border',
                                getStatusConfig(previewOrder.status).badgeClass,
                            ]">
                                <span :class="[
                                    'w-1.5 h-1.5 rounded-full',
                                    getStatusConfig(previewOrder.status).dotClass,
                                ]" />
                                {{ getStatusConfig(previewOrder.status).label }}
                            </span>
                        </div>

                        <div class="text-right">
                            <span class="text-[10px] uppercase font-bold text-stone-400 block mb-1">
                                Total Pembayaran
                            </span>
                            <span class="text-base font-extrabold text-stone-900">
                                {{ formatPrice(previewOrder.total_price) }}
                            </span>
                        </div>
                    </div>

                    <!-- Customer Contact Info -->
                    <div class="p-3 rounded-xl border border-stone-200/80 space-y-2">
                        <h4 class="font-bold text-stone-800 flex items-center gap-1.5">
                            <User class="w-3.5 h-3.5 text-stone-500" />
                            Informasi Pembeli
                        </h4>
                        <div class="grid grid-cols-2 gap-2 text-stone-600">
                            <div>
                                <span class="text-stone-400 block text-[10px]">Nama:</span>
                                <span class="font-medium text-stone-800">{{ previewOrder.name }}</span>
                            </div>
                            <div>
                                <span class="text-stone-400 block text-[10px]">Telepon:</span>
                                <span class="font-medium text-stone-800">{{ previewOrder.phone || '-' }}</span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-stone-400 block text-[10px]">Email:</span>
                                <span class="font-medium text-stone-800">{{ previewOrder.email || '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Destination -->
                    <div class="p-3 rounded-xl border border-stone-200/80 space-y-1.5">
                        <h4 class="font-bold text-stone-800 flex items-center gap-1.5">
                            <MapPin class="w-3.5 h-3.5 text-stone-500" />
                            Alamat Pengiriman
                        </h4>
                        <p class="text-stone-700 leading-relaxed">
                            {{ previewOrder.address }}
                        </p>
                        <p class="text-stone-500">
                            {{ [previewOrder.village, previewOrder.district, previewOrder.city,
                            previewOrder.province].filter(Boolean).join(', ') }}
                        </p>
                        <div class="pt-1 flex items-center gap-2 text-stone-600">
                            <span class="text-stone-400">Metode Pengiriman:</span>
                            <span class="font-semibold text-stone-800">
                                {{ previewOrder.shipping_method || 'Kurir Toko' }}
                            </span>
                            <span v-if="previewOrder.tracking_number" class="text-stone-500 font-mono">
                                (Resi: {{ previewOrder.tracking_number }})
                            </span>
                        </div>
                    </div>

                    <!-- Payment Proof Preview (if digital wallet) -->
                    <div v-if="previewOrder.payment_method === 'digital_wallet'"
                        class="p-3 rounded-xl border border-stone-200/80 space-y-2">
                        <h4 class="font-bold text-stone-800 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <FileText class="w-3.5 h-3.5 text-stone-500" />
                                Bukti Pembayaran
                            </span>
                            <span v-if="previewOrder.payment_proof" class="text-[10px] text-emerald-600 font-semibold">
                                Tersedia
                            </span>
                            <span v-else class="text-[10px] text-amber-600 font-semibold">
                                Belum Diunggah
                            </span>
                        </h4>

                        <div v-if="previewOrder.payment_proof"
                            class="relative rounded-lg overflow-hidden border border-stone-200 bg-stone-100 max-h-48 flex items-center justify-center">
                            <img :src="getPaymentProofUrl(previewOrder.payment_proof)" alt="Bukti Pembayaran"
                                class="w-full h-auto max-h-48 object-contain" />
                        </div>
                        <p v-else class="text-stone-400 italic">
                            Pelanggan belum mengunggah struk transfer / bukti bayar.
                        </p>
                    </div>

                    <!-- Order Note -->
                    <div v-if="previewOrder.note"
                        class="p-3 rounded-xl bg-amber-50/60 border border-amber-100 text-stone-700">
                        <span class="font-bold text-amber-800 block text-[10px] uppercase mb-0.5">
                            Catatan Pesanan:
                        </span>
                        {{ previewOrder.note }}
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-end gap-2">
                    <button type="button" @click="closePreviewModal"
                        class="px-4 py-2 text-xs font-semibold text-stone-700 bg-stone-100 hover:bg-stone-200 rounded-xl transition-colors">
                        Tutup
                    </button>

                    <Link :href="route('admin.orders.show', previewOrder.id)"
                        class="px-4 py-2 text-xs font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl transition-colors inline-flex items-center gap-1.5 shadow-xs">
                        <span>Kelola Detail Lengkap</span>
                        <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

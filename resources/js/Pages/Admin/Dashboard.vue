<script setup>
import { computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    Plus,
    ShoppingCart,
    Package,
    Clock,
    TrendingUp,
    AlertTriangle,
    Star,
    ArrowRight,
    ExternalLink,
    Coins,
    Flame,
    ImageOff,
    CheckCircle2,
    Calendar,
} from "lucide-vue-next";

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalRevenue: 0,
            pendingOrdersCount: 0,
            totalProductsCount: 0,
            lowStockCount: 0,
        }),
    },
    recentOrders: {
        type: Array,
        default: () => [],
    },
    topSelling: {
        type: Array,
        default: () => [],
    },
    mostRated: {
        type: Array,
        default: () => [],
    },
    lowStock: {
        type: Array,
        default: () => [],
    },
});

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return "-";
    try {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat("id-ID", {
            day: "numeric",
            month: "short",
            year: "numeric",
        }).format(date);
    } catch {
        return dateString;
    }
};

const todayFormatted = computed(() => {
    return new Intl.DateTimeFormat("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(new Date());
});

const getStatusBadge = (status) => {
    switch (status) {
        case "waiting":
            return {
                label: "Menunggu",
                class: "bg-amber-100 text-amber-800 border-amber-200",
            };
        case "checking":
            return {
                label: "Verifikasi",
                class: "bg-orange-100 text-orange-800 border-orange-200",
            };
        case "processing":
            return {
                label: "Diproses",
                class: "bg-blue-100 text-blue-800 border-blue-200",
            };
        case "shiping":
            return {
                label: "Dikirim",
                class: "bg-purple-100 text-purple-800 border-purple-200",
            };
        case "completed":
            return {
                label: "Selesai",
                class: "bg-emerald-100 text-emerald-800 border-emerald-200",
            };
        case "cancelled":
            return {
                label: "Dibatalkan",
                class: "bg-rose-100 text-rose-800 border-rose-200",
            };
        default:
            return {
                label: status || "Pending",
                class: "bg-stone-100 text-stone-700 border-stone-200",
            };
    }
};

const handleImageError = (event) => {
    event.target.style.display = "none";
    if (event.target.nextElementSibling) {
        event.target.nextElementSibling.classList.remove("hidden");
    }
};
</script>

<template>
    <Head title="Dashboard Admin" />

    <AdminLayout pageTitle="Dashboard">
        <div class="space-y-6 sm:space-y-8">
            <!-- Greeting & Quick Actions Header Banner -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 p-6 sm:p-8 text-white shadow-lg shadow-orange-500/10"
            >
                <div
                    class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4"
                >
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 text-orange-100 text-xs sm:text-sm font-medium">
                            <Calendar class="w-4 h-4" />
                            <span>{{ todayFormatted }}</span>
                        </div>
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight">
                            Selamat Datang, {{ $page.props.auth?.user?.name }}! 👋
                        </h1>
                        <p class="text-orange-100 text-xs sm:text-sm max-w-xl">
                            Kelola pesanan pelanggan, pantau ketersediaan stok, dan tinjau performa toko Nyuwi Creation Anda.
                        </p>
                    </div>

                    <!-- Quick Action Buttons -->
                    <div class="flex flex-wrap items-center gap-2.5 pt-2 md:pt-0">
                        <Link
                            :href="route('products.create')"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white text-orange-600 hover:bg-orange-50 font-bold text-xs sm:text-sm shadow-sm transition-all duration-150 active:scale-95"
                        >
                            <Plus class="w-4 h-4" />
                            <span>Tambah Produk</span>
                        </Link>
                        <Link
                            :href="route('admin.orders.index')"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-orange-700/50 hover:bg-orange-700/70 border border-white/20 text-white font-semibold text-xs sm:text-sm backdrop-blur-xs transition-all duration-150 active:scale-95"
                        >
                            <ShoppingCart class="w-4 h-4" />
                            <span>Kelola Pesanan</span>
                        </Link>
                    </div>
                </div>

                <!-- Abstract Decorative Shapes -->
                <div
                    class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"
                ></div>
                <div
                    class="absolute -left-10 -top-10 w-40 h-40 bg-amber-400/20 rounded-full blur-xl pointer-events-none"
                ></div>
            </div>

            <!-- KPI Metric Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                <!-- Total Revenue -->
                <div
                    class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-2xs hover:shadow-md transition-shadow duration-200 flex flex-col justify-between"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">
                            Total Pendapatan
                        </span>
                        <div
                            class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"
                        >
                            <Coins class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-xl sm:text-2xl font-bold text-stone-800 tracking-tight">
                            {{ formatPrice(stats.totalRevenue) }}
                        </div>
                        <div class="flex items-center gap-1.5 mt-1 text-xs text-stone-500">
                            <CheckCircle2 class="w-3.5 h-3.5 text-emerald-500" />
                            <span>Pesanan terverifikasi</span>
                        </div>
                    </div>
                </div>

                <!-- Pending Orders -->
                <Link
                    :href="route('admin.orders.index')"
                    class="group bg-white rounded-2xl p-5 border border-stone-200/80 shadow-2xs hover:border-amber-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">
                            Pesanan Perlu Diproses
                        </span>
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-105 transition-transform"
                        >
                            <Clock class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-xl sm:text-2xl font-bold text-stone-800 tracking-tight">
                            {{ stats.pendingOrdersCount }}
                        </div>
                        <div class="flex items-center justify-between mt-1 text-xs">
                            <span
                                :class="[
                                    'font-medium',
                                    stats.pendingOrdersCount > 0 ? 'text-amber-600' : 'text-stone-500',
                                ]"
                            >
                                {{ stats.pendingOrdersCount > 0 ? 'Menunggu tindakan' : 'Semua diproses' }}
                            </span>
                            <span class="text-stone-400 group-hover:text-amber-600 flex items-center gap-0.5">
                                Lihat <ArrowRight class="w-3 h-3" />
                            </span>
                        </div>
                    </div>
                </Link>

                <!-- Total Active Products -->
                <Link
                    :href="route('products.index')"
                    class="group bg-white rounded-2xl p-5 border border-stone-200/80 shadow-2xs hover:border-blue-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">
                            Total Produk
                        </span>
                        <div
                            class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-105 transition-transform"
                        >
                            <Package class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-xl sm:text-2xl font-bold text-stone-800 tracking-tight">
                            {{ stats.totalProductsCount }}
                        </div>
                        <div class="flex items-center justify-between mt-1 text-xs">
                            <span class="text-stone-500 font-medium">Katalog aktif</span>
                            <span class="text-stone-400 group-hover:text-blue-600 flex items-center gap-0.5">
                                Kelola <ArrowRight class="w-3 h-3" />
                            </span>
                        </div>
                    </div>
                </Link>

                <!-- Low Stock Products Alert -->
                <div
                    class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-2xs hover:shadow-md transition-shadow duration-200 flex flex-col justify-between"
                    :class="{ 'border-rose-300 bg-rose-50/20': stats.lowStockCount > 0 }"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">
                            Peringatan Stok
                        </span>
                        <div
                            :class="[
                                'w-10 h-10 rounded-xl flex items-center justify-center',
                                stats.lowStockCount > 0
                                    ? 'bg-rose-100 text-rose-600'
                                    : 'bg-stone-100 text-stone-500',
                            ]"
                        >
                            <AlertTriangle class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-xl sm:text-2xl font-bold text-stone-800 tracking-tight">
                            {{ stats.lowStockCount }}
                        </div>
                        <div class="flex items-center gap-1 mt-1 text-xs">
                            <span
                                :class="[
                                    'font-medium',
                                    stats.lowStockCount > 0 ? 'text-rose-600 font-bold' : 'text-stone-500',
                                ]"
                            >
                                {{ stats.lowStockCount > 0 ? 'Perlu re-stock (< 10 unit)' : 'Stok aman' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Section -->
            <div class="bg-white rounded-2xl border border-stone-200/80 shadow-2xs overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-stone-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-stone-800">
                            Pesanan Terbaru
                        </h2>
                        <p class="text-xs text-stone-500 mt-0.5">
                            Daftar transaksi pelanggan terkini
                        </p>
                    </div>
                    <Link
                        :href="route('admin.orders.index')"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-orange-600 hover:text-orange-700 bg-orange-50 hover:bg-orange-100/70 px-3 py-1.5 rounded-lg transition-colors"
                    >
                        <span>Lihat Semua Pesanan</span>
                        <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-stone-50/70 text-[11px] font-bold text-stone-500 uppercase tracking-wider border-b border-stone-100">
                                <th class="py-3 px-6">ID Pesanan</th>
                                <th class="py-3 px-6">Pelanggan</th>
                                <th class="py-3 px-6">Metode</th>
                                <th class="py-3 px-6">Tanggal</th>
                                <th class="py-3 px-6">Total</th>
                                <th class="py-3 px-6">Status</th>
                                <th class="py-3 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-xs sm:text-sm">
                            <tr
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="hover:bg-stone-50/60 transition-colors"
                            >
                                <td class="py-3.5 px-6 font-semibold text-stone-800">
                                    #{{ order.id }}
                                </td>
                                <td class="py-3.5 px-6 font-medium text-stone-800">
                                    {{ order.name }}
                                </td>
                                <td class="py-3.5 px-6 text-stone-500 capitalize">
                                    {{ order.payment_method ? order.payment_method.replace('_', ' ') : '-' }}
                                </td>
                                <td class="py-3.5 px-6 text-stone-500">
                                    {{ formatDate(order.created_at) }}
                                </td>
                                <td class="py-3.5 px-6 font-bold text-stone-800">
                                    {{ formatPrice(order.total_price) }}
                                </td>
                                <td class="py-3.5 px-6">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border',
                                            getStatusBadge(order.status).class,
                                        ]"
                                    >
                                        {{ getStatusBadge(order.status).label }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-right">
                                    <Link
                                        :href="route('admin.orders.show', order.id)"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-orange-600 hover:text-orange-700 p-1 rounded-md hover:bg-orange-50"
                                    >
                                        <span>Detail</span>
                                        <ArrowRight class="w-3.5 h-3.5" />
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="recentOrders.length === 0">
                                <td colspan="7" class="py-8 text-center text-stone-400">
                                    Belum ada pesanan terbaru tercatat.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card List View -->
                <div class="md:hidden divide-y divide-stone-100">
                    <div
                        v-for="order in recentOrders"
                        :key="order.id"
                        class="p-4 space-y-2.5 hover:bg-stone-50/60 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-stone-800">
                                #{{ order.id }} &bull; {{ order.name }}
                            </span>
                            <span
                                :class="[
                                    'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border',
                                    getStatusBadge(order.status).class,
                                ]"
                            >
                                {{ getStatusBadge(order.status).label }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-stone-500">
                            <span>{{ formatDate(order.created_at) }}</span>
                            <span class="font-bold text-stone-800">{{ formatPrice(order.total_price) }}</span>
                        </div>
                        <div class="flex justify-end pt-1">
                            <Link
                                :href="route('admin.orders.show', order.id)"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-orange-600 hover:text-orange-700"
                            >
                                <span>Lihat Rincian Pesanan</span>
                                <ArrowRight class="w-3 h-3" />
                            </Link>
                        </div>
                    </div>

                    <div v-if="recentOrders.length === 0" class="p-6 text-center text-stone-400 text-xs">
                        Belum ada pesanan terbaru tercatat.
                    </div>
                </div>
            </div>

            <!-- Product Analytics Grid (3 Columns) -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <!-- Top Selling Products -->
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-stone-200/80 shadow-2xs flex flex-col">
                    <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center">
                                <Flame class="w-4 h-4" />
                            </div>
                            <h2 class="text-sm sm:text-base font-bold text-stone-800">
                                Produk Terlaris
                            </h2>
                        </div>
                        <span class="text-xs font-semibold text-stone-400">Top 5</span>
                    </div>

                    <div class="mt-4 space-y-3.5 flex-1">
                        <div
                            v-for="(product, index) in topSelling"
                            :key="product.id"
                            class="flex items-center gap-3.5 p-2 rounded-xl hover:bg-stone-50 transition-colors"
                        >
                            <!-- Rank badge -->
                            <div
                                class="w-6 h-6 rounded-full bg-stone-100 text-stone-700 font-bold text-xs flex items-center justify-center flex-shrink-0"
                            >
                                {{ index + 1 }}
                            </div>

                            <!-- Product Thumbnail -->
                            <div class="relative w-12 h-12 rounded-lg bg-stone-100 overflow-hidden flex-shrink-0 border border-stone-200/60">
                                <img
                                    v-if="product.image"
                                    :src="`/storage/products/${product.image}`"
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                    @error="handleImageError"
                                />
                                <div
                                    :class="[
                                        'w-full h-full flex items-center justify-center text-stone-400',
                                        product.image ? 'hidden' : '',
                                    ]"
                                >
                                    <Package class="w-5 h-5 text-stone-400" />
                                </div>
                            </div>

                            <!-- Product Info -->
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-xs sm:text-sm text-stone-800 truncate" :title="product.name">
                                    {{ product.name }}
                                </h3>
                                <div class="flex items-center justify-between mt-1 text-xs">
                                    <span class="text-stone-500 font-medium">
                                        {{ formatPrice(product.price) }}
                                    </span>
                                    <span class="font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-md">
                                        {{ product.total_sold }} Terjual
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div
                            v-if="topSelling.length === 0"
                            class="py-10 text-center text-stone-400 flex flex-col items-center justify-center"
                        >
                            <Package class="w-8 h-8 text-stone-300 mb-2" />
                            <p class="text-xs">Belum ada data penjualan tercatat.</p>
                        </div>
                    </div>
                </div>

                <!-- Most Rated Products -->
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-stone-200/80 shadow-2xs flex flex-col">
                    <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                                <Star class="w-4 h-4 fill-amber-500" />
                            </div>
                            <h2 class="text-sm sm:text-base font-bold text-stone-800">
                                Rating Tertinggi
                            </h2>
                        </div>
                        <span class="text-xs font-semibold text-stone-400">Top 5</span>
                    </div>

                    <div class="mt-4 space-y-3.5 flex-1">
                        <div
                            v-for="product in mostRated"
                            :key="product.id"
                            class="flex items-center gap-3.5 p-2 rounded-xl hover:bg-stone-50 transition-colors"
                        >
                            <!-- Product Thumbnail -->
                            <div class="relative w-12 h-12 rounded-lg bg-stone-100 overflow-hidden flex-shrink-0 border border-stone-200/60">
                                <img
                                    v-if="product.image"
                                    :src="`/storage/products/${product.image}`"
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                    @error="handleImageError"
                                />
                                <div
                                    :class="[
                                        'w-full h-full flex items-center justify-center text-stone-400',
                                        product.image ? 'hidden' : '',
                                    ]"
                                >
                                    <Package class="w-5 h-5 text-stone-400" />
                                </div>
                            </div>

                            <!-- Product Info -->
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-xs sm:text-sm text-stone-800 truncate" :title="product.name">
                                    {{ product.name }}
                                </h3>
                                <div class="flex items-center justify-between mt-1">
                                    <div class="flex items-center gap-1">
                                        <Star class="w-3.5 h-3.5 fill-amber-400 text-amber-400" />
                                        <span class="text-xs font-bold text-stone-700">
                                            {{ product.average_rating }}
                                        </span>
                                        <span class="text-[11px] text-stone-400">
                                            ({{ product.total_reviews }})
                                        </span>
                                    </div>
                                    <span class="text-xs font-medium text-stone-500">
                                        {{ formatPrice(product.price) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div
                            v-if="mostRated.length === 0"
                            class="py-10 text-center text-stone-400 flex flex-col items-center justify-center"
                        >
                            <Star class="w-8 h-8 text-stone-300 mb-2" />
                            <p class="text-xs">Belum ada ulasan produk pelanggan.</p>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-stone-200/80 shadow-2xs flex flex-col">
                    <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center">
                                <AlertTriangle class="w-4 h-4" />
                            </div>
                            <h2 class="text-sm sm:text-base font-bold text-stone-800">
                                Stok Menipis
                            </h2>
                        </div>
                        <Link
                            :href="route('products.index')"
                            class="text-xs font-semibold text-orange-600 hover:text-orange-700"
                        >
                            Kelola
                        </Link>
                    </div>

                    <div class="mt-4 space-y-3.5 flex-1">
                        <div
                            v-for="product in lowStock"
                            :key="product.id"
                            class="flex items-center gap-3.5 p-2 rounded-xl hover:bg-stone-50 transition-colors"
                        >
                            <!-- Product Thumbnail -->
                            <div class="relative w-12 h-12 rounded-lg bg-stone-100 overflow-hidden flex-shrink-0 border border-stone-200/60">
                                <img
                                    v-if="product.image"
                                    :src="`/storage/products/${product.image}`"
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                    @error="handleImageError"
                                />
                                <div
                                    :class="[
                                        'w-full h-full flex items-center justify-center text-stone-400',
                                        product.image ? 'hidden' : '',
                                    ]"
                                >
                                    <Package class="w-5 h-5 text-stone-400" />
                                </div>
                            </div>

                            <!-- Product Info -->
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-xs sm:text-sm text-stone-800 truncate" :title="product.name">
                                    {{ product.name }}
                                </h3>
                                <div class="flex items-center justify-between mt-1 text-xs">
                                    <span class="text-stone-500 font-medium">
                                        {{ formatPrice(product.price) }}
                                    </span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-600 border border-rose-200"
                                    >
                                        Sisa {{ product.stock }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div
                            v-if="lowStock.length === 0"
                            class="py-10 text-center text-stone-400 flex flex-col items-center justify-center"
                        >
                            <CheckCircle2 class="w-8 h-8 text-emerald-400 mb-2" />
                            <p class="text-xs text-stone-500 font-medium">Semua stok produk dalam kondisi aman.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

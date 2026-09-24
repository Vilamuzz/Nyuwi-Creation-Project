<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { Head, Link, useForm, router, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Modal from "@/Components/Modal.vue";
import ToastNotification from "@/Components/Customer/Sub-main/ToastNotification.vue";
import debounce from "lodash/debounce";
import {
    Search,
    Plus,
    Filter,
    ArrowUpDown,
    Trash2,
    Edit3,
    Eye,
    AlertTriangle,
    CheckCircle2,
    XCircle,
    Package,
    Tag,
    X,
    ChevronLeft,
    ChevronRight,
    LayoutGrid,
    List,
    ImageOff,
    ExternalLink,
    Scale,
    Sparkles,
    RotateCcw,
    Layers,
    SlidersHorizontal,
} from "lucide-vue-next";

const props = defineProps({
    products: {
        type: Object,
        default: () => ({
            data: [],
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
            from: 0,
            to: 0,
        }),
    },
    categories: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();

// Toast notification state
const toastMessage = ref("");
const toastType = ref("info");
const showToast = ref(false);

const showNotification = (message, type = "info") => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
};

const closeToast = () => {
    showToast.value = false;
};

// Listen for flash messages from backend
onMounted(() => {
    if (page.props.flash?.success) {
        showNotification(page.props.flash.success, "success");
    } else if (page.props.flash?.error) {
        showNotification(page.props.flash.error, "error");
    }
});

// View mode: 'table' or 'grid'
const viewMode = ref(
    typeof window !== "undefined"
        ? localStorage.getItem("adminProductViewMode") || "table"
        : "table"
);

const setViewMode = (mode) => {
    viewMode.value = mode;
    if (typeof window !== "undefined") {
        localStorage.setItem("adminProductViewMode", mode);
    }
};

// Filter states
const search = ref(props.filters?.search || "");
const sortField = ref(props.filters?.sortField || "created_at");
const sortDirection = ref(props.filters?.sortDirection || "desc");
const selectedCategory = ref("");
const selectedStockStatus = ref("all"); // 'all', 'in_stock', 'low_stock', 'out_of_stock'

// Available sort options
const sortOptions = [
    { field: "created_at", direction: "desc", label: "Terbaru" },
    { field: "created_at", direction: "asc", label: "Terlama" },
    { field: "name", direction: "asc", label: "Nama (A - Z)" },
    { field: "name", direction: "desc", label: "Nama (Z - A)" },
    { field: "price", direction: "asc", label: "Harga: Termurah" },
    { field: "price", direction: "desc", label: "Harga: Termahal" },
    { field: "stock", direction: "asc", label: "Stok: Terendah" },
    { field: "stock", direction: "desc", label: "Stok: Tertinggi" },
];

const selectedSortKey = computed({
    get: () => `${sortField.value}:${sortDirection.value}`,
    set: (val) => {
        const [field, direction] = val.split(":");
        handleSort(field, direction);
    },
});

// Category helper
const getCategoryName = (categoryId) => {
    if (!categoryId) return "Tanpa Kategori";
    const category = props.categories?.find((cat) => cat.id === categoryId);
    return category ? category.name : "Tanpa Kategori";
};

// Price and Currency formatter
const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price || 0);
};

// Image helpers
const parseImages = (product) => {
    if (!product || !product.images) return [];
    let imgs = product.images;
    if (typeof imgs === "string") {
        try {
            imgs = JSON.parse(imgs);
        } catch {
            imgs = [imgs];
        }
    }
    if (Array.isArray(imgs)) {
        return imgs.map((img) =>
            img.startsWith("http") || img.startsWith("/")
                ? img
                : `/storage/products/${img}`
        );
    }
    return [];
};

const getProductThumbnail = (product) => {
    const images = parseImages(product);
    return images.length > 0 ? images[0] : null;
};

// Stock status helper
const getStockStatus = (stock) => {
    const num = Number(stock) || 0;
    if (num <= 0) {
        return {
            label: "Habis",
            colorClass: "bg-red-50 text-red-700 border-red-200",
            dotClass: "bg-red-500",
            icon: XCircle,
            badgeClass: "badge-error",
            type: "out_of_stock",
        };
    }
    if (num <= 5) {
        return {
            label: "Menipis",
            colorClass: "bg-amber-50 text-amber-700 border-amber-200",
            dotClass: "bg-amber-500",
            icon: AlertTriangle,
            badgeClass: "badge-warning",
            type: "low_stock",
        };
    }
    return {
        label: "Tersedia",
        colorClass: "bg-emerald-50 text-emerald-700 border-emerald-200",
        dotClass: "bg-emerald-500",
        icon: CheckCircle2,
        badgeClass: "badge-success",
        type: "in_stock",
    };
};

// Watch for search changes with debounce
watch(
    search,
    debounce((value) => {
        updateFilters({ search: value, page: 1 });
    }, 350)
);

// Clear search helper
const clearSearch = () => {
    search.value = "";
    updateFilters({ search: "", page: 1 });
};

// Handle sorting
const handleSort = (field, direction) => {
    sortField.value = field;
    sortDirection.value = direction;
    updateFilters({
        sortField: field,
        sortDirection: direction,
        page: 1,
    });
};

// Update filters via Inertia router
const updateFilters = (newFilters) => {
    router.get(
        route("products.index"),
        {
            search: search.value || undefined,
            sortField: sortField.value,
            sortDirection: sortDirection.value,
            page: props.products.current_page || 1,
            ...newFilters,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

// Pagination change
const changePage = (pageNumber) => {
    if (
        pageNumber < 1 ||
        pageNumber > props.products.last_page ||
        pageNumber === props.products.current_page
    ) {
        return;
    }
    updateFilters({ page: pageNumber });
};

// Smart pagination numbers with windowing
const displayedPages = computed(() => {
    const current = props.products.current_page || 1;
    const last = props.products.last_page || 1;
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

// Client-side filtering on current page for category & stock status
const filteredProducts = computed(() => {
    if (!props.products?.data) return [];
    let list = [...props.products.data];

    if (selectedCategory.value) {
        list = list.filter(
            (p) => String(p.category_id) === String(selectedCategory.value)
        );
    }

    if (selectedStockStatus.value !== "all") {
        if (selectedStockStatus.value === "in_stock") {
            list = list.filter((p) => Number(p.stock) > 5);
        } else if (selectedStockStatus.value === "low_stock") {
            list = list.filter(
                (p) => Number(p.stock) > 0 && Number(p.stock) <= 5
            );
        } else if (selectedStockStatus.value === "out_of_stock") {
            list = list.filter((p) => Number(p.stock) <= 0);
        }
    }

    return list;
});

// KPI Quick Stats computed from current page data
const stats = computed(() => {
    const all = props.products?.data || [];
    const totalCount = props.products?.total || 0;
    const inStock = all.filter((p) => Number(p.stock) > 5).length;
    const lowStock = all.filter(
        (p) => Number(p.stock) > 0 && Number(p.stock) <= 5
    ).length;
    const outOfStock = all.filter((p) => Number(p.stock) <= 0).length;

    return {
        total: totalCount,
        inStock,
        lowStock,
        outOfStock,
    };
});

// Toggle stock filter via clicking KPI card
const toggleStockFilter = (status) => {
    if (selectedStockStatus.value === status) {
        selectedStockStatus.value = "all";
    } else {
        selectedStockStatus.value = status;
    }
};

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return (
        Boolean(search.value) ||
        Boolean(selectedCategory.value) ||
        selectedStockStatus.value !== "all" ||
        sortField.value !== "created_at" ||
        sortDirection.value !== "desc"
    );
});

// Reset all filters
const resetAllFilters = () => {
    search.value = "";
    selectedCategory.value = "";
    selectedStockStatus.value = "all";
    sortField.value = "created_at";
    sortDirection.value = "desc";
    updateFilters({
        search: "",
        sortField: "created_at",
        sortDirection: "desc",
        page: 1,
    });
};

// Delete Product Modal state
const showDeleteModal = ref(false);
const productToDelete = ref(null);
const deleteForm = useForm({});

const openDeleteModal = (product) => {
    productToDelete.value = product;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    productToDelete.value = null;
};

const confirmDelete = () => {
    if (!productToDelete.value) return;

    deleteForm.delete(route("products.destroy", productToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal();
            showNotification("Produk berhasil dihapus dari inventaris!", "success");
        },
        onError: (errors) => {
            const errorMessages = Object.values(errors).flat();
            showNotification(
                errorMessages[0] || "Terjadi kesalahan saat menghapus produk",
                "error"
            );
        },
    });
};

// Quick Preview Modal state
const showPreviewModal = ref(false);
const previewProduct = ref(null);
const activePreviewImageIndex = ref(0);

const openPreviewModal = (product) => {
    previewProduct.value = product;
    activePreviewImageIndex.value = 0;
    showPreviewModal.value = true;
};

const closePreviewModal = () => {
    showPreviewModal.value = false;
    previewProduct.value = null;
    activePreviewImageIndex.value = 0;
};
</script>

<template>

    <Head title="Manajemen Produk" />

    <AdminLayout pageTitle="Manajemen Produk">
        <!-- Toast Notification Component -->
        <ToastNotification :message="toastMessage" :type="toastType" :show="showToast" @close="closeToast" />

        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2">
                <div>
                    <h1 class="text-2xl font-bold text-stone-900 tracking-tight flex items-center gap-2.5">
                        <Package class="w-7 h-7 text-orange-600" />
                        Daftar Produk & Inventaris
                    </h1>
                    <p class="text-sm text-stone-500 mt-1">
                        Kelola katalog produk, pantau ketersediaan stok, harga, dan varian toko Anda.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link :href="route('products.create')"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-orange-600 hover:bg-orange-700 active:bg-orange-800 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                        <Plus class="w-4 h-4 stroke-[2.5]" />
                        <span>Tambah Produk</span>
                    </Link>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- Total Products -->
                <div @click="toggleStockFilter('all')" :class="[
                    'p-4 rounded-2xl border transition-all duration-200 cursor-pointer relative overflow-hidden',
                    selectedStockStatus === 'all'
                        ? 'bg-orange-50/70 border-orange-300 ring-2 ring-orange-500/20 shadow-sm'
                        : 'bg-white border-stone-200/80 hover:border-orange-200 hover:shadow-sm',
                ]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">
                            Total Produk
                        </span>
                        <div class="p-2 rounded-xl bg-orange-100 text-orange-700">
                            <Package class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-stone-900">
                            {{ stats.total }}
                        </span>
                        <span class="text-xs text-stone-400">item</span>
                    </div>
                </div>

                <!-- In Stock -->
                <div @click="toggleStockFilter('in_stock')" :class="[
                    'p-4 rounded-2xl border transition-all duration-200 cursor-pointer relative overflow-hidden',
                    selectedStockStatus === 'in_stock'
                        ? 'bg-emerald-50/70 border-emerald-300 ring-2 ring-emerald-500/20 shadow-sm'
                        : 'bg-white border-stone-200/80 hover:border-emerald-200 hover:shadow-sm',
                ]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">
                            Stok Aman (>5)
                        </span>
                        <div class="p-2 rounded-xl bg-emerald-100 text-emerald-700">
                            <CheckCircle2 class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-emerald-700">
                            {{ stats.inStock }}
                        </span>
                        <span class="text-xs text-stone-400">di halaman ini</span>
                    </div>
                </div>

                <!-- Low Stock -->
                <div @click="toggleStockFilter('low_stock')" :class="[
                    'p-4 rounded-2xl border transition-all duration-200 cursor-pointer relative overflow-hidden',
                    selectedStockStatus === 'low_stock'
                        ? 'bg-amber-50/70 border-amber-300 ring-2 ring-amber-500/20 shadow-sm'
                        : 'bg-white border-stone-200/80 hover:border-amber-200 hover:shadow-sm',
                ]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">
                            Stok Menipis (1-5)
                        </span>
                        <div class="p-2 rounded-xl bg-amber-100 text-amber-700">
                            <AlertTriangle class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-amber-700">
                            {{ stats.lowStock }}
                        </span>
                        <span class="text-xs text-stone-400">perlu restock</span>
                    </div>
                </div>

                <!-- Out of Stock -->
                <div @click="toggleStockFilter('out_of_stock')" :class="[
                    'p-4 rounded-2xl border transition-all duration-200 cursor-pointer relative overflow-hidden',
                    selectedStockStatus === 'out_of_stock'
                        ? 'bg-red-50/70 border-red-300 ring-2 ring-red-500/20 shadow-sm'
                        : 'bg-white border-stone-200/80 hover:border-red-200 hover:shadow-sm',
                ]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">
                            Stok Habis (0)
                        </span>
                        <div class="p-2 rounded-xl bg-red-100 text-red-700">
                            <XCircle class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-red-700">
                            {{ stats.outOfStock }}
                        </span>
                        <span class="text-xs text-stone-400">habis</span>
                    </div>
                </div>
            </div>

            <!-- Main Card Container -->
            <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
                <!-- Search & Filters Toolbar -->
                <div class="p-4 sm:p-5 border-b border-stone-100 bg-stone-50/50 space-y-3">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                        <!-- Search Bar -->
                        <div class="relative flex-1 min-w-[260px]">
                            <Search
                                class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" />
                            <input type="text" v-model="search" placeholder="Cari nama produk atau deskripsi..."
                                class="w-full pl-10 pr-9 py-2.5 text-sm bg-white border border-stone-200 rounded-xl placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors" />
                            <button v-if="search" @click="clearSearch" type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-0.5 rounded-full text-stone-400 hover:text-stone-600 hover:bg-stone-100"
                                title="Hapus pencarian">
                                <X class="w-3.5 h-3.5" />
                            </button>
                        </div>

                        <!-- Dropdowns: Category, Stock Status, Sort, View Toggle -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <!-- Category Filter Dropdown -->
                            <div class="relative min-w-[150px]">
                                <select v-model="selectedCategory"
                                    class="w-full appearance-none bg-none pl-3.5 pr-8 py-2.5 text-sm bg-white border border-stone-200 rounded-xl text-stone-700 font-medium focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors cursor-pointer">
                                    <option value="">Semua Kategori</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <Tag
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" />
                            </div>

                            <!-- Stock Filter Dropdown -->
                            <div class="relative min-w-[140px]">
                                <select v-model="selectedStockStatus"
                                    class="w-full appearance-none bg-none pl-3.5 pr-8 py-2.5 text-sm bg-white border border-stone-200 rounded-xl text-stone-700 font-medium focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors cursor-pointer">
                                    <option value="all">Semua Stok</option>
                                    <option value="in_stock">Tersedia (>5)</option>
                                    <option value="low_stock">Menipis (1-5)</option>
                                    <option value="out_of_stock">Habis (0)</option>
                                </select>
                                <SlidersHorizontal
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" />
                            </div>

                            <!-- Sort Dropdown -->
                            <div class="relative min-w-[160px]">
                                <select v-model="selectedSortKey"
                                    class="w-full appearance-none bg-none pl-3.5 pr-8 py-2.5 text-sm bg-white border border-stone-200 rounded-xl text-stone-700 font-medium focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors cursor-pointer">
                                    <option v-for="opt in sortOptions" :key="`${opt.field}:${opt.direction}`"
                                        :value="`${opt.field}:${opt.direction}`">
                                        Urut: {{ opt.label }}
                                    </option>
                                </select>
                                <ArrowUpDown
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" />
                            </div>

                            <!-- View Mode Toggle -->
                            <div class="flex items-center bg-stone-200/70 p-1 rounded-xl">
                                <button type="button" @click="setViewMode('table')" :class="[
                                    'p-1.5 rounded-lg text-sm font-medium transition-all duration-150',
                                    viewMode === 'table'
                                        ? 'bg-white text-stone-900 shadow-xs'
                                        : 'text-stone-500 hover:text-stone-800',
                                ]" title="Tampilan Tabel">
                                    <List class="w-4 h-4" />
                                </button>
                                <button type="button" @click="setViewMode('grid')" :class="[
                                    'p-1.5 rounded-lg text-sm font-medium transition-all duration-150',
                                    viewMode === 'grid'
                                        ? 'bg-white text-stone-900 shadow-xs'
                                        : 'text-stone-500 hover:text-stone-800',
                                ]" title="Tampilan Grid">
                                    <LayoutGrid class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Active Filters Pill Bar -->
                    <div v-if="hasActiveFilters"
                        class="flex flex-wrap items-center gap-2 pt-2 border-t border-stone-200/60">
                        <span class="text-xs font-medium text-stone-500 flex items-center gap-1">
                            <Filter class="w-3.5 h-3.5 text-stone-400" />
                            Filter Aktif:
                        </span>

                        <!-- Search Chip -->
                        <span v-if="search"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-orange-50 text-orange-800 border border-orange-200">
                            Cari: "{{ search }}"
                            <button @click="clearSearch" class="hover:text-orange-950">
                                <X class="w-3 h-3" />
                            </button>
                        </span>

                        <!-- Category Chip -->
                        <span v-if="selectedCategory"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-orange-50 text-orange-800 border border-orange-200">
                            Kategori: {{ getCategoryName(Number(selectedCategory)) }}
                            <button @click="selectedCategory = ''" class="hover:text-orange-950">
                                <X class="w-3 h-3" />
                            </button>
                        </span>

                        <!-- Stock Status Chip -->
                        <span v-if="selectedStockStatus !== 'all'"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-orange-50 text-orange-800 border border-orange-200">
                            Stok: {{ selectedStockStatus === 'in_stock' ? 'Tersedia (>5)' : selectedStockStatus ===
                                'low_stock' ? 'Menipis (1-5)' : 'Habis (0)' }}
                            <button @click="selectedStockStatus = 'all'" class="hover:text-orange-950">
                                <X class="w-3 h-3" />
                            </button>
                        </span>

                        <!-- Reset All -->
                        <button @click="resetAllFilters"
                            class="text-xs font-semibold text-orange-600 hover:text-orange-700 hover:underline ml-1 inline-flex items-center gap-1">
                            <RotateCcw class="w-3 h-3" />
                            Reset Semua Filter
                        </button>
                    </div>
                </div>

                <!-- TABLE VIEW -->
                <div v-if="viewMode === 'table'" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-stone-50/75 border-b border-stone-200 text-stone-500 text-xs uppercase tracking-wider font-semibold">
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-4 min-w-[280px]">Produk</th>
                                <th class="py-3.5 px-4 min-w-[140px]">Kategori</th>
                                <th class="py-3.5 px-4 min-w-[130px]">Harga</th>
                                <th class="py-3.5 px-4 min-w-[130px]">Stok</th>
                                <th class="py-3.5 px-4 min-w-[100px]">Berat</th>
                                <th class="py-3.5 px-4 text-center min-w-[140px]">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-stone-100 text-sm">
                            <tr v-for="(item, index) in filteredProducts" :key="item.id"
                                class="hover:bg-stone-50/80 transition-colors group">
                                <!-- Index Number -->
                                <td class="py-3.5 px-4 text-center font-medium text-stone-400 text-xs">
                                    {{
                                        ((products.current_page || 1) - 1) *
                                        (products.per_page || 10) +
                                        index +
                                        1
                                    }}
                                </td>

                                <!-- Product Info (Thumbnail + Name + Slug) -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <!-- Thumbnail -->
                                        <div class="relative w-12 h-12 rounded-xl bg-stone-100 border border-stone-200/80 overflow-hidden shrink-0 flex items-center justify-center cursor-pointer group/img"
                                            @click="openPreviewModal(item)" title="Klik untuk pratinjau cepat">
                                            <img v-if="getProductThumbnail(item)" :src="getProductThumbnail(item)"
                                                :alt="item.name"
                                                class="w-full h-full object-cover transition-transform duration-300 group-hover/img:scale-110"
                                                @error="(e) => e.target.style.display = 'none'" />
                                            <ImageOff v-else class="w-5 h-5 text-stone-300" />

                                            <!-- Multi image indicator -->
                                            <span v-if="parseImages(item).length > 1"
                                                class="absolute bottom-0.5 right-0.5 bg-black/70 text-white text-[9px] font-bold px-1 rounded">
                                                +{{ parseImages(item).length - 1 }}
                                            </span>
                                        </div>

                                        <!-- Details -->
                                        <div class="min-w-0 flex-1">
                                            <button type="button" @click="openPreviewModal(item)"
                                                class="text-left font-semibold text-stone-800 hover:text-orange-600 transition-colors truncate block max-w-[240px] sm:max-w-xs"
                                                :title="item.name">
                                                {{ item.name }}
                                            </button>
                                            <div class="flex items-center gap-2 mt-0.5 text-xs text-stone-400">
                                                <span class="truncate max-w-[180px]">
                                                    {{ item.slug || `ID #${item.id}` }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-stone-100 text-stone-700 border border-stone-200">
                                        {{ getCategoryName(item.category_id) }}
                                    </span>
                                </td>

                                <!-- Price -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-bold text-stone-900">
                                        {{ formatPrice(item.price) }}
                                    </span>
                                </td>

                                <!-- Stock Status -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span :class="[
                                            'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border',
                                            getStockStatus(item.stock).colorClass,
                                        ]">
                                            <span :class="[
                                                'w-1.5 h-1.5 rounded-full',
                                                getStockStatus(item.stock).dotClass,
                                            ]" />
                                            {{ item.stock }}
                                            <span class="text-[10px] font-medium opacity-80">
                                                ({{ getStockStatus(item.stock).label }})
                                            </span>
                                        </span>
                                    </div>
                                </td>

                                <!-- Weight -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-xs text-stone-600">
                                    <span v-if="item.weight" class="inline-flex items-center gap-1">
                                        <Scale class="w-3.5 h-3.5 text-stone-400" />
                                        {{ item.weight }} g
                                    </span>
                                    <span v-else class="text-stone-300">-</span>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                    <div class="inline-flex items-center justify-center gap-1">
                                        <!-- Quick Preview -->
                                        <button type="button" @click="openPreviewModal(item)"
                                            class="p-2 rounded-lg text-stone-500 hover:text-stone-800 hover:bg-stone-100 transition-colors"
                                            title="Pratinjau Cepat">
                                            <Eye class="w-4 h-4" />
                                        </button>

                                        <!-- Edit -->
                                        <Link :href="route('products.edit', item.id)"
                                            class="p-2 rounded-lg text-blue-600 hover:text-blue-800 hover:bg-blue-50 transition-colors"
                                            title="Edit Produk">
                                            <Edit3 class="w-4 h-4" />
                                        </Link>

                                        <!-- Delete -->
                                        <button type="button" @click="openDeleteModal(item)"
                                            class="p-2 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors"
                                            title="Hapus Produk">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- GRID / CARD VIEW -->
                <div v-else class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
                        <div v-for="item in filteredProducts" :key="item.id"
                            class="group bg-white rounded-2xl border border-stone-200/80 hover:border-orange-300 hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col">
                            <!-- Card Image with Overlays -->
                            <div class="relative aspect-4/3 bg-stone-100 overflow-hidden">
                                <img v-if="getProductThumbnail(item)" :src="getProductThumbnail(item)" :alt="item.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    @error="(e) => e.target.style.display = 'none'" />
                                <div v-else
                                    class="w-full h-full flex flex-col items-center justify-center text-stone-300 gap-1">
                                    <ImageOff class="w-8 h-8" />
                                    <span class="text-xs">Tidak ada foto</span>
                                </div>

                                <!-- Top Badges -->
                                <div
                                    class="absolute top-2.5 inset-x-2.5 flex items-center justify-between pointer-events-none">
                                    <span
                                        class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-white/90 backdrop-blur-xs text-stone-800 shadow-xs">
                                        {{ getCategoryName(item.category_id) }}
                                    </span>

                                    <span :class="[
                                        'px-2 py-0.5 rounded-md text-[11px] font-semibold shadow-xs',
                                        getStockStatus(item.stock).colorClass,
                                        'bg-white/95 backdrop-blur-xs',
                                    ]">
                                        Stok: {{ item.stock }}
                                    </span>
                                </div>

                                <!-- Multi image indicator -->
                                <div v-if="parseImages(item).length > 1"
                                    class="absolute bottom-2.5 right-2.5 bg-black/60 backdrop-blur-xs text-white text-[10px] font-semibold px-1.5 py-0.5 rounded-md flex items-center gap-1">
                                    <Layers class="w-3 h-3" />
                                    {{ parseImages(item).length }}
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                                <div>
                                    <button type="button" @click="openPreviewModal(item)"
                                        class="text-left font-semibold text-stone-800 hover:text-orange-600 line-clamp-1 transition-colors text-base"
                                        :title="item.name">
                                        {{ item.name }}
                                    </button>
                                    <p class="text-xs text-stone-400 mt-0.5 line-clamp-2">
                                        {{ item.description || "Tidak ada deskripsi." }}
                                    </p>
                                </div>

                                <div class="pt-2 border-t border-stone-100 flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-stone-400 block">
                                            Harga
                                        </span>
                                        <span class="font-extrabold text-stone-900 text-base">
                                            {{ formatPrice(item.price) }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="openPreviewModal(item)"
                                            class="p-1.5 rounded-lg text-stone-500 hover:text-stone-800 hover:bg-stone-100 transition-colors"
                                            title="Pratinjau">
                                            <Eye class="w-4 h-4" />
                                        </button>
                                        <Link :href="route('products.edit', item.id)"
                                            class="p-1.5 rounded-lg text-blue-600 hover:text-blue-800 hover:bg-blue-50 transition-colors"
                                            title="Edit">
                                            <Edit3 class="w-4 h-4" />
                                        </Link>
                                        <button type="button" @click="openDeleteModal(item)"
                                            class="p-1.5 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors"
                                            title="Hapus">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EMPTY STATE: When filtered products is 0 -->
                <div v-if="filteredProducts.length === 0"
                    class="py-16 px-4 text-center flex flex-col items-center justify-center">
                    <div
                        class="w-16 h-16 rounded-2xl bg-stone-100 text-stone-400 flex items-center justify-center mb-4">
                        <Package class="w-8 h-8 text-stone-400" />
                    </div>

                    <h3 class="text-base font-bold text-stone-800">
                        {{
                            hasActiveFilters
                                ? "Tidak ada produk yang cocok"
                                : "Belum ada produk di inventaris"
                        }}
                    </h3>

                    <p class="text-sm text-stone-500 max-w-sm mt-1 mb-5">
                        {{
                            hasActiveFilters
                                ? "Cobalah mengubah kata kunci pencarian atau sesuaikan filter kategori dan status stok."
                                : "Mulai tambahkan produk pertama Anda untuk menampilkan katalog barang di toko."
                        }}
                    </p>

                    <div class="flex items-center gap-3">
                        <button v-if="hasActiveFilters" @click="resetAllFilters"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 transition-colors">
                            <RotateCcw class="w-4 h-4" />
                            Reset Semua Filter
                        </button>

                        <Link :href="route('products.create')"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl bg-orange-600 hover:bg-orange-700 text-white transition-colors">
                            <Plus class="w-4 h-4 stroke-[2.5]" />
                            Tambah Produk Baru
                        </Link>
                    </div>
                </div>

                <!-- PAGINATION CONTROLS -->
                <div v-if="products.total > 0"
                    class="p-4 sm:p-5 border-t border-stone-100 bg-stone-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <!-- Pagination Summary -->
                    <div class="text-xs sm:text-sm text-stone-500">
                        Menampilkan
                        <span class="font-semibold text-stone-800">{{ products.from || 0 }}</span>
                        sampai
                        <span class="font-semibold text-stone-800">{{ products.to || 0 }}</span>
                        dari
                        <span class="font-semibold text-stone-800">{{ products.total }}</span>
                        produk
                    </div>

                    <!-- Page Buttons -->
                    <div v-if="products.last_page > 1" class="flex items-center gap-1.5 self-center sm:self-auto">
                        <!-- Prev -->
                        <button @click="changePage(products.current_page - 1)" :disabled="products.current_page <= 1"
                            class="p-2 rounded-xl border border-stone-200 bg-white text-stone-600 hover:bg-stone-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                            title="Halaman Sebelumnya">
                            <ChevronLeft class="w-4 h-4" />
                        </button>

                        <!-- Numbers -->
                        <template v-for="(pageNum, idx) in displayedPages" :key="idx">
                            <span v-if="pageNum === '...'" class="px-2 py-1 text-xs text-stone-400 font-bold">
                                ...
                            </span>
                            <button v-else @click="changePage(pageNum)" :class="[
                                'min-w-[36px] h-9 px-2.5 rounded-xl text-xs font-bold transition-all duration-150',
                                pageNum === products.current_page
                                    ? 'bg-orange-600 text-white shadow-xs'
                                    : 'bg-white border border-stone-200 text-stone-700 hover:bg-stone-50',
                            ]">
                                {{ pageNum }}
                            </button>
                        </template>

                        <!-- Next -->
                        <button @click="changePage(products.current_page + 1)"
                            :disabled="products.current_page >= products.last_page"
                            class="p-2 rounded-xl border border-stone-200 bg-white text-stone-600 hover:bg-stone-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                            title="Halaman Selanjutnya">
                            <ChevronRight class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- DELETE CONFIRMATION MODAL -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal" maxWidth="md">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                        <AlertTriangle class="w-6 h-6 stroke-[2]" />
                    </div>

                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-stone-900">
                            Hapus Produk?
                        </h3>
                        <p class="text-sm text-stone-500 mt-1">
                            Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan dan produk
                            akan
                            dihapus secara permanen dari katalog toko.
                        </p>
                    </div>
                </div>

                <!-- Product Preview Snapshot inside Modal -->
                <div v-if="productToDelete"
                    class="mt-4 p-3 rounded-xl bg-stone-50 border border-stone-200/80 flex items-center gap-3">
                    <div
                        class="w-12 h-12 rounded-lg bg-stone-200 overflow-hidden shrink-0 flex items-center justify-center">
                        <img v-if="getProductThumbnail(productToDelete)" :src="getProductThumbnail(productToDelete)"
                            :alt="productToDelete.name" class="w-full h-full object-cover" />
                        <ImageOff v-else class="w-5 h-5 text-stone-400" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-stone-900 truncate">
                            {{ productToDelete.name }}
                        </p>
                        <p class="text-xs text-stone-500 mt-0.5">
                            {{ getCategoryName(productToDelete.category_id) }} • Stok: {{ productToDelete.stock }}
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" @click="closeDeleteModal" :disabled="deleteForm.processing"
                        class="px-4 py-2.5 text-sm font-semibold text-stone-700 bg-white border border-stone-300 rounded-xl hover:bg-stone-50 transition-colors disabled:opacity-50">
                        Batal
                    </button>

                    <button type="button" @click="confirmDelete" :disabled="deleteForm.processing"
                        class="px-4 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 active:bg-red-800 rounded-xl shadow-xs transition-colors disabled:opacity-50 inline-flex items-center gap-2">
                        <span v-if="deleteForm.processing" class="loading loading-spinner loading-xs" />
                        <span>{{ deleteForm.processing ? "Menghapus..." : "Ya, Hapus Produk" }}</span>
                    </button>
                </div>
            </div>
        </Modal>

        <!-- QUICK PRODUCT DETAILS PREVIEW MODAL -->
        <Modal :show="showPreviewModal" @close="closePreviewModal" maxWidth="2xl">
            <div v-if="previewProduct" class="p-6">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                    <div class="flex items-center gap-2">
                        <Sparkles class="w-5 h-5 text-orange-600" />
                        <h3 class="text-lg font-bold text-stone-900">
                            Pratinjau Cepat Produk
                        </h3>
                    </div>
                    <button type="button" @click="closePreviewModal"
                        class="p-1.5 rounded-lg text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Modal Content: 2-column on desktop -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-5">
                    <!-- Gallery Preview -->
                    <div class="space-y-3">
                        <div
                            class="aspect-square rounded-2xl bg-stone-100 border border-stone-200 overflow-hidden flex items-center justify-center">
                            <img v-if="parseImages(previewProduct)[activePreviewImageIndex]"
                                :src="parseImages(previewProduct)[activePreviewImageIndex]" :alt="previewProduct.name"
                                class="w-full h-full object-cover" />
                            <div v-else class="text-stone-300 flex flex-col items-center">
                                <ImageOff class="w-10 h-10 mb-1" />
                                <span class="text-xs">Foto tidak tersedia</span>
                            </div>
                        </div>

                        <!-- Thumbnails Row -->
                        <div v-if="parseImages(previewProduct).length > 1"
                            class="flex items-center gap-2 overflow-x-auto pb-1">
                            <button v-for="(img, idx) in parseImages(previewProduct)" :key="idx" type="button"
                                @click="activePreviewImageIndex = idx" :class="[
                                    'w-14 h-14 rounded-xl overflow-hidden border-2 shrink-0 transition-all',
                                    activePreviewImageIndex === idx
                                        ? 'border-orange-600 ring-2 ring-orange-500/20'
                                        : 'border-stone-200 opacity-60 hover:opacity-100',
                                ]">
                                <img :src="img" class="w-full h-full object-cover" />
                            </button>
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="space-y-4">
                        <div>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-stone-100 text-stone-700 mb-2">
                                {{ getCategoryName(previewProduct.category_id) }}
                            </span>
                            <h4 class="text-xl font-bold text-stone-900 leading-snug">
                                {{ previewProduct.name }}
                            </h4>
                            <p class="text-xs text-stone-400 mt-1">
                                Slug: {{ previewProduct.slug || '-' }}
                            </p>
                        </div>

                        <div
                            class="p-3.5 rounded-xl bg-orange-50/60 border border-orange-100 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-bold uppercase text-orange-700 block">
                                    Harga Jual
                                </span>
                                <span class="text-2xl font-black text-stone-900">
                                    {{ formatPrice(previewProduct.price) }}
                                </span>
                            </div>

                            <span :class="[
                                'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border',
                                getStockStatus(previewProduct.stock).colorClass,
                            ]">
                                <span :class="[
                                    'w-1.5 h-1.5 rounded-full',
                                    getStockStatus(previewProduct.stock).dotClass,
                                ]" />
                                Stok: {{ previewProduct.stock }}
                            </span>
                        </div>

                        <!-- Meta Attributes: Weight, Sizes, Colors -->
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between py-1.5 border-b border-stone-100">
                                <span class="text-stone-500 font-medium">Berat Barang:</span>
                                <span class="text-stone-800 font-semibold">{{ previewProduct.weight ?
                                    `${previewProduct.weight}
                                    gram` : '-' }}</span>
                            </div>

                            <div v-if="previewProduct.sizes && previewProduct.sizes.length > 0"
                                class="flex items-center justify-between py-1.5 border-b border-stone-100">
                                <span class="text-stone-500 font-medium">Varian Ukuran:</span>
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="s in previewProduct.sizes" :key="s"
                                        class="px-2 py-0.5 rounded bg-stone-100 text-stone-700 font-medium text-[11px]">
                                        {{ s }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="previewProduct.colors && previewProduct.colors.length > 0"
                                class="flex items-center justify-between py-1.5 border-b border-stone-100">
                                <span class="text-stone-500 font-medium">Varian Warna:</span>
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="c in previewProduct.colors" :key="c"
                                        class="px-2 py-0.5 rounded bg-stone-100 text-stone-700 font-medium text-[11px]">
                                        {{ c }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <span class="text-xs font-bold text-stone-500 uppercase tracking-wider block mb-1">
                                Deskripsi
                            </span>
                            <p
                                class="text-xs text-stone-600 bg-stone-50 p-3 rounded-xl border border-stone-200/60 max-h-32 overflow-y-auto whitespace-pre-line leading-relaxed">
                                {{ previewProduct.description || "Tidak ada deskripsi produk." }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between">
                    <Link v-if="previewProduct.slug" :href="`/product/${previewProduct.slug}`" target="_blank"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-stone-500 hover:text-stone-800 transition-colors">
                        <ExternalLink class="w-3.5 h-3.5" />
                        Lihat Halaman Toko
                    </Link>
                    <span v-else />

                    <div class="flex items-center gap-2">
                        <Link :href="route('products.edit', previewProduct.id)"
                            class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors inline-flex items-center gap-1.5">
                            <Edit3 class="w-3.5 h-3.5" />
                            Edit Produk Ini
                        </Link>
                        <button type="button" @click="closePreviewModal"
                            class="px-4 py-2 text-xs font-semibold text-stone-700 bg-stone-100 hover:bg-stone-200 rounded-xl transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

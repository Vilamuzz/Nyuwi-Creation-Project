<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, nextTick, computed } from "vue";
import debounce from "lodash/debounce";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Hero from "@/Components/Customer/Main/Hero.vue";
import Product from "@/Components/Customer/Sub-main/Product.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { ChevronDown, Settings2, X } from "lucide-vue-next";

const props = defineProps({
    products: {
        type: Object,
        default: () => ({ data: [], last_page: 1, current_page: 1 }),
    },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const products = computed(() => props.products.data || []);
const categories = computed(() => props.categories);
const meta = computed(() => props.products);
const isLoading = ref(false);
const error = ref(null);
const isFilterOpen = ref(false);

// Filter states
const search = ref("");
const sortField = ref("created_at");
const sortDirection = ref("desc");
const currentPage = ref(props.products.current_page || 1);
search.value = props.filters.search || "";
sortField.value = props.filters.sortField || "created_at";
sortDirection.value = props.filters.sortDirection || "desc";

const fetchShopData = () => {
    error.value = null;
    router.get(
        route("flowers"),
        {
            search: search.value,
            sortField: sortField.value,
            sortDirection: sortDirection.value,
            page: currentPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onStart: () => {
                isLoading.value = true;
            },
            onFinish: () => {
                isLoading.value = false;
            },
            onError: () => {
                error.value = "Failed to load flowers";
            },
        },
    );
};

// Watch for search changes with debounce
watch(
    search,
    debounce((value) => {
        currentPage.value = 1;
        fetchShopData();
    }, 300),
);

// Handle sorting
const handleSort = (event) => {
    const [field, direction] = event.target.value.split("|");
    sortField.value = field;
    sortDirection.value = direction;
    currentPage.value = 1;
    fetchShopData();
};

// Handle pagination with instant scroll to top
const changePage = async (page) => {
    currentPage.value = page;
    fetchShopData();

    await nextTick();
    window.scrollTo({
        top: 0,
        behavior: "auto",
    });
};

const getCategoryName = (categoryId) => {
    const category = categories.value.find((cat) => cat.id === categoryId);
    return category ? category.name : "Flowers";
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
};
</script>

<template>
    <Head title="Flowers" />
    <CustomersLayout>
        <Hero title="FLOWERS" subtitle="Fresh & artificial handmade flower arrangements" />
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <section class="mt-8 sm:mt-16 flex items-center justify-between">
                <Breadcrumb :items="[
                    { label: 'Home', href: '/' },
                    { label: 'Flowers' }
                ]" />
                <button type="button" class="flex items-center space-x-2 hover:underline" aria-controls="flowers-filters"
                    :aria-expanded="isFilterOpen" @click="isFilterOpen = true">
                    <Settings2 /> <span>FILTERS</span>
                </button>
            </section>

            <section class="mb-20">
                <div class="flex flex-col items-center">
                    <!-- Loading state -->
                    <div v-if="isLoading" class="text-center py-8">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
                        <p class="mt-4 text-gray-600">Loading flowers...</p>
                    </div>

                    <!-- Error state -->
                    <div v-else-if="error" class="text-center py-8 text-red-500">
                        <p>{{ error }}</p>
                        <button @click="fetchShopData"
                            class="mt-4 px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                            Retry
                        </button>
                    </div>

                    <!-- Products grid -->
                    <div v-else class="flex flex-col items-center space-y-8 my-14 products-grid">
                        <div v-if="products.length > 0" class="grid grid-cols-2 gap-3 sm:gap-6 lg:grid-cols-3 xl:grid-cols-4">
                            <Product v-for="(item, index) in products" :key="index" :id="item.id" :slug="item.slug"
                                :name="item.name" :price="formatPrice(item.price)"
                                :category="getCategoryName(item.category_id)" :image="item.image ||
                                    (item.images && item.images[0]) ||
                                    null
                                    " :rating="Number(item.average_rating) || 0"
                                :total-reviews="Number(item.total_reviews) || 0" :stock="item.stock"
                                :colors="item.colors" :sizes="item.sizes" />
                        </div>

                        <!-- Empty state -->
                        <div v-else class="text-center py-16 text-gray-500">
                            <p class="text-lg font-medium">No flowers found</p>
                            <p class="text-sm mt-1">Check back soon for new flower arrangements!</p>
                        </div>

                        <!-- Pagination -->
                        <div v-if="meta.last_page > 1" class="flex gap-2">
                            <button v-for="page in meta.last_page" :key="page" @click="changePage(page)" :class="[
                                'py-2 px-4 rounded-md border',
                                page === meta.current_page
                                    ? 'bg-orange-500 text-white border-orange-500'
                                    : 'bg-orange-100 hover:bg-orange-500 hover:text-white duration-300',
                            ]">
                                {{ page }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </CustomersLayout>
    <Transition appear enter-active-class="transition-opacity duration-300 ease-out" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition-opacity duration-300 ease-in"
        leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isFilterOpen" class="fixed inset-0 z-50 bg-black/30 transition-opacity duration-300 ease-out"
            @click="isFilterOpen = false" />
    </Transition>
    <Transition appear enter-active-class="transition-transform duration-300 ease-out"
        enter-from-class="translate-y-full md:translate-y-0 md:translate-x-full"
        enter-to-class="translate-y-0 md:translate-x-0" leave-active-class="transition-transform duration-300 ease-in"
        leave-from-class="translate-y-0 md:translate-x-0"
        leave-to-class="translate-y-full md:translate-y-0 md:translate-x-full">
        <div v-if="isFilterOpen" id="flowers-filters"
            class="fixed inset-0 z-[51] overflow-hidden pointer-events-none p-4 flex items-end justify-center md:items-stretch md:justify-end">
            <div
                class="pointer-events-auto h-3/4 max-h-[80vh] w-full max-w-md bg-white flex flex-col justify-between pb-6 shadow-xl md:h-full md:max-h-none rounded-2xl">
                <div>
                    <div class="flex h-20 w-full px-10 items-center justify-between border-b border-slate-500">
                        <h1 class="font-bold text-2xl tracking-wider">
                            FILTERS
                        </h1>
                        <button type="button" aria-label="Close filters" @click="isFilterOpen = false">
                            <X />
                        </button>
                    </div>

                    <div class="flex flex-col px-10 text-xl">
                        <div class="flex items-center py-6 justify-between border-b border-slate-500">
                            <p>Warna</p>
                            <ChevronDown />
                        </div>
                        <div class="flex items-center py-6 justify-between border-b border-slate-500">
                            <p>Ukuran</p>
                            <ChevronDown />
                        </div>
                    </div>
                </div>

                <div class="flex justify-center px-6">
                    <button
                        class="w-full h-auto p-4 rounded-lg bg-slate-700 text-white hover:bg-white hover:text-slate-700 hover:ring-slate-700 hover:ring-4 transition-colors ring-inset">
                        APPLY (0)
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

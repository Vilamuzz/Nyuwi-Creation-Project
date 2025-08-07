<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, watch, onMounted, nextTick } from "vue";
import debounce from "lodash/debounce";
import axios from "axios";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Hero from "@/Components/Customer/Main/Hero.vue";
import Product from "@/Components/Customer/Sub-main/Product.vue";

// Reactive data
const products = ref([]);
const categories = ref([]);
const meta = ref({});
const isLoading = ref(true);
const error = ref(null);

// Filter states
const search = ref("");
const sortField = ref("created_at");
const sortDirection = ref("desc");
const selectedCategory = ref("");
const currentPage = ref(1);

// Define sort options
const sortOptions = [
    { field: "created_at", label: "Terbaru", direction: "desc", default: true },
    { field: "created_at", label: "Terlama", direction: "asc" },
    { field: "name", label: "Nama (A-Z)", direction: "asc" },
    { field: "name", label: "Nama (Z-A)", direction: "desc" },
    { field: "price", label: "Harga (Rendah-Tinggi)", direction: "asc" },
    { field: "price", label: "Harga (Tinggi-Rendah)", direction: "desc" },
];

// Fetch data from API
const fetchShopData = async () => {
    try {
        isLoading.value = true;
        const response = await axios.get("/api/shop", {
            params: {
                search: search.value,
                sortField: sortField.value,
                sortDirection: sortDirection.value,
                category: selectedCategory.value,
                page: currentPage.value,
            },
        });

        if (response.data.success) {
            products.value = response.data.data.products;
            categories.value = response.data.data.categories;
            meta.value = response.data.data.meta;
        } else {
            error.value = "Failed to load shop data";
        }
    } catch (err) {
        console.error("Error fetching shop data:", err);
        error.value = "Failed to load shop data";
    } finally {
        isLoading.value = false;
    }
};

// Watch for search changes with debounce
watch(
    search,
    debounce((value) => {
        currentPage.value = 1; // Reset to page 1 on search
        fetchShopData();
    }, 300)
);

// Watch for category changes
watch(selectedCategory, (value) => {
    currentPage.value = 1; // Reset to page 1 on category change
    fetchShopData();
});

// Handle sorting
const handleSort = (event) => {
    const [field, direction] = event.target.value.split("|");
    sortField.value = field;
    sortDirection.value = direction;
    currentPage.value = 1; // Reset to page 1 on sort
    fetchShopData();
};

// Handle pagination with instant scroll to top
const changePage = async (page) => {
    currentPage.value = page;
    await fetchShopData();

    // Instantly scroll to top after data is loaded
    await nextTick();
    window.scrollTo({
        top: 0,
        behavior: "auto", // Changed from "smooth" to "auto" for instant scroll
    });
};

// Fetch data on component mount
onMounted(() => {
    fetchShopData();
});

const getCategoryName = (categoryId) => {
    const category = categories.value.find((cat) => cat.id === categoryId);
    return category ? category.name : "No category";
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
    <Head title="Shopping Page" />
    <CustomersLayout>
        <Hero title="Shop" breadcrumb="Home > Shop" />

        <section class="bg-orange-200">
            <div
                class="w-full bg-[#fdf7f2] py-4 px-24 flex items-center justify-between border-t border-b border-gray-200"
            >
                <div class="flex items-center space-x-4">
                    <span>Filter by</span>
                    <!-- Category Filter Dropdown -->
                    <select
                        v-model="selectedCategory"
                        class="px-4 py-2 border w-32 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                        <option value="">All</option>
                        <option
                            v-for="category in categories"
                            :key="category.id"
                            :value="category.id"
                        >
                            {{ category.name }}
                        </option>
                    </select>

                    <!-- Search Input -->
                    <div class="relative">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search products..."
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-orange-500"
                        />
                        <img
                            src="/img/icon/search.svg"
                            alt="Search Icon"
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4"
                        />
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span>Sort by</span>
                        <select
                            @change="handleSort"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none"
                            :value="`${sortField}|${sortDirection}`"
                        >
                            <option value="">Default</option>
                            <option
                                v-for="option in sortOptions"
                                :key="`${option.field}-${option.direction}`"
                                :value="`${option.field}|${option.direction}`"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="flex flex-col items-center space-y-8 my-14">
                <!-- Loading state -->
                <div v-if="isLoading" class="text-center py-8">
                    <div
                        class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"
                    ></div>
                    <p class="mt-4 text-gray-600">Loading products...</p>
                </div>

                <!-- Error state -->
                <div v-else-if="error" class="text-center py-8 text-red-500">
                    <p>{{ error }}</p>
                    <button
                        @click="fetchShopData"
                        class="mt-4 px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600"
                    >
                        Retry
                    </button>
                </div>

                <!-- Products grid - Added class for targeting -->
                <div
                    v-else
                    class="flex flex-col items-center space-y-8 my-14 products-grid"
                >
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <Product
                            v-for="(item, index) in products"
                            :key="index"
                            :slug="item.slug"
                            :name="item.name"
                            :price="formatPrice(item.price)"
                            :category="getCategoryName(item.category_id)"
                            :image="
                                item.image ||
                                (item.images && item.images[0]) ||
                                null
                            "
                            :rating="Number(item.average_rating) || 0"
                            :total-reviews="Number(item.total_reviews) || 0"
                        />
                    </div>

                    <!-- Pagination -->
                    <div v-if="meta.last_page > 1" class="flex gap-2">
                        <button
                            v-for="page in meta.last_page"
                            :key="page"
                            @click="changePage(page)"
                            :class="[
                                'py-2 px-4 rounded-md border',
                                page === meta.current_page
                                    ? 'bg-orange-500 text-white border-orange-500'
                                    : 'bg-orange-100 hover:bg-orange-500 hover:text-white duration-300',
                            ]"
                        >
                            {{ page }}
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-[#fdf7f2]">
            <div class="flex justify-center py-20">
                <div class="flex space-x-20">
                    <div class="flex items-center space-x-2">
                        <img
                            src="/img/icon/trophy.svg"
                            alt="High Quality"
                            class="w-11 h-11"
                        />
                        <div>
                            <h4 class="text-lg font-bold">High Quality</h4>
                            <p class="text-gray-600">
                                Crafted from top materials
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <img
                            src="/img/icon/check-badge.svg"
                            alt="Warranty Protection"
                            class="w-12 h-12"
                        />
                        <div>
                            <h4 class="text-lg font-bold">
                                Warranty Protection
                            </h4>
                            <p class="text-gray-600">1 day</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <img
                            src="/img/icon/boxheart.svg"
                            alt="Free Shipping"
                            class="w-10 h-10"
                        />
                        <div>
                            <h4 class="text-lg font-bold">Free Shipping</h4>
                            <p class="text-gray-600">Order over 10 $</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <img
                            src="/img/icon/support.svg"
                            alt="24/7 Support"
                            class="w-11 h-11"
                        />
                        <div>
                            <h4 class="text-lg font-bold">24 / 7 Support</h4>
                            <p class="text-gray-600">Dedicated support</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </CustomersLayout>
</template>

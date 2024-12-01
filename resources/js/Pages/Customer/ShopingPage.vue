<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import debounce from "lodash/debounce";
import { router } from "@inertiajs/vue3";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Hero from "@/Components/Customer/Main/Hero.vue";
import Product from "@/Components/Customer/Sub-main/Product.vue";

const props = defineProps({
    products: Array,
    categories: Array,
    filters: Object,
});

// Add search and sort states
const search = ref(props.filters?.search || "");
const sortField = ref(props.filters?.sortField || "");
const sortDirection = ref(props.filters?.sortDirection || "asc");

// Define sort options
const sortOptions = [
    { field: "name", label: "Name (A-Z)", direction: "asc" },
    { field: "name", label: "Name (Z-A)", direction: "desc" },
    { field: "created_at", label: "Newest", direction: "desc" },
    { field: "created_at", label: "Oldest", direction: "asc" },
    { field: "price", label: "Price (Low-High)", direction: "asc" },
    { field: "price", label: "Price (High-Low)", direction: "desc" },
];

// Watch for search changes with debounce
watch(
    search,
    debounce((value) => {
        updateFilters({ search: value });
    }, 300)
);

// Handle sorting
const handleSort = (event) => {
    const [field, direction] = event.target.value.split("|");
    sortField.value = field;
    sortDirection.value = direction;
    updateFilters({
        sortField: field,
        sortDirection: direction,
    });
};

// Update filters and reload data
const updateFilters = (newFilters) => {
    router.get(
        route("shop"),
        {
            search: search.value,
            sortField: sortField.value,
            sortDirection: sortDirection.value,
            ...newFilters,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const getCategoryName = (categoryId) => {
    const category = props.categories.find((cat) => cat.id === categoryId);
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
    <Head title="Shoping Page" />
    <CustomersLayout>
        <Hero title="Shop" breadcrumb="Home > Shop" />
        <section class="bg-orange-200">
            <div
                class="w-full bg-[#fdf7f2] py-4 px-24 flex items-center justify-between border-t border-b border-gray-200"
            >
                <div class="flex items-center space-x-4">
                    <button
                        class="flex items-center space-x-2 text-black hover:text-gray-800"
                    >
                        <!-- Icon -->
                        <img
                            src="/img/icon/filter.svg"
                            alt="Filter Icon"
                            class="h-5 w-5"
                        />

                        <!-- Text -->
                        <span
                            class="text-1xl"
                            style="font-family: '', sans-serif"
                            >Filter</span
                        >
                    </button>
                    <button
                        class="flex items-center text-gray-600 hover:text-gray-800"
                    >
                        <!-- Icon -->
                        <img
                            src="/img/icon/more.svg"
                            alt="Filter Icon"
                            class="w-5"
                        />
                    </button>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-th-large text-gray-600"></i>
                        <i class="fas fa-th-list text-gray-600"></i>
                    </div>
                    <div class="flex items-center space-x-4 -ml-2">
                        <!-- Vertical Line -->
                        <div
                            class="border-l h-6 -ml-3 mx-2 border-gray-400"
                        ></div>
                    </div>
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
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <Product
                        v-for="(item, index) in products"
                        :key="index"
                        :id="item.id"
                        :name="item.name"
                        :price="formatPrice(item.price)"
                        :category="getCategoryName(item.category_id)"
                        :image="item.image"
                        :rating="item.average_rating || 0"
                        :total-reviews="item.total_reviews || 0"
                    />
                </div>
                <nav class="space-x-4">
                    <a
                        href=""
                        class="bg-orange-100 py-3 px-4 rounded-md hover:bg-orange-500 hover:text-white duration-300"
                        >1</a
                    >
                    <a
                        href=""
                        class="bg-orange-100 py-3 px-4 rounded-md hover:bg-orange-500 hover:text-white duration-300"
                        >2</a
                    >
                    <a
                        href=""
                        class="bg-orange-100 py-3 px-4 rounded-md hover:bg-orange-500 hover:text-white duration-300"
                        >3</a
                    >
                    <a
                        href=""
                        class="bg-orange-100 py-3 px-4 rounded-md hover:bg-orange-500 hover:text-white duration-300"
                        >Next</a
                    >
                </nav>
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

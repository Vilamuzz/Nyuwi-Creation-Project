<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import ToastNotification from "@/Components/Customer/Sub-main/ToastNotification.vue";
import { ref, watch, computed } from "vue";
import debounce from "lodash/debounce";
import { Search } from "lucide-vue-next";

const props = defineProps({
    products: Object, // Change from Array to Object for pagination
    categories: Array,
    filters: Object,
});

// Toast notification state
const toastMessage = ref("");
const toastType = ref("info");
const showToast = ref(false);

const search = ref(props.filters?.search || "");
const sortField = ref("created_at");
const sortDirection = ref("desc");

// Available sort options
const sortOptions = [
    { field: "created_at", label: "Terbaru", direction: "desc", default: true },
    { field: "created_at", label: "Terlama", direction: "asc" },
    { field: "name", label: "Nama (A-Z)", direction: "asc" },
    { field: "name", label: "Nama (Z-A)", direction: "desc" },
    { field: "price", label: "Harga (Rendah-Tinggi)", direction: "asc" },
    { field: "price", label: "Harga (Tinggi-Rendah)", direction: "desc" },
    { field: "stock", label: "Stok (Rendah-Tinggi)", direction: "asc" },
    { field: "stock", label: "Stok (Tinggi-Rendah)", direction: "desc" },
];

// Get current sort label
const currentSortLabel = computed(() => {
    const currentOption = sortOptions.find(
        (option) =>
            option.field === sortField.value &&
            option.direction === sortDirection.value
    );
    return currentOption ? currentOption.label : "Terbaru";
});

// Function to show toast notifications
const showNotification = (message, type = "info") => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
};

// Function to close toast
const closeToast = () => {
    showToast.value = false;
};

// Watch for search changes with debounce
watch(
    search,
    debounce((value) => {
        updateFilters({ search: value, page: 1 }); // Reset to page 1 on search
    }, 300)
);

// Function to handle sorting
const handleSort = (field, direction) => {
    sortField.value = field;
    sortDirection.value = direction;
    updateFilters({
        sortField: field,
        sortDirection: direction,
        page: 1, // Reset to page 1 on sort
    });
};

// Update filters and reload data
const updateFilters = (newFilters) => {
    router.get(
        route("products.index"),
        {
            search: search.value,
            sortField: sortField.value,
            sortDirection: sortDirection.value,
            page: props.products.current_page, // Keep current page unless specified
            ...newFilters,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

// Add pagination function
const changePage = (page) => {
    updateFilters({ page });
};

const form = useForm({});

const deleteProduct = (productId) => {
    if (confirm("Are you sure you want to delete this product?")) {
        form.delete(route("products.destroy", productId), {
            onSuccess: () => {
                showNotification("Produk berhasil dihapus!", "success");
            },
            onError: (errors) => {
                const errorMessages = Object.values(errors).flat();
                showNotification(
                    errorMessages[0] ||
                        "Terjadi kesalahan saat menghapus produk",
                    "error"
                );
            },
        });
    }
};

const getCategoryName = (categoryId) => {
    const category = props.categories.find((cat) => cat.id === categoryId);
    return category ? category.name : "Tidak ada kategori";
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
    <Head title="Inventory" />

    <AdminLayout pageTitle="Inventory Management">
        <!-- Toast Notification Component -->
        <ToastNotification
            :message="toastMessage"
            :type="toastType"
            :show="showToast"
            @close="closeToast"
        />

        <div class="mt-4">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <!-- Search and Sort section -->
                    <div class="flex justify-between items-center p-4">
                        <div class="flex items-center space-x-4 w-full">
                            <!-- Search input -->
                            <label
                                class="input input-bordered flex items-center gap-2 w-1/3"
                            >
                                <Search />
                                <input
                                    type="search"
                                    class="grow border-none focus:ring-0"
                                    placeholder="Search products..."
                                    v-model="search"
                                />
                            </label>

                            <!-- Sort dropdown -->
                            <div class="dropdown dropdown-center">
                                <div tabindex="0" role="button" class="btn m-1">
                                    {{ currentSortLabel }}
                                </div>
                                <ul
                                    tabindex="0"
                                    class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow-sm gap-y-1"
                                >
                                    <li
                                        v-for="option in sortOptions"
                                        :key="`${option.field}-${option.direction}`"
                                    >
                                        <a
                                            @click="
                                                handleSort(
                                                    option.field,
                                                    option.direction
                                                )
                                            "
                                            :class="{
                                                active:
                                                    option.field ===
                                                        sortField &&
                                                    option.direction ===
                                                        sortDirection,
                                            }"
                                        >
                                            {{ option.label }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Add Product button -->
                        <Link
                            :href="route('products.create')"
                            class="text-white w-1/5 btn btn-neutral"
                        >
                            Tambah Produk
                        </Link>
                    </div>

                    <!-- Table content -->
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-700 uppercase"
                                >
                                    No
                                </th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-700 uppercase"
                                >
                                    Foto
                                </th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-700 uppercase"
                                >
                                    Nama
                                </th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-700 uppercase"
                                >
                                    Kategori
                                </th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-700 uppercase"
                                >
                                    Jumlah Stok
                                </th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-700 uppercase"
                                >
                                    Harga
                                </th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-gray-700 uppercase text-center"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="(item, index) in products.data"
                                :key="item.id"
                            >
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap"
                                >
                                    {{
                                        (products.current_page - 1) *
                                            products.per_page +
                                        index +
                                        1
                                    }}
                                </td>
                                <td class="px-6 py-4">
                                    <img
                                        :src="`/storage/products/${item.images[0]}`"
                                        alt="product image"
                                        class="w-16 h-16 object-cover rounded"
                                    />
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap"
                                >
                                    {{ item.name }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap"
                                >
                                    {{ getCategoryName(item.category_id) }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap"
                                >
                                    {{ item.stock }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap"
                                >
                                    {{ formatPrice(item.price) }}
                                </td>
                                <td
                                    class="flex items-center justify-center px-6 py-6 space-x-2"
                                >
                                    <Link
                                        :href="route('products.edit', item.id)"
                                        class="text-white btn btn-info"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteProduct(item.id)"
                                        type="submit"
                                        class="text-white btn btn-error"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination Controls -->
                    <div
                        v-if="products.last_page > 1"
                        class="flex justify-center items-center p-4 space-x-2"
                    >
                        <!-- Previous Button -->
                        <button
                            @click="changePage(products.current_page - 1)"
                            :disabled="products.current_page === 1"
                            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-orange-500 hover:text-white hover:border-orange-500 transition duration-300 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>

                        <!-- Page Numbers -->
                        <button
                            v-for="page in products.last_page"
                            :key="page"
                            @click="changePage(page)"
                            :class="[
                                'px-3 py-2 text-sm font-medium border rounded-md',
                                page === products.current_page
                                    ? 'bg-orange-500 text-white border-orange-500'
                                    : 'text-gray-700 bg-white border-gray-300 hover:bg-orange-500 hover:text-white hover:border-orange-500 transition duration-300 ease-in-out',
                            ]"
                        >
                            {{ page }}
                        </button>

                        <!-- Next Button -->
                        <button
                            @click="changePage(products.current_page + 1)"
                            :disabled="
                                products.current_page === products.last_page
                            "
                            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-orange-500 hover:text-white hover:border-orange-500 transition duration-300 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next
                        </button>
                    </div>

                    <!-- Pagination Info -->
                    <div
                        class="px-4 py-3 text-sm text-gray-700 border-t border-gray-200"
                    >
                        Showing {{ products.from }} to {{ products.to }} of
                        {{ products.total }} results
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

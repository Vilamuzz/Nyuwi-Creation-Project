<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { ref, watch } from "vue";
import debounce from "lodash/debounce";

const props = defineProps({
    products: Array,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters?.search || "");
const sortField = ref(props.filters?.sortField || "");
const sortDirection = ref(props.filters?.sortDirection || "asc");

// Available sort options
const sortOptions = [
    { field: "name", label: "Nama (A-Z)", direction: "asc" },
    { field: "name", label: "Nama (Z-A)", direction: "desc" },
    { field: "created_at", label: "Terbaru", direction: "desc" },
    { field: "created_at", label: "Terlama", direction: "asc" },
    { field: "price", label: "Harga (Rendah-Tinggi)", direction: "asc" },
    { field: "price", label: "Harga (Tinggi-Rendah)", direction: "desc" },
    { field: "stock", label: "Stok (Rendah-Tinggi)", direction: "asc" },
    { field: "stock", label: "Stok (Tinggi-Rendah)", direction: "desc" },
];

// Watch for search changes with debounce
watch(
    search,
    debounce((value) => {
        updateFilters({ search: value });
    }, 300)
);

// Function to handle sorting
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
        route("products.index"),
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

const form = useForm({});

const deleteProduct = (productId) => {
    if (confirm("Are you sure you want to delete this product?")) {
        form.delete(route("products.destroy", productId));
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
    <Head title="Products" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <!-- Search and Sort section -->
                    <div class="flex justify-between items-center p-4">
                        <div class="flex items-center space-x-4 w-full">
                            <!-- Search input -->
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search products..."
                                class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-1/3"
                            />

                            <!-- Sort dropdown -->
                            <select
                                @change="handleSort"
                                class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :value="`${sortField}|${sortDirection}`"
                            >
                                <option value="">Urutkan Berdasarkan</option>
                                <option
                                    v-for="option in sortOptions"
                                    :key="`${option.field}-${option.direction}`"
                                    :value="`${option.field}|${option.direction}`"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Add Product button -->
                        <Link
                            :href="route('products.create')"
                            class="px-4 py-2 text-white w-1/5 text-center bg-green-500 rounded hover:bg-green-700"
                        >
                            Tambah Produk
                        </Link>
                    </div>

                    <!-- Table content remains the same -->
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
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-700 uppercase hover:bg-gray-100"
                                >
                                    Jumlah Stok
                                </th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-700 uppercase hover:bg-gray-100"
                                >
                                    Harga
                                </th>

                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-700 uppercase"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(item, index) in products" :key="index">
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap"
                                >
                                    {{ index + 1 }}
                                </td>
                                <td class="px-6 py-4">
                                    <img
                                        :src="`/storage/products/${item.image}`"
                                        alt="product image"
                                        class="w-16 h-16 object-cover"
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
                                    class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap"
                                >
                                    <Link
                                        :href="route('products.edit', item.id)"
                                        class="px-4 py-2 mr-2 text-white bg-blue-500 rounded hover:bg-blue-700"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteProduct(item.id)"
                                        type="submit"
                                        class="px-4 py-2 text-white bg-red-500 rounded hover:bg-red-700"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

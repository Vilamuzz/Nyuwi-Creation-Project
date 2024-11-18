<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
// import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    products: Array,
    categories: Array,
});

const form = useForm({});

const deleteProduct = (productId) => {
    if (confirm("Are you sure you want to delete this product?")) {
        form.delete(route("products.destroy", { dashboard: productId }));
    }
};
const getCategoryName = (categoryId) => {
    const category = props.categories.find((cat) => cat.id === categoryId);
    return category ? category.name : "Tidak ada kategori";
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
                <div
                    id="isi"
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <!-- Added button container above the table -->
                    <div class="flex justify-end p-4">
                        <Link
                            :href="route('products.create')"
                            class="px-4 py-2 text-white bg-green-500 rounded hover:bg-green-700"
                        >
                            Tambah Produk
                        </Link>
                    </div>

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
                                    {{ item.price }}
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

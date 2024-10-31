<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
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
                        <button
                            class="px-4 py-2 text-white bg-green-500 rounded hover:bg-green-700"
                        >
                            Tambah Produk
                        </button>
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
                                    Gambar
                                </th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-700 uppercase"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(item, index) in items" :key="index">
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap"
                                >
                                    {{ index + 1 }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap"
                                >
                                    {{ item.nama }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap"
                                >
                                    {{ item.jumlahStok }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap"
                                >
                                    {{ item.harga }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap"
                                >
                                    <img
                                        :src="item.gambar"
                                        alt="Gambar produk"
                                        class="w-16 h-16 object-cover rounded"
                                    />
                                </td>
                                <td
                                    class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap"
                                >
                                    <button
                                        class="px-4 py-2 mr-2 text-white bg-blue-500 rounded hover:bg-blue-700"
                                    >
                                        Edit
                                    </button>
                                    <button
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
    </AuthenticatedLayout>
</template>

<script>
// Add a data source for the table
const items = [
    {
        nama: "Produk 1",
        jumlahStok: 10,
        harga: "Rp10,000",
        gambar: "/path/to/image1.jpg",
    },
    {
        nama: "Produk 2",
        jumlahStok: 5,
        harga: "Rp15,000",
        gambar: "/path/to/image2.jpg",
    },
    // Add more items as needed
];
</script>

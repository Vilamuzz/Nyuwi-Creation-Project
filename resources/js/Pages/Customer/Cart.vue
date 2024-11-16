<script setup>
import { Head } from "@inertiajs/vue3";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Hero from "@/Components/Main/Hero.vue";
</script>
<template>
    <Head title="Product" />
    <CustomersLayout>
        <Hero />
        <section class="mx-24 flex flex-row">
            <!-- Bagian Tabel Produk -->
            <div class="w-3/4">
                <div class="bg-orange-100">
                    <table class="w-full text-left">
                        <!-- Header Tabel -->
                        <thead>
                            <tr class="px-4 py-8 bg-orange-200">
                                <th class="px-4 py-2">Gambar</th>
                                <th class="px-4 py-2">Nama</th>
                                <th class="px-4 py-2">Kategori</th>
                                <th class="px-4 py-2">Harga</th>
                                <th class="px-4 py-2">Jumlah</th>
                                <th class="px-4 py-2">Total</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>

                        <!-- Isi Tabel -->
                        <tbody>
                            <!-- Contoh Item Produk -->
                            <tr class="border-b border-gray-200">
                                <td class="px-4 py-4">
                                    <img
                                        src="/path/to/image.jpg"
                                        alt="Product Image"
                                        class="w-16 h-16 object-cover rounded-md"
                                    />
                                </td>
                                <td class="px-4 py-4">Nama Produk</td>
                                <td class="px-4 py-4">Kategori</td>
                                <td class="px-4 py-4">Rp 100.000</td>
                                <td class="px-4 py-4">
                                    <input
                                        type="number"
                                        value="1"
                                        class="w-16 px-2 py-1 border rounded-md"
                                    />
                                </td>
                                <td class="px-4 py-4">Rp 100.000</td>
                                <td class="px-4 py-4">
                                    <button
                                        class="text-red-500 hover:text-red-700"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <!-- Tambahkan item lain sesuai kebutuhan -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bagian Ringkasan Keranjang -->
            <div class="bg-red-100 w-1/4 p-8">
                <h2 class="text-xl font-bold mb-4">Ringkasan Belanja</h2>
                <p>Subtotal: Rp 100.000</p>
                <p>Ongkos Kirim: Rp 10.000</p>
                <p>Total: Rp 110.000</p>
                <button
                    class="w-full mt-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600"
                >
                    Checkout
                </button>
            </div>
        </section>
    </CustomersLayout>
</template>

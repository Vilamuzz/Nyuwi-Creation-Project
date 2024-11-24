<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3"; // Tambahkan useForm
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Hero from "@/Components/Customer/Main/Hero.vue";
import { computed, ref } from "vue"; // Tambahkan ref

const props = defineProps({
    cartItems: Array,
});

// Form untuk delete item
const form = useForm({});

// Form untuk update quantity
const updateForm = useForm({
    quantity: null,
});

// Fungsi untuk menghapus item
const deleteCartItem = (itemId) => {
    if (confirm("Apakah anda yakin ingin menghapus item ini?")) {
        form.delete(route("cart.remove", itemId), {
            preserveScroll: true,
        });
    }
};

// Fungsi untuk mengupdate quantity
const updateQuantity = (item, newQuantity) => {
    updateForm.quantity = newQuantity;
    updateForm.put(route("cart.update", item.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Reset form setelah berhasil
            updateForm.reset();
        },
    });
};

// Compute total price from all items
const cartTotal = computed(() => {
    if (!props.cartItems || props.cartItems.length === 0) return 0;
    return props.cartItems.reduce((total, item) => {
        return total + item.price * item.quantity;
    }, 0);
});

// Format price to IDR
const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
};
</script>
<template>
    <Head title="Shoping Cart" />
    <CustomersLayout>
        <Hero />
        <section class="mx-24 flex flex-row my-16 gap-x-8">
            <!-- Bagian Tabel Produk -->
            <div class="w-3/4">
                <div>
                    <table class="w-full text-left">
                        <!-- Header Tabel -->
                        <thead>
                            <tr class="px-4 py-8 bg-orange-100">
                                <th class="px-4 py-5">Gambar</th>
                                <th class="px-4 py-5">Nama</th>
                                <th class="px-4 py-5">Harga</th>
                                <th class="px-4 py-5">Jumlah</th>
                                <th class="px-4 py-5">Harga</th>
                                <th class="px-4 py-5">Aksi</th>
                            </tr>
                        </thead>

                        <!-- Isi Tabel -->
                        <tbody v-if="cartItems && cartItems.length > 0">
                            <!-- Contoh Item Produk -->
                            <tr v-for="item in cartItems" :key="item.id">
                                <td class="px-4 py-4">
                                    <img
                                        src="/img/background/category1.svg"
                                        alt="Product Image"
                                        class="w-16 h-16 object-cover rounded-md"
                                    />
                                </td>
                                <td class="px-4 py-4">
                                    {{ item.product.name }}
                                </td>

                                <td class="px-4 py-4">{{ item.price }}</td>
                                <td class="px-4 py-4">
                                    <input
                                        type="number"
                                        :value="item.quantity"
                                        min="1"
                                        class="w-16 px-2 py-1 border rounded-md"
                                        @change="
                                            updateQuantity(
                                                item,
                                                $event.target.value
                                            )
                                        "
                                    />
                                </td>
                                <td class="px-4 py-4">
                                    {{ item.price * item.quantity }}
                                </td>
                                <td class="px-4 py-4">
                                    <button
                                        @click="deleteCartItem(item.id)"
                                        class="text-red-500 hover:text-red-700"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <!-- Tambahkan item lain sesuai kebutuhan -->
                        </tbody>

                        <tbody v-else>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    Keranjang belanja kosong
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bagian Ringkasan Keranjang -->
            <div class="bg-red-100 w-1/4 p-8">
                <h2 class="text-xl font-bold mb-4">Ringkasan Belanja</h2>
                <p>Total: {{ formatPrice(cartTotal) }}</p>
                <div class="mt-4 flex items-center">
                    <Link
                        :href="route('order.store')"
                        class="w-full text-center px-8 py-2 border border-black hover:border-transparent hover:text-white rounded-md hover:bg-orange-500 duration-150"
                    >
                        Checkout
                    </Link>
                </div>
            </div>
        </section>
    </CustomersLayout>
</template>

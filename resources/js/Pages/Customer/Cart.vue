<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3"; // Tambahkan useForm
import { computed, ref, onMounted, watch } from "vue";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Hero from "@/Components/Customer/Main/Hero.vue";
import PaymentInformationModal from "@/Components/Customer/Sub-main/PaymentInformationModal.vue";

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

const showPaymentModal = ref(false);
const paymentInfo = {
    phone: "088123456789", // Nomor DANA/digital wallet
    accountName: "Nyuwi Creation",
};

// Tambahkan fungsi untuk mengecek query parameter
onMounted(() => {
    // Cek localStorage alih-alih query parameter
    const showPaymentInfo = localStorage.getItem("showPaymentInfo");
    const amount = localStorage.getItem("paymentAmount");

    if (showPaymentInfo === "true") {
        showPaymentModal.value = true;
        // Bersihkan localStorage setelah digunakan
        localStorage.removeItem("showPaymentInfo");
        localStorage.removeItem("paymentAmount");
    }
});

const closePaymentModal = () => {
    showPaymentModal.value = false;
};

watch(
    () => props.cartItems,
    () => {
        // Cek localStorage setiap kali cartItems berubah
        const showPaymentInfo = localStorage.getItem("showPaymentInfo");
        if (showPaymentInfo === "true") {
            showPaymentModal.value = true;
            localStorage.removeItem("showPaymentInfo");
            localStorage.removeItem("paymentAmount");
        }
    },
    { immediate: true }
);
</script>
<template>
    <Head title="Shoping Cart" />
    <CustomersLayout>
        <Hero title="Cart" breadcrumb="Home > Cart" />
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
                                        :src="
                                            '/storage/products/' +
                                            item.product.image
                                        "
                                        alt="Product Image"
                                        class="w-16 h-16 object-cover rounded-md"
                                    />
                                </td>
                                <td class="px-4 py-4">
                                    {{ item.product.name }}
                                    <div class="text-sm text-gray-500">
                                        <span v-if="item.size"
                                            >Size: {{ item.size }}</span
                                        >
                                        <span v-if="item.color" class="ml-2"
                                            >Color:
                                            <span
                                                class="inline-block w-4 h-4 rounded-full ml-1"
                                                :style="{
                                                    backgroundColor: item.color,
                                                }"
                                            ></span>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    {{ formatPrice(item.price) }}
                                </td>
                                <td class="px-4 py-4">
                                    <input
                                        type="number"
                                        :value="item.quantity"
                                        min="1"
                                        class="text-center w-16 px-2 py-1 border rounded-md [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                        @change="
                                            updateQuantity(
                                                item,
                                                $event.target.value
                                            )
                                        "
                                    />
                                </td>
                                <td class="px-4 py-4">
                                    {{
                                        formatPrice(item.price * item.quantity)
                                    }}
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
                        v-if="cartItems && cartItems.length > 0"
                        :href="route('checkout')"
                        class="w-full text-center px-8 py-2 border border-black hover:border-transparent hover:text-white rounded-md hover:bg-orange-500 duration-150"
                    >
                        Checkout
                    </Link>
                    <button
                        v-else
                        disabled
                        class="w-full text-center px-8 py-2 border border-gray-300 text-gray-500 rounded-md bg-gray-100 cursor-not-allowed"
                    >
                        Checkout
                    </button>
                </div>
            </div>
        </section>
        <!-- Payment Modal -->
        <PaymentInformationModal
            v-if="showPaymentModal"
            :payment-info="paymentInfo"
            :on-close="closePaymentModal"
        />
    </CustomersLayout>
</template>

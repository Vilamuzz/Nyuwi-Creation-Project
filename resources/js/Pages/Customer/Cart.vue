<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref, onMounted } from "vue";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Hero from "@/Components/Customer/Main/Hero.vue";
import PaymentInformationModal from "@/Components/Customer/Sub-main/PaymentInformationModal.vue";
import axios from "axios";

// Configure axios for session auth
axios.defaults.withCredentials = true; // Important for sending cookies
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
}

// Use reactive state for cart items from API
const cartItems = ref([]);
const isLoading = ref(true);
const error = ref(null);

// Get cart data
const fetchCart = async () => {
    try {
        isLoading.value = true;
        const response = await axios.get("/api/cart");
        if (response.data.success) {
            cartItems.value = response.data.data.cartItems;
        }
    } catch (error) {
        console.error("Error fetching cart:", error);
        error.value = "Failed to fetch cart items";
    } finally {
        isLoading.value = false;
    }
};

// Delete cart item
const deleteCartItem = async (itemId) => {
    if (confirm("Apakah anda yakin ingin menghapus item ini?")) {
        try {
            const response = await axios.delete(`/api/cart/${itemId}`);
            if (response.data.success) {
                cartItems.value = response.data.data.cartItems;
            }
        } catch (error) {
            console.error("Error removing item:", error);
        }
    }
};

// Update quantity
const updateQuantity = async (item, newQuantity) => {
    try {
        const response = await axios.put(`/api/cart/${item.id}`, {
            quantity: newQuantity,
        });
        if (response.data.success) {
            cartItems.value = response.data.data.cartItems;
        }
    } catch (error) {
        console.error("Error updating quantity:", error);
        // Handle error - possibly show a notification
    }
};

// Compute total price from all items
const cartTotal = computed(() => {
    if (!cartItems.value || cartItems.value.length === 0) return 0;
    return cartItems.value.reduce((total, item) => {
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

onMounted(() => {
    // Fetch cart data when component mounts
    fetchCart();

    // Check localStorage for payment info
    const showPaymentInfo = localStorage.getItem("showPaymentInfo");
    const paymentMethod = localStorage.getItem("paymentMethod");

    if (showPaymentInfo === "true") {
        showPaymentModal.value = true;
        // Clear localStorage after use
        localStorage.removeItem("showPaymentInfo");
        localStorage.removeItem("paymentAmount");
        localStorage.removeItem("paymentMethod");
    }
});

const closePaymentModal = () => {
    showPaymentModal.value = false;
};
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
                                            item.product.images[0]
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
        <!-- Payment Information Modal -->
        <PaymentInformationModal
            v-if="showPaymentModal"
            :onClose="closePaymentModal"
            :paymentMethod="
                localStorage.getItem('paymentMethod') || 'digital_wallet'
            "
        />
    </CustomersLayout>
</template>

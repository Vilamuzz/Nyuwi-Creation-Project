<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed, ref, onMounted } from "vue";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Hero from "@/Components/Customer/Main/Hero.vue";
import PaymentInformationModal from "@/Components/Customer/Sub-main/PaymentInformationModal.vue";
const props = defineProps({
    cartItems: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
});

const cartItems = computed(() => props.cartItems);
const isLoading = ref(false);
const error = ref(null);

const fetchCart = () => { };

const deleteForm = useForm({});
const updateForm = useForm({ quantity: 1 });

// Delete cart item
const deleteCartItem = (itemId) => {
    if (confirm("Apakah anda yakin ingin menghapus item ini?")) {
        deleteForm.delete(route("cart.destroy", itemId), { preserveScroll: true });
    }
};

// Update quantity
const updateQuantity = (item, newQuantity) => {
    updateForm.quantity = Number(newQuantity);
    updateForm.put(route("cart.update", item.id), { preserveScroll: true });
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

    <Head title="Your Shoping Cart" />
    <CustomersLayout>
        <div class="relative flex flex-col items-center justify-center text-center h-48">
            <h1 class="text-7xl font-bold">Cart</h1>
        </div>
        <section class="mx-auto max-w-7xl flex flex-col md:flex-row my-8 md:my-16 gap-8 px-4 sm:px-6 lg:px-8">
            <!-- Bagian Tabel Produk -->
            <div class="w-full md:w-3/4 overflow-x-auto">
                <div>
                    <table class="w-full text-left">
                        <!-- Header Tabel -->
                        <thead>
                            <tr class="px-4 py-8 bg-orange-100">
                                <th class="px-4 py-5">Produk</th>
                                <th class="px-4 py-5 text-center">Jumlah</th>
                                <th class="px-4 py-5">Total</th>
                            </tr>
                        </thead>

                        <!-- Isi Tabel -->
                        <tbody v-if="cartItems && cartItems.length > 0">
                            <!-- Contoh Item Produk -->
                            <tr v-for="item in cartItems" :key="item.id">
                                <td class="px-4 py-4">
                                    <div class="flex gap-4 items-center">
                                        <img :src="'/storage/products/' +
                                            item.product.images[0]
                                            " alt="Product Image" class="w-16 h-16 object-cover rounded-md" />

                                        <div class="flex flex-col text-sm text-gray-500">
                                            <p class="font-semibold text-black">{{ item.product.name }}</p>
                                            <p class="font-semibold text-black">{{ formatPrice(item.price) }}</p>
                                            <span v-if="item.color">Color:
                                                <span class="inline-block w-4 h-4 rounded-full" :style="{
                                                    backgroundColor: item.color,
                                                }"></span>
                                            </span>
                                            <span v-if="item.size">Size: {{ item.size }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-col items-center gap-2">
                                        <input type="number" :value="item.quantity" min="1"
                                            class="h-10 w-10 p-0 text-center font-medium border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                            @change="
                                                updateQuantity(
                                                    item,
                                                    $event.target.value
                                                )
                                                " />

                                        <button @click="deleteCartItem(item.id)"
                                            class="text-red-500 hover:text-red-700 underline">
                                            Hapus
                                        </button>
                                    </div>

                                </td>
                                <td class="px-4 py-4">
                                    {{
                                        formatPrice(item.price * item.quantity)
                                    }}
                                </td>
                            </tr>
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

            <div class="bg-red-100 w-full md:w-1/4 p-6 md:p-8 rounded-lg shrink-0">
                <h2 class="text-xl font-bold mb-4">Ringkasan Belanja</h2>
                <p>Total: {{ formatPrice(summary.subtotal || cartTotal) }}</p>
                <div class="mt-4 flex items-center">
                    <Link v-if="cartItems && cartItems.length > 0" :href="route('checkout')"
                        class="w-full text-center px-8 py-2 border border-black hover:border-transparent hover:text-white rounded-md hover:bg-orange-500 duration-150">
                        Checkout
                    </Link>
                    <button v-else disabled
                        class="w-full text-center px-8 py-2 border border-gray-300 text-gray-500 rounded-md bg-gray-100 cursor-not-allowed">
                        Checkout
                    </button>
                </div>
            </div>
        </section>
        <!-- Payment Information Modal -->
        <PaymentInformationModal v-if="showPaymentModal" :onClose="closePaymentModal" :paymentMethod="localStorage.getItem('paymentMethod') || 'digital_wallet'
            " />
    </CustomersLayout>
</template>

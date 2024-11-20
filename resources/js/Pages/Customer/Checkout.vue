<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Hero from "@/Components/Customer/Main/Hero.vue";

const props = defineProps({
    cartItems: Array, // Add this prop
});

const form = useForm({
    name: "",
    address: "",
    city: "",
    province: "",
    phone: "",
    note: "",
    payment_method: "",
});

// const submitCheckout = () => {
//     form.post(route("checkout.store"), {
//         onSuccess: () => {
//             form.reset();
//         },
//     });
// };

const showModal = ref(false);

const validateForm = () => {
    // Reset any existing errors
    form.clearErrors();

    // Check required fields
    if (!form.name) form.setError("name", "Name is required");
    if (!form.address) form.setError("address", "Address is required");
    if (!form.city) form.setError("city", "City is required");
    if (!form.province) form.setError("province", "Province is required");
    if (!form.phone) form.setError("phone", "Phone is required");
    if (!form.payment_method)
        form.setError("payment_method", "Payment method is required");

    // Return true if no errors
    return Object.keys(form.errors).length === 0;
};

const openConfirmModal = () => {
    if (validateForm()) {
        showModal.value = true;
    }
};

const closeModal = () => {
    showModal.value = false;
};

const confirmCheckout = () => {
    form.post(route("order.store"), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

// Format price to IDR
const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
};

// Calculate total price
const cartTotal = computed(() => {
    if (!props.cartItems || props.cartItems.length === 0) return 0;
    return props.cartItems.reduce((total, item) => {
        return total + item.price * item.quantity;
    }, 0);
});
</script>
<template>
    <Head title="Checkout" />
    <CustomersLayout>
        <Hero />
        <form @submit.prevent="submitCheckout">
            <section class="mx-24 flex flex-row my-16 gap-x-8">
                <div class="felx flex-col space-y-4 w-1/2">
                    <h1 class="font-bold text-2xl">Billing details</h1>

                    <div class="flex flex-col space-y-2">
                        <label for="first_name">First Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="rounded-md border-gray-400"
                            required
                        />
                        <div
                            v-if="form.errors.name"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.name }}
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="address">Address</label>
                        <input
                            v-model="form.address"
                            type="text"
                            class="rounded-md border-gray-400"
                            required
                        />
                        <div
                            v-if="form.errors.address"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.address }}
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="city">City</label>
                        <input
                            v-model="form.city"
                            type="text"
                            class="rounded-md border-gray-400"
                            required
                        />
                        <div
                            v-if="form.errors.city"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.city }}
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="state">Province</label>
                        <input
                            v-model="form.province"
                            type="text"
                            class="rounded-md border-gray-400"
                            required
                        />
                        <div
                            v-if="form.errors.province"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.province }}
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="phone">Phone</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="rounded-md border-gray-400"
                            required
                        />
                        <div
                            v-if="form.errors.phone"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.phone }}
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="note">Note</label>
                        <input
                            v-model="form.note"
                            type="text"
                            class="rounded-md border-gray-400"
                        />
                    </div>
                </div>
                <div class="flex flex-col w-1/2">
                    <div class="flex flex-row justify-between">
                        <h1 class="font-bold text-2xl">Product</h1>
                        <h1 class="font-bold text-2xl">Price</h1>
                    </div>
                    <div
                        v-for="item in cartItems"
                        :key="item.id"
                        class="flex flex-row justify-between"
                    >
                        <h1 class="text-gray-400">
                            {{ item.product.name }} x {{ item.quantity }}
                        </h1>
                        <h1 class="text-gray-400">
                            {{ formatPrice(item.price * item.quantity) }}
                        </h1>
                    </div>
                    <div class="flex flex-row justify-between">
                        <h1 class="">Total</h1>
                        <h1 class="">{{ formatPrice(cartTotal) }}</h1>
                    </div>
                    <hr />
                    <div class="flex flex-col space-y-3">
                        <h1 class="font-bold text-2xl">Payment Method</h1>
                        <div class="flex flex-col space-y-2">
                            <div class="space-x-2">
                                <input
                                    type="radio"
                                    id="credit_card"
                                    value="credit_card"
                                    v-model="form.payment_method"
                                    name="payment_method"
                                />
                                <label for="credit_card">Credit Card</label>
                            </div>
                            <div class="space-x-2">
                                <input
                                    type="radio"
                                    id="digital_wallet"
                                    value="digital_wallet"
                                    v-model="form.payment_method"
                                    name="payment_method"
                                />
                                <label for="digital_wallet"
                                    >Digital Wallet (Dana)</label
                                >
                            </div>
                            <div class="space-x-2">
                                <input
                                    type="radio"
                                    id="cash_on_delivery"
                                    value="cash_on_delivery"
                                    v-model="form.payment_method"
                                    name="payment_method"
                                />
                                <label for="cash_on_delivery"
                                    >Cash on Delivery</label
                                >
                            </div>

                            <!-- Add error message -->
                            <div
                                v-if="form.errors.payment_method"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ form.errors.payment_method }}
                            </div>
                            <div class="items-center flex">
                                <button
                                    type="button"
                                    @click="openConfirmModal"
                                    class="mx-auto w-1/2 mt-4 py-2 border border-black hover:border-transparent hover:text-white rounded-md hover:bg-orange-500 duration-150"
                                >
                                    Place Order
                                </button>
                                <!-- Modal -->
                                <div
                                    v-if="showModal"
                                    class="fixed inset-0 z-50 overflow-y-auto"
                                >
                                    <!-- Backdrop -->
                                    <div
                                        class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                                    ></div>

                                    <!-- Modal Content -->
                                    <div
                                        class="flex min-h-full items-center justify-center p-4"
                                    >
                                        <div
                                            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                                        >
                                            <div
                                                class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4"
                                            >
                                                <div
                                                    class="sm:flex sm:items-start"
                                                >
                                                    <div
                                                        class="mt-3 text-center sm:mt-0 sm:text-left"
                                                    >
                                                        <h3
                                                            class="text-lg font-bold leading-6 text-gray-900"
                                                        >
                                                            Confirm Order
                                                        </h3>
                                                        <div class="mt-2">
                                                            <p
                                                                class="text-sm text-gray-500"
                                                            >
                                                                Are you sure you
                                                                want to place
                                                                this order?
                                                                Total amount:
                                                                {{
                                                                    formatPrice(
                                                                        cartTotal
                                                                    )
                                                                }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
                                            >
                                                <button
                                                    type="button"
                                                    class="inline-flex w-full justify-center rounded-md bg-orange-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-600 sm:ml-3 sm:w-auto"
                                                    @click="confirmCheckout"
                                                    :disabled="form.processing"
                                                >
                                                    {{
                                                        form.processing
                                                            ? "Processing..."
                                                            : "Confirm Order"
                                                    }}
                                                </button>
                                                <button
                                                    type="button"
                                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                                    @click="closeModal"
                                                >
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </CustomersLayout>
</template>

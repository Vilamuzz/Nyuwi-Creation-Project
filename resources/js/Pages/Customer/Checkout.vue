<script setup>
import { useForm } from "@inertiajs/vue3";
import CustomerLayout from "@/Layouts/CustomersLayout.vue";

const props = defineProps({
    cart_items: {
        type: Array,
        default: () => [],
    },
    user: {
        type: Object,
        default: () => ({
            name: "",
            email: "",
        }),
    },
});

const form = useForm({
    // Use optional chaining and provide default values
    name: props.user?.name ?? "",
    email: props.user?.email ?? "",
    phone: "",
    address: "",
    payment_method: "",
});

const calculateSubtotal = (item) => {
    return item.price * item.quantity;
};

const calculateTotal = () => {
    if (!props.cart_items || props.cart_items.length === 0) {
        return 0;
    }
    return props.cart_items.reduce((total, item) => {
        return total + calculateSubtotal(item);
    }, 0);
};

const submitOrder = () => {
    form.post(route("orders.store"), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <CustomerLayout>
        <form @submit.prevent="submitOrder" class="flex flex-row gap-8 p-8">
            <!-- Customer Details Section -->
            <div class="flex flex-col space-y-4 w-1/2">
                <h2 class="font-bold text-2xl mb-4">Customer Details</h2>

                <div class="flex flex-col space-y-2">
                    <label for="name">Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="rounded-md border-gray-400"
                        required
                    />
                </div>

                <div class="flex flex-col space-y-2">
                    <label for="email">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="rounded-md border-gray-400"
                        required
                    />
                </div>

                <div class="flex flex-col space-y-2">
                    <label for="phone">Phone</label>
                    <input
                        v-model="form.phone"
                        type="tel"
                        class="rounded-md border-gray-400"
                        required
                    />
                </div>

                <div class="flex flex-col space-y-2">
                    <label for="address">Shipping Address</label>
                    <textarea
                        v-model="form.address"
                        rows="3"
                        class="rounded-md border-gray-400"
                        required
                    ></textarea>
                </div>

                <div class="flex flex-col space-y-2">
                    <label for="payment">Payment Method</label>
                    <select
                        v-model="form.payment_method"
                        class="rounded-md border-gray-400"
                        required
                    >
                        <option value="">Select Payment Method</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="digital_wallet">Digital Wallet</option>
                        <option value="cash_on_delivery">
                            Cash on Delivery
                        </option>
                    </select>
                </div>
            </div>

            <!-- Order Summary Section -->
            <div class="w-1/2 bg-gray-50 p-6 rounded-lg">
                <h2 class="font-bold text-2xl mb-4">Order Summary</h2>

                <!-- Cart Items -->
                <div class="space-y-4 mb-6">
                    <div
                        v-for="item in cart_items"
                        :key="item.id"
                        class="flex justify-between items-center border-b pb-4"
                    >
                        <div class="flex items-center space-x-4">
                            <img
                                :src="item.product.image"
                                alt="Product"
                                class="w-16 h-16 object-cover rounded"
                            />
                            <div>
                                <h3 class="font-semibold">
                                    {{ item.product.name }}
                                </h3>
                                <p class="text-gray-600">
                                    Quantity: {{ item.quantity }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold">
                                ${{ calculateSubtotal(item) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Total Section -->
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-lg">Total:</span>
                        <span class="font-bold text-2xl"
                            >${{ calculateTotal() }}</span
                        >
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full mt-6 bg-orange-500 text-white py-3 px-4 rounded-md hover:bg-orange-600 transition-colors"
                >
                    Place Order
                </button>
            </div>
        </form>
    </CustomerLayout>
</template>

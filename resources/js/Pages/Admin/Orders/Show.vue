<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    order: Object,
});

const updateStatus = (orderId, newStatus) => {
    const form = useForm({
        status: newStatus,
    });

    form.put(route("orders.update", orderId), {
        preserveScroll: true,
        onSuccess: () => {},
        onError: (errors) => {
            console.error("Error updating status:", errors);
        },
    });
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
    <Head title="Order Details" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Order Details #{{ order.id }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6"
                >
                    <!-- Order Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">
                            Order Information
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p>
                                    <span class="font-semibold">Order ID:</span>
                                    #{{ order.id }}
                                </p>
                                <p>
                                    <span class="font-semibold"
                                        >Customer Name:</span
                                    >
                                    {{ order.name }}
                                </p>
                                <p>
                                    <span class="font-semibold">Phone:</span>
                                    {{ order.phone }}
                                </p>
                                <p>
                                    <span class="font-semibold"
                                        >Payment Method:</span
                                    >
                                    {{ order.payment_method }}
                                </p>
                            </div>
                            <div>
                                <p>
                                    <span class="font-semibold">Status:</span>
                                    <span
                                        :class="{
                                            'px-2 py-1 text-xs font-semibold rounded-full': true,
                                            'bg-yellow-100 text-yellow-800':
                                                order.status === 'awaiting',
                                            'bg-blue-100 text-blue-800':
                                                order.status === 'processing',
                                            'bg-green-100 text-green-800':
                                                order.status === 'completed',
                                            'bg-red-100 text-red-800':
                                                order.status === 'cancelled',
                                        }"
                                    >
                                        {{ order.status }}
                                    </span>
                                </p>
                                <p>
                                    <span class="font-semibold">Date:</span>
                                    {{
                                        new Date(
                                            order.created_at
                                        ).toLocaleDateString()
                                    }}
                                </p>
                                <p>
                                    <span class="font-semibold"
                                        >Total Amount:</span
                                    >
                                    {{ formatPrice(order.total_price) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">
                            Shipping Information
                        </h3>
                        <div class="grid grid-cols-1 gap-2">
                            <p>
                                <span class="font-semibold">Address:</span>
                                {{ order.address }}
                            </p>
                            <p>
                                <span class="font-semibold">City:</span>
                                {{ order.city }}
                            </p>
                            <p>
                                <span class="font-semibold">Province:</span>
                                {{ order.province }}
                            </p>
                            <p v-if="order.note">
                                <span class="font-semibold">Note:</span>
                                {{ order.note }}
                            </p>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Order Items</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Product
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Size
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Color
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Quantity
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Price
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="item in order.order_items"
                                    :key="item.id"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ item.product.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ item.size }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="w-6 h-6 rounded-full"
                                            :style="{
                                                backgroundColor: item.color,
                                            }"
                                        ></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ item.quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ formatPrice(item.price) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ formatPrice(item.total_price) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Status Update Buttons -->
                    <div class="mt-6 flex justify-end space-x-4">
                        <button
                            @click="updateStatus(order.id, 'processing')"
                            :disabled="order.status === 'processing'"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
                        >
                            Accept Order
                        </button>
                        <button
                            @click="updateStatus(order.id, 'cancelled')"
                            :disabled="order.status === 'cancelled'"
                            class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 disabled:opacity-50"
                        >
                            Cancel Order
                        </button>
                        <button
                            @click="updateStatus(order.id, 'completed')"
                            :disabled="order.status === 'completed'"
                            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50"
                        >
                            Complete Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from "vue";

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["viewOrder", "uploadPayment"]);

// Format price for displaying
const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
};

const getStatusClass = (status) => {
    return {
        "px-2 py-1 text-xs font-semibold rounded-full": true,
        "bg-yellow-100 text-yellow-800": status === "waiting",
        "bg-purple-100 text-purple-800": status === "checking",
        "bg-purple-100 text-cyan-800": status === "shiping",
        "bg-orange-100 text-orange-800": status === "pending",
        "bg-blue-100 text-blue-800": status === "processing",
        "bg-green-100 text-green-800": status === "completed",
        "bg-red-100 text-red-800": status === "cancelled",
    };
};
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-xl font-bold mb-4">Riwayat Belanja</h2>
        <div v-if="orders?.length" class="grid gap-4">
            <!-- Order Cards -->
            <div
                v-for="order in orders"
                :key="order.id"
                class="border rounded-lg p-4 hover:shadow-md transition-shadow"
            >
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-semibold">Order #{{ order.id }}</p>
                        <p class="text-sm text-gray-500">
                            {{
                                new Date(order.created_at).toLocaleDateString()
                            }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <p class="font-bold">
                            {{ formatPrice(order.total_price) }}
                        </p>
                        <span :class="getStatusClass(order.status)">
                            {{ order.status }}
                        </span>

                        <!-- Add payment proof button for digital wallet orders -->
                        <button
                            v-if="
                                order.payment_method === 'digital_wallet' &&
                                order.status === 'waiting'
                            "
                            @click="emit('uploadPayment', order)"
                            class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600"
                        >
                            Upload Bukti Pembayaran
                        </button>

                        <button
                            @click="emit('viewOrder', order)"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                        >
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-gray-500">No orders found</div>
    </div>
</template>

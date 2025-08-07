<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
    reviews: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["viewReviews"]);

// Add new data for reviews section
const reviewedOrders = computed(() => {
    return props.orders?.filter((order) => order.status === "completed");
});
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-xl font-bold mb-4">Ulasan Belanja</h2>
        <div v-if="reviewedOrders?.length" class="space-y-4">
            <!-- List of completed orders with reviews -->
            <div
                v-for="order in reviewedOrders"
                :key="order.id"
                class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
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
                    <button
                        @click="emit('viewReviews', order)"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                    >
                        View Details
                    </button>
                </div>
            </div>
        </div>
        <div v-else class="text-gray-500">No completed orders found</div>
    </div>
</template>

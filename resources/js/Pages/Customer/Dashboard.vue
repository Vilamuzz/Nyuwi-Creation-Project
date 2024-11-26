<script setup>
import { Head } from "@inertiajs/vue3";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import { ref } from "vue";

const props = defineProps({
    orders: Array,
});

// Track selected action
const selectedAction = ref("history");

// Action options
const actions = [
    { id: "history", label: "Riwayat Belanja" },
    { id: "wishlist", label: "Wishlist" },
    { id: "reviews", label: "Ulasan Belanja" },
];

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
        "bg-yellow-100 text-yellow-800": status === "awaiting",
        "bg-blue-100 text-blue-800": status === "processing",
        "bg-green-100 text-green-800": status === "completed",
        "bg-red-100 text-red-800": status === "cancelled",
    };
};

// Track expanded orders
const expandedOrders = ref(new Set());

const toggleOrder = (orderId) => {
    if (expandedOrders.value.has(orderId)) {
        expandedOrders.value.delete(orderId);
    } else {
        expandedOrders.value.add(orderId);
    }
};
</script>

<template>
    <Head title="Dashboard" />
    <CustomersLayout>
        <section class="mx-16 flex flex-row my-8 gap-x-8">
            <!-- Sidebar -->
            <div class="w-1/4">
                <div class="border rounded-md p-8 shadow-md">
                    <h1 class="text-xl font-bold mb-4">Profile</h1>
                    <h1 class="mb-2">{{ $page.props.auth.user.name }}</h1>
                    <h1 class="mb-4">{{ $page.props.auth.user.email }}</h1>

                    <h1 class="font-semibold mb-3">Aksi</h1>
                    <div class="space-y-2">
                        <button
                            v-for="action in actions"
                            :key="action.id"
                            @click="selectedAction = action.id"
                            class="block w-full text-left px-3 py-2 rounded-md transition-colors"
                            :class="{
                                'bg-orange-500 text-white':
                                    selectedAction === action.id,
                                'hover:bg-orange-100':
                                    selectedAction !== action.id,
                            }"
                        >
                            {{ action.label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="w-3/4 rounded-md p-8">
                <!-- Shopping History -->
                <div v-if="selectedAction === 'history'" class="space-y-4">
                    <h2 class="text-xl font-bold mb-4">Riwayat Belanja</h2>
                    <div v-if="orders?.length" class="space-y-4">
                        <div
                            v-for="order in orders"
                            :key="order.id"
                            class="border rounded-lg overflow-hidden"
                        >
                            <!-- Order Header - Always visible -->
                            <div
                                @click="toggleOrder(order.id)"
                                class="p-4 bg-white hover:bg-gray-50 cursor-pointer"
                            >
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-semibold">
                                            Order #{{ order.id }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{
                                                new Date(
                                                    order.created_at
                                                ).toLocaleDateString()
                                            }}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <p class="font-bold">
                                            {{ formatPrice(order.total_price) }}
                                        </p>
                                        <span
                                            :class="
                                                getStatusClass(order.status)
                                            "
                                        >
                                            {{ order.status }}
                                        </span>
                                        <span
                                            class="transform transition-transform duration-200"
                                            :class="{
                                                'rotate-180':
                                                    expandedOrders.has(
                                                        order.id
                                                    ),
                                            }"
                                        >
                                            ▼
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Add transition wrapper around collapsible content -->
                            <transition
                                enter-active-class="transition-all duration-300 ease-in-out"
                                leave-active-class="transition-all duration-300 ease-in-out"
                                enter-from-class="max-h-0 opacity-0"
                                enter-to-class="max-h-[1000px] opacity-100"
                                leave-from-class="max-h-[1000px] opacity-100"
                                leave-to-class="max-h-0 opacity-0"
                            >
                                <div
                                    v-show="expandedOrders.has(order.id)"
                                    class="border-t overflow-hidden"
                                >
                                    <!-- Product List -->
                                    <div class="divide-y">
                                        <div
                                            v-for="item in order.order_items"
                                            :key="item.id"
                                            class="p-4 bg-gray-50"
                                        >
                                            <div
                                                class="flex justify-between items-center"
                                            >
                                                <div
                                                    class="flex items-center space-x-4"
                                                >
                                                    <img
                                                        :src="`/storage/products/${item.product.image}`"
                                                        alt="Product"
                                                        class="w-16 h-16 object-cover rounded"
                                                    />
                                                    <div>
                                                        <p class="font-medium">
                                                            {{
                                                                item.product
                                                                    .name
                                                            }}
                                                        </p>
                                                        <div
                                                            class="text-sm text-gray-500"
                                                        >
                                                            <span
                                                                v-if="item.size"
                                                                >Size:
                                                                {{
                                                                    item.size
                                                                }}</span
                                                            >
                                                            <span
                                                                v-if="
                                                                    item.color
                                                                "
                                                                class="ml-2"
                                                            >
                                                                Color:
                                                                <span
                                                                    class="inline-block w-4 h-4 rounded-full ml-1"
                                                                    :style="{
                                                                        backgroundColor:
                                                                            item.color,
                                                                    }"
                                                                ></span>
                                                            </span>
                                                        </div>
                                                        <p
                                                            class="text-sm text-gray-500"
                                                        >
                                                            Qty:
                                                            {{ item.quantity }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <p class="font-medium">
                                                    {{
                                                        formatPrice(
                                                            item.total_price
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Order Summary -->
                                    <div class="p-4 bg-white border-t">
                                        <div
                                            class="flex justify-between items-center"
                                        >
                                            <div>
                                                <p class="font-medium">
                                                    Total Amount:
                                                </p>
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{
                                                        order.shipping_method ||
                                                        ""
                                                    }}
                                                </p>
                                            </div>
                                            <p class="font-bold">
                                                {{
                                                    formatPrice(
                                                        order.total_price
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                    <div v-else class="text-gray-500">No orders found</div>
                </div>

                <!-- Wishlist -->
                <div
                    v-else-if="selectedAction === 'wishlist'"
                    class="space-y-4"
                >
                    <h2 class="text-xl font-bold mb-4">Wishlist</h2>
                    <div v-if="wishlist?.length" class="grid grid-cols-3 gap-4">
                        <!-- Add wishlist items here -->
                        <p>Your wishlist items will appear here</p>
                    </div>
                    <div v-else class="text-gray-500">
                        Your wishlist is empty
                    </div>
                </div>

                <!-- Reviews -->
                <div v-else-if="selectedAction === 'reviews'" class="space-y-4">
                    <h2 class="text-xl font-bold mb-4">Ulasan Belanja</h2>
                    <div v-if="reviews?.length" class="space-y-4">
                        <!-- Add reviews content here -->
                        <p>Your reviews will appear here</p>
                    </div>
                    <div v-else class="text-gray-500">No reviews yet</div>
                </div>
            </div>
        </section>
    </CustomersLayout>
</template>

<style scoped>
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>

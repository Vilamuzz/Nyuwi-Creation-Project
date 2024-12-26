<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { ref } from "vue";

const props = defineProps({
    order: Object,
});

const showTrackingModal = ref(false);
const showTrackingInfo = ref(false);
const trackingInfo = ref(null);
const trackingError = ref(null);

// Add new state for payment proof modal
const showPaymentProofModal = ref(false);

const form = useForm({
    status: "processing",
    tracking_number: "",
});

// Function to track shipment
const trackShipment = async () => {
    try {
        const response = await axios.get(
            "https://api.binderbyte.com/v1/track",
            {
                params: {
                    api_key:
                        "151a782863970433251abbcbf51fe253f4625de1eda00f7a49ba55d90e7419a5",
                    courier: props.order.shipping_method.toLowerCase(),
                    awb: props.order.tracking_number,
                },
            }
        );

        if (response.data.status === 200) {
            trackingInfo.value = response.data.data;
            showTrackingInfo.value = true;
        } else {
            trackingError.value = response.data.message;
        }
    } catch (error) {
        trackingError.value = "Failed to fetch tracking information";
        console.error("Tracking error:", error);
    }
};

const openTrackingModal = () => {
    if (props.order.shipping_method === "GoSend") {
        updateStatus(props.order.id, "shiping");
    } else {
        showTrackingModal.value = true;
    }
};

const closeTrackingModal = () => {
    showTrackingModal.value = false;
    form.reset();
};

const confirmTracking = () => {
    form.put(route("orders.update", props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeTrackingModal();
        },
    });
};

const updateStatus = (orderId, newStatus) => {
    const statusForm = useForm({
        status: newStatus,
    });

    statusForm.put(route("orders.update", orderId), {
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

// Add method to open/close payment proof modal
const openPaymentProofModal = () => {
    showPaymentProofModal.value = true;
};

const closePaymentProofModal = () => {
    showPaymentProofModal.value = false;
};

// Add method to approve payment and update status
const approvePayment = () => {
    form.status = "processing";
    form.put(route("orders.update", props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            closePaymentProofModal();
        },
    });
};

const showEditTrackingModal = ref(false);
const editTrackingForm = useForm({
    tracking_number: "",
});

const openEditTrackingModal = () => {
    editTrackingForm.tracking_number = props.order.tracking_number;
    showEditTrackingModal.value = true;
};

const closeEditTrackingModal = () => {
    showEditTrackingModal.value = false;
    editTrackingForm.reset();
};

const updateTrackingNumber = () => {
    editTrackingForm.put(route("orders.update", props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditTrackingModal();
        },
    });
};

const cancelOrder = () => {
    if (confirm("Are you sure you want to cancel this order?")) {
        const cancelForm = useForm({
            status: "cancelled",
        });

        cancelForm.put(route("orders.update", props.order.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: tambahkan feedback sukses
            },
            onError: (errors) => {
                // Optional: tambahkan handling error
                console.error("Error cancelling order:", errors);
            },
        });
    }
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
                                <p>
                                    <span class="font-semibold"
                                        >Shipping Method:</span
                                    >
                                    {{ order.shipping_method || "Not set" }}
                                </p>
                            </div>
                            <div>
                                <p>
                                    <span class="font-semibold">Status:</span>
                                    <span
                                        :class="{
                                            'px-2 py-1 text-xs font-semibold rounded-full': true,
                                            'bg-yellow-100 text-yellow-800':
                                                order.status === 'waiting',
                                            'bg-purple-100 text-purple-800':
                                                order.status === 'checking',
                                            'bg-purple-100 text-cyan-800':
                                                order.status === 'shiping',
                                            'bg-orange-100 text-orange-800':
                                                order.status === 'pending',
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
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p>
                                    <span class="font-semibold">Address:</span>
                                    {{ order.address }}
                                </p>
                                <p>
                                    <span class="font-semibold">District:</span>
                                    {{ order.district }}
                                </p>
                                <p>
                                    <span class="font-semibold">Village:</span>
                                    {{ order.village }}
                                </p>
                            </div>
                            <div>
                                <p>
                                    <span class="font-semibold">City:</span>
                                    {{ order.city }}
                                </p>
                                <p>
                                    <span class="font-semibold">Province:</span>
                                    {{ order.province }}
                                </p>
                                <p>
                                    <span class="font-semibold">Phone:</span>
                                    {{ order.phone }}
                                </p>
                            </div>
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

                    <!-- Add tracking information section after order details -->
                    <div
                        v-if="
                            order.tracking_number &&
                            order.shipping_method !== 'GoSend'
                        "
                        class="mt-6"
                    >
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">
                                Tracking Information
                            </h3>
                            <button
                                @click="trackShipment"
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                            >
                                Track Shipment
                            </button>
                        </div>

                        <!-- Tracking Number Display -->
                        <p class="mb-4">
                            <span class="font-semibold">Tracking Number:</span>
                            {{ order.tracking_number }}
                            <button
                                v-if="
                                    order.tracking_number &&
                                    order.shipping_method !== 'GoSend'
                                "
                                @click="openEditTrackingModal"
                                class="ml-2 px-2 py-1 text-sm bg-yellow-500 text-white rounded hover:bg-yellow-600"
                            >
                                Edit
                            </button>
                        </p>

                        <!-- Tracking Information Display -->
                        <div
                            v-if="showTrackingInfo && trackingInfo"
                            class="border rounded-lg p-4"
                        >
                            <!-- Summary -->
                            <div class="mb-4">
                                <p>
                                    <span class="font-semibold">Status:</span>
                                    {{ trackingInfo.summary.status }}
                                </p>
                                <p>
                                    <span class="font-semibold">Service:</span>
                                    {{ trackingInfo.summary.service }}
                                </p>
                                <p>
                                    <span class="font-semibold">Amount:</span>
                                    {{ trackingInfo.summary.amount }}
                                </p>
                                <p>
                                    <span class="font-semibold">Weight:</span>
                                    {{ trackingInfo.summary.weight }}
                                </p>
                            </div>

                            <!-- Tracking History -->
                            <div class="space-y-4">
                                <h4 class="font-semibold">Tracking History</h4>
                                <div
                                    v-for="(
                                        history, index
                                    ) in trackingInfo.history"
                                    :key="index"
                                    class="border-l-2 border-gray-200 pl-4 pb-4 relative"
                                >
                                    <div
                                        class="absolute w-3 h-3 bg-blue-500 rounded-full -left-[7px]"
                                    ></div>
                                    <p class="font-semibold">
                                        {{ history.date }}
                                    </p>
                                    <p>{{ history.desc }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ history.location }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div v-if="trackingError" class="text-red-500 mt-2">
                            {{ trackingError }}
                        </div>
                    </div>

                    <!-- Status Update Buttons -->
                    <div class="mt-6 flex justify-end space-x-4">
                        <button
                            v-if="
                                order.status !== 'shiping' &&
                                order.status !== 'completed' &&
                                order.status !== 'cancelled'
                            "
                            @click="cancelOrder"
                            class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                        >
                            Cancel Order
                        </button>

                        <!-- Existing Accept Order button -->
                        <button
                            @click="openTrackingModal"
                            :disabled="
                                order.status === 'shiping' ||
                                order.status === 'completed' ||
                                order.status === 'cancelled' ||
                                (order.payment_method === 'digital_wallet' &&
                                    order.status === 'waiting')
                            "
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
                        >
                            Accept Order
                        </button>
                    </div>

                    <!-- Tracking Number Modal -->
                    <div
                        v-if="showTrackingModal"
                        class="fixed inset-0 z-50 overflow-y-auto"
                    >
                        <div
                            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                        ></div>

                        <div
                            class="flex min-h-full items-center justify-center p-4"
                        >
                            <div
                                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                            >
                                <!-- Modal Content -->
                                <div
                                    class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4"
                                >
                                    <div class="sm:flex sm:items-start">
                                        <div
                                            class="mt-3 text-center sm:mt-0 sm:text-left w-full"
                                        >
                                            <h3
                                                class="text-lg font-bold leading-6 text-gray-900"
                                            >
                                                Enter Tracking Number
                                            </h3>
                                            <div class="mt-4">
                                                <label
                                                    class="block text-sm font-medium text-gray-700"
                                                >
                                                    Tracking Number for
                                                    {{ order.shipping_method }}
                                                </label>
                                                <input
                                                    type="text"
                                                    v-model="
                                                        form.tracking_number
                                                    "
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    required
                                                />
                                                <div
                                                    v-if="
                                                        form.errors
                                                            .tracking_number
                                                    "
                                                    class="text-red-500 text-sm mt-1"
                                                >
                                                    {{
                                                        form.errors
                                                            .tracking_number
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Actions -->
                                <div
                                    class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
                                >
                                    <button
                                        type="button"
                                        class="inline-flex w-full justify-center rounded-md bg-blue-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-600 sm:ml-3 sm:w-auto"
                                        @click="confirmTracking"
                                        :disabled="!form.tracking_number"
                                    >
                                        Confirm
                                    </button>
                                    <button
                                        type="button"
                                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                        @click="closeTrackingModal"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Tracking Number Modal -->
                    <div
                        v-if="showEditTrackingModal"
                        class="fixed inset-0 z-50 overflow-y-auto"
                    >
                        <div
                            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                        ></div>
                        <div
                            class="flex min-h-full items-center justify-center p-4"
                        >
                            <div
                                class="relative bg-white rounded-lg max-w-lg w-full shadow-xl"
                            >
                                <div class="px-6 py-4 border-b">
                                    <h3 class="text-lg font-semibold">
                                        Edit Tracking Number
                                    </h3>
                                </div>

                                <div class="p-6">
                                    <div class="mb-4">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            New Tracking Number
                                        </label>
                                        <input
                                            v-model="
                                                editTrackingForm.tracking_number
                                            "
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            required
                                        />
                                    </div>

                                    <div class="flex justify-end space-x-3">
                                        <button
                                            @click="closeEditTrackingModal"
                                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            @click="updateTrackingNumber"
                                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                                            :disabled="
                                                editTrackingForm.processing
                                            "
                                        >
                                            {{
                                                editTrackingForm.processing
                                                    ? "Updating..."
                                                    : "Update"
                                            }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add button to view payment proof -->
                    <div
                        v-if="order.payment_method === 'digital_wallet'"
                        class="mt-4"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold">Payment Proof:</p>
                                <p
                                    v-if="order.payment_proof"
                                    class="text-sm text-gray-600"
                                >
                                    Payment proof uploaded
                                </p>
                                <p v-else class="text-sm text-red-500">
                                    Waiting for payment proof
                                </p>
                            </div>
                            <button
                                v-if="order.payment_proof"
                                @click="openPaymentProofModal"
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                            >
                                View Payment Proof
                            </button>
                        </div>
                    </div>

                    <!-- Payment Proof Modal -->
                    <div
                        v-if="showPaymentProofModal"
                        class="fixed inset-0 z-50 overflow-y-auto"
                    >
                        <div
                            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                        ></div>
                        <div
                            class="flex min-h-full items-center justify-center p-4"
                        >
                            <div
                                class="relative bg-white rounded-lg max-w-3xl w-full shadow-xl"
                            >
                                <!-- Modal Header -->
                                <div class="px-6 py-4 border-b">
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <h3 class="text-xl font-semibold">
                                            Payment Proof
                                        </h3>
                                        <button
                                            @click="closePaymentProofModal"
                                            class="text-gray-400 hover:text-gray-500"
                                        >
                                            ×
                                        </button>
                                    </div>
                                </div>

                                <!-- Modal Content -->
                                <div class="p-6">
                                    <div class="space-y-4">
                                        <div class="aspect-w-16 aspect-h-9">
                                            <img
                                                :src="`/storage/payment_proofs/${order.payment_proof}`"
                                                :alt="
                                                    'Payment proof for order #' +
                                                    order.id
                                                "
                                                class="object-contain w-full h-full"
                                            />
                                        </div>

                                        <!-- Action buttons for admin -->
                                        <div
                                            class="flex justify-end space-x-3 mt-4"
                                        >
                                            <button
                                                @click="closePaymentProofModal"
                                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                                            >
                                                Close
                                            </button>
                                            <button
                                                v-if="
                                                    order.status === 'waiting'
                                                "
                                                @click="approvePayment"
                                                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600"
                                            >
                                                Approve Payment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

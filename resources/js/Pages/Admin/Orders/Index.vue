<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineProps({
    orders: Array,
});
</script>

<template>
    <Head title="Orders Management" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Orders Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Order ID
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Customer
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Total Amount
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Date
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="order in orders" :key="order.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    #{{ order.id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ order.user.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ${{ order.total_amount }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="{
                                            'px-2 py-1 text-xs font-semibold rounded-full': true,
                                            'bg-yellow-100 text-yellow-800':
                                                order.status === 'pending',
                                            'bg-green-100 text-green-800':
                                                order.status === 'processing',
                                            'bg-blue-100 text-blue-800':
                                                order.status === 'completed',
                                            'bg-red-100 text-red-800':
                                                order.status === 'cancelled',
                                        }"
                                    >
                                        {{ order.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{
                                        new Date(
                                            order.created_at
                                        ).toLocaleDateString()
                                    }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap space-x-2"
                                >
                                    <button
                                        @click="
                                            updateStatus(order.id, 'processing')
                                        "
                                        class="px-3 py-1 text-sm text-white bg-blue-500 rounded hover:bg-blue-600"
                                    >
                                        Accept
                                    </button>
                                    <button
                                        @click="
                                            updateStatus(order.id, 'cancelled')
                                        "
                                        class="px-3 py-1 text-sm text-white bg-red-500 rounded hover:bg-red-600"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        @click="
                                            updateStatus(order.id, 'pending')
                                        "
                                        class="px-3 py-1 text-sm text-white bg-yellow-500 rounded hover:bg-yellow-600"
                                    >
                                        Pending
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

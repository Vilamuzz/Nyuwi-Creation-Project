<script setup>
import { Head } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
</script>

<template>
    <Head title="Dashboard Admin" />
    <AdminLayout>
        <div class="p-6 space-y-6">
            <!-- Search Bar -->
            <div class="relative w-3/4">
                <!-- Search Input -->
                <input
                    type="text"
                    placeholder="Search"
                    class="w-full px-4 py-1 border border-gray-100 bg-gray-100 -mt-6 rounded-md focus:outline-none focus:ring focus:ring-yellow-500 pr-10"
                />
            </div>
            <!-- Dashboard Content -->
            <div class="w-full p-4">
            </div>
        </div>
    </AdminLayout>
</template>

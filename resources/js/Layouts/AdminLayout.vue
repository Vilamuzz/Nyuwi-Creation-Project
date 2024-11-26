<script setup>
import Sidebar from "@/Components/Admin/Main/Sidebar.vue";
</script>

<template>
    <div class="flex">
        <Sidebar />
        <!-- Main Content -->
        <main class="ml-64 flex-1 p-4">
            <!-- Added margin-left to match sidebar width -->
            <slot></slot>
        </main>
    </div>
</template>

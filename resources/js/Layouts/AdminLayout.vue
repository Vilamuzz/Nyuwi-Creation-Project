<script setup>
import Sidebar from "@/Components/Admin/Main/Sidebar.vue";
import Navbar from "@/Components/Admin/Main/Navbar.vue";
import { ref, onMounted, onUnmounted } from "vue";

const props = defineProps({
    pageTitle: {
        type: String,
        default: "",
    },
});

// Track sidebar state
const isSidebarOpen = ref(
    localStorage.getItem("sidebarOpen") === "true" ||
        localStorage.getItem("sidebarOpen") === null
);

// Handle sidebar toggle events
const handleSidebarToggle = (event) => {
    isSidebarOpen.value = event.detail.isOpen;
};

onMounted(() => {
    // Listen for sidebar toggle events
    window.addEventListener("sidebarToggle", handleSidebarToggle);

    // Set initial state
    const stored = localStorage.getItem("sidebarOpen");
    isSidebarOpen.value = stored === "true" || stored === null;
});

onUnmounted(() => {
    window.removeEventListener("sidebarToggle", handleSidebarToggle);
});
</script>

<template>
    <div class="flex">
        <Sidebar />
        <!-- Main Content -->
        <main
            :class="[
                'flex-1 p-4 transition-all duration-300',
                isSidebarOpen ? 'ml-[230px]' : 'ml-16',
            ]"
        >
            <Navbar :title="pageTitle" />
            <slot></slot>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import Sidebar from "@/Components/Admin/Main/Sidebar.vue";
import Navbar from "@/Components/Admin/Main/Navbar.vue";

const props = defineProps({
    pageTitle: {
        type: String,
        default: "Admin Panel",
    },
});

// Mobile drawer state
const isMobileOpen = ref(false);

// Desktop sidebar collapsed state
const isCollapsed = ref(
    typeof window !== "undefined"
        ? localStorage.getItem("adminSidebarCollapsed") === "true"
        : false
);

const toggleMobileSidebar = () => {
    isMobileOpen.value = !isMobileOpen.value;
};

const closeMobileSidebar = () => {
    isMobileOpen.value = false;
};

const toggleDesktopSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem("adminSidebarCollapsed", isCollapsed.value.toString());
};

let removeFinishListener = null;

onMounted(() => {
    // Auto-close mobile drawer when navigating
    removeFinishListener = router.on("finish", () => {
        isMobileOpen.value = false;
    });

    const handleKeydown = (e) => {
        if (e.key === "Escape" && isMobileOpen.value) {
            closeMobileSidebar();
        }
    };
    window.addEventListener("keydown", handleKeydown);

    onUnmounted(() => {
        window.removeEventListener("keydown", handleKeydown);
    });
});

onUnmounted(() => {
    if (removeFinishListener) {
        removeFinishListener();
    }
});
</script>

<template>
    <div class="min-h-screen bg-stone-100/60 flex flex-col font-sans text-stone-800">
        <!-- Sidebar Component -->
        <Sidebar
            :is-mobile-open="isMobileOpen"
            :is-collapsed="isCollapsed"
            @close-mobile="closeMobileSidebar"
            @toggle-collapse="toggleDesktopSidebar"
        />

        <!-- Main Content Area -->
        <div
            :class="[
                'flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out',
                'ml-0',
                isCollapsed ? 'lg:ml-20' : 'lg:ml-60',
            ]"
        >
            <Navbar
                :title="pageTitle"
                :is-collapsed="isCollapsed"
                @toggle-mobile="toggleMobileSidebar"
            />

            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

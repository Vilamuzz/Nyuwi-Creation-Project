<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import {
    LayoutDashboard,
    Package,
    ShoppingCart,
    Settings,
    ChevronsRight,
    ChevronsLeft,
    Store
} from "lucide-vue-next";

// Initialize sidebar state from localStorage, default to true if not found
const isSidebarOpen = ref(
    localStorage.getItem("sidebarOpen") === "true" ||
        localStorage.getItem("sidebarOpen") === null
);

// Toggle sidebar
const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
    // Emit custom event for layout to listen
    window.dispatchEvent(
        new CustomEvent("sidebarToggle", {
            detail: { isOpen: isSidebarOpen.value },
        })
    );
};

// Watch for changes in sidebar state and save to localStorage
watch(
    isSidebarOpen,
    (newValue) => {
        localStorage.setItem("sidebarOpen", newValue.toString());
        // Emit event when state changes
        window.dispatchEvent(
            new CustomEvent("sidebarToggle", {
                detail: { isOpen: newValue },
            })
        );
    },
    { immediate: true }
);

const storeName = usePage().props.storeName;

// Updated navigation items array with Lucide icons
const navigation = [
    {
        name: "Dashboard",
        href: route("dashboard"),
        icon: LayoutDashboard,
    },
    {
        name: "Inventory",
        href: route("products.index"),
        icon: Package,
    },
    {
        name: "Orders",
        href: route("orders.show"),
        icon: ShoppingCart,
    },
    {
        name: "Profile Store",
        href: route("profile-store.edit", storeName),
        icon: Store,
    },
];
</script>

<template>
    <div class="relative">
        <!-- Sidebar -->
        <aside
            :class="[
                'fixed top-0 left-0 z-40 h-screen transition-all duration-300 bg-[#ffedd5] shadow-2xl pt-5',
                isSidebarOpen ? 'w-[230px]' : 'w-16',
            ]"
        >
            <!-- Logo Section -->
            <div class="flex items-center justify-center h-16">
                <ApplicationLogo
                    :class="[
                        'transition-all duration-300',
                        isSidebarOpen ? 'w-12 h-12' : 'w-8 h-8',
                    ]"
                />
            </div>

            <!-- Navigation Menu -->
            <div class="h-full px-3 py-4 overflow-y-auto border-r">
                <ul class="space-y-4">
                    <li v-for="item in navigation" :key="item.name">
                        <Link
                            :href="item.href"
                            :class="[
                                'flex items-center p-3 text-[#ffedd5] rounded-lg hover:bg-orange-200 hover:text-[#381d21] duration-200 group bg-[#ea580c]',
                                {
                                    'bg-orange-100': $page.url.startsWith(
                                        item.href
                                    ),
                                    'justify-center': !isSidebarOpen,
                                },
                            ]"
                            :title="!isSidebarOpen ? item.name : ''"
                        >
                            <component
                                :is="item.icon"
                                class="w-5 h-5 flex-shrink-0"
                            />
                            <span
                                v-if="isSidebarOpen"
                                class="ml-3 transition-opacity duration-300"
                            >
                                {{ item.name }}
                            </span>
                        </Link>
                    </li>
                </ul>

                <!-- Overlay Image -->
                <img
                    src="/img/overlay/flower2.png"
                    class="absolute bottom-0 left-0 w-auto h-96 object-cover opacity-20 pointer-events-none -z-10"
                />
                <!-- Bottom Section -->
                <div
                    :class="[
                        'absolute bottom-0 left-0 px-3 my-10 transition-all duration-300',
                        isSidebarOpen ? 'w-64' : 'w-16',
                    ]"
                >
                    <Link
                        :href="route('data')"
                        as="button"
                        :class="[
                            'flex items-center p-3 text-[#ffedd5] rounded-lg hover:bg-orange-200 hover:text-[#381d21] bg-[#ea580c] duration-200',
                            isSidebarOpen ? 'w-[200px]' : 'w-10 justify-center',
                        ]"
                        :title="!isSidebarOpen ? 'Setting' : ''"
                    >
                        <Settings class="w-5 h-5 flex-shrink-0" />
                        <span
                            v-if="isSidebarOpen"
                            class="ml-3 transition-opacity duration-300"
                        >
                            Setting
                        </span>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Toggle Button Outside Sidebar -->
        <button
            @click="toggleSidebar"
            :class="[
                'fixed z-50 top-1/2 p-2 bg-white shadow-lg border border-gray-200 text-gray-600 hover:text-black hover:bg-gray-50 transition-all duration-300',
                isSidebarOpen ? 'left-[210px]' : 'left-[44px]',
            ]"
        >
            <ChevronsRight v-if="!isSidebarOpen" class="w-5 h-5" />
            <ChevronsLeft v-else class="w-5 h-5" />
        </button>
    </div>
</template>

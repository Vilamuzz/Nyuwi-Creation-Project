<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";

// Updated navigation items array to match web.php routes
const navigation = [
    {
        name: "Dashboard",
        href: route("dashboard"), // Points to /dashboard
        icon: "fas fa-home",
    },
    {
        name: "Inventory",
        href: route("products.index"), // Points to /inventory (products.index)
        icon: "fas fa-box",
    },
    {
        name: "Profile",
        href: route("profile.edit"), // Points to /customers (customers.index)
        icon: "fas fa-users",
    },
];
</script>

<template>
    <aside
        class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0 bg-orange-100"
    >
        <!-- Logo Section -->
        <div class="flex items-center justify-center h-16">
            <ApplicationLogo class="w-12 h-12" />
        </div>
        <hr class="h-px bg-gray-200 border-0 dark:bg-gray-700" />

        <!-- Navigation Menu -->
        <div class="h-full px-3 py-4 overflow-y-auto border-r">
            <ul class="space-y-2">
                <li v-for="item in navigation" :key="item.name">
                    <Link
                        :href="item.href"
                        class="flex items-center p-3 text-gray-400 rounded-lg hover:bg-orange-200 hover:text-black duration-200 group"
                        :class="{
                            'bg-orange-100': $page.url.startsWith(item.href),
                        }"
                    >
                        <i
                            :class="[
                                item.icon,
                                'w-5 h-5 text-gray-500 transition duration-75 group-hover:text-orange-500',
                            ]"
                        ></i>
                        <span class="ml-3">{{ item.name }}</span>
                    </Link>
                </li>
            </ul>

            <!-- Bottom Section -->
            <div class="absolute bottom-0 left-0 w-full p-4 border-t">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex items-center w-full p-3 text-gray-900 rounded-lg hover:bg-orange-100"
                >
                    <i class="fas fa-sign-out-alt w-5 h-5 text-gray-500"></i>
                    <span class="ml-3">Logout</span>
                </Link>
            </div>
        </div>
    </aside>
</template>

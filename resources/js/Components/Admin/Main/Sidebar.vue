<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";

// Updated navigation items array to match web.php routes
const menu = [
    {
        name: "Dashboard",
        href: route("dashboard"), // Points to /dashboard
        icon: "/img/icon/dashboard.svg",
    },
    {
        name: "Inventory",
        href: route("products.index"), // Points to /inventory (products.index)
        icon: "/img/icon/cart2.svg",
    },
    {
        name: "Customers",
        href: route("orders.show"), // Points to /inventory (products.index)
        icon: "/img/icon/person.svg",
    },
    {
        name: "Customer Review",
        href: route("profile.edit"), // Points to /customers (customers.index)
        icon: "/img/icon/chatbubble.svg",
    },
];

const others = [
    {
        name: "Settings",
        href: route("dashboard"), // belum diubah
        icon: "/img/icon/settings.svg",
    },
    {
        name: "Accounts",
        href: route("products.index"), // belum diubah
        icon: "/img/icon/person.svg",
    },
    {
        name: "Help",
        href: route("orders.show"), // belum diubah
        icon: "/img/icon/help.svg",
    },
];
</script>

<template>
    <aside
        class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0 bg-[#fdf7f2]"
    >
        <!-- Logo Section -->
        <div class="flex items-center justify-center h-16 gap-4">
            <ApplicationLogo class="w-7 h-7" />
            <h1 class="font-black text-4xl">Nyuwi</h1>
        </div>
        <hr class="h-px bg-gray-200 border-0 dark:bg-gray-700" />

        <!-- Navigation Menu -->
        <div class="h-full px-3 py-20 overflow-y-auto border-r">
            <div class="flex h-5">
                <h1 class="text-gray-400 text-base ml-11">MENU</h1>
            </div>
            <ul>
                <li v-for="item in menu" :key="item.name">
                    <Link
                        :href="item.href"
                        class="flex items-center p-3 text-gray-400 rounded-lg hover:bg-orange-200 hover:text-black duration-200 group"
                        :class="{
                            'bg-orange-100': $page.url.startsWith(item.href),
                        }"
                    >

                        <!-- icon SVG -->
                        <img
                            :src="item.icon"
                            alt="Menu Icon"
                            class="w-5 h-5 ml-8 transition group-hover:scale-110"
                        />
                        <!-- nama menu-->
                        <span class="ml-3 transition group-hover:scale-110">{{ item.name }}</span>
                    </Link>
                </li>
            </ul>
            <div class="flex h-5 mt-12">
                <h1 class="text-gray-400 text-base ml-11">OTHERS</h1>
            </div>
            <ul>
                <li v-for="item in others" :key="item.name">
                    <Link
                        :href="item.href"
                        class="flex items-center p-3 text-gray-400 rounded-lg hover:bg-orange-200 hover:text-black duration-200 group"
                        :class="{
                            'bg-orange-100': $page.url.startsWith(item.href),
                        }"
                    >

                        <!-- icon SVG -->
                        <img
                            :src="item.icon"
                            alt="Others Icon"
                            class="w-5 h-5 ml-8 transition group-hover:scale-110"
                        />
                        <!-- nama menu-->
                        <span class="ml-3 transition group-hover:scale-110">{{ item.name }}</span>
                    </Link>
                </li>
            </ul>
        </div>
    </aside>
</template>

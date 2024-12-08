<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";

// Updated navigation items array to match web.php routes
const menu = [
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
        name: "Orders",
        href: route("orders.show"), // Points to /inventory (products.index)
        icon: "fas fa-box",
    },
    {
        name: "Profile",
        href: route("profile.edit"), // Points to /customers (customers.index)
        icon: "fas fa-users",
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
        class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0 bg-orange-100"
    >
        <!-- Logo Section -->
        <div class="flex items-center justify-center h-16">
            <ApplicationLogo class="w-12 h-12" />
        </div>
        <hr class="h-px bg-gray-200 border-0 dark:bg-gray-700" />

        <!-- Navigation Menu -->
<<<<<<< HEAD
        <div class="h-full px-3 py-20 overflow-y-auto border-r">
            <div class="flex h-5">
                <h1 class="text-gray-400 text-base ml-11">MENU</h1>
            </div>
            <ul>
                <li v-for="item in menu" :key="item.name">
=======
        <div class="h-full px-3 py-4 overflow-y-auto border-r">
            <ul class="space-y-2">
                <li v-for="item in navigation" :key="item.name">
>>>>>>> parent of 9235155 (edit hero.vue)
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
<<<<<<< HEAD
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
=======

            <!-- Bottom Section -->
            <div class="absolute bottom-0 left-0 w-full p-4 border-t">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex items-center p-3 text-gray-400 rounded-lg hover:bg-orange-200 hover:text-black duration-200"
                >
                    <i class="fas fa-sign-out-alt w-5 h-5 text-gray-500"></i>
                    <span class="ml-3">Logout</span>
                </Link>
            </div>
>>>>>>> parent of 9235155 (edit hero.vue)
        </div>
    </aside>
</template>

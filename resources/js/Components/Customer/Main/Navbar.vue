<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { usePage, Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    isLoggedIn: Boolean,
    orders: Array, // Tambahkan prop orders
});

// Computed property untuk cek order awaiting
const hasAwaitingOrders = computed(() => {
    return props.orders?.some((order) => order.status === "waiting");
});
</script>

<template>
    <div
        class="sticky top-0 p-4 px-10 flex flex-row justify-between items-center bg-white shadow-md z-50"
    >
        <!-- Logo -->
        <div class="flex items-center space-x-4">
            <ApplicationLogo class="h-10 w-auto fill-current text-gray-800" />
            <h1 class="font-bold text-2xl">Nyuwi</h1>
        </div>

        <!-- Links -->
        <div class="hidden md:flex space-x-10 text-gray-700">
            <<<<<<< HEAD
            <Link href="/" class="font-bold hover:text-[#b5853b]">Home</Link>
            <Link href="/shop" class="font-bold hover:text-[#b5853b]"
                >Shop</Link
            >
            <Link href="/about" class="font-bold hover:text-[#b5853b]"
                >About</Link
            >
            =======
            <Link href="/" class="font-bold hover:text-orange-500">Home</Link>
            <Link href="/shop" class="font-bold hover:text-orange-500"
                >Shop</Link
            >
            <Link href="/about" class="font-bold hover:text-orange-500">
                About
            </Link>
        </div>

        <!-- Icons or Actions -->
        <div class="flex items-center">
            <!-- Only show these items if user is logged in -->
            <template v-if="isLoggedIn">
                <!-- Dropdown -->
                <div class="dropdown relative">
                    <span
                        tabindex="0"
                        role="button"
                        class="btn btn-ghost m-1 hover:bg-transparent relative"
                    >
                        <img :src="'/img/icon/profile.svg'" alt="" />
                        <!-- Badge Notifikasi -->
                        <div
                            v-if="hasAwaitingOrders"
                            class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full"
                        ></div>
                    </span>
                    <ul
                        tabindex="0"
                        class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow"
                    >
                        <li>
                            <Link
                                :href="route('customer.profile')"
                                class="relative"
                            >
                                Profile
                                <!-- Badge di menu dropdown -->
                                <span
                                    v-if="hasAwaitingOrders"
                                    class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-500 rounded-full ml-2"
                                >
                                    !
                                </span>
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('logout')" method="post"
                                >Logout</Link
                            >
                        </li>
                    </ul>
                </div>

                <!-- Wishlist -->
                <span
                    role="button"
                    class="btn btn-ghost m-1 hover:bg-transparent"
                >
                    <img :src="'/img/icon/wishlist.svg'" alt="" />
                </span>

                <!-- Cart -->
                <Link
                    :href="route('cart.show')"
                    class="font-bold hover:text-orange-500"
                >
                    <span
                        role="button"
                        class="btn btn-ghost m-1 hover:bg-transparent"
                    >
                        <img :src="'/img/icon/cart.svg'" alt="" />
                    </span>
                </Link>
            </template>

            <!-- Show login/register links if user is not logged in -->
            <template v-else>
                <Link
                    :href="route('login')"
                    class="font-bold hover:text-orange-500 mr-4"
                >
                    Login
                </Link>
                <Link
                    :href="route('register')"
                    class="font-bold hover:text-orange-500"
                >
                    Register
                </Link>
            </template>
        </div>
    </div>
</template>

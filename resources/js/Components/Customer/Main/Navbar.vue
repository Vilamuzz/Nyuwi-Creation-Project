<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

import { usePage, Link } from "@inertiajs/vue3";

const { props } = usePage();
const isLoggedIn = props.auth.user;
const role = props.auth?.user?.role || null;
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
            <Link href="/" class="font-bold hover:text-orange-500">Home</Link>
            <Link href="/shop" class="font-bold hover:text-orange-500"
                >Shop</Link
            >
            <Link href="#" class="font-bold hover:text-orange-500">About</Link>
            <Link href="#" class="font-bold hover:text-orange-500"
                >Contact</Link
            >
        </div>

        <!-- Icons or Actions -->
        <div class="flex items-center">
            <div class="dropdown">
                <span
                    tabindex="0"
                    role="button"
                    class="btn btn-ghost m-1 hover:bg-transparent"
                    ><img :src="'/img/icon/profile.svg'" alt=""
                /></span>
                <ul
                    tabindex="0"
                    class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow"
                >
                    <li v-if="!isLoggedIn">
                        <Link :href="route('login')">Login</Link>
                    </li>
                    <li v-if="!isLoggedIn">
                        <Link :href="route('register')">Register</Link>
                    </li>
                    <li v-else>
                        <Link :href="route('logout')" method="post"
                            >Logout</Link
                        >
                    </li>
                </ul>
            </div>
            <span role="button" class="btn btn-ghost m-1 hover:bg-transparent"
                ><img :src="'/img/icon/wishlist.svg'" alt=""
            /></span>
            <Link
                :href="route('cart.show')"
                class="font-bold hover:text-orange-500"
                ><span
                    role="button"
                    class="btn btn-ghost m-1 hover:bg-transparent"
                    ><img :src="'/img/icon/cart.svg'" alt="" /></span
            ></Link>
        </div>
    </div>
</template>

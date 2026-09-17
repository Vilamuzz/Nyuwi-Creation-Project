<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
import { Search, ShoppingCart, User } from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref } from "vue";

const props = defineProps({
    isLoggedIn: Boolean,
    orders: Array,
});

const hasAwaitingOrders = computed(() =>
    props.orders?.some((order) => order.status === "waiting")
);

const isProfileOpen = ref(false);
const profileMenu = ref(null);

const toggleProfile = () => {
    isProfileOpen.value = !isProfileOpen.value;
};

const closeProfile = () => {
    isProfileOpen.value = false;
};

const handleClickOutside = (event) => {
    if (profileMenu.value && !profileMenu.value.contains(event.target)) {
        closeProfile();
    }
};

const handleKeydown = (event) => {
    if (event.key === "Escape") {
        closeProfile();
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
    document.addEventListener("keydown", handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
    document.removeEventListener("keydown", handleKeydown);
});
</script>

<template>
    <div
        class="sticky top-0 p-4 px-10 flex flex-row justify-between items-center bg-white shadow-md z-50"
    >
        <!-- Logo -->
        <div class="flex items-center space-x-4">
            <Link href="/">
                <ApplicationLogo
                    class="h-10 w-auto fill-current text-gray-800"
                />
            </Link>
        </div>

        <!-- Links -->
        <div class="hidden md:flex space-x-10 text-gray-700 text-xl">
            <Link
                href="/shop"
                class="font-bold rounded hover:text-orange-500"
                >New & Featured</Link
            >
            <Link
                href="/shop"
                class="font-bold rounded hover:text-orange-500"
                >Boquets</Link
            >
            <Link
                href="/shop"
                class="font-bold rounded hover:text-orange-500"
                >Flowers</Link
            >
            <Link
                href="/shop"
                class="font-bold rounded hover:text-orange-500"
                >Accessories</Link
            >
            <Link
                href="/shop"
                class="font-bold rounded hover:text-orange-500"
                >Bags</Link
            >
            <Link
                href="/shop"
                class="font-bold rounded hover:text-orange-500"
                >Sale</Link
            >
        </div>

        <!-- Icons or Actions -->
        <div class="flex items-center">
            <!-- Only show these items if user is logged in -->
            <template v-if="isLoggedIn">
                <!-- Wishlist -->
                <Link
                    :href="route('wishlist.index')"
                    class="inline-flex items-center justify-center rounded-lg p-2 text-gray-700 transition hover:bg-gray-100 hover:text-orange-500"
                >
                    <img :src="'/img/icon/wishlist.svg'" alt="Wishlist" />
                </Link>

                <!-- Cart -->
                <Link
                    :href="route('cart.show')"
                    class="inline-flex items-center justify-center rounded-lg p-2 text-gray-700 transition hover:bg-gray-100 hover:text-orange-500"
                >
                    <img :src="'/img/icon/cart.svg'" alt="Cart" />
                </Link>

                <!-- Profile Dropdown -->
                <div ref="profileMenu" class="relative ml-1">
                    <button
                        type="button"
                        @click="toggleProfile"
                        class="inline-flex items-center justify-center rounded-lg p-2 text-gray-700 transition hover:bg-gray-100 hover:text-orange-500 relative"
                        aria-haspopup="true"
                        :aria-expanded="isProfileOpen"
                    >
                        <img :src="'/img/icon/profile.svg'" alt="Profile" />
                        <span
                            v-if="hasAwaitingOrders"
                            class="absolute -top-0.5 -right-0.5 h-3 w-3 rounded-full bg-red-500"
                        ></span>
                    </button>
                    <div
                        v-if="isProfileOpen"
                        class="absolute right-0 mt-2 w-52 rounded-lg border border-gray-200 bg-white shadow-lg z-50"
                    >
                        <Link
                            :href="route('customer.profile')"
                            class="flex items-center justify-between rounded-t-lg px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50"
                            @click="closeProfile"
                        >
                            Profile
                            <span
                                v-if="hasAwaitingOrders"
                                class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"
                            >
                                !
                            </span>
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="block w-full rounded-b-lg px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50"
                            @click="closeProfile"
                        >
                            Logout
                        </Link>
                    </div>
                </div>
            </template>

            <!-- Show login/register buttons if user is not logged in -->
            <template v-else>
                <div class="flex items-center gap-8">
                    <Link
                        class="inline-flex min-h-9 items-center justify-center text-sm"
                    >
                        <Search />
                    </Link>
                    <Link
                        :href="route('login')"
                        class="inline-flex min-h-9 items-center justify-center text-sm"
                    >
                        <User />
                    </Link>
                    <Link
                        class="inline-flex min-h-9 items-center justify-center text-sm"
                    >
                        <ShoppingCart />
                    </Link>
                </div>
            </template>
        </div>
    </div>
</template>

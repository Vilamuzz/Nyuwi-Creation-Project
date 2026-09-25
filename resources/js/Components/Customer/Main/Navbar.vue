<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import MenuDrawer from "@/Components/Customer/Main/MenuDrawer.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { ArrowRight, Loader2, Menu, Package, Search, ShoppingCart, User, X } from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref } from "vue";

const props = defineProps({
    isLoggedIn: Boolean,
    orders: Array,
});

const emit = defineEmits(["show-cart"]);

const page = usePage();
const cartItemCount = computed(() =>
    page.props.cart?.summary?.itemCount || 0
);

const hasAwaitingOrders = computed(() =>
    props.orders?.some((order) => order.payment_status === "pending")
);

const isMenuOpen = ref(false);

const isProfileOpen = ref(false);
const profileMenu = ref(null);

const isSearchOpen = ref(false);
const searchQuery = ref("");
const searchResults = ref([]);
const isSearching = ref(false);
const searchContainer = ref(null);
const searchInputRef = ref(null);
let searchDebounceTimer = null;

const toggleProfile = () => {
    isProfileOpen.value = !isProfileOpen.value;
    if (isProfileOpen.value) {
        isSearchOpen.value = false;
    }
};

const closeProfile = () => {
    isProfileOpen.value = false;
};

const toggleSearch = () => {
    isSearchOpen.value = !isSearchOpen.value;
    if (isSearchOpen.value) {
        isProfileOpen.value = false;
        setTimeout(() => {
            searchInputRef.value?.focus();
        }, 50);
    }
};

const closeSearch = () => {
    isSearchOpen.value = false;
};

const clearSearch = () => {
    searchQuery.value = "";
    searchResults.value = [];
    searchInputRef.value?.focus();
};

const performSearch = async (query) => {
    if (!query.trim()) {
        searchResults.value = [];
        isSearching.value = false;
        return;
    }
    isSearching.value = true;
    try {
        const response = await fetch(`/api/products/search?q=${encodeURIComponent(query)}`);
        if (response.ok) {
            searchResults.value = await response.json();
        }
    } catch (e) {
        console.error("Search error:", e);
    } finally {
        isSearching.value = false;
    }
};

const handleSearchInput = () => {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        performSearch(searchQuery.value);
    }, 300);
};

const submitSearch = () => {
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.trim();
        closeSearch();
        router.visit(`/sale?search=${encodeURIComponent(q)}`);
    }
};

const handleClickOutside = (event) => {
    if (profileMenu.value && !profileMenu.value.contains(event.target)) {
        closeProfile();
    }
    if (searchContainer.value && !searchContainer.value.contains(event.target)) {
        closeSearch();
    }
};

const handleKeydown = (event) => {
    if (event.key === "Escape") {
        closeProfile();
        closeSearch();
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
    <div class="sticky top-0 p-4 px-4 sm:px-10 flex flex-row justify-between items-center bg-white shadow-md z-50">
        <!-- Mobile menu + search (left, mobile only) -->
        <div class="flex items-center gap-1 md:hidden">
            <button type="button"
                class="inline-flex items-center justify-center p-2 text-gray-700 hover:text-orange-500 transition"
                aria-label="Open menu" @click="isMenuOpen = true">
                <Menu />
            </button>
            <button type="button"
                class="inline-flex items-center justify-center p-2 text-gray-700 hover:text-orange-500 transition"
                aria-label="Search" @click="toggleSearch">
                <Search />
            </button>
        </div>

        <!-- Logo (centered on mobile, left on md+) -->
        <Link href="/" class="flex items-center absolute left-1/2 -translate-x-1/2 md:static md:translate-x-0">
            <ApplicationLogo class="h-10 w-auto fill-current text-gray-800" />
        </Link>

        <!-- Links -->
        <div class="hidden md:flex space-x-10 text-gray-700 text-xl">
            <Link href="/new-featured" class="font-bold rounded hover:text-orange-500">New & Featured</Link>
            <Link href="/boquets" class="font-bold rounded hover:text-orange-500">Boquets</Link>
            <Link href="/flowers" class="font-bold rounded hover:text-orange-500">Flowers</Link>
            <Link href="/accessories" class="font-bold rounded hover:text-orange-500">Accessories</Link>
            <Link href="/bags" class="font-bold rounded hover:text-orange-500">Bags</Link>
            <Link href="/sale" class="font-bold rounded hover:text-orange-500">Sale</Link>
        </div>

        <!-- Icons or Actions -->
        <div ref="searchContainer" class="relative flex items-center">
            <!-- Only show these items if user is logged in -->
            <template v-if="$page.props.auth.user">
                <!-- Search Button (Auth) -->
                <button type="button"
                    class="inline-flex items-center justify-center p-2 text-gray-700 hover:text-orange-500 transition"
                    aria-label="Search" @click="toggleSearch">
                    <Search />
                </button>

                <!-- Cart -->
                <button type="button"
                    class="inline-flex items-center justify-center p-2 text-gray-700 hover:text-orange-500 transition relative"
                    aria-label="Open cart" @click="emit('show-cart')">
                    <ShoppingCart />
                    <span v-if="cartItemCount > 0"
                        class="absolute -top-0.5 -right-0.5 h-4 min-w-4 px-1 rounded-full bg-orange-500 text-white text-[10px] font-bold flex items-center justify-center">
                        {{ cartItemCount > 99 ? "99+" : cartItemCount }}
                    </span>
                </button>

                <!-- Profile Dropdown -->
                <div ref="profileMenu" class="relative ml-1">
                    <button type="button" @click="toggleProfile"
                        class="inline-flex items-center justify-center p-2 text-gray-700 transition hover:text-orange-500 relative"
                        aria-haspopup="true" :aria-expanded="isProfileOpen">
                        <User />
                        <span v-if="hasAwaitingOrders"
                            class="absolute -top-0.5 -right-0.5 h-3 w-3 rounded-full bg-red-500"></span>
                    </button>
                    <div v-if="isProfileOpen"
                        class="absolute right-0 mt-2 w-52 rounded-lg border border-gray-200 bg-white shadow-lg z-50">
                        <Link :href="route('customer.profile')"
                            class="flex items-center justify-between rounded-t-lg px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50"
                            @click="closeProfile">
                            Profile
                            <span v-if="hasAwaitingOrders"
                                class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                                !
                            </span>
                        </Link>
                        <Link :href="route('logout')" method="post" as="button"
                            class="block w-full rounded-b-lg px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50"
                            @click="closeProfile">
                            Logout
                        </Link>
                    </div>
                </div>
            </template>

            <!-- Show login/register buttons if user is not logged in -->
            <template v-else>
                <div class="flex items-center gap-1 sm:gap-2">
                    <button type="button"
                        class="hidden md:inline-flex items-center justify-center p-2 text-gray-700 hover:text-orange-500 transition"
                        aria-label="Search" @click="toggleSearch">
                        <Search />
                    </button>
                    <button type="button"
                        class="inline-flex items-center justify-center p-2 text-gray-700 hover:text-orange-500 transition relative"
                        aria-label="Open cart" @click="emit('show-cart')">
                        <ShoppingCart />
                        <span v-if="cartItemCount > 0"
                            class="absolute -top-0.5 -right-0.5 h-4 min-w-4 px-1 rounded-full bg-orange-500 text-white text-[10px] font-bold flex items-center justify-center">
                            {{ cartItemCount > 99 ? "99+" : cartItemCount }}
                        </span>
                    </button>
                    <Link :href="route('login')"
                        class="hidden md:inline-flex items-center justify-center p-2 text-gray-700 hover:text-orange-500 transition"
                        aria-label="Login">
                        <User />
                    </Link>
                </div>
            </template>

            <!-- Floating Search Dropdown Window -->
            <div v-if="isSearchOpen"
                class="absolute right-0 top-full mt-3 w-80 sm:w-96 bg-white rounded-2xl border border-gray-200 shadow-2xl p-4 z-50 animate-in fade-in zoom-in-95 duration-150">
                <!-- Search Input Header -->
                <div class="relative flex items-center">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input ref="searchInputRef" v-model="searchQuery" @input="handleSearchInput"
                        @keydown.enter="submitSearch" type="text" placeholder="Search products..."
                        class="w-full pl-10 pr-9 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none transition" />
                    <button v-if="searchQuery" type="button" @click="clearSearch"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-0.5">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Results Area -->
                <div class="mt-3 max-h-80 overflow-y-auto divide-y divide-gray-100">
                    <!-- Loading state -->
                    <div v-if="isSearching" class="py-6 flex items-center justify-center text-gray-400 gap-2 text-sm">
                        <Loader2 class="w-4 h-4 animate-spin text-orange-500" />
                        <span>Searching products...</span>
                    </div>

                    <!-- Results list -->
                    <template v-else-if="searchResults.length > 0">
                        <div class="py-1">
                            <Link v-for="product in searchResults" :key="product.id" :href="`/product/${product.slug}`"
                                @click="closeSearch"
                                class="flex items-center gap-3 p-2 rounded-xl hover:bg-orange-50/80 transition group">
                                <img v-if="product.image" :src="product.image" :alt="product.name"
                                    class="w-12 h-12 object-cover rounded-lg border border-gray-100 shrink-0" />
                                <div v-else
                                    class="w-12 h-12 rounded-lg bg-orange-100 text-orange-500 flex items-center justify-center shrink-0">
                                    <Package class="w-6 h-6" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div
                                        class="text-sm font-semibold text-gray-800 truncate group-hover:text-orange-600 transition">
                                        {{ product.name }}
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span v-if="product.category"
                                            class="text-[11px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full font-medium">
                                            {{ product.category }}
                                        </span>
                                        <span class="text-xs font-bold text-orange-600">
                                            {{ product.formatted_price }}
                                        </span>
                                    </div>
                                </div>
                            </Link>
                        </div>
                        <!-- View all results footer -->
                        <div class="pt-2">
                            <button type="button" @click="submitSearch"
                                class="w-full py-2 px-3 flex items-center justify-center gap-2 text-xs font-semibold text-orange-600 hover:bg-orange-50 rounded-xl transition">
                                <span>See all results for "{{ searchQuery }}"</span>
                                <ArrowRight class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </template>

                    <!-- Empty state when searching with no results -->
                    <div v-else-if="searchQuery.trim() !== ''" class="py-8 text-center text-gray-400">
                        <p class="text-sm font-medium text-gray-600">No products found</p>
                        <p class="text-xs mt-1">Try searching with a different keyword</p>
                    </div>

                    <!-- Prompt state when query is empty -->
                    <div v-else class="py-6 text-center text-xs text-gray-400">
                        Type a product name or category to search...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <MenuDrawer :is-open="isMenuOpen" @close="isMenuOpen = false" />
</template>

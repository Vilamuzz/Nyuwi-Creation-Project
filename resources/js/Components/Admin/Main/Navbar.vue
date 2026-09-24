<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import {
    LogOut,
    UserPen,
    Store,
    ExternalLink,
    Menu,
    ChevronDown,
} from "lucide-vue-next";

const props = defineProps({
    title: {
        type: String,
        default: "Admin Panel",
    },
    isCollapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["toggleMobile", "toggleDesktop"]);

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const storeName = computed(() => page.props.storeName || "store");

const isDropdownOpen = ref(false);

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const closeDropdown = () => {
    isDropdownOpen.value = false;
};

const userInitial = computed(() => {
    if (user.value?.name) {
        return user.value.name.charAt(0).toUpperCase();
    }
    return "A";
});

const handleClickOutside = (e) => {
    if (isDropdownOpen.value && !e.target.closest("#user-profile-menu")) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
    <header
        class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-stone-200/80 px-4 sm:px-6 py-3 shadow-2xs">
        <div class="flex items-center justify-between gap-4">
            <!-- Left Side: Toggles & Title -->
            <div class="flex items-center gap-3 min-w-0">
                <!-- Mobile Drawer Toggle -->
                <button type="button"
                    class="lg:hidden p-2 -ml-1 text-stone-600 hover:text-stone-900 hover:bg-orange-50 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500"
                    aria-label="Buka menu navigasi" @click="emit('toggleMobile')">
                    <Menu class="w-6 h-6" />
                </button>

                <!-- Page Title -->
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-stone-800 tracking-tight truncate">
                        {{ title }}
                    </h1>
                </div>
            </div>

            <!-- Right Side: Store Preview & User Profile -->
            <div class="flex items-center gap-2 sm:gap-4 flex-shrink-0">
                <!-- User Profile Dropdown -->
                <div id="user-profile-menu" class="relative">
                    <button type="button"
                        class="flex items-center gap-2 p-1 sm:px-2 sm:py-1.5 rounded-full sm:rounded-xl hover:bg-stone-100 transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500"
                        aria-haspopup="true" :aria-expanded="isDropdownOpen" @click.stop="toggleDropdown">
                        <!-- Avatar -->
                        <div
                            class="w-9 h-9 rounded-full bg-gradient-to-tr from-orange-500 to-amber-500 text-white font-bold text-sm flex items-center justify-center shadow-xs">
                            {{ userInitial }}
                        </div>

                        <!-- User Info on larger screens -->
                        <div class="hidden md:flex flex-col text-left">
                            <span class="text-xs font-semibold text-stone-800 leading-tight max-w-[120px] truncate">
                                {{ user.name || "Administrator" }}
                            </span>
                            <span class="text-[10px] font-medium text-orange-600">
                                Admin Toko
                            </span>
                        </div>

                        <ChevronDown class="hidden sm:block w-4 h-4 text-stone-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isDropdownOpen }" />
                    </button>

                    <!-- Dropdown Menu -->
                    <transition enter-active-class="transition duration-150 ease-out"
                        enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="transform scale-100 opacity-100"
                        leave-to-class="transform scale-95 opacity-0">
                        <div v-if="isDropdownOpen"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-stone-100 py-1.5 z-50 divide-y divide-stone-100">
                            <!-- User header details -->
                            <div class="px-4 py-2.5">
                                <p class="text-xs font-semibold text-stone-900 truncate">
                                    {{ user.name || "Administrator" }}
                                </p>
                                <p class="text-[11px] text-stone-500 truncate">
                                    {{ user.email || "" }}
                                </p>
                            </div>

                            <!-- Links -->
                            <div class="py-1">
                                <Link :href="route('profile.edit')"
                                    class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-stone-700 hover:bg-stone-50 hover:text-stone-900 transition-colors"
                                    @click="closeDropdown">
                                    <UserPen class="w-4 h-4 text-stone-500" />
                                    <span>Profil Saya</span>
                                </Link>
                            </div>

                            <!-- Logout -->
                            <div class="py-1">
                                <Link :href="route('logout')" method="post" as="button"
                                    class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors text-left"
                                    @click="closeDropdown">
                                    <LogOut class="w-4 h-4 text-red-500" />
                                    <span>Logout</span>
                                </Link>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
    </header>
</template>

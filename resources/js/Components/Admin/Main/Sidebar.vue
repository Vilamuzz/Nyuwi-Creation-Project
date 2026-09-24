<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import {
    LayoutDashboard,
    Package,
    ShoppingCart,
    Store,
    UserPen,
    Database,
    X,
    ExternalLink,
    ChevronsLeft,
    ChevronsRight,
} from "lucide-vue-next";

const props = defineProps({
    isMobileOpen: {
        type: Boolean,
        default: false,
    },
    isCollapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["closeMobile", "toggleCollapse"]);

const page = usePage();
const storeName = computed(() => page.props.storeName || "store");

const currentUrl = computed(() => page.url);

const isRouteActive = (url) => {
    if (!url || url === "#") return false;
    try {
        const path = new URL(url, window.location.origin).pathname;
        return currentUrl.value.startsWith(path);
    } catch {
        return currentUrl.value.startsWith(url);
    }
};

const mainNavigation = computed(() => [
    {
        name: "Dashboard",
        href: route("dashboard"),
        icon: LayoutDashboard,
    },
    {
        name: "Inventaris",
        href: route("products.index"),
        icon: Package,
    },
    {
        name: "Pesanan",
        href: route("admin.orders.index"),
        icon: ShoppingCart,
    },
]);

const secondaryNavigation = computed(() => [
    {
        name: "Profil Toko",
        href: route("profile-store.index"),
        icon: Store,
    },
    {
        name: "Data Wilayah",
        href: route("data"),
        icon: Database,
    },
]);
</script>

<template>
    <div>
        <!-- Mobile Backdrop Overlay -->
        <transition enter-active-class="transition-opacity duration-300 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="isMobileOpen" class="fixed inset-0 z-40 bg-stone-900/50 backdrop-blur-xs lg:hidden"
                aria-hidden="true" @click="emit('closeMobile')"></div>
        </transition>

        <!-- Sidebar Container -->
        <aside :class="[
            'fixed top-0 bottom-0 left-0 z-50 flex flex-col bg-[#fffaf5] border-r border-orange-100/80 shadow-xl lg:shadow-none transition-all duration-300 ease-in-out',
            // Mobile slide state
            isMobileOpen
                ? 'translate-x-0 w-72 max-w-[85vw]'
                : '-translate-x-full lg:translate-x-0',
            // Desktop width
            isCollapsed ? 'lg:w-20' : 'lg:w-60',
        ]" aria-label="Sidebar Menu">
            <!-- Logo Section -->
            <div
                class="relative flex items-center justify-between h-16 px-4 border-b border-orange-100/60 bg-[#fff5ea]">
                <button type="button" @click="emit('toggleCollapse')"
                    class="flex items-center gap-3 overflow-hidden group">
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-xl bg-orange-500/10 group-hover:bg-orange-500/20 transition-colors">
                        <ApplicationLogo class="w-7 h-7 object-contain" />
                    </div>
                    <div v-if="!isCollapsed || isMobileOpen" class="flex flex-col min-w-0">
                        <span class="text-sm font-bold text-stone-800 tracking-tight truncate">
                            Nyuwi Creation
                        </span>
                        <span class="text-[11px] font-medium text-orange-600 truncate">
                            Admin Workspace
                        </span>
                    </div>
                </button>

                <button v-if="!isCollapsed" type="button"
                    class="hidden lg:flex items-center gap-2.5 w-auto px-3 py-2 rounded-xl text-xs font-semibold text-stone-500 hover:text-stone-800 hover:bg-orange-100/60 transition-colors"
                    :title="isCollapsed ? 'Perluas Menu' : 'Ciutkan Menu'" @click="emit('toggleCollapse')">
                    <ChevronsLeft class="w-4 h-4" />
                </button>

                <!-- Mobile Close Button -->
                <button type="button"
                    class="lg:hidden p-2 text-stone-500 hover:text-stone-800 hover:bg-orange-100/60 rounded-lg transition-colors"
                    aria-label="Tutup Menu" @click="emit('closeMobile')">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 px-3 py-4 space-y-6 overflow-y-auto overflow-x-hidden relative">
                <!-- Main Nav Group -->
                <div>
                    <div v-if="!isCollapsed || isMobileOpen"
                        class="px-3 mb-2 text-[10px] font-bold tracking-wider text-stone-600 uppercase">
                        Menu Utama
                    </div>
                    <ul class="space-y-1">
                        <li v-for="item in mainNavigation" :key="item.name">
                            <Link :href="item.href" :title="isCollapsed && !isMobileOpen
                                ? item.name
                                : ''
                                " :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150',
                                    isRouteActive(item.href)
                                        ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/20'
                                        : 'text-stone-600 hover:text-orange-600 hover:bg-orange-100/60',
                                    isCollapsed && !isMobileOpen
                                        ? 'justify-center px-2'
                                        : '',
                                ]">
                                <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
                                <span v-if="!isCollapsed || isMobileOpen" class="truncate">
                                    {{ item.name }}
                                </span>
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Secondary Nav Group -->
                <div>
                    <div v-if="!isCollapsed || isMobileOpen"
                        class="px-3 mb-2 text-[10px] font-bold tracking-wider text-stone-600 uppercase">
                        Pengaturan & Toko
                    </div>
                    <ul class="space-y-1">
                        <li v-for="item in secondaryNavigation" :key="item.name">
                            <Link :href="item.href" :title="isCollapsed && !isMobileOpen
                                ? item.name
                                : ''
                                " :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150',
                                    isRouteActive(item.href)
                                        ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/20'
                                        : 'text-stone-600 hover:text-orange-600 hover:bg-orange-100/60',
                                    isCollapsed && !isMobileOpen
                                        ? 'justify-center px-2'
                                        : '',
                                ]">
                                <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
                                <span v-if="!isCollapsed || isMobileOpen" class="truncate">
                                    {{ item.name }}
                                </span>
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Subtle Decorative Overlay -->
                <img src="/img/overlay/flower2.png" alt=""
                    class="absolute bottom-0 left-0 w-full h-[13vw] object-cover opacity-10 pointer-events-none -z-10 select-none"
                    aria-hidden="true" />
            </div>

            <!-- Footer Section -->
            <div class="p-3 border-t border-orange-100/80 bg-[#fff5ea]/70 flex flex-col gap-2">
                <!-- View Storefront Action -->
                <a :href="route('home')" target="_blank" rel="noopener noreferrer"
                    :title="isCollapsed && !isMobileOpen ? 'Buka Toko' : ''" :class="[
                        'flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-stone-700 bg-white/80 hover:bg-white hover:text-orange-600 border border-orange-200/60 transition-colors shadow-2xs',
                        isCollapsed && !isMobileOpen
                            ? 'justify-center px-2'
                            : '',
                    ]">
                    <ExternalLink class="w-4 h-4 flex-shrink-0 text-orange-500" />
                    <span v-if="!isCollapsed || isMobileOpen" class="truncate">
                        Buka Toko
                    </span>
                </a>
            </div>
        </aside>
    </div>
</template>

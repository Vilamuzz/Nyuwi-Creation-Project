<script setup>
import { Link } from "@inertiajs/vue3";
import { ChevronRight, X } from "lucide-vue-next";
import { onMounted, onUnmounted } from "vue";

const props = defineProps({
    isOpen: Boolean,
});

const emit = defineEmits(["close"]);

const navItems = [
    { label: "New & Featured", href: "/new-featured" },
    { label: "Boquets", href: "/boquets" },
    { label: "Flowers", href: "/flowers" },
    { label: "Accessories", href: "/accessories" },
    { label: "Bags", href: "/bags" },
    { label: "Sale", href: "/sale" },
    { label: "Sign In/Create Account", href: "/login" },
];

const handleKeydown = (event) => {
    if (event.key === "Escape" && props.isOpen) {
        emit("close");
    }
};

onMounted(() => {
    document.addEventListener("keydown", handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener("keydown", handleKeydown);
});
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isOpen"
            class="fixed inset-0 z-50 bg-black/30 transition-opacity duration-300 ease-out"
            @click="emit('close')"
        />
    </Transition>

    <Transition
        enter-active-class="transition-transform duration-300 ease-out"
        enter-from-class="translate-y-full md:translate-y-0 md:translate-x-full"
        enter-to-class="translate-y-0 md:translate-x-0"
        leave-active-class="transition-transform duration-300 ease-in"
        leave-from-class="translate-y-0 md:translate-x-0"
        leave-to-class="translate-y-full md:translate-y-0 md:translate-x-full"
    >
        <div
            v-if="isOpen"
            id="menu-drawer"
            class="fixed inset-0 z-[51] overflow-hidden pointer-events-none p-4 flex items-end justify-center md:items-stretch md:justify-end"
        >
            <div
                class="pointer-events-auto h-3/4 max-h-[80vh] w-full max-w-md bg-white flex flex-col shadow-xl md:h-full md:max-h-none rounded-2xl"
            >
                <!-- Nav links -->
                <div class="flex-1 overflow-y-auto flex flex-col px-10 py-6">
                    <Link
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        class="flex items-center justify-between py-6 border-b border-slate-500 text-xl font-bold hover:text-orange-500 transition-colors"
                        @click="emit('close')"
                    >
                        {{ item.label }}
                        <ChevronRight class="w-5 h-5" />
                    </Link>
                </div>
            </div>
        </div>
    </Transition>
</template>

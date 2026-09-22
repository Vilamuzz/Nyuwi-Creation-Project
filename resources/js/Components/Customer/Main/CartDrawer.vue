<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { Minus, Plus, Trash2, X } from "lucide-vue-next";

const props = defineProps({
    isOpen: Boolean,
});

const emit = defineEmits(["close"]);

const page = usePage();
const cart = computed(() => page.props.cart || { items: [], summary: {} });
const items = computed(() => cart.value.items || []);
const summary = computed(() => cart.value.summary || {});

const notice = ref(null);
const noticeTimer = ref(null);

const showNotice = (message) => {
    notice.value = message;
    clearTimeout(noticeTimer.value);
    noticeTimer.value = setTimeout(() => {
        notice.value = null;
    }, 3000);
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(Number(price) || 0);
};

const productImage = (item) => {
    if (item.product?.images && Array.isArray(item.product.images) && item.product.images.length > 0) {
        return "/storage/products/" + item.product.images[0];
    }
    return null;
};

const notFoundImage = "/img/placeholder.png";

const updateQuantity = (item, newQuantity) => {
    if (newQuantity < 1) return;
    router.put(
        route("cart.update", item.id),
        { quantity: Number(newQuantity) },
        {
            preserveScroll: true,
            onError: (errors) => {
                const message = errors.quantity || Object.values(errors)[0] || "Gagal memperbarui keranjang.";
                showNotice(message);
            },
        },
    );
};

const removeItem = (item) => {
    router.delete(route("cart.destroy", item.id), {
        preserveScroll: true,
        onError: (errors) => {
            const message = Object.values(errors)[0] || "Gagal menghapus item.";
            showNotice(message);
        },
    });
};

const handleKeydown = (event) => {
    if (event.key === "Escape" && props.isOpen) {
        emit("close");
    }
};

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            notice.value = null;
        }
    },
);

onMounted(() => {
    document.addEventListener("keydown", handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener("keydown", handleKeydown);
    clearTimeout(noticeTimer.value);
});
</script>

<template>
    <Transition enter-active-class="transition-opacity duration-300 ease-out" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition-opacity duration-300 ease-in"
        leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isOpen" class="fixed inset-0 z-50 bg-black/30 transition-opacity duration-300 ease-out"
            @click="emit('close')" />
    </Transition>

    <Transition enter-active-class="transition-transform duration-300 ease-out"
        enter-from-class="translate-y-full md:translate-y-0 md:translate-x-full"
        enter-to-class="translate-y-0 md:translate-x-0" leave-active-class="transition-transform duration-300 ease-in"
        leave-from-class="translate-y-0 md:translate-x-0"
        leave-to-class="translate-y-full md:translate-y-0 md:translate-x-full">
        <div v-if="isOpen" id="cart-drawer"
            class="fixed inset-0 z-[51] overflow-hidden pointer-events-none p-4 flex items-end justify-center md:items-stretch md:justify-end">
            <div
                class="pointer-events-auto h-3/4 max-h-[80vh] w-full max-w-md bg-white flex flex-col shadow-xl md:h-full md:max-h-none rounded-2xl">
                <!-- Header -->
                <div class="flex h-20 w-full px-10 items-center justify-between border-b border-slate-500">
                    <h1 class="flex font-bold text-2xl tracking-wider items-center gap-2">
                        KERANJANG
                        <span v-if="summary.itemCount"
                            class="h-4 min-w-4 px-1 rounded-full bg-orange-500 text-white text-[10px] font-bold flex items-center justify-center">
                            {{ summary.itemCount }}
                        </span>
                    </h1>
                    <button type="button" aria-label="Close cart" @click="emit('close')">
                        <X />
                    </button>
                </div>

                <!-- Notice -->
                <div v-if="notice" class="px-10 py-3 text-sm text-red-600 bg-red-50 border-b border-red-100">
                    {{ notice }}
                </div>

                <!-- Items -->
                <div class="flex-1 overflow-y-auto px-10 py-6 space-y-6">
                    <template v-if="items.length > 0">
                        <div v-for="item in items" :key="item.id" class="flex gap-4 items-start">
                            <img :src="productImage(item) || notFoundImage" :alt="item.product?.name || 'Product image'"
                                class="w-16 h-16 object-cover rounded-md" />
                            <div class="flex-1 min-w-0">
                                <p class="font-medium truncate">
                                    {{ item.product?.name || "Product" }}
                                </p>
                                <div class="text-sm text-gray-500">
                                    <span v-if="item.size">Size: {{ item.size }}</span>
                                    <span v-if="item.color" class="ml-2">
                                        Color:
                                        <span class="inline-block w-4 h-4 rounded-full ml-1 align-middle"
                                            :style="{ backgroundColor: item.color }"></span>
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-700">
                                    {{ formatPrice(item.price) }}
                                </p>

                                <div class="mt-2 flex items-center justify-between">
                                    <div class="flex items-center border border-slate-300 rounded-md">
                                        <input type="number" :value="item.quantity" min="1"
                                            class="h-10 w-10 p-0 text-center font-medium border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                            @change="
                                                updateQuantity(
                                                    item,
                                                    $event.target.value
                                                )
                                                " />
                                    </div>
                                    <button type="button" class="text-red-500 hover:text-red-700 underline"
                                        aria-label="Remove item" @click="removeItem(item)">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div v-else class="py-10 text-center">
                        <p class="text-gray-600">Keranjang belanja kosong</p>
                        <Link :href="route('sale')"
                            class="inline-block mt-4 px-6 py-2 border border-black hover:border-transparent hover:text-white rounded-md hover:bg-orange-500 duration-150"
                            @click="emit('close')">
                            Belanja Sekarang
                        </Link>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-10 py-6 border-t border-slate-500 flex flex-col gap-2">
                    <div class="flex items-center justify-between text-lg">
                        <span>Subtotal</span>
                        <span class="font-bold">{{ formatPrice(summary.subtotal) }}</span>
                    </div>

                    <p class="text-sm text-center text-gray-500">Tax included. Shipping calculated at checkout.</p>
                    <Link :href="route('checkout')"
                        class="w-full font-bold text-center text-lg px-6 py-3 bg-slate-700 text-white rounded-md hover:bg-white hover:text-slate-700 hover:ring-slate-700 hover:ring-4 transition-colors ring-inset"
                        @click="emit('close')">
                        Checkout
                    </Link>
                    <Link :href="route('cart.show')"
                        class="mt-2 underline text-center text-sm font-bold tracking-wider hover:text-orange-500 duration-150"
                        @click="emit('close')">
                        Lihat Keranjang
                    </Link>

                </div>
            </div>
        </div>
    </Transition>
</template>
<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Hero from "@/Components/Customer/Main/Hero.vue";

const props = defineProps({
    wishlistItems: { type: Object, default: () => ({ data: [], last_page: 1, current_page: 1, total: 0 }) },
});

const wishlistItems = computed(() => props.wishlistItems.data || []);
const pagination = computed(() => ({
    current_page: props.wishlistItems.current_page,
    last_page: props.wishlistItems.last_page,
    total: props.wishlistItems.total,
}));
const isLoading = ref(false);
const error = ref(null);

// Toast notification state
const message = ref("");
const messageType = ref("");
const showMessage = ref(false);

const fetchWishlist = (page = 1) => {
    isLoading.value = false;
};

const deleteForm = useForm({});

// Remove item from wishlist
const removeFromWishlist = (itemId) => {
    if (confirm("Are you sure you want to remove this item from wishlist?")) {
        deleteForm.delete(route("wishlist.destroy", itemId), {
            preserveScroll: true,
            onSuccess: () => {
                message.value = "Item removed from wishlist";
                messageType.value = "success";
                showMessage.value = true;

                setTimeout(() => {
                    showMessage.value = false;
                }, 3000);
            },
            onError: () => {
                message.value = "Failed to remove item from wishlist";
                messageType.value = "error";
                showMessage.value = true;

                setTimeout(() => {
                    showMessage.value = false;
                }, 3000);
            },
        });
    }
};

// Change page
const changePage = (page) => {
    router.get(route("wishlist.index"), { page }, { preserveScroll: true });
};
</script>

<template>
    <CustomersLayout>
        <Head title="My Wishlist" />
        <Hero title="Wishlist" breadcrumb="Home > Wishlist" />

        <!-- DaisyUI Toast Notification -->
        <div v-if="showMessage" class="toast toast-top toast-end z-50">
            <div
                class="alert"
                :class="
                    messageType === 'success' ? 'alert-success' : 'alert-error'
                "
            >
                <span
                    v-if="messageType === 'success'"
                    class="flex items-center"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="stroke-current shrink-0 h-6 w-6 mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    {{ message }}
                </span>
                <span v-else class="flex items-center">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="stroke-current shrink-0 h-6 w-6 mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    {{ message }}
                </span>
            </div>
        </div>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold mb-6">My Wishlist</h1>

            <!-- Loading state -->
            <div v-if="isLoading" class="text-center py-20">
                <div
                    class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"
                ></div>
                <p class="mt-4 text-gray-600">Loading wishlist...</p>
            </div>

            <!-- Error state -->
            <div v-else-if="error" class="text-center py-20 text-red-500">
                <p>{{ error }}</p>
                <button
                    @click="fetchWishlist"
                    class="mt-4 px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600"
                >
                    Retry
                </button>
            </div>

            <!-- Wishlist content -->
            <div v-else-if="wishlistItems.length" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="item in wishlistItems"
                        :key="item.id"
                        class="border rounded-lg overflow-hidden shadow-sm"
                    >
                        <img
                            :src="`/storage/products/${item.product.images[0]}`"
                            :alt="item.product.name"
                            class="w-full h-48 object-cover"
                        />

                        <div class="p-4">
                            <h2 class="text-lg font-semibold">
                                {{ item.product.name }}
                            </h2>
                            <p class="text-gray-600">
                                {{
                                    new Intl.NumberFormat("id-ID", {
                                        style: "currency",
                                        currency: "IDR",
                                        minimumFractionDigits: 0,
                                    }).format(item.product.price)
                                }}
                            </p>

                            <div class="mt-4 flex justify-between">
                                <Link
                                    :href="
                                        route('product', {
                                            slug: item.product.slug,
                                        })
                                    "
                                    class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600"
                                >
                                    View Product
                                </Link>
                                <button
                                    @click="removeFromWishlist(item.id)"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="pagination.last_page > 1"
                    class="flex justify-center gap-2 mt-8"
                >
                    <button
                        v-for="page in pagination.last_page"
                        :key="page"
                        @click="changePage(page)"
                        :class="[
                            'py-2 px-4 rounded-md border',
                            page === pagination.current_page
                                ? 'bg-orange-500 text-white border-orange-500'
                                : 'bg-orange-100 hover:bg-orange-500 hover:text-white duration-300',
                        ]"
                    >
                        {{ page }}
                    </button>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center text-gray-500 py-8">
                Your wishlist is empty
            </div>
        </div>
    </CustomersLayout>
</template>

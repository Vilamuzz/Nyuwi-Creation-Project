<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Hero from "@/Components/Customer/Main/Hero.vue";

const props = defineProps({
    wishlistItems: Object, // Change from Array to Object to handle pagination
});

const form = useForm({});

const removeFromWishlist = (itemId) => {
    if (confirm("Are you sure you want to remove this item from wishlist?")) {
        form.delete(route("wishlist.destroy", itemId), {
            preserveScroll: true,
        });
    }
};

const changePage = (page) => {
    router.get(
        route("wishlist.index"),
        { page: page },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};
</script>

<template>
    <CustomersLayout>
        <Head title="My Wishlist" />
        <Hero title="Wishlist" breadcrumb="Home > Wishlist" />

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold mb-6">My Wishlist</h1>

            <div v-if="wishlistItems.data.length" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="item in wishlistItems.data"
                        :key="item.id"
                        class="border rounded-lg overflow-hidden shadow-sm"
                    >
                        <!-- Your existing wishlist item template -->
                        <img
                            :src="`/storage/products/${item.product.image}`"
                            :alt="item.product.name"
                            class="w-full h-48 object-cover"
                        />

                        <div class="p-4">
                            <h2 class="text-lg font-semibold">
                                {{ item.product.name }}
                            </h2>
                            <p class="text-gray-600">
                                {{ item.product.price }}
                            </p>

                            <div class="mt-4 flex justify-between">
                                <Link
                                    :href="route('product', item.product.id)"
                                    class="bg-blue-500 text-white px-4 py-2 rounded"
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
                    v-if="wishlistItems.last_page > 1"
                    class="flex justify-center gap-2 mt-8"
                >
                    <button
                        v-for="page in wishlistItems.last_page"
                        :key="page"
                        @click="changePage(page)"
                        :class="[
                            'py-2 px-4 rounded-md border',
                            page === wishlistItems.current_page
                                ? 'bg-orange-500 text-white border-orange-500'
                                : 'bg-orange-100 hover:bg-orange-500 hover:text-white duration-300',
                        ]"
                    >
                        {{ page }}
                    </button>
                </div>
            </div>
            <div v-else class="text-center text-gray-500 py-8">
                Your wishlist is empty
            </div>
        </div>
    </CustomersLayout>
</template>

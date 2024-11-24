<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";

// Update form to handle product data
const form = useForm({
    product_id: null,
    quantity: 1,
    price: 0,
});

const quantity = ref(1);

// Watch for quantity changes
watch(quantity, (newValue) => {
    form.quantity = newValue;
});

// Add to cart handler
const addToCart = () => {
    form.product_id = props.product.id;
    form.price = props.product.price;
    form.quantity = quantity.value;
    form.post(route("cart.add"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            quantity.value = 1;
        },
    });
};

const props = defineProps({
    errors: Object,
    product: Object,
    categories: Array,
});

const getCategoryName = (categoryId) => {
    const category = props.categories.find((cat) => cat.id === categoryId);
    return category ? category.name : "Tidak ada kategori";
};
</script>

<template>
    <Head title="Product" />
    <CustomersLayout>
        <section class="bg-orange-100 px-24 py-6 flex flex-row">
            <div class="text-gray-400 mr-2">Home > Shop ></div>
            <div>Product</div>
        </section>
        <section class="mx-24 my-6 flex flex-row gap-4">
            <!-- Image Section -->
            <div class="w-1/12 flex flex-col">
                <div v-if="product.image" class="p-8 bg-gray-200 rounded-md">
                    <img
                        :src="`/storage/products/${product.image}`"
                        :alt="product.name"
                        class="w-full h-auto object-cover"
                    />
                </div>
            </div>

            <!-- Product Details Section -->
            <div class="w-1/2 flex flex-col ml-16">
                <h1 class="font-bold text-3xl">{{ product.name }}</h1>
                <h2 class="font-thin text-gray-400 text-2xl">
                    Rp {{ product.price }}
                </h2>
                <p class="mt-4">{{ product.description }}</p>

                <!-- Category -->
                <div class="mt-4">
                    <span class="font-semibold">Category: </span>
                    <span>{{ getCategoryName(product.category_id) }}</span>
                </div>

                <!-- Stock -->
                <div class="mt-2">
                    <span class="font-semibold">Stock: </span>
                    <span>{{ product.stock }}</span>
                </div>

                <!-- Colors -->
                <div
                    v-if="product.colors && product.colors.length"
                    class="mt-4"
                >
                    <h3 class="font-semibold mb-2">Colors</h3>
                    <div class="flex gap-2">
                        <div
                            v-for="color in product.colors"
                            :key="color.id"
                            class="w-8 h-8 rounded-full"
                            :style="{ backgroundColor: color.color }"
                        ></div>
                    </div>
                </div>

                <!-- Sizes -->
                <div v-if="product.sizes && product.sizes.length" class="mt-4">
                    <h3 class="font-semibold mb-2">Sizes</h3>
                    <div class="flex gap-2">
                        <div
                            v-for="size in product.sizes"
                            :key="size.id"
                            class="px-4 py-2 border rounded-md hover:bg-orange-500 hover:text-white duration-300"
                        >
                            {{ size.size }}
                        </div>
                    </div>
                </div>

                <!-- Quantity and Add to Cart -->
                <div class="flex flex-row gap-x-4 mt-8">
                    <div class="flex flex-row items-center p-0">
                        <button
                            type="button"
                            @click="quantity > 1 ? quantity-- : null"
                            class="p-4 hover:bg-orange-500 rounded-l-md hover:text-white duration-300"
                        >
                            -
                        </button>
                        <input
                            type="number"
                            v-model="quantity"
                            class="p-4 hover:bg-orange-500 hover:text-white duration-300 w-16"
                            min="1"
                            :max="product.stock"
                        />
                        <button
                            type="button"
                            @click="
                                quantity < product.stock ? quantity++ : null
                            "
                            class="p-4 hover:bg-orange-500 rounded-r-md hover:text-white duration-300"
                        >
                            +
                        </button>
                    </div>
                    <button
                        @click="addToCart"
                        class="rounded-md border border-gray-300 px-10 py-4 hover:bg-orange-500 hover:text-white duration-300"
                        :disabled="product.stock < 1"
                    >
                        {{ product.stock < 1 ? "Out of Stock" : "Add To Cart" }}
                    </button>
                </div>
            </div>
        </section>
    </CustomersLayout>
</template>

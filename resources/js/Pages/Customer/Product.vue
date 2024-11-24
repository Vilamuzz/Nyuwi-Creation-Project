<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";

// Update form to handle product data
const form = useForm({
    product_id: null,
    quantity: 1,
    price: 0,
    size: "",
    color: "",
});

const quantity = ref(1);
const selectedSize = ref("");
const selectedColor = ref("");

// Watch for quantity changes
watch(quantity, (newValue) => {
    form.quantity = newValue;
});

// Add to cart handler
const addToCart = () => {
    form.product_id = props.product.id;
    form.price = props.product.price;
    form.quantity = quantity.value;
    form.size = selectedSize.value;
    form.color = selectedColor.value;

    form.post(route("cart.add"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            quantity.value = 1;
            selectedSize.value = "";
            selectedColor.value = "";
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
                        <button
                            v-for="color in product.colors"
                            :key="color.id"
                            @click="selectedColor = color.color"
                            class="w-8 h-8 rounded-full border-2"
                            :class="{
                                'border-orange-500':
                                    selectedColor === color.color,
                            }"
                            :style="{ backgroundColor: color.color }"
                        ></button>
                    </div>
                </div>

                <!-- Sizes -->
                <div v-if="product.sizes && product.sizes.length" class="mt-4">
                    <h3 class="font-semibold mb-2">Sizes</h3>
                    <div class="flex gap-2">
                        <button
                            v-for="size in product.sizes"
                            :key="size.id"
                            @click="selectedSize = size.size"
                            :class="[
                                'px-4 py-2 border rounded-md hover:bg-orange-500 hover:text-white duration-300',
                                selectedSize === size.size
                                    ? 'bg-orange-500 text-white'
                                    : '',
                            ]"
                        >
                            {{ size.size }}
                        </button>
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
                        :disabled="
                            product.stock < 1 ||
                            (!selectedSize && product.sizes?.length) ||
                            (!selectedColor && product.colors?.length)
                        "
                    >
                        {{
                            product.stock < 1
                                ? "Out of Stock"
                                : !selectedSize && product.sizes?.length
                                ? "Please select size"
                                : !selectedColor && product.colors?.length
                                ? "Please select color"
                                : "Add To Cart"
                        }}
                    </button>
                </div>
            </div>
        </section>
    </CustomersLayout>
</template>

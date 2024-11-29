import axios from "axios";

data() {
    return {
        products: [],
    };
},
mounted() {
    axios.get("/path/to/products.json").then((response) => {
        this.products = response.data;
    });
}

<template>
    <div class="flex flex-wrap gap-4">
        <!-- Menggunakan ProductCard untuk setiap produk -->
        <ProductCard
            v-for="(product, index) in products"
            :key="index"
            :name="product.name"
            :price="product.price"
            :image="product.image"
            :description="product.description"
        />
    </div>
</template>

<script>
import ProductCard from "./ProductCard.vue";

export default {
    components: {
        ProductCard,
    },
    data() {
        return {
            products: [
                {
                    name: "Product 1",
                    price: "Rp.0",
                    image: "img/product/aksesoris1",
                    description: "This is a great product.",
                },
                {
                    name: "Product 2",
                    price: "Rp.0",
                    image: "img/product/buket1",
                    description: "Another amazing product.",
                },
                {
                    name: "Product 3",
                    price: "Rp.0",
                    image: "img/product/hiasan1",
                    description: "You will love this product.",
                },
                {
                    name: "Product 4",
                    price: "Rp.0",
                    image: "img/product/hiasan2",
                    description: "Top-notch quality product.",
                },
            ],
        };
    },
};
</script>

<style scoped>
.flex-wrap {
    justify-content: center;
}
</style>

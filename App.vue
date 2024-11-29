<template>
    <div id="app" class="p-4">
        <h1 class="text-center text-2xl font-bold mb-4">Product List</h1>
        <ProductList />
    </div>
</template>

<script>
import ProductList from "./components/ProductList.vue";

export default {
    components: {
        ProductList,
    },
};
</script>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f9f9f9;
}
</style>

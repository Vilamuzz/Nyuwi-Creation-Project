<script setup>
import Navbar from "@/Components/Customer/Main/Navbar.vue";
import Footer from "@/Components/Customer/Main/Footer.vue";
import { usePage } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios";

const page = usePage();
const auth = page.props.auth;
const orders = ref([]);

onMounted(async () => {
    try {
        const response = await axios.get(route("orders.info"));
        orders.value = response.data.orders;
    } catch (error) {
        console.error("Error fetching orders:", error);
    }
});
</script>

<template>
    <Navbar :is-logged-in="auth.user" :orders="orders" />
    <main>
        <slot></slot>
    </main>
    <Footer />
</template>

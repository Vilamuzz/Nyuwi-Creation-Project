<script setup>
import Navbar from "@/Components/Customer/Main/Navbar.vue";
import Footer from "@/Components/Customer/Main/Footer.vue";
</script>
<template>
    <Navbar />
    <main>
        <slot></slot>
    </main>
    <Footer />
</template>

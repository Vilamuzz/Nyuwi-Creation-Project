<script setup>
import Navbar from "@/Components/Navbar.vue";
import Footer from "@/Components/Footer.vue";
</script>
<template>
    <Navbar />
    <!-- This is where the content inside HomeLayout will be injected -->
    <main>
        <slot></slot>
    </main>
    <Footer />
</template>

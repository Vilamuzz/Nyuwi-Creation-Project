<script setup>
import Navbar from "@/Components/Main/Navbar.vue";
import Footer from "@/Components/Main/Footer.vue";
</script>
<template>
    <Navbar />
    <main>
        <slot></slot>
    </main>
    <Footer />
</template>

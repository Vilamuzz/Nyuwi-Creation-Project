<script setup>
import Navbar from "@/Components/Navbar.vue";
import Footer from "@/Components/Footer.vue";
</script>
<template>
    <Navbar></Navbar>
    <!-- This is where the content inside HomeLayout will be injected -->
    <main class="pt-20">
        <slot></slot>
    </main>
    <Footer></Footer>
</template>

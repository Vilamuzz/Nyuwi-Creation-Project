<script setup>
import { Head } from "@inertiajs/vue3";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
</script>

<template>
    <Head title="Contact Page" />
    <CustomersLayout>
        <section
            class="bg-cover bg-center h-screen flex items-center justify-start"
            :style="{
                backgroundImage:
                    'linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url(/img/background/hero.svg)',
            }"
        >
        </section>
    </CustomersLayout>
</template>
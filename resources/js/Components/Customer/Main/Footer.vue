<script setup>
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";
import { MessageCircle, Instagram } from "lucide-vue-next";
import PaymentInformationModal from "@/Components/Customer/Sub-main/PaymentInformationModal.vue";

const showPaymentModal = ref(false);

const openPaymentModal = () => {
    showPaymentModal.value = true;
};

const closePaymentModal = () => {
    showPaymentModal.value = false;
};
</script>

<template>
    <div
        class="flex flex-row space-x-4 px-24 py-10 justify-between item-start bg-[#ffedd5] relative overflow-hidden"
    >
        <!-- Overlay Image -->
        <img
            width="400"
            height="300"
            class="absolute z-10 top-36 right-12 md:top-8 md:right-8 lg:top-36 lg:right-12 opacity-30 pointer-events-none"
            :src="'/img/overlay/flower1.png'"
        />

        <div class="flex flex-col items-start w-1/4 relative z-20">
            <h1 class="font-bold text-2xl mb-4">Nyuwi.</h1>
            <p>
                Margorejo RT.04/RW.03, Karanganom, Klaten Utara, Kabupaten
                Klaten 57438.
            </p>
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3320.6788951519497!2d110.61498747415362!3d-7.691237176157267!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a41bc6af588c9%3A0x228d0db4691fd351!2sNyuwi%20Creation!5e1!3m2!1sid!2sid!4v1752551355919!5m2!1sid!2sid"
                width="350"
                height="200"
                style="border: 0"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
        </div>

        <div class="flex flex-col space-y-10 relative z-20">
            <h1>Navigation</h1>
            <Link href="/" class="font-bold hover:text-orange-500"> Home </Link>
            <Link href="/shop" class="font-bold hover:text-orange-500">
                Shop
            </Link>
            <Link href="/about" class="font-bold hover:text-orange-500">
                About
            </Link>
        </div>

        <div class="flex flex-col space-y-10 relative z-20">
            <h1>Help</h1>
            <button
                @click="openPaymentModal"
                class="font-bold hover:text-orange-500 text-left"
            >
                Payment Information
            </button>
        </div>

        <div class="flex flex-col space-y-6 relative z-20">
            <h1 class="text-center">Contact</h1>
            <div class="flex flex-row">
                <!-- WhatsApp Link -->
                <a href="https://wa.me/+6289514923727" target="_blank">
                    <span
                        role="button"
                        class="btn btn-ghost m-1 hover:bg-transparent group"
                    >
                        <MessageCircle
                            class="group-hover:text-orange-500 transition-colors duration-300"
                        />
                    </span>
                </a>

                <!-- Instagram Link -->
                <a href="https://instagram.com/nyuwi.creation" target="_blank">
                    <span
                        role="button"
                        class="btn btn-ghost m-1 hover:bg-transparent group"
                    >
                        <Instagram
                            class="group-hover:text-orange-500 transition-colors duration-300"
                        />
                    </span>
                </a>
            </div>
        </div>
    </div>

    <PaymentInformationModal
        v-if="showPaymentModal"
        :on-close="closePaymentModal"
    />
</template>

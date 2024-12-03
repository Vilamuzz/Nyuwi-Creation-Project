<script setup>
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";
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
    <div class="flex flex-row space-x-4 px-24 py-20 justify-between item-start">
        <div class="flex flex-col items-start space-y-10 w-1/4">
            <h1 class="font-bold text-2xl">Nyuwi.</h1>
            <p>
                Margorejo RT.04/RW.03, Karanganom, Klaten Utara, Kabupaten
                Klaten 57438.
            </p>
        </div>
        <div class="flex flex-col space-y-10">
            <h1>Link</h1>
            <Link href="/" class="font-bold hover:text-orange-500"> Home </Link>
            <Link href="/shop" class="font-bold hover:text-orange-500">
                Shop
            </Link>
            <Link href="/about" class="font-bold hover:text-orange-500">
                About
            </Link>
        </div>
        <div class="flex flex-col space-y-10">
            <h1>Help</h1>
            <button
                @click="openPaymentModal"
                class="font-bold hover:text-orange-500 text-left"
            >
                Payment Information
            </button>
        </div>
        <div class="flex flex-col space-y-6">
            <h1 class="text-center">Contact</h1>
            <div class="flex flex-row">
                <a href="https://wa.me/+6289514923727" target="_blank"
                    ><span
                        role="button"
                        class="btn btn-ghost m-1 hover:bg-transparent"
                    >
                        <img
                            :src="'/img/icon/whatsapp.svg'"
                            class="w-5"
                            alt=""
                        />
                    </span>
                </a>
                <a href="https://instagram.com/nyuwi.creation" target="_blank"
                    ><span
                        role="button"
                        class="btn btn-ghost m-1 hover:bg-transparent"
                    >
                        <img
                            :src="'/img/icon/instagram.svg'"
                            class="w-5"
                            alt=""
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

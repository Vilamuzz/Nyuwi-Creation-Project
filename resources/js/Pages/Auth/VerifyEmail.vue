<script setup>
import { computed } from "vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route("verification.send"));
};

const verificationLinkSent = computed(
    () => props.status === "verification-link-sent"
);
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi Email" />

        <div class="mb-6 text-center">
            <div
                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-100 text-orange-600"
            >
                <svg
                    class="h-6 w-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                    />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">
                Verifikasi Email Anda
            </h1>
            <p class="mt-2 text-sm text-stone-600 leading-relaxed">
                Terima kasih telah mendaftar! Sebelum memulai, silakan periksa kotak masuk email Anda dan klik tautan verifikasi yang baru saja kami kirimkan.
            </p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-5 flex items-center gap-2 rounded-xl bg-green-50 border border-green-200 p-3.5 text-sm text-green-700"
            role="status"
        >
            <svg
                class="h-5 w-5 shrink-0 text-green-500"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                    clip-rule="evenodd"
                />
            </svg>
            <span>
                Tautan verifikasi baru telah dikirimkan ke alamat email Anda.
            </span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <PrimaryButton
                    class="w-full"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Mengirim...</span>
                    <span v-else>Kirim Ulang Email Verifikasi</span>
                </PrimaryButton>
            </div>

            <div class="pt-2 text-center">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm font-medium text-stone-500 hover:text-stone-800 transition-colors"
                >
                    Keluar / Ganti Akun
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>

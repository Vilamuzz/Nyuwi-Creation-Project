<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("password.store"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Atur Ulang Kata Sandi" />

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">
                Atur Ulang Kata Sandi
            </h1>
            <p class="mt-1.5 text-sm text-stone-500">
                Masukkan kata sandi baru untuk mengamankan akun Anda
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Email" class="text-stone-700 font-medium" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5 block w-full bg-stone-50"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Kata Sandi Baru" class="text-stone-700 font-medium" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5 block w-full"
                    v-model="form.password"
                    placeholder="Minimal 8 karakter"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Konfirmasi Kata Sandi Baru"
                    class="text-stone-700 font-medium"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1.5 block w-full"
                    v-model="form.password_confirmation"
                    placeholder="Ulangi kata sandi baru"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else>Simpan Kata Sandi Baru</span>
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-6 pt-5 border-t border-stone-100 text-center text-sm text-stone-600">
            <Link
                :href="route('login')"
                class="font-semibold text-orange-600 hover:text-orange-700 hover:underline transition-colors"
            >
                Batal dan Kembali ke Masuk
            </Link>
        </div>
    </GuestLayout>
</template>

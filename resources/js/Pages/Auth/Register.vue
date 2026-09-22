<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("register"), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar Akun" />

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">
                Buat Akun Baru
            </h1>
            <p class="mt-1.5 text-sm text-stone-500">
                Daftar untuk menikmati kemudahan berbelanja di Nyuwi Creation
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="name" value="Nama Lengkap" class="text-stone-700 font-medium" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1.5 block w-full"
                    v-model="form.name"
                    placeholder="Nama Lengkap Anda"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-1.5" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" class="text-stone-700 font-medium" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5 block w-full"
                    v-model="form.email"
                    placeholder="nama@email.com"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Kata Sandi" class="text-stone-700 font-medium" />

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
                    value="Konfirmasi Kata Sandi"
                    class="text-stone-700 font-medium"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1.5 block w-full"
                    v-model="form.password_confirmation"
                    placeholder="Ulangi kata sandi"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="pt-3">
                <PrimaryButton
                    class="w-full"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Mendaftarkan...</span>
                    <span v-else>Daftar Akun</span>
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-6 pt-5 border-t border-stone-100 text-center text-sm text-stone-600">
            Sudah memiliki akun?
            <Link
                :href="route('login')"
                class="font-semibold text-orange-600 hover:text-orange-700 hover:underline ml-1 transition-colors"
            >
                Masuk di Sini
            </Link>
        </div>
    </GuestLayout>
</template>

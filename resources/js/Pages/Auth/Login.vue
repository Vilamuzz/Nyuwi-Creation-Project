<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset("password");
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk" />

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">
                Selamat Datang Kembali
            </h1>
            <p class="mt-1.5 text-sm text-stone-500">
                Masuk untuk melanjutkan belanja di Nyuwi Creation
            </p>
        </div>

        <div
            v-if="status"
            class="mb-5 flex items-center gap-2 rounded-xl bg-green-50 border border-green-200 p-3 text-sm text-green-700"
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
            <span>{{ status }}</span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Email" class="text-stone-700 font-medium" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5 block w-full"
                    v-model="form.email"
                    placeholder="nama@email.com"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Kata Sandi" class="text-stone-700 font-medium" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-medium text-orange-600 hover:text-orange-700 hover:underline transition-colors"
                    >
                        Lupa kata sandi?
                    </Link>
                </div>

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5 block w-full"
                    v-model="form.password"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div class="pt-1">
                <label class="flex items-center cursor-pointer select-none">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2.5 text-sm text-stone-600">Ingat saya</span>
                </label>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Memproses...</span>
                    <span v-else>Masuk ke Akun</span>
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-6 pt-5 border-t border-stone-100 text-center text-sm text-stone-600">
            Belum memiliki akun?
            <Link
                :href="route('register')"
                class="font-semibold text-orange-600 hover:text-orange-700 hover:underline ml-1 transition-colors"
            >
                Daftar Sekarang
            </Link>
        </div>
    </GuestLayout>
</template>

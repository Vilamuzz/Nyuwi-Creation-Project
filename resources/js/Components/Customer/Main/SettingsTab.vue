<script setup>
import { ref } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { User, Lock, Shield, CheckCircle2 } from "lucide-vue-next";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: "",
    },
});

const user = usePage().props.auth.user;

// Profile Information Form
const profileForm = useForm({
    name: user?.name || "",
    email: user?.email || "",
});

const updateProfile = () => {
    profileForm.patch(route("profile.update"), {
        preserveScroll: true,
    });
};

// Password Update Form
const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const updatePassword = () => {
    passwordForm.put(route("password.update"), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset("password", "password_confirmation");
                passwordInput.value?.focus();
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset("current_password");
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <div class="space-y-8">
        <!-- Header -->
        <div class="border-b border-gray-100 pb-4">
            <h2 class="text-xl font-bold text-gray-900">Pengaturan Akun</h2>
            <p class="text-sm text-gray-500 mt-1">
                Kelola informasi identitas profil dan keamanan kata sandi Anda.
            </p>
        </div>

        <!-- Section 1: Profil Pengguna -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2.5 bg-orange-50 text-orange-600 rounded-xl">
                    <User :size="20" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Pribadi</h3>
                    <p class="text-sm text-gray-500">Perbarui nama lengkap dan alamat surel akun Anda</p>
                </div>
            </div>

            <form @submit.prevent="updateProfile" class="space-y-5">
                <div>
                    <InputLabel for="settings_name" value="Nama Lengkap" />
                    <TextInput
                        id="settings_name"
                        type="text"
                        class="mt-1.5 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                        v-model="profileForm.name"
                        required
                        autocomplete="name"
                    />
                    <InputError class="mt-1.5" :message="profileForm.errors.name" />
                </div>

                <div>
                    <InputLabel for="settings_email" value="Alamat Email" />
                    <TextInput
                        id="settings_email"
                        type="email"
                        class="mt-1.5 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                        v-model="profileForm.email"
                        required
                        autocomplete="username"
                    />
                    <InputError class="mt-1.5" :message="profileForm.errors.email" />
                </div>

                <!-- Email Verification Notice -->
                <div
                    v-if="mustVerifyEmail && user?.email_verified_at === null"
                    class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800"
                >
                    <p class="font-medium">Alamat email Anda belum diverifikasi.</p>
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="mt-2 inline-flex items-center text-sm font-semibold text-amber-900 underline hover:text-amber-700"
                    >
                        Kirim ulang email verifikasi
                    </Link>

                    <div
                        v-show="status === 'verification-link-sent'"
                        class="mt-2 text-sm font-medium text-emerald-700 flex items-center gap-1.5"
                    >
                        <CheckCircle2 :size="16" />
                        Tautan verifikasi baru telah dikirim ke alamat email Anda.
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <PrimaryButton :disabled="profileForm.processing" class="min-w-[120px]">
                        <span v-if="profileForm.processing">Menyimpan...</span>
                        <span v-else>Simpan Perubahan</span>
                    </PrimaryButton>

                    <Transition
                        enter-active-class="transition ease-out duration-300"
                        enter-from-class="opacity-0 translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition ease-in duration-200"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 translate-y-1"
                    >
                        <p
                            v-if="profileForm.recentlySuccessful"
                            class="text-sm font-medium text-emerald-600 flex items-center gap-1"
                        >
                            <CheckCircle2 :size="16" />
                            Perubahan profil berhasil disimpan.
                        </p>
                    </Transition>
                </div>
            </form>
        </div>

        <!-- Section 2: Keamanan / Ubah Kata Sandi -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2.5 bg-orange-50 text-orange-600 rounded-xl">
                    <Lock :size="20" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Keamanan & Kata Sandi</h3>
                    <p class="text-sm text-gray-500">Pastikan akun menggunakan kata sandi yang kuat dan aman</p>
                </div>
            </div>

            <form @submit.prevent="updatePassword" class="space-y-5">
                <div>
                    <InputLabel for="settings_current_password" value="Kata Sandi Saat Ini" />
                    <TextInput
                        id="settings_current_password"
                        ref="currentPasswordInput"
                        v-model="passwordForm.current_password"
                        type="password"
                        class="mt-1.5 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                        autocomplete="current-password"
                    />
                    <InputError :message="passwordForm.errors.current_password" class="mt-1.5" />
                </div>

                <div>
                    <InputLabel for="settings_password" value="Kata Sandi Baru" />
                    <TextInput
                        id="settings_password"
                        ref="passwordInput"
                        v-model="passwordForm.password"
                        type="password"
                        class="mt-1.5 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                        autocomplete="new-password"
                    />
                    <InputError :message="passwordForm.errors.password" class="mt-1.5" />
                </div>

                <div>
                    <InputLabel for="settings_password_confirmation" value="Konfirmasi Kata Sandi Baru" />
                    <TextInput
                        id="settings_password_confirmation"
                        v-model="passwordForm.password_confirmation"
                        type="password"
                        class="mt-1.5 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                        autocomplete="new-password"
                    />
                    <InputError :message="passwordForm.errors.password_confirmation" class="mt-1.5" />
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <PrimaryButton :disabled="passwordForm.processing" class="min-w-[120px]">
                        <span v-if="passwordForm.processing">Menyimpan...</span>
                        <span v-else>Perbarui Kata Sandi</span>
                    </PrimaryButton>

                    <Transition
                        enter-active-class="transition ease-out duration-300"
                        enter-from-class="opacity-0 translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition ease-in duration-200"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 translate-y-1"
                    >
                        <p
                            v-if="passwordForm.recentlySuccessful"
                            class="text-sm font-medium text-emerald-600 flex items-center gap-1"
                        >
                            <CheckCircle2 :size="16" />
                            Kata sandi berhasil diperbarui.
                        </p>
                    </Transition>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profil Admin" />

    <AdminLayout pageTitle="Profil Admin">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-stone-800">
                Profil Admin
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <div class="bg-white p-4 shadow-sm sm:rounded-2xl border border-stone-200/80 sm:p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="bg-white p-4 shadow-sm sm:rounded-2xl border border-stone-200/80 sm:p-8">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

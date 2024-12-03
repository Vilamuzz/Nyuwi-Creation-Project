<!-- resources/js/Pages/Auth/AdminRegister.vue -->
<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    admin_code: "", // Add admin code field
});

const submit = () => {
    form.post(route("admin.register"));
};
</script>

<template>
    <GuestLayout>
        <Head title="Admin Register" />

        <form @submit.prevent="submit">
            <!-- Name -->
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <!-- Admin Code -->
            <div class="mt-4">
                <InputLabel for="admin_code" value="Admin Registration Code" />
                <TextInput
                    id="admin_code"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.admin_code"
                    required
                />
                <InputError :message="form.errors.admin_code" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    href="/"
                    class="underline text-sm text-gray-600 hover:text-gray-900"
                >
                    Back to Home
                </Link>

                <PrimaryButton class="ml-4" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

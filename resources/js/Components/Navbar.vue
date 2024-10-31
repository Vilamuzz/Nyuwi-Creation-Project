<script setup>
import { Link } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";

const { props } = usePage();
const isLoggedIn = props.auth.user;
const role = props.auth?.user?.role || null;
</script>

<template>
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 w-full bg-white shadow z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo Perusahaan -->
                <div class="flex-shrink-0">
                    <img
                        :src="'/img/Logo/Logo.svg'"
                        alt="Logo Perusahaan"
                        class="h-12"
                    />
                </div>

                <!-- Input Pencarian -->
                <div class="flex-grow mx-4">
                    <input
                        type="text"
                        placeholder="Cari..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <!-- Tombol Register dan Masuk -->
                <div v-if="!isLoggedIn" class="flex space-x-4">
                    <Link
                        :href="route('register')"
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 text-center"
                    >
                        Register
                    </Link>
                    <Link
                        :href="route('login')"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-center"
                    >
                        Masuk
                    </Link>
                </div>
                <div v-else class="flex space-x-4">
                    <Link
                        :href="route('logout')"
                        method="post"
                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 text-center"
                    >
                        Log out
                    </Link>
                </div>
            </div>
        </div>
    </nav>
    <div class="pt-16"></div>
</template>

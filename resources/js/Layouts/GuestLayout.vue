<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
import { ArrowLeft } from "lucide-vue-next";
</script>

<template>
    <div
        class="min-h-screen bg-gradient-to-br from-amber-50/50 via-stone-50 to-orange-50/40 flex flex-col justify-between relative overflow-hidden selection:bg-orange-500 selection:text-white">
        <!-- Ambient Decorative Background Gradients -->
        <div class="pointer-events-none absolute -top-32 -right-32 h-96 w-96 rounded-full bg-orange-200/30 blur-3xl"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-amber-200/30 blur-3xl"
            aria-hidden="true"></div>

        <!-- Main Auth Card Container -->
        <main class="w-full flex-1 flex flex-col items-center justify-center px-4 py-8 sm:px-6 z-10">
            <div class="w-full sm:max-w-md">
                <!-- Brand Header -->
                <div class="flex flex-col items-center mb-6 text-center">
                    <Link href="/" class="flex flex-col items-center gap-2 group">
                        <ApplicationLogo class="h-12 w-auto transition-transform group-hover:scale-105" />
                        <span class="text-xl font-bold tracking-tight text-stone-800">
                            Nyuwi Creation
                        </span>
                    </Link>
                </div>

                <!-- Form Card -->
                <div
                    class="w-full bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl shadow-stone-200/60 border border-stone-200/80 p-6 sm:p-8">
                    <slot />
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full py-6 text-center text-xs text-stone-500 z-10">
            <p>&copy; {{ new Date().getFullYear() }} Nyuwi Creation. All rights reserved.</p>
        </footer>
    </div>
</template>

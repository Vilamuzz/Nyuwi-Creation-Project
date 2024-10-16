<script setup>
import HomeLayout from "@/Layouts/HomeLayout.vue";
</script>

<template>
    <HomeLayout>
        <!-- Content that will be rendered inside the slot of HomeLayout -->

        <div class="bg-base-200">
            <div
                class="bg-blue-500 text-white py-16 px-8 mx-10 md:px-16 lg:px-32 text-center"
            >
                <h1 class="text-3xl md:text-5xl font-bold mb-4">
                    Welcome to Our Website!
                </h1>
                <p class="text-lg md:text-xl mb-6">
                    Explore our amazing content and stay tuned for more updates.
                </p>
                <button
                    class="bg-white text-blue-500 px-6 py-2 rounded-md font-semibold hover:bg-blue-100"
                >
                    Learn More
                </button>
            </div>
        </div>

        <div class="hero bg-base-200 min-h-screen px-24">
            <div class="hero-content flex-col lg:flex-row-reverse">
                <img
                    src="https://img.daisyui.com/images/stock/photo-1635805737707-575885ab0820.webp"
                    class="max-w-sm rounded-lg shadow-2xl"
                />
                <div>
                    <h1 class="text-5xl font-bold">Box Office News!</h1>
                    <p class="py-6">
                        Provident cupiditate voluptatem et in. Quaerat fugiat ut
                        assumenda excepturi exercitationem quasi. In deleniti
                        eaque aut repudiandae et a id nisi.
                    </p>
                    <button class="btn btn-primary">Get Started</button>
                </div>
            </div>
        </div>
    </HomeLayout>
</template>

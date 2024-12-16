<script setup>
import { Head } from "@inertiajs/vue3";
import Hero from "@/Components/Customer/Main/Hero.vue";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
</script>

<template>
    <Head title="Shoping Page" />
    <CustomersLayout>
        <Hero
            title="About"
            breadcrumb="Home > About"
            backgroundImage="/img/background/hero.svg"
        />
        <section class="bg-[#ffffff] py-20">
    <div class="max-w-6xl mx-auto flex items-center">
        <!-- Teks di sebelah kiri -->
        <div class="flex-1">
            <h1 class="text-4xl font-bold">Our Story</h1>
            <p class="text-black mt-6 max-w-100">
                Nyuwi Creation percaya bahwa setiap benda buatan tangan
                menceritakan sebuah kisah tentang seni. Dibuat dengan
                semangat untuk kreativitas, Nyuwi Creation menghadirkan
                produk-produk unik dan bermakna yang mengubah bahan-bahan
                sederhana menjadi keindahan abadi. Dari perhiasan yang
                elegan hingga karangan bunga yang menawan, setiap bagian
                dibuat dengan hati untuk membangkitkan kegembiraan dan
                kekaguman terhadap seni kreasi buatan tangan.
            </p>
        </div>

        <!-- Gambar di sebelah kanan -->
        <div class="flex-1 ml-10">
            <img src="img\products\buck.jpg" alt="Nyuwi Creation Story" class="w-90 ml-20 h-auto rounded-lg shadow-md" />
        </div>
    </div>
</section>


        <section class="bg-[#fdf7f2]">
            <div class="flex justify-center py-20">
                <div class="flex space-x-20">
                    <div class="flex items-center space-x-2">
                        <img
                            src="/img/icon/trophy.svg"
                            alt="High Quality"
                            class="w-11 h-11"
                        />
                        <div>
                            <h4 class="text-lg font-bold">High Quality</h4>
                            <p class="text-gray-600">
                                Crafted from top materials
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <img
                            src="/img/icon/check-badge.svg"
                            alt="Warranty Protection"
                            class="w-12 h-12"
                        />
                        <div>
                            <h4 class="text-lg font-bold">
                                Warranty Protection
                            </h4>
                            <p class="text-gray-600">1 day</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <img
                            src="/img/icon/boxheart.svg"
                            alt="Free Shipping"
                            class="w-10 h-10"
                        />
                        <div>
                            <h4 class="text-lg font-bold">Free Shipping</h4>
                            <p class="text-gray-600">Order over 10 $</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <img
                            src="/img/icon/support.svg"
                            alt="24/7 Support"
                            class="w-11 h-11"
                        />
                        <div>
                            <h4 class="text-lg font-bold">24 / 7 Support</h4>
                            <p class="text-gray-600">Dedicated support</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </CustomersLayout>
</template>

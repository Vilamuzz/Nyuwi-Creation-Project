<script setup>
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";
import { MessageCircle, Instagram, MapPin, Clock, CreditCard } from "lucide-vue-next";
import PaymentInformationModal from "@/Components/Customer/Sub-main/PaymentInformationModal.vue";

const showPaymentModal = ref(false);

const openPaymentModal = () => {
    showPaymentModal.value = true;
};

const closePaymentModal = () => {
    showPaymentModal.value = false;
};
</script>

<template>
    <footer class="relative overflow-hidden bg-stone-950 text-stone-300">
        <!-- Overlay Floral Decoration -->
        <img width="400" height="300" alt=""
            class="pointer-events-none absolute right-0 top-12 z-10 opacity-20 md:opacity-25"
            :src="'/img/overlay/flower1.png'" />

        <div class="relative z-20 mx-auto max-w-7xl px-6 py-16 lg:px-8">
            <div class="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-4">
                <!-- Kolom 1: Brand & Lokasi Workshop -->
                <div class="flex flex-col">
                    <Link href="/" class="text-2xl font-bold tracking-tight text-white">
                        Nyuwi<span class="text-orange-500">.</span>
                    </Link>
                    <p class="mt-3 text-sm leading-relaxed text-stone-400">
                        Kreasi buatan tangan dan buket istimewa yang dirangkai sepenuh hati untuk menyempurnakan setiap
                        momen bahagiamu.
                    </p>
                    <div class="mt-4 flex items-start gap-2 text-xs text-stone-400">
                        <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-orange-500" />
                        <span>Margorejo RT.04/RW.03, Karanganom, Klaten Utara, Kab. Klaten, Jawa Tengah 57438.</span>
                    </div>
                    <div class="mt-4 h-36 w-full overflow-hidden rounded-xl border border-stone-800 shadow-inner">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3320.6788951519497!2d110.61498747415362!3d-7.691237176157267!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a41bc6af588c9%3A0x228d0db4691fd351!2sNyuwi%20Creation!5e1!3m2!1sid!2sid!4v1752551355919!5m2!1sid!2sid"
                            class="h-full w-full border-0" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Lokasi Nyuwi Creation di Google Maps"></iframe>
                    </div>
                </div>

                <!-- Kolom 2: Navigasi Cepat -->
                <div class="flex flex-col">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Jelajahi</h3>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li>
                            <Link href="/shop" class="transition-colors hover:text-orange-400">
                                Semua Produk
                            </Link>
                        </li>
                        <li>
                            <Link href="/shop" class="transition-colors hover:text-orange-400">
                                Buket & Karangan
                            </Link>
                        </li>
                        <li>
                            <Link href="/shop" class="transition-colors hover:text-orange-400">
                                Aksesoris & Kerajinan
                            </Link>
                        </li>
                        <li>
                            <a href="/#about" class="transition-colors hover:text-orange-400">
                                Cerita Kami
                            </a>
                        </li>

                    </ul>
                </div>

                <!-- Kolom 3: Layanan & Bantuan -->
                <div class="flex flex-col">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Layanan & Bantuan</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li>
                            <button type="button" @click="openPaymentModal"
                                class="flex items-center gap-2 text-left font-medium text-orange-400 transition-colors hover:text-orange-300 focus:outline-none">
                                <CreditCard class="h-4 w-4" />
                                <span>Informasi Pembayaran</span>
                            </button>
                        </li>
                        <li>
                            <a href="https://wa.me/+6289514923727?text=Halo%20Nyuwi,%20saya%20ingin%20tanya%20pesanan%20kustom"
                                target="_blank" rel="noopener noreferrer"
                                class="transition-colors hover:text-orange-400">
                                Pesanan Kustom (Custom Order)
                            </a>
                        </li>
                    </ul>
                    <div class="mt-4 rounded-xl border border-stone-800 bg-stone-900/70 p-3.5 text-xs text-stone-400">
                        <p class="font-medium text-stone-200">Pengemasan Terlindungi</p>
                        <p class="mt-1 text-stone-400 leading-relaxed">
                            Setiap buket dan kerajinan dikemas dengan proteksi ekstra bubble wrap dan kardus tebal.
                        </p>
                    </div>
                </div>

                <!-- Kolom 4: Hubungi Kami & Jam Kerja -->
                <div class="flex flex-col">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Hubungi Kami</h3>
                    <div class="mt-4 space-y-3 text-sm">
                        <a href="https://wa.me/+6289514923727" target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-2.5 transition-colors hover:text-orange-400">
                            <MessageCircle class="h-4 w-4 text-emerald-400" />
                            <span>+62 895-1492-3727</span>
                        </a>
                        <a href="https://instagram.com/nyuwi.creation" target="_blank" rel="noopener noreferrer"
                            aria-label="Follow Nyuwi Creation on Instagram"
                            class="flex items-center gap-2.5 transition-colors hover:text-orange-400">
                            <Instagram class="h-4 w-4 text-pink-400" />
                            <span>@nyuwi.creation</span>
                        </a>
                    </div>

                    <!-- Jam Operasional -->
                    <div class="mt-5 border-t border-stone-800/80 pt-4 text-xs text-stone-400">
                        <div class="flex items-center gap-1.5 font-medium text-stone-300">
                            <Clock class="h-4 w-4 text-orange-400" />
                            <span>Jam Operasional</span>
                        </div>
                        <p class="mt-1.5">Senin – Sabtu: 09.00 – 18.00 WIB</p>
                        <p class="mt-0.5 text-[11px] text-stone-500">Minggu & Hari Libur: Pemesanan online tetap dibuka
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Bar: Copyright & Payment summary -->
        <div class="border-t border-stone-800/80 bg-stone-950/80">
            <div
                class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-6 py-6 text-xs text-stone-500 sm:flex-row lg:px-8">
                <p>© 2026 Nyuwi Creation. Dibuat dengan sepenuh hati di Klaten, Indonesia.</p>
                <div class="flex items-center gap-3 text-[11px] text-stone-400">
                    <span>Menerima QRIS</span>
                    <span>•</span>
                    <span>Transfer Bank</span>
                    <span>•</span>
                    <span>E-Wallet</span>
                </div>
            </div>
        </div>
    </footer>

    <PaymentInformationModal v-if="showPaymentModal" :on-close="closePaymentModal" />
</template>

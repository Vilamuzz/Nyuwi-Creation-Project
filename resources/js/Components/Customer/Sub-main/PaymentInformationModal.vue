<script setup>
import { usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    onClose: {
        type: Function,
        required: true,
    },
    paymentMethod: {
        type: String,
        default: "digital_wallet",
    },
});

// Get store profile for QRIS image and phone number
const page = usePage();
const storePaymentMethod = page.props.storePaymentMethod;
const storeName = page.props.storeName; // Get store name separately

// Tab state
const activeTab = ref(props.paymentMethod === "qris" ? "qris" : "dana");

const setActiveTab = (tab) => {
    activeTab.value = tab;
};
</script>

<template>
    <!-- Modal wrapper -->
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
        ></div>

        <!-- Modal content -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-lg max-w-lg w-full shadow-xl">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold">
                            Informasi Pembayaran
                        </h3>
                        <button
                            @click="onClose"
                            class="text-gray-400 hover:text-gray-500 text-2xl"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <div class="border-b">
                    <div class="flex">
                        <button
                            @click="setActiveTab('dana')"
                            :class="[
                                'flex-1 px-6 py-3 text-sm font-medium border-b-2 transition-colors',
                                activeTab === 'dana'
                                    ? 'border-orange-500 text-orange-600 bg-orange-50'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50',
                            ]"
                        >
                            Digital Wallet (DANA)
                        </button>
                        <button
                            @click="setActiveTab('qris')"
                            :class="[
                                'flex-1 px-6 py-3 text-sm font-medium border-b-2 transition-colors',
                                activeTab === 'qris'
                                    ? 'border-orange-500 text-orange-600 bg-orange-50'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50',
                            ]"
                        >
                            QRIS Payment
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-6">
                    <!-- DANA Tab Content -->
                    <div v-if="activeTab === 'dana'" class="space-y-4">
                        <div class="text-center">
                            <p class="text-lg font-semibold mb-2">
                                Silakan transfer ke:
                            </p>
                            <p class="text-2xl font-bold text-orange-500 mb-2">
                                {{
                                    storePaymentMethod?.storeDana ||
                                    "089514923727"
                                }}
                            </p>
                            <p class="text-gray-600">
                                a.n {{ storeName || "Nyuwi Creation" }}
                            </p>

                            <!-- DANA Logo/Icon -->
                            <div class="flex justify-center my-4">
                                <div
                                    class="w-20 h-20 bg-blue-500 rounded-lg flex items-center justify-center"
                                >
                                    <span class="text-white font-bold text-xl"
                                        >DANA</span
                                    >
                                </div>
                            </div>

                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p
                                    class="text-sm text-gray-700 mb-3 font-medium"
                                >
                                    Langkah-langkah pembayaran DANA:
                                </p>
                                <ol
                                    class="list-decimal list-inside text-sm text-gray-600 space-y-2 text-left"
                                >
                                    <li>
                                        Buka aplikasi DANA di smartphone Anda
                                    </li>
                                    <li>Pilih menu "Kirim" atau "Transfer"</li>
                                    <li>
                                        Masukkan nomor
                                        {{
                                            storePaymentMethod?.storeDana ||
                                            "089514923727"
                                        }}
                                    </li>
                                    <li>Masukkan jumlah yang harus dibayar</li>
                                    <li>Periksa kembali detail transaksi</li>
                                    <li>
                                        Konfirmasi dan selesaikan pembayaran
                                    </li>
                                    <li>Screenshot bukti pembayaran</li>
                                    <li>
                                        Upload bukti pembayaran di halaman
                                        dashboard
                                    </li>
                                </ol>
                            </div>

                            <div
                                class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg"
                            >
                                <p class="text-sm text-yellow-800">
                                    <strong>Penting:</strong> Pastikan nomor
                                    yang dituju sudah benar dan simpan bukti
                                    pembayaran untuk diupload nanti.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- QRIS Tab Content -->
                    <div v-if="activeTab === 'qris'" class="space-y-4">
                        <div class="text-center">
                            <p class="text-lg font-semibold mb-4">
                                Scan QRIS Code untuk pembayaran:
                            </p>

                            <!-- QRIS Image -->
                            <div class="flex justify-center mb-4">
                                <div
                                    class="w-56 h-56 border-2 border-gray-300 rounded-lg overflow-hidden bg-gray-100 shadow-md"
                                >
                                    <img
                                        v-if="storePaymentMethod?.storeQris"
                                        :src="`/storage/${storePaymentMethod.storeQris}`"
                                        class="w-full h-full object-cover"
                                        alt="QRIS Code"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full flex flex-col items-center justify-center text-gray-500"
                                    >
                                        <svg
                                            class="w-16 h-16 mb-2"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M4 3a2 2 0 00-2 2v1.5h16V5a2 2 0 00-2-2H4zm14 4.5H2V14a2 2 0 002 2h12a2 2 0 002-2V7.5zM5 9a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <p class="text-sm">
                                            QRIS Code Not Available
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            Contact admin to setup QRIS
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-green-50 p-4 rounded-lg">
                                <p
                                    class="text-sm text-gray-700 mb-3 font-medium"
                                >
                                    Langkah-langkah pembayaran QRIS:
                                </p>
                                <ol
                                    class="list-decimal list-inside text-sm text-gray-600 space-y-2 text-left"
                                >
                                    <li>
                                        Buka aplikasi mobile banking atau
                                        e-wallet Anda (DANA, OVO, GoPay, dll)
                                    </li>
                                    <li>Pilih menu "Scan QR" atau "QRIS"</li>
                                    <li>Scan kode QR di atas dengan kamera</li>
                                    <li>Masukkan jumlah yang harus dibayar</li>
                                    <li>
                                        Periksa detail merchant:
                                        {{ storeName || "Nyuwi Creation" }}
                                    </li>
                                    <li>
                                        Konfirmasi dan selesaikan pembayaran
                                    </li>
                                    <li>Screenshot bukti pembayaran</li>
                                    <li>
                                        Upload bukti pembayaran di halaman
                                        dashboard
                                    </li>
                                </ol>
                            </div>

                            <div
                                class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg"
                            >
                                <p class="text-sm text-blue-800">
                                    <strong>Info:</strong> QRIS dapat digunakan
                                    dengan semua aplikasi e-wallet dan mobile
                                    banking yang mendukung QRIS.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="mt-6 text-center">
                        <button
                            @click="onClose"
                            class="px-8 py-3 bg-orange-500 text-white rounded-md hover:bg-orange-600 transition-colors font-medium"
                        >
                            Saya Mengerti
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

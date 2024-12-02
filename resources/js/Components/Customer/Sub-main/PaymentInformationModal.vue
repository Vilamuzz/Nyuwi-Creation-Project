<script setup>
defineProps({
    onClose: {
        type: Function,
        required: true,
    },
});
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
            <div class="relative bg-white rounded-lg max-w-md w-full shadow-xl">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold">
                            Informasi Pembayaran Digital Wallet
                        </h3>
                        <button
                            @click="onClose"
                            class="text-gray-400 hover:text-gray-500"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="text-center">
                            <img
                                src="/img/dana-logo.png"
                                alt="DANA"
                                class="h-16 mx-auto mb-4"
                            />
                            <p class="text-lg font-semibold mb-2">
                                Silakan transfer ke:
                            </p>
                            <p class="text-2xl font-bold text-orange-500 mb-4">
                                089514923727
                            </p>
                            <p class="text-gray-600">a.n Nyuwi Creation</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">
                                Langkah-langkah pembayaran:
                            </p>
                            <ol
                                class="list-decimal list-inside text-sm text-gray-600 space-y-2"
                            >
                                <li>Buka aplikasi DANA di smartphone Anda</li>
                                <li>Pilih menu "Kirim"</li>
                                <li>Masukkan nomor 089514923727</li>
                                <li>Masukkan jumlah yang harus dibayar</li>
                                <li>Periksa kembali detail transaksi</li>
                                <li>Konfirmasi dan selesaikan pembayaran</li>
                            </ol>
                        </div>

                        <div class="mt-6 text-center">
                            <button
                                @click="onClose"
                                class="px-6 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600"
                            >
                                Saya Mengerti
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

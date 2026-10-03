<script setup>
import Modal from "@/Components/Modal.vue";
import { Trash2 } from "lucide-vue-next";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: "Konfirmasi Hapus",
    },
    message: {
        type: String,
        default: "Apakah Anda yakin ingin menghapus item ini?",
    },
    confirmText: {
        type: String,
        default: "Hapus",
    },
    cancelText: {
        type: String,
        default: "Batal",
    },
});

const emit = defineEmits(["confirm", "close"]);

const handleConfirm = () => {
    emit("confirm");
};

const handleClose = () => {
    emit("close");
};
</script>

<template>
    <Modal :show="show" max-width="sm" @close="handleClose">
        <div class="p-6 text-center font-sans">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 mb-4">
                <Trash2 class="h-7 w-7 text-red-600" />
            </div>

            <h3 class="text-lg font-bold text-stone-900 mb-2">
                {{ title }}
            </h3>

            <p class="text-sm text-stone-600 mb-6 leading-relaxed">
                {{ message }}
            </p>

            <div class="flex items-center justify-center gap-3">
                <button
                    type="button"
                    class="w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-700 shadow-sm hover:bg-stone-50 transition"
                    @click="handleClose"
                >
                    {{ cancelText }}
                </button>
                <button
                    type="button"
                    class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition"
                    @click="handleConfirm"
                >
                    {{ confirmText }}
                </button>
            </div>
        </div>
    </Modal>
</template>

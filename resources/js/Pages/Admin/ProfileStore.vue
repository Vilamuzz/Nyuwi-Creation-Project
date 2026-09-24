<script setup>
import { ref, watch } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import ToastNotification from "@/Components/Customer/Sub-main/ToastNotification.vue";
import {
    Store,
    Edit3,
    Plus,
    MapPin,
    Phone,
    Instagram,
    Facebook,
    Image as ImageIcon,
    Upload,
    X,
    Save,
    Eye,
    Check,
    Copy,
    ExternalLink,
    QrCode,
    Sparkles,
    ShieldCheck,
    Building2,
    MessageCircle,
    Maximize2,
} from "lucide-vue-next";

const props = defineProps({
    profile: {
        type: Object,
        default: () => null,
    },
});

const isEditing = ref(!props.profile);
const showQrisModal = ref(false);
const copiedField = ref(null);

const form = useForm({
    name: props.profile?.name || "",
    address: props.profile?.address || "",
    city: props.profile?.city || "",
    phone: props.profile?.phone || "",
    instagram: props.profile?.instagram || "",
    facebook: props.profile?.facebook || "",
    tiktok: props.profile?.tiktok || "",
    logo: null,
    qris: null,
});

const logoPreview = ref(
    props.profile?.logo ? `/storage/${props.profile.logo}` : null
);
const qrisPreview = ref(
    props.profile?.qris ? `/storage/${props.profile.qris}` : null
);

watch(
    () => props.profile,
    (newProfile) => {
        if (newProfile) {
            form.name = newProfile.name || "";
            form.address = newProfile.address || "";
            form.city = newProfile.city || "";
            form.phone = newProfile.phone || "";
            form.instagram = newProfile.instagram || "";
            form.facebook = newProfile.facebook || "";
            form.tiktok = newProfile.tiktok || "";
            logoPreview.value = newProfile.logo ? `/storage/${newProfile.logo}` : null;
            qrisPreview.value = newProfile.qris ? `/storage/${newProfile.qris}` : null;
        }
    },
    { deep: true }
);

const toastMessage = ref("");
const toastType = ref("info");
const showToast = ref(false);

const showNotification = (message, type = "info") => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
};

const closeToast = () => {
    showToast.value = false;
};

const formatSocialUrl = (url) => {
    if (!url) return "#";
    return url.startsWith("http") ? url : `https://${url}`;
};

const copyToClipboard = async (text, fieldName) => {
    if (!text) return;
    try {
        await navigator.clipboard.writeText(text);
        copiedField.value = fieldName;
        showNotification(`${fieldName} berhasil disalin!`, "success");
        setTimeout(() => {
            if (copiedField.value === fieldName) {
                copiedField.value = null;
            }
        }, 2000);
    } catch (err) {
        showNotification("Gagal menyalin teks.", "error");
    }
};

const handleLogoUpload = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    form.logo = file;
    logoPreview.value = URL.createObjectURL(file);
};

const removeLogo = () => {
    form.logo = null;
    logoPreview.value = null;
};

const handleQrisUpload = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    form.qris = file;
    qrisPreview.value = URL.createObjectURL(file);
};

const removeQris = () => {
    form.qris = null;
    qrisPreview.value = null;
};

const cancelEditing = () => {
    if (props.profile) {
        form.name = props.profile.name || "";
        form.address = props.profile.address || "";
        form.city = props.profile.city || "";
        form.phone = props.profile.phone || "";
        form.instagram = props.profile.instagram || "";
        form.facebook = props.profile.facebook || "";
        form.tiktok = props.profile.tiktok || "";
        logoPreview.value = props.profile.logo ? `/storage/${props.profile.logo}` : null;
        qrisPreview.value = props.profile.qris ? `/storage/${props.profile.qris}` : null;
        isEditing.value = false;
    }
};

const submit = () => {
    form.transform((data) => {
        const payload = {
            ...data,
            _method: "PUT",
        };
        if (!payload.logo) delete payload.logo;
        if (!payload.qris) delete payload.qris;
        return payload;
    }).post(route("profile-store.update"), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showNotification("Profil toko berhasil diperbarui!", "success");
            isEditing.value = false;
        },
        onError: () => {
            showNotification("Gagal memperbarui profil toko. Silakan periksa formulir.", "error");
        },
    });
};
</script>

<template>

    <Head title="Pengaturan Toko" />

    <AdminLayout pageTitle="Pengaturan Toko">
        <ToastNotification :show="showToast" :message="toastMessage" :type="toastType" @close="closeToast" />

        <div class="max-w-5xl mx-auto space-y-6 pb-12">
            <!-- Top Header & Navigation Bar -->
            <div
                class="bg-white p-6 rounded-2xl border border-stone-200/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-12 h-12 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                        <Store class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-stone-900 tracking-tight">
                                Pengaturan Profil Toko
                            </h1>
                            <span v-if="profile"
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <ShieldCheck class="w-3 h-3" />
                                Toko Aktif
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">
                            Kelola identitas publik, informasi kontak, dan metode pembayaran toko Anda.
                        </p>
                    </div>
                </div>

                <!-- Mode Switcher Buttons -->
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button v-if="profile && !isEditing" @click="isEditing = true" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm hover:shadow cursor-pointer">
                        <Edit3 class="w-4 h-4" />
                        Edit Profil
                    </button>
                </div>
            </div>

            <!-- VIEW MODE -->
            <div v-if="!isEditing && profile" class="space-y-6">
                <!-- Hero Identity Card -->
                <div
                    class="bg-gradient-to-br from-stone-900 via-stone-800 to-stone-900 rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
                    <div
                        class="absolute -right-12 -top-12 w-64 h-64 bg-orange-500/10 rounded-full blur-3xl pointer-events-none">
                    </div>
                    <div
                        class="absolute right-20 -bottom-16 w-64 h-64 bg-amber-500/10 rounded-full blur-2xl pointer-events-none">
                    </div>

                    <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6">
                        <!-- Logo Frame -->
                        <div class="relative group shrink-0">
                            <div
                                class="w-28 h-28 sm:w-36 sm:h-36 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 overflow-hidden flex items-center justify-center p-2 shadow-inner transition-transform duration-300 group-hover:scale-105">
                                <img v-if="profile.logo" :src="`/storage/${profile.logo}`" :alt="profile.name"
                                    class="w-full h-full object-contain drop-shadow" />
                                <ImageIcon v-else class="w-12 h-12 text-stone-400" />
                            </div>
                        </div>

                        <!-- Store Overview -->
                        <div class="flex-1 text-center sm:text-left space-y-3">
                            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                                    {{ profile.name }}
                                </h2>
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-orange-500/20 text-orange-300 border border-orange-500/30 backdrop-blur-sm">
                                    <Sparkles class="w-3.5 h-3.5" />
                                    Nyuwi Official Store
                                </span>
                            </div>

                            <div
                                class="flex flex-wrap items-center justify-center sm:justify-start gap-4 text-sm text-stone-300">
                                <div
                                    class="flex items-center gap-1.5 bg-white/5 px-3 py-1.5 rounded-xl border border-white/10">
                                    <MapPin class="w-4 h-4 text-orange-400 shrink-0" />
                                    <span>{{ profile.address }}, {{ profile.city }}</span>
                                </div>
                                <button @click="copyToClipboard(profile.phone, 'Nomor Telepon')" type="button"
                                    class="flex items-center gap-1.5 bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-xl border border-white/10 transition-colors cursor-pointer group">
                                    <Phone class="w-4 h-4 text-emerald-400 shrink-0" />
                                    <span>{{ profile.phone }}</span>
                                    <Copy
                                        class="w-3.5 h-3.5 text-stone-400 group-hover:text-white transition-colors ml-1" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Two Column Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- QRIS Payment Card -->
                    <div
                        class="bg-white p-6 rounded-2xl border border-stone-200/80 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                        <QrCode class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-stone-900">QRIS Pembayaran</h3>
                                        <p class="text-xs text-stone-500">Metode pembayaran QRIS resmi toko</p>
                                    </div>
                                </div>
                                <span
                                    class="px-2.5 py-1 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">
                                    Siap Pakai
                                </span>
                            </div>

                            <div v-if="profile.qris"
                                class="mt-4 flex flex-col items-center justify-center p-4 bg-stone-50 rounded-xl border border-stone-200/60 relative group">
                                <div
                                    class="w-44 h-44 rounded-lg bg-white p-2 border border-stone-200 shadow-sm overflow-hidden flex items-center justify-center">
                                    <img :src="`/storage/${profile.qris}`" alt="QRIS Toko"
                                        class="w-full h-full object-contain" />
                                </div>
                                <button @click="showQrisModal = true" type="button"
                                    class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-orange-600 hover:text-orange-700 transition-colors cursor-pointer">
                                    <Maximize2 class="w-3.5 h-3.5" />
                                    Perbesar Kode QRIS
                                </button>
                            </div>
                            <div v-else
                                class="mt-4 p-8 text-center bg-stone-50 rounded-xl border border-dashed border-stone-200 text-stone-400">
                                <QrCode class="w-10 h-10 mx-auto mb-2 opacity-50" />
                                <p class="text-xs">Kode QRIS belum diunggah.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Handles Card -->
                    <div
                        class="bg-white p-6 rounded-2xl border border-stone-200/80 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2.5 mb-4">
                                <div
                                    class="w-9 h-9 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center">
                                    <MessageCircle class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-stone-900">Kanal Media Sosial</h3>
                                    <p class="text-xs text-stone-500">Tautan media sosial resmi toko</p>
                                </div>
                            </div>

                            <div class="space-y-3 mt-4">
                                <!-- Instagram -->
                                <div
                                    class="flex items-center justify-between p-3.5 bg-stone-50 rounded-xl border border-stone-100 hover:border-stone-200 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-gradient-to-tr from-amber-500 via-rose-500 to-purple-600 text-white flex items-center justify-center shrink-0">
                                            <Instagram class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-stone-500">Instagram</p>
                                            <p class="text-sm font-medium text-stone-900 truncate">
                                                {{ profile.instagram || 'Belum diatur' }}
                                            </p>
                                        </div>
                                    </div>
                                    <a v-if="profile.instagram" :href="formatSocialUrl(profile.instagram)"
                                        target="_blank" rel="noopener noreferrer"
                                        class="p-2 text-stone-400 hover:text-pink-600 hover:bg-pink-50 rounded-lg transition-colors cursor-pointer"
                                        title="Buka Instagram">
                                        <ExternalLink class="w-4 h-4" />
                                    </a>
                                </div>

                                <!-- Facebook -->
                                <div
                                    class="flex items-center justify-between p-3.5 bg-stone-50 rounded-xl border border-stone-100 hover:border-stone-200 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0">
                                            <Facebook class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-stone-500">Facebook</p>
                                            <p class="text-sm font-medium text-stone-900 truncate">
                                                {{ profile.facebook || 'Belum diatur' }}
                                            </p>
                                        </div>
                                    </div>
                                    <a v-if="profile.facebook" :href="formatSocialUrl(profile.facebook)" target="_blank"
                                        rel="noopener noreferrer"
                                        class="p-2 text-stone-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                        title="Buka Facebook">
                                        <ExternalLink class="w-4 h-4" />
                                    </a>
                                </div>

                                <!-- TikTok -->
                                <div
                                    class="flex items-center justify-between p-3.5 bg-stone-50 rounded-xl border border-stone-100 hover:border-stone-200 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-stone-900 text-white flex items-center justify-center shrink-0 font-bold text-xs">
                                            TT
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-stone-500">TikTok</p>
                                            <p class="text-sm font-medium text-stone-900 truncate">
                                                {{ profile.tiktok || 'Belum diatur' }}
                                            </p>
                                        </div>
                                    </div>
                                    <a v-if="profile.tiktok" :href="formatSocialUrl(profile.tiktok)" target="_blank"
                                        rel="noopener noreferrer"
                                        class="p-2 text-stone-400 hover:text-stone-900 hover:bg-stone-200 rounded-lg transition-colors cursor-pointer"
                                        title="Buka TikTok">
                                        <ExternalLink class="w-4 h-4" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EMPTY STATE -->
            <div v-else-if="!isEditing && !profile"
                class="bg-white rounded-3xl border border-stone-200/80 shadow-sm p-12 text-center">
                <div
                    class="w-20 h-20 rounded-3xl bg-orange-50 text-orange-600 flex items-center justify-center mx-auto mb-5 shadow-inner">
                    <Store class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-extrabold text-stone-900">Belum Ada Profil Toko</h3>
                <p class="text-sm text-stone-500 max-w-md mt-2 mb-6 mx-auto">
                    Profil toko belum dikonfigurasi. Klik tombol di bawah untuk mengisi identitas utama, logo, dan
                    metode pembayaran
                    toko Anda.
                </p>
                <button @click="isEditing = true" type="button"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white text-sm font-bold rounded-xl transition-all shadow-md hover:shadow-lg cursor-pointer">
                    <Plus class="w-5 h-5" />
                    Buat Profil Toko Sekarang
                </button>
            </div>

            <!-- EDIT MODE FORM -->
            <form v-else @submit.prevent="submit" novalidate class="space-y-6">
                <!-- Section 1: General Info -->
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200/80 shadow-sm space-y-5">
                    <div class="flex items-center gap-3 border-b border-stone-100 pb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                            <Building2 class="w-5 h-5" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-stone-900">Informasi & Identitas Toko</h2>
                            <p class="text-xs text-stone-500">Masukkan detail utama yang akan ditampilkan kepada
                                pelanggan.</p>
                        </div>
                    </div>

                    <div class="space-y-4 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">
                                Nama Toko <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.name" type="text"
                                class="w-full px-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors"
                                placeholder="Contoh: Nyuwi Creation" />
                            <p v-if="form.errors.name" class="text-xs text-red-500 mt-1 font-medium">{{ form.errors.name
                            }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">
                                    Nomor Telepon / WhatsApp <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <Phone class="w-4 h-4 text-stone-400 absolute left-3.5 top-3.5" />
                                    <input v-model="form.phone" type="text"
                                        class="w-full pl-10 pr-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors"
                                        placeholder="08xxxxxxxxxx" />
                                </div>
                                <p v-if="form.errors.phone" class="text-xs text-red-500 mt-1 font-medium">{{
                                    form.errors.phone }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">
                                    Kota Asal Pengiriman <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <MapPin class="w-4 h-4 text-stone-400 absolute left-3.5 top-3.5" />
                                    <input v-model="form.city" type="text"
                                        class="w-full pl-10 pr-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors"
                                        placeholder="Contoh: Surabaya" />
                                </div>
                                <p v-if="form.errors.city" class="text-xs text-red-500 mt-1 font-medium">{{
                                    form.errors.city }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">
                                Alamat Lengkap Toko <span class="text-red-500">*</span>
                            </label>
                            <textarea v-model="form.address" rows="3"
                                class="w-full px-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors resize-none"
                                placeholder="Jalan, No. Rumah, Kecamatan, Kode Pos"></textarea>
                            <p v-if="form.errors.address" class="text-xs text-red-500 mt-1 font-medium">{{
                                form.errors.address }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Visual Media -->
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200/80 shadow-sm space-y-5">
                    <div class="flex items-center gap-3 border-b border-stone-100 pb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                            <ImageIcon class="w-5 h-5" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-stone-900">Gambar Logo & Kode QRIS</h2>
                            <p class="text-xs text-stone-500">Unggah berkas gambar dengan format PNG, JPG, atau SVG
                                (maks. 2MB).</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                        <!-- Logo Upload Zone -->
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                                Logo Toko
                            </label>
                            <div class="relative">
                                <div v-if="logoPreview"
                                    class="w-full aspect-square rounded-2xl bg-stone-50 border border-stone-200 overflow-hidden flex items-center justify-center relative group p-4">
                                    <img :src="logoPreview" class="w-full h-full object-contain" />
                                    <button @click="removeLogo" type="button"
                                        class="absolute top-3 right-3 p-2 bg-red-500 hover:bg-red-600 text-white rounded-xl shadow transition-colors cursor-pointer"
                                        title="Hapus gambar">
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>
                                <label v-else
                                    class="flex flex-col items-center justify-center w-full aspect-square rounded-2xl border-2 border-dashed border-stone-300 bg-stone-50 hover:bg-orange-50/50 hover:border-orange-400 cursor-pointer transition-all group p-4 text-center">
                                    <input type="file" accept="image/*" @change="handleLogoUpload" class="hidden" />
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-stone-100 text-stone-400 group-hover:bg-orange-100 group-hover:text-orange-600 flex items-center justify-center mb-3 transition-colors">
                                        <Upload class="w-6 h-6" />
                                    </div>
                                    <span
                                        class="text-sm font-bold text-stone-700 group-hover:text-orange-600 transition-colors">Pilih
                                        Berkas Logo</span>
                                    <span class="text-xs text-stone-400 mt-1">PNG, JPG, SVG (Maks. 2MB)</span>
                                </label>
                            </div>
                            <input v-if="logoPreview" type="file" accept="image/*" @change="handleLogoUpload"
                                class="mt-3 block w-full text-xs text-stone-500 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 cursor-pointer" />
                        </div>

                        <!-- QRIS Upload Zone -->
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                                Gambar QRIS Pembayaran
                            </label>
                            <div class="relative">
                                <div v-if="qrisPreview"
                                    class="w-full aspect-square rounded-2xl bg-stone-50 border border-stone-200 overflow-hidden flex items-center justify-center relative group p-4">
                                    <img :src="qrisPreview" class="w-full h-full object-contain" />
                                    <button @click="removeQris" type="button"
                                        class="absolute top-3 right-3 p-2 bg-red-500 hover:bg-red-600 text-white rounded-xl shadow transition-colors cursor-pointer"
                                        title="Hapus gambar">
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>
                                <label v-else
                                    class="flex flex-col items-center justify-center w-full aspect-square rounded-2xl border-2 border-dashed border-stone-300 bg-stone-50 hover:bg-orange-50/50 hover:border-orange-400 cursor-pointer transition-all group p-4 text-center">
                                    <input type="file" accept="image/*" @change="handleQrisUpload" class="hidden" />
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-stone-100 text-stone-400 group-hover:bg-orange-100 group-hover:text-orange-600 flex items-center justify-center mb-3 transition-colors">
                                        <QrCode class="w-6 h-6" />
                                    </div>
                                    <span
                                        class="text-sm font-bold text-stone-700 group-hover:text-orange-600 transition-colors">Pilih
                                        Kode QRIS</span>
                                    <span class="text-xs text-stone-400 mt-1">PNG, JPG, SVG (Maks. 2MB)</span>
                                </label>
                            </div>
                            <input v-if="qrisPreview" type="file" accept="image/*" @change="handleQrisUpload"
                                class="mt-3 block w-full text-xs text-stone-500 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 cursor-pointer" />
                        </div>
                    </div>
                </div>

                <!-- Section 3: Social Media -->
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200/80 shadow-sm space-y-5">
                    <div class="flex items-center gap-3 border-b border-stone-100 pb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                            <MessageCircle class="w-5 h-5" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-stone-900">Media Sosial Toko</h2>
                            <p class="text-xs text-stone-500">Tautan profil resmi untuk dipublikasikan di toko.</p>
                        </div>
                    </div>

                    <div class="space-y-4 pt-2">
                        <div>
                            <label
                                class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">Instagram</label>
                            <div class="relative">
                                <Instagram class="w-4 h-4 text-pink-500 absolute left-3.5 top-3.5" />
                                <input v-model="form.instagram" type="text"
                                    class="w-full pl-10 pr-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors"
                                    placeholder="https://instagram.com/nyuwi_creation" />
                            </div>
                            <p v-if="form.errors.instagram" class="text-xs text-red-500 mt-1 font-medium">{{
                                form.errors.instagram
                            }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">Facebook</label>
                                <div class="relative">
                                    <Facebook class="w-4 h-4 text-blue-600 absolute left-3.5 top-3.5" />
                                    <input v-model="form.facebook" type="text"
                                        class="w-full pl-10 pr-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors"
                                        placeholder="https://facebook.com/nyuwicreation" />
                                </div>
                                <p v-if="form.errors.facebook" class="text-xs text-red-500 mt-1 font-medium">{{
                                    form.errors.facebook
                                }}</p>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">TikTok</label>
                                <div class="relative">
                                    <span
                                        class="w-4 h-4 text-stone-900 absolute left-3.5 top-3.5 font-bold text-xs">TT</span>
                                    <input v-model="form.tiktok" type="text"
                                        class="w-full pl-10 pr-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors"
                                        placeholder="https://tiktok.com/@nyuwicreation" />
                                </div>
                                <p v-if="form.errors.tiktok" class="text-xs text-red-500 mt-1 font-medium">{{
                                    form.errors.tiktok }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Floating Action Bar in Edit Mode -->
                <div
                    class="bg-white/90 backdrop-blur-md p-4 rounded-2xl border border-stone-200/80 shadow-lg flex items-center justify-between sticky bottom-6 z-30">
                    <span class="text-xs text-stone-500 font-medium hidden sm:inline">
                        Pastikan data yang dimasukkan sudah benar sebelum menyimpan.
                    </span>
                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <button v-if="profile" @click="cancelEditing" type="button"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-sm font-semibold rounded-xl transition-colors cursor-pointer">
                            <X class="w-4 h-4" />
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-orange-600 hover:bg-orange-700 disabled:opacity-50 text-white text-sm font-bold rounded-xl transition-all shadow-md hover:shadow-lg cursor-pointer">
                            <span v-if="form.processing" class="loading loading-spinner loading-sm" />
                            <Save v-else class="w-4 h-4" />
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Zoom QRIS Modal -->
        <div v-if="showQrisModal && profile?.qris"
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="showQrisModal = false">
            </div>
            <div class="relative bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl z-10 space-y-4">
                <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                    <h3 class="text-base font-bold text-stone-900 flex items-center gap-2">
                        <QrCode class="w-5 h-5 text-emerald-600" />
                        QRIS {{ profile.name }}
                    </h3>
                    <button @click="showQrisModal = false"
                        class="p-1 rounded-lg text-stone-400 hover:bg-stone-100 transition-colors cursor-pointer">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <div
                    class="w-full aspect-square rounded-2xl bg-stone-50 border border-stone-200 p-4 flex items-center justify-center overflow-hidden">
                    <img :src="`/storage/${profile.qris}`" alt="QRIS Large" class="w-full h-full object-contain" />
                </div>
                <div class="text-center">
                    <button @click="showQrisModal = false"
                        class="w-full py-2.5 bg-stone-900 text-white rounded-xl font-semibold text-sm hover:bg-stone-800 transition-colors cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import ToastNotification from "@/Components/Customer/Sub-main/ToastNotification.vue";
import ColorPicker from "@/Components/Customer/Sub-main/ColorPicker.vue";
import ImageInput from "@/Components/Customer/Sub-main/ImageInput.vue";
import CategoryInput from "@/Components/Customer/Sub-main/CategoryInput.vue";
import SizeInput from "@/Components/Customer/Sub-main/SizeInput.vue";
import {
    PackagePlus,
    Tag,
    DollarSign,
    Package,
    Scale,
    Palette,
    Image as ImageIcon,
    ArrowLeft,
    Save,
    Eye,
} from "lucide-vue-next";

// Toast notification state
const toastMessage = ref("");
const toastType = ref("info");
const showToast = ref(false);

const props = defineProps({ errors: Object, categories: Array });

const form = useForm({
    name: "",
    stock: "",
    price: "",
    weight: "",
    category_id: "",
    description: "",
    images: [],
    colors: [],
    sizes: [],
});

const selectedCategory = ref("");

// Function to show toast notifications
const showNotification = (message, type = "info") => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
};

// Function to close toast
const closeToast = () => {
    showToast.value = false;
};

const formatRupiah = (number) => {
    if (!number && number !== 0) return "Rp 0";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(number);
};

const selectedCategoryName = computed(() => {
    const cat = props.categories?.find((c) => c.id === selectedCategory.value);
    return cat ? cat.name : "Belum Dipilih";
});

const previewImageUrl = computed(() => {
    if (form.images && form.images.length > 0) {
        const first = form.images[0];
        if (first instanceof File) {
            return URL.createObjectURL(first);
        }
    }
    return null;
});

const saveProduct = () => {
    form.category_id = selectedCategory.value;

    form.post(route("products.store"), {
        onSuccess: () => {
            form.reset();
            selectedCategory.value = "";
            showNotification("Produk berhasil ditambahkan!", "success");
        },
        onError: (errors) => {
            const errorMessages = Object.values(errors).flat();
            showNotification(
                errorMessages[0] || "Terjadi kesalahan saat menyimpan produk",
                "error"
            );
        },
    });
};

const handleCategoryChange = (categoryId) => {
    selectedCategory.value = categoryId;
};
</script>

<template>

    <Head title="Tambah Produk Baru" />

    <AdminLayout pageTitle="Tambah Produk">
        <!-- Toast Notification Component -->
        <ToastNotification :message="toastMessage" :type="toastType" :show="showToast" @close="closeToast" />

        <div class="max-w-6xl mx-auto space-y-6 pb-16">
            <!-- Header Card -->
            <div
                class="bg-white p-6 rounded-2xl border border-stone-200/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-stone-900 tracking-tight">
                                Tambah Produk Baru
                            </h1>
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-50 text-orange-700 border border-orange-200">
                                <PackagePlus class="w-3.5 h-3.5" />
                                Katalog Baru
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">
                            Isi detail informasi produk, harga, stok, serta foto katalog.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Body Grid -->
            <form @submit.prevent="saveProduct" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 Columns: Spec Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Section 1: Product Main Info -->
                    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200/80 shadow-sm space-y-5">
                        <div class="flex items-center gap-3 border-b border-stone-100 pb-4">
                            <div
                                class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                                <Tag class="w-5 h-5" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-stone-900">Informasi Utama Produk</h2>
                                <p class="text-xs text-stone-500">Nama produk, deskripsi lengkap, dan kategori katalog.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4 pt-2">
                            <!-- Nama Produk -->
                            <div>
                                <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">
                                    Nama Produk <span class="text-red-500">*</span>
                                </label>
                                <input type="text" v-model="form.name"
                                    class="w-full px-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors"
                                    :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': errors.name }"
                                    placeholder="Contoh: Buket Bunga Mawar Red Velvet" required />
                                <p v-if="errors.name" class="mt-1 text-xs text-red-600 font-medium">{{ errors.name }}
                                </p>
                            </div>

                            <!-- Kategori Component -->
                            <CategoryInput :categories="categories" v-model="selectedCategory"
                                @category-change="handleCategoryChange" />

                            <!-- Deskripsi Produk -->
                            <div>
                                <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">
                                    Deskripsi Produk <span class="text-red-500">*</span>
                                </label>
                                <textarea v-model="form.description" rows="4"
                                    class="w-full px-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors resize-none"
                                    :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.description }"
                                    placeholder="Jelaskan bahan, detail bunga/hadiah, instruksi perawatan, atau catatan khusus..."
                                    required></textarea>
                                <p v-if="form.errors.description" class="mt-1 text-xs text-red-600 font-medium">{{
                                    form.errors.description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Pricing, Stock & Logistics -->
                    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200/80 shadow-sm space-y-5">
                        <div class="flex items-center gap-3 border-b border-stone-100 pb-4">
                            <div
                                class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                                <DollarSign class="w-5 h-5" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-stone-900">Harga, Stok & Logistik</h2>
                                <p class="text-xs text-stone-500">Harga jual, ketersediaan persediaan stok, dan berat
                                    pengiriman.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                            <!-- Harga -->
                            <div>
                                <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">
                                    Harga Jual (Rp) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-3.5 text-xs font-bold text-stone-500">Rp</span>
                                    <input type="number" v-model="form.price"
                                        class="w-full pl-10 pr-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors"
                                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': errors.price }"
                                        placeholder="0" required min="1" />
                                </div>
                                <p class="text-[11px] text-stone-400 mt-1" v-if="form.price">
                                    Format: {{ formatRupiah(form.price) }}
                                </p>
                                <p v-if="errors.price" class="mt-1 text-xs text-red-600 font-medium">{{ errors.price }}
                                </p>
                            </div>

                            <!-- Jumlah Stok -->
                            <div>
                                <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">
                                    Jumlah Stok <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <Package class="w-4 h-4 text-stone-400 absolute left-3.5 top-3.5" />
                                    <input type="number" v-model="form.stock"
                                        class="w-full pl-10 pr-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors"
                                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': errors.stock }"
                                        placeholder="0" required min="0" />
                                </div>
                                <p v-if="errors.stock" class="mt-1 text-xs text-red-600 font-medium">{{ errors.stock }}
                                </p>
                            </div>

                            <!-- Berat -->
                            <div>
                                <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">
                                    Berat (Gram) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <Scale class="w-4 h-4 text-stone-400 absolute left-3.5 top-3.5" />
                                    <input type="number" v-model="form.weight"
                                        class="w-full pl-10 pr-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors"
                                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': errors.weight }"
                                        placeholder="gram" required min="1" />
                                </div>
                                <p v-if="errors.weight" class="mt-1 text-xs text-red-600 font-medium">{{ errors.weight
                                }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Color & Size Variants -->
                    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200/80 shadow-sm space-y-5">
                        <div class="flex items-center gap-3 border-b border-stone-100 pb-4">
                            <div
                                class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                                <Palette class="w-5 h-5" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-stone-900">Variasi Warna & Ukuran</h2>
                                <p class="text-xs text-stone-500">Pilihan variasi warna dan ukuran opsional yang dapat
                                    dipilih pelanggan.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                            <!-- Color Picker Component -->
                            <ColorPicker v-model:colors="form.colors" @notification="showNotification" />

                            <!-- Size Input Component -->
                            <SizeInput v-model:sizes="form.sizes" @notification="showNotification" />
                        </div>
                    </div>
                </div>

                <!-- Right 1 Column: Media & Live Storefront Preview Card -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Image Input Component -->
                    <div class="bg-white p-6 rounded-3xl border border-stone-200/80 shadow-sm">
                        <ImageInput v-model="form.images" :errors="errors" :max-images="10"
                            @notification="showNotification" />
                    </div>

                    <!-- Live Storefront Preview Card -->
                    <div class="bg-white p-6 rounded-3xl border border-stone-200/80 shadow-sm space-y-4">
                        <div
                            class="flex items-center gap-2 text-stone-900 font-bold text-sm border-b border-stone-100 pb-3">
                            <Eye class="w-4 h-4 text-orange-600" />
                            Pratinjau Kartu Produk
                        </div>

                        <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200/60">
                            <!-- Product Image Preview Container -->
                            <div
                                class="w-full aspect-square rounded-xl bg-stone-200/80 overflow-hidden flex items-center justify-center mb-3 relative group">
                                <img v-if="previewImageUrl" :src="previewImageUrl" alt="Preview"
                                    class="w-full h-full object-cover" />
                                <div v-else
                                    class="flex flex-col items-center justify-center text-stone-400 p-4 text-center">
                                    <ImageIcon class="w-10 h-10 mb-1" />
                                    <span class="text-xs">Foto Produk</span>
                                </div>

                                <span
                                    class="absolute top-2 right-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/90 backdrop-blur text-stone-800 shadow-sm">
                                    {{ selectedCategoryName }}
                                </span>
                            </div>

                            <!-- Details -->
                            <div class="space-y-1.5">
                                <h4 class="font-bold text-stone-900 text-sm truncate">
                                    {{ form.name || 'Nama Produk...' }}
                                </h4>

                                <div class="flex items-baseline justify-between">
                                    <span class="text-base font-extrabold text-orange-600">
                                        {{ formatRupiah(form.price) }}
                                    </span>
                                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md"
                                        :class="form.stock > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-200 text-stone-600'">
                                        {{ form.stock > 0 ? `Stok: ${form.stock}` : 'Stok Kosong' }}
                                    </span>
                                </div>

                                <!-- Variations preview -->
                                <div v-if="form.colors.length > 0 || form.sizes.length > 0"
                                    class="pt-2 flex flex-wrap gap-1 border-t border-stone-200/60">
                                    <span v-for="color in form.colors.slice(0, 3)" :key="color"
                                        class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-stone-200 text-stone-700">
                                        {{ color }}
                                    </span>
                                    <span v-for="size in form.sizes.slice(0, 3)" :key="size"
                                        class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-orange-100 text-orange-800">
                                        {{ size }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Floating Bottom Action Bar -->
                <div
                    class="col-span-1 lg:col-span-3 bg-white/95 backdrop-blur-md p-4 rounded-2xl border border-stone-200/80 shadow-lg flex items-center justify-between sticky bottom-6 z-30">
                    <span class="text-xs text-stone-500 font-medium hidden sm:inline">
                        Pastikan semua kolom dengan tanda <span class="text-red-500">*</span> sudah terisi dengan benar.
                    </span>
                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <Link :href="route('products.index')"
                            class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-sm font-semibold rounded-xl transition-colors cursor-pointer">
                            Kembali
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-orange-600 hover:bg-orange-700 disabled:opacity-50 text-white text-sm font-bold rounded-xl transition-all shadow-md hover:shadow-lg cursor-pointer">
                            <span v-if="form.processing" class="loading loading-spinner loading-sm" />
                            <Save v-else class="w-4 h-4" />
                            Simpan Produk
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

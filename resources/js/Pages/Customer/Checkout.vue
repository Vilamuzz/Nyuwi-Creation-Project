<script setup>
import { Head, useForm, router, usePage, Link } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import FormInput from "@/Components/FormInput.vue";
import FormSelect from "@/Components/FormSelect.vue";
import ToastNotification from "@/Components/Customer/Sub-main/ToastNotification.vue";
import { computed, ref, onMounted, watch } from "vue";
import { ChevronDown, ChevronLeft, ChevronRight, Mail } from "lucide-vue-next";
import { formatPrice } from "@/Utils/format";

const form = useForm({
    name: "",
    address: "",
    city: "",
    district: "",
    village: "",
    province: "",
    phone: "",
    email: "",
    note: "",
    payment_method: "",
    shipping_method: "",
    shipping_cost: 0,
});

const cartTotal = computed(() => {
    if (!cartItems.value || cartItems.value.length === 0) return 0;
    return cartItems.value.reduce((total, item) => {
        return total + item.price * item.quantity;
    }, 0);
});

const props = defineProps({
    cartItems: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
});

const cartItems = computed(() => props.cartItems);
const isLoading = ref(false);
const error = ref(null);
const isOrderSummaryOpen = ref(false);

const onEnter = (el) => {
    el.style.height = '0';
    void el.offsetHeight;
    el.style.height = `${el.scrollHeight}px`;
};

const onAfterEnter = (el) => {
    el.style.height = 'auto';
};

const onLeave = (el) => {
    el.style.height = `${el.scrollHeight}px`;
    void el.offsetHeight;
    el.style.height = '0';
};

const regions = ref({
    provinces: [],
    cities: [],
    districts: [],
    villages: [],
});

const currentType = ref(null);

const regionConfig = {
    provinces: { route: "regions.provinces", param: () => null },
    cities: {
        route: "regions.regencies",
        param: () => regions.value.provinces.find((p) => p.name === form.province || p.id === form.province)?.id ?? form.province,
    },
    districts: {
        route: "regions.districts",
        param: () => regions.value.cities.find((c) => c.name === form.city || c.id === form.city)?.id ?? form.city,
    },
    villages: {
        route: "regions.villages",
        param: () => regions.value.districts.find((d) => d.name === form.district || d.id === form.district)?.id ?? form.district,
    },
};

const fetchRegions = async (type) => {
    const config = regionConfig[type];
    if (!config) return;
    currentType.value = type;
    const param = config.param?.();

    if (type !== "provinces" && (param === undefined || param === null || param === "")) return;

    try {
        if (type === "cities") {
            form.city = null;
            form.district = null;
            form.village = null;
            regions.value.cities = [];
            regions.value.districts = [];
            regions.value.villages = [];
        } else if (type === "districts") {
            form.district = null;
            form.village = null;
            regions.value.districts = [];
            regions.value.villages = [];
        } else if (type === "villages") {
            form.village = null;
            regions.value.villages = [];
        }

        await router.get(
            param !== null && param !== undefined ? route(config.route, param) : route(config.route),
            {},
            {
                only: ["regionData"],
                preserveState: true,
                preserveScroll: true,
            },
        );
    } catch (error) {
        console.error(`Error fetching ${type}:`, error);
    }
};

const totalWithShipping = computed(() => {
    const subtotal = cartTotal.value;
    const shippingCost = form.shipping_cost || 0;
    return subtotal + shippingCost;
});

const page = usePage();

watch(
    () => page.props.regionData,
    (data) => {
        if (!data || !Array.isArray(data)) return;
        if (currentType.value) {
            regions.value[currentType.value] = data;
        }
    },
    { deep: true },
);

const currentStep = ref("information"); // 'information' | 'shipping' | 'payment'

const shippingOptions = ref([]);
const isCalculatingShipping = ref(false);
const shippingError = ref(null);

const paymentOptions = [
    { id: "qris", name: "QRIS", description: "Scan QRIS (GoPay, OVO, ShopeePay, DANA, Mobile Banking)" },
    { id: "digital_wallet", name: "Digital Wallet", description: "Pembayaran instan via E-Wallet" },
];

const selectShippingOption = (option) => {
    form.shipping_method = option.methodLabel || option.service;
    form.shipping_cost = option.cost;
};

const calculateShippingRates = async () => {
    if (!form.city) return;

    isCalculatingShipping.value = true;
    shippingError.value = null;

    try {
        const weight = props.summary?.totalWeight || 1000;
        const response = await fetch(route("shipping.calculate"), {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "",
            },
            body: JSON.stringify({
                destination: form.city,
                weight: weight,
            }),
        });

        const data = await response.json();

        if (response.ok && data.status === "success" && Array.isArray(data.rates)) {
            shippingOptions.value = data.rates.map((rate, index) => ({
                id: `${rate.courier}_${rate.service}_${index}`,
                name: rate.name,
                courier: rate.courier,
                service: rate.service,
                methodLabel: `${rate.courier.toUpperCase()} - ${rate.service}`,
                description: rate.description,
                cost: rate.cost,
                etd: rate.etd,
            }));

            if (shippingOptions.value.length > 0) {
                const existing = shippingOptions.value.find(
                    (opt) => opt.methodLabel === form.shipping_method || opt.service === form.shipping_method
                );
                if (existing) {
                    selectShippingOption(existing);
                } else {
                    selectShippingOption(shippingOptions.value[0]);
                }
            } else {
                form.shipping_method = "";
                form.shipping_cost = 0;
                shippingError.value = "Tidak ada layanan kurir yang tersedia untuk wilayah tujuan ini.";
            }
        } else {
            shippingError.value = data.message || "Gagal memuat opsi pengiriman.";
        }
    } catch (err) {
        console.error("Error calculating shipping:", err);
        shippingError.value = "Terjadi gangguan saat menghitung ongkos kirim. Silakan coba lagi.";
    } finally {
        isCalculatingShipping.value = false;
    }
};

const toast = ref({
    show: false,
    message: "",
    type: "warning",
});

const triggerToast = (message, type = "warning") => {
    toast.value = {
        show: true,
        message,
        type,
    };
};

const hideToast = () => {
    toast.value.show = false;
};

const goToStep = async (step) => {
    if (step === "shipping") {
        if (!form.name || !form.address || !form.province || !form.city || !form.district || !form.village || !form.phone) {
            triggerToast("Silakan lengkapi semua data alamat pengiriman terlebih dahulu.", "warning");
            return;
        }
        currentStep.value = "shipping";
        await calculateShippingRates();
        return;
    }
    if (step === "payment") {
        if (!form.shipping_method || form.shipping_cost < 0) {
            triggerToast("Silakan pilih opsi pengiriman terlebih dahulu.", "warning");
            return;
        }
        if (!form.payment_method && paymentOptions.length > 0) {
            form.payment_method = paymentOptions[0].id;
        }
    }
    currentStep.value = step;
};

const submitOrder = () => {
    form.post(route("customer.orders.store"), {
        preserveScroll: true,
        onError: (errors) => {
            console.error("Error submitting order:", errors);
        },
    });
};

onMounted(() => {
    fetchRegions("provinces");
});
</script>

<template>

    <Head title="Checkout" />
    <ToastNotification :show="toast.show" :message="toast.message" :type="toast.type" @close="hideToast" />
    <!-- Add loading state -->
    <div v-if="isLoading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500"></div>
        <p class="ml-3">Loading cart items...</p>
    </div>

    <!-- Add error state -->
    <div v-else-if="error" class="text-center py-20">
        <p class="text-red-500">{{ error }}</p>
        <button @click="() => { }" class="mt-4 px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
            Retry
        </button>
    </div>

    <form v-else>
        <div class="flex min-h-screen">
            <section class="flex flex-col items-center w-full md:w-1/2 px-4 sm:px-10 py-10 md:py-14 gap-12">
                <div class="w-full max-w-md space-y-10">
                    <div class="flex flex-col items-center gap-5">
                        <ApplicationLogo class="h-16 w-auto fill-current text-gray-800" />
                        <div>
                            <button type="button" @click="isOrderSummaryOpen = !isOrderSummaryOpen"
                                class="flex md:hidden justify-between items-center w-[100vw] -mx-4 sm:-mx-10 px-4 sm:px-10 py-3 bg-orange-500 text-white cursor-pointer shadow-sm">
                                <span class="flex items-center gap-2 font-medium"> Order Summary
                                    <ChevronDown class="w-5 h-5 transition-transform duration-300"
                                        :class="{ 'rotate-180': isOrderSummaryOpen }" />
                                </span>
                                <h1 class="font-extrabold text-lg">
                                    {{ formatPrice(totalWithShipping) }}
                                </h1>
                            </button>

                            <!-- Collapsible Order Summary for Mobile -->
                            <Transition enter-active-class="transition-all duration-300 ease-out overflow-hidden"
                                enter-from-class="opacity-0" enter-to-class="opacity-100"
                                leave-active-class="transition-all duration-300 ease-in overflow-hidden"
                                leave-from-class="opacity-100" leave-to-class="opacity-0" @enter="onEnter"
                                @after-enter="onAfterEnter" @leave="onLeave">
                                <div v-if="isOrderSummaryOpen"
                                    class="block md:hidden w-[100vw] -mx-4 sm:-mx-10 bg-orange-500 text-white border-t border-orange-400">
                                    <div class="px-6 sm:px-10 py-6 flex flex-col">
                                        <div v-for="item in cartItems" :key="item.id"
                                            class="flex flex-row justify-between items-center py-2 text-white border-orange-400/40 last:border-0">
                                            <div class="flex items-center gap-4">
                                                <img :src="'/storage/products/' + item.product.images[0]"
                                                    :alt="item.product.name"
                                                    class="w-16 h-16 object-cover rounded-2xl" />
                                                <div class="flex flex-col">
                                                    <h1 class="font-bold">
                                                        {{ item.product.name }} x {{ item.quantity }}
                                                    </h1>
                                                    <div class="flex items-center text-gray-200 text-sm">
                                                        <span v-if="item.size">Size: {{ item.size }}</span>
                                                        <span v-if="item.color" class="ml-2 flex items-center">
                                                            Color:
                                                            <span
                                                                class="inline-block w-4 h-4 rounded-full ml-1 border border-white/30"
                                                                :style="{ backgroundColor: item.color }"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <h1 class="text-xl font-semibold">
                                                {{ formatPrice(item.price * item.quantity) }}
                                            </h1>
                                        </div>
                                        <div
                                            class="flex flex-col mt-4 pt-4 border-t border-orange-300 text-white text-xl">
                                            <div class="flex justify-between">
                                                <h1>Subtotal</h1>
                                                <h1>{{ formatPrice(cartTotal) }}</h1>
                                            </div>
                                            <div v-if="form.shipping_cost > 0"
                                                class="flex justify-between text-gray-200">
                                                <h1>Shipping</h1>
                                                <h1>{{ formatPrice(form.shipping_cost) }}</h1>
                                            </div>
                                            <div
                                                class="flex justify-between pt-2 font-extrabold text-2xl border-orange-400">
                                                <h1>Total</h1>
                                                <h1>{{ formatPrice(totalWithShipping) }}</h1>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <nav aria-label="Checkout progress"
                            class="flex items-center justify-center text-sm text-gray-600 flex-wrap gap-y-1">
                            <Link href="/cart"
                                class="px-1 font-medium text-gray-900 hover:text-orange-500 transition-colors">Cart
                            </Link>
                            <ChevronRight :size="16" class="text-gray-400" />
                            <button type="button" @click="goToStep('information')" class="px-1 transition-colors"
                                :class="currentStep === 'information' ? 'font-semibold text-gray-900' : 'font-medium text-gray-500 hover:text-gray-800'">
                                Information
                            </button>
                            <ChevronRight :size="16" class="text-gray-400" />
                            <button type="button" @click="goToStep('shipping')" class="px-1 transition-colors"
                                :class="currentStep === 'shipping' ? 'font-semibold text-gray-900' : 'font-medium text-gray-400 hover:text-gray-800'">
                                Shipping
                            </button>
                            <ChevronRight :size="16" class="text-gray-400" />
                            <button type="button" @click="goToStep('payment')" class="px-1 transition-colors"
                                :class="currentStep === 'payment' ? 'font-semibold text-gray-900' : 'font-medium text-gray-400 hover:text-gray-800'">
                                Payment
                            </button>
                        </nav>
                    </div>

                    <!-- STEP 1: INFORMATION -->
                    <div v-if="currentStep === 'information'" class="space-y-6">
                        <div class="space-y-5">
                            <h2 class="flex items-center gap-3 text-xl font-bold text-gray-900">
                                Contact
                            </h2>
                            <FormInput v-if="!page.props.auth?.user" v-model="form.email" type="email"
                                placeholder="contoh@email.com" :error="form.errors.email" required />
                            <p v-else class="flex items-center gap-2 text-sm text-gray-600">
                                <Mail :size="16" class="text-gray-400" />
                                {{ page.props.auth.user.email }}
                            </p>
                        </div>

                        <div class="space-y-4">
                            <h2 class="flex items-center gap-3 text-xl font-bold text-gray-900 mb-5">
                                Shipping address
                            </h2>
                            <FormInput v-model="form.name" type="text" placeholder="Nama lengkap penerima"
                                :error="form.errors.name" required />
                            <FormInput v-model="form.address" type="text" placeholder="Alamat lengkap"
                                :error="form.errors.address" required />
                            <FormSelect v-model="form.province" :options="regions.provinces" option-label="name"
                                option-value="name" placeholder="Pilih Provinsi" :error="form.errors.province" required
                                @update:model-value="() => fetchRegions('cities')" />
                            <FormSelect v-model="form.city" :options="regions.cities" option-label="name"
                                option-value="name" placeholder="Pilih Kota" :error="form.errors.city" required
                                :disabled="!form.province" @update:model-value="() => fetchRegions('districts')" />
                            <FormSelect v-model="form.district" :options="regions.districts" option-label="name"
                                option-value="name" placeholder="Pilih Kecamatan" :error="form.errors.district" required
                                :disabled="!form.city" @update:model-value="() => fetchRegions('villages')" />
                            <FormSelect v-model="form.village" :options="regions.villages" option-label="name"
                                option-value="name" placeholder="Pilih Kelurahan" :error="form.errors.village" required
                                :disabled="!form.district" />
                            <FormInput v-model="form.phone" type="text" placeholder="08xxxxxxxxxx"
                                :error="form.errors.phone" required />
                        </div>
                        <div class="flex flex-col sm:flex-row items-center justify-between pt-2 gap-4">
                            <Link :href="route('cart.show')"
                                class="inline-flex items-center justify-center w-full sm:w-auto gap-1 text-sm font-medium text-gray-500 hover:text-gray-800 transition-colors">
                                <ChevronLeft :size="18" />Kembali ke Cart
                            </Link>
                            <button type="button" @click="goToStep('shipping')"
                                class="inline-flex items-center justify-center w-full sm:w-auto gap-2 rounded-full bg-slate-800 py-3.5 px-8 text-sm font-semibold text-white shadow-sm transition-all hover:bg-slate-900 hover:shadow-md active:scale-[0.98]">
                                Lanjutkan ke Pengiriman
                                <ChevronRight :size="18" />
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: SHIPPING TAB -->
                    <div v-else-if="currentStep === 'shipping'" class="space-y-6">
                        <!-- Summary Card -->
                        <div class="rounded-xl border border-gray-200 p-4 space-y-3 bg-gray-50 text-sm">
                            <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                                <div>
                                    <span class="text-gray-500 block text-xs">Kontak</span>
                                    <span class="text-gray-900 font-medium">{{ form.email ||
                                        page.props.auth?.user?.email }}</span>
                                </div>
                                <button type="button" @click="goToStep('information')"
                                    class="text-xs font-semibold text-orange-600 hover:underline">Ubah</button>
                            </div>
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-gray-500 block text-xs">Kirim ke</span>
                                    <span class="text-gray-900 font-medium">{{ form.name }}, {{ form.address }}, {{
                                        form.city }}, {{ form.province }} ({{ form.phone }})</span>
                                </div>
                                <button type="button" @click="goToStep('information')"
                                    class="text-xs font-semibold text-orange-600 hover:underline">Ubah</button>
                            </div>
                        </div>

                        <!-- Shipping Method Selection -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-bold text-gray-900">Metode Pengiriman</h2>
                            </div>

                            <!-- Loading State -->
                            <div v-if="isCalculatingShipping"
                                class="p-8 text-center bg-gray-50 border border-gray-200 rounded-2xl space-y-3">
                                <div class="loading loading-spinner text-orange-500 loading-md"></div>
                                <p class="text-sm font-medium text-gray-600">Menghitung tarif ongkos kirim ke {{
                                    form.city }}...</p>
                            </div>

                            <!-- Error State -->
                            <div v-else-if="shippingError"
                                class="p-5 bg-amber-50 border border-amber-200 rounded-2xl space-y-2">
                                <p class="text-sm font-medium text-amber-800">{{ shippingError }}</p>
                                <button type="button" @click="calculateShippingRates"
                                    class="text-xs font-semibold text-orange-600 hover:text-orange-700 underline cursor-pointer">
                                    Coba kalkulasi ulang
                                </button>
                            </div>

                            <!-- Dynamic Options List -->
                            <div v-else-if="shippingOptions.length > 0" class="space-y-2">
                                <label v-for="option in shippingOptions" :key="option.id"
                                    @click="selectShippingOption(option)"
                                    class="flex items-center justify-between p-4 border rounded-xl cursor-pointer transition-all"
                                    :class="(form.shipping_cost === option.cost && (form.shipping_method === option.methodLabel || form.shipping_method === option.service)) ? 'border-orange-500 bg-orange-50/50 ring-1 ring-orange-500' : 'border-gray-200 hover:border-gray-300'">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="shipping_option"
                                            :checked="form.shipping_cost === option.cost && (form.shipping_method === option.methodLabel || form.shipping_method === option.service)"
                                            class="text-orange-500 focus:ring-orange-500" />
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ option.name }}</p>
                                            <p class="text-xs text-gray-500">
                                                Estimasi: <span class="font-medium text-gray-700">{{ option.etd ||
                                                    '1-3hari' }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-gray-900">{{ formatPrice(option.cost) }}</span>
                                </label>
                            </div>

                            <div v-else
                                class="p-6 text-center bg-gray-50 border border-dashed border-gray-300 rounded-2xl">
                                <p class="text-sm text-gray-500">Tidak ada opsi pengiriman yang tersedia untuk wilayah
                                    ini.</p>
                                <button type="button" @click="calculateShippingRates"
                                    class="mt-2 text-xs font-semibold text-orange-600 hover:underline cursor-pointer">
                                    Kalkulasi Ulang
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between pt-4 gap-4">
                            <button type="button" @click="goToStep('information')"
                                class="inline-flex items-center justify-center w-full sm:w-auto gap-1 text-sm font-medium text-gray-500 hover:text-gray-800 transition-colors">
                                <ChevronLeft :size="18" />Kembali ke Informasi
                            </button>
                            <button type="button" @click="goToStep('payment')"
                                class="inline-flex items-center justify-center w-full sm:w-auto gap-2 rounded-full bg-slate-800 py-3.5 px-8 text-sm font-semibold text-white shadow-sm transition-all hover:bg-slate-900 hover:shadow-md active:scale-[0.98]">
                                Lanjutkan ke Pembayaran
                                <ChevronRight :size="18" />
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3: PAYMENT TAB -->
                    <div v-else-if="currentStep === 'payment'" class="space-y-6">
                        <!-- Summary Card -->
                        <div class="rounded-xl border border-gray-200 p-4 space-y-3 bg-gray-50 text-sm">
                            <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                                <div>
                                    <span class="text-gray-500 block text-xs">Kirim ke</span>
                                    <span class="text-gray-900 font-medium">{{ form.name }}, {{ form.address }}, {{
                                        form.city }}</span>
                                </div>
                                <button type="button" @click="goToStep('information')"
                                    class="text-xs font-semibold text-orange-600 hover:underline">Ubah</button>
                            </div>
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-gray-500 block text-xs">Metode Pengiriman</span>
                                    <span class="text-gray-900 font-medium">{{ form.shipping_method }} ({{
                                        formatPrice(form.shipping_cost) }})</span>
                                </div>
                                <button type="button" @click="goToStep('shipping')"
                                    class="text-xs font-semibold text-orange-600 hover:underline">Ubah</button>
                            </div>
                        </div>

                        <!-- Payment Method Selection -->
                        <div class="space-y-3">
                            <h2 class="text-xl font-bold text-gray-900">Metode Pembayaran</h2>
                            <div class="space-y-2">
                                <label v-for="option in paymentOptions" :key="option.id"
                                    @click="form.payment_method = option.id"
                                    class="flex items-center justify-between p-4 border rounded-xl cursor-pointer transition-all"
                                    :class="form.payment_method === option.id ? 'border-orange-500 bg-orange-50/50 ring-1 ring-orange-500' : 'border-gray-200 hover:border-gray-300'">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="payment_option"
                                            :checked="form.payment_method === option.id"
                                            class="text-orange-500 focus:ring-orange-500" />
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ option.name }}</p>
                                            <p class="text-xs text-gray-500">{{ option.description }}</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between pt-2 gap-4">
                            <button type="button" @click="goToStep('shipping')"
                                class="inline-flex items-center justify-center w-full sm:w-auto gap-1 text-sm font-medium text-gray-500 hover:text-gray-800 transition-colors">
                                <ChevronLeft :size="18" />Kembali ke Pengiriman
                            </button>
                            <button type="button" @click="submitOrder" :disabled="form.processing"
                                class="inline-flex items-center justify-center w-full sm:w-auto gap-2 rounded-full bg-orange-500 py-3.5 px-8 text-sm font-bold text-white shadow-md transition-all hover:bg-orange-600 hover:shadow-lg active:scale-[0.98] disabled:opacity-50">
                                {{ form.processing ? 'Memproses...' : 'Buat Pesanan' }}
                                <ChevronRight :size="18" />
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            <section
                class="md:flex flex-col items-center w-full md:w-1/2 px-6 sm:px-10 py-10 md:py-14 gap-12 hidden bg-orange-500 p-10">
                <div class="w-1/2">
                    <div class="flex flex-col">
                        <div v-for="item in cartItems" :key="item.id"
                            class="flex flex-row justify-between items-center py-2 text-white">
                            <div class="flex items-center gap-4">
                                <img :src="'/storage/products/' +
                                    item.product.images[0]
                                    " :alt="item.product.name" class="w-16 h-16 object-cover rounded-2xl" />
                                <div class="flex flex-col">
                                    <h1 class="font-bold">
                                        {{ item.product.name }} x
                                        {{ item.quantity }}
                                    </h1>
                                    <div class="flex items-center text-gray-200">
                                        <span v-if="item.size">Size: {{ item.size }}</span>
                                        <span v-if="item.color" class="ml-2 flex items-center">
                                            Color:
                                            <span class="inline-block w-4 h-4 rounded-full ml-1" :style="{
                                                backgroundColor: item.color,
                                            }"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <h1 class="text-xl">
                                {{ formatPrice(item.price * item.quantity) }}
                            </h1>
                        </div>
                        <div class="flex flex-col mt-4 pt-4 border-t text-white text-xl">
                            <div class="flex justify-between">
                                <h1>Subtotal</h1>
                                <h1>{{ formatPrice(cartTotal) }}</h1>
                            </div>
                            <div class="flex justify-between">
                                <h1 v-if="form.shipping_cost > 0">
                                    Shipping
                                </h1>
                                <h1 v-if="form.shipping_cost > 0">
                                    {{ formatPrice(form.shipping_cost) }}
                                </h1>
                            </div>
                            <div class="flex justify-between">
                                <h1 class="font-extrabold text-2xl">Total</h1>
                                <h1 class="font-extrabold text-2xl">
                                    {{ formatPrice(totalWithShipping) }}
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </form>
</template>

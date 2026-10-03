<script setup>
import { Head, useForm, Link, usePage, router } from "@inertiajs/vue3";
import { computed, ref, onMounted, watch } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import FormInput from "@/Components/FormInput.vue";
import FormSelect from "@/Components/FormSelect.vue";
import ToastNotification from "@/Components/Customer/Sub-main/ToastNotification.vue";
import { ChevronDown, ChevronLeft, ChevronRight, Mail, ShoppingCart, ShieldCheck } from "lucide-vue-next";
import { formatPrice } from "@/Utils/format";
import { triggerSnapPayment } from "@/Utils/midtrans";
import axios from "axios";

const page = usePage();

const props = defineProps({
    cartItems: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
});

const form = useForm({
    recipient_name: "",
    address: "",
    province_id: "",
    province_name: "",
    city_id: "",
    city_name: "",
    district_id: "",
    district_name: "",
    postal_code: "",
    phone: "",
    email: page.props.auth?.user?.email ?? "",
    courier: "",
    shipping_service: "",
});

const shippingCost = ref(0);

const cartItems = computed(() => props.cartItems);

const cartTotal = computed(() => {
    if (!cartItems.value || cartItems.value.length === 0) return 0;
    return cartItems.value.reduce((total, item) => {
        return total + item.price * item.quantity;
    }, 0);
});

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
});

const fetchProvinces = async () => {
    try {
        regions.value.provinces = (await axios.get("/locations/provinces")).data;
    } catch (error) {
        console.error("Error fetching provinces:", error);
    }
};

watch(
    () => form.province_id,
    async (id) => {
        form.province_name =
            regions.value.provinces.find((p) => String(p.id) === String(id))?.name ?? "";

        form.city_id = null;
        form.city_name = "";
        form.district_id = null;
        form.district_name = "";
        regions.value.cities = [];
        regions.value.districts = [];

        if (!id) return;

        try {
            regions.value.cities = (await axios.get(`/locations/provinces/${id}/cities`)).data;
        } catch (error) {
            console.error("Error fetching cities:", error);
        }
    }
);

watch(
    () => form.city_id,
    async (id) => {
        form.city_name = regions.value.cities.find((c) => String(c.id) === String(id))?.name ?? "";

        form.district_id = null;
        form.district_name = "";
        regions.value.districts = [];

        if (!id) return;

        try {
            regions.value.districts = (await axios.get(`/locations/cities/${id}/districts`)).data;
        } catch (error) {
            console.error("Error fetching districts:", error);
        }
    }
);

watch(
    () => form.district_id,
    async (id) => {
        form.district_name =
            regions.value.districts.find((d) => String(d.id) === String(id))?.name ?? "";

        // Reset the shipping choice whenever the destination changes.
        shippingOptions.value = [];
        shippingError.value = null;
        form.courier = "";
        form.shipping_service = "";
        shippingCost.value = 0;

        if (id) {
            await calculateShippingRates();
        }
    }
);

const totalWithShipping = computed(() => {
    const subtotal = cartTotal.value;
    return subtotal + (shippingCost.value || 0);
});

const currentStep = ref("information"); // 'information' | 'shipping' | 'payment'

const shippingOptions = ref([]);
const isCalculatingShipping = ref(false);
const shippingError = ref(null);
const isProcessingPayment = ref(false);

const isShippingSelected = (option) =>
    form.courier === option.courier &&
    form.shipping_service === option.service &&
    shippingCost.value === option.cost;

const selectShippingOption = (option) => {
    form.courier = option.courier;
    form.shipping_service = option.service;
    shippingCost.value = option.cost;
};

const calculateShippingRates = async () => {
    const districtId = form.district_id;

    if (!districtId || isCalculatingShipping.value) return;

    isCalculatingShipping.value = true;
    shippingError.value = null;

    try {
        const weight = props.summary?.totalWeight || 1000;

        const { data } = await axios.post(route("shipping.calculate"), {
            destination: String(districtId),
            weight: weight,
        });

        if (data.status === "success" && Array.isArray(data.rates)) {
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
                    (opt) =>
                        opt.courier === form.courier && opt.service === form.shipping_service
                );
                selectShippingOption(existing ?? shippingOptions.value[0]);
            } else {
                form.courier = "";
                form.shipping_service = "";
                shippingCost.value = 0;
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

// Listen for flash messages from backend (e.g. stock/order creation errors)
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.error) {
            triggerToast(flash.error, "error");
        } else if (flash?.success) {
            triggerToast(flash.success, "success");
        }
    },
    { immediate: true, deep: true }
);

const goToStep = async (step) => {
    if (step === "shipping") {
        const isGuest = !page.props.auth?.user;
        if (
            !form.recipient_name ||
            !form.address ||
            !form.province_id ||
            !form.city_id ||
            !form.district_id ||
            !form.phone ||
            (isGuest && !form.email)
        ) {
            triggerToast("Silakan lengkapi semua data kontak dan alamat pengiriman terlebih dahulu.", "warning");
            return;
        }
        currentStep.value = "shipping";
        if (shippingOptions.value.length === 0 && !isCalculatingShipping.value) {
            await calculateShippingRates();
        }
        return;
    }
    if (step === "payment") {
        if (!form.courier || !form.shipping_service || shippingCost.value < 0) {
            triggerToast("Silakan pilih opsi pengiriman terlebih dahulu.", "warning");
            return;
        }
    }
    currentStep.value = step;
};

const launchPaymentModal = async (orderId) => {
    isProcessingPayment.value = true;
    try {
        const response = await axios.post(route("orders.payment", orderId));
        const snapToken = response.data.snap_token;
        const midtrans = page.props.midtrans || {};

        triggerSnapPayment({
            snapToken,
            snapJsUrl: midtrans.snapJsUrl,
            clientKey: midtrans.clientKey,
            onSuccess: (result) => {
                triggerToast("Pembayaran berhasil!", "success");
                if (page.props.auth?.user) {
                    router.visit(route("customer.profile"));
                } else {
                    router.visit(route("cart.show"));
                }
            },
            onPending: (result) => {
                triggerToast("Menunggu penyelesaian pembayaran.", "info");
                if (page.props.auth?.user) {
                    router.visit(route("customer.profile"));
                } else {
                    router.visit(route("cart.show"));
                }
            },
            onError: (result) => {
                triggerToast("Pembayaran gagal atau terjadi kesalahan.", "error");
            },
            onClose: () => {
                triggerToast("Anda menutup popup pembayaran sebelum menyelesaikan transaksi.", "warning");
                if (page.props.auth?.user) {
                    router.visit(route("customer.profile"));
                } else {
                    router.visit(route("cart.show"));
                }
            },
        });
    } catch (err) {
        console.error("Gagal mendapatkan token pembayaran Midtrans:", err);
        const msg = err.response?.data?.message || "Gagal memulai pembayaran Midtrans. Silakan coba lagi.";
        triggerToast(msg, "error");
    } finally {
        isProcessingPayment.value = false;
    }
};

const submitOrder = () => {
    if (form.processing || isProcessingPayment.value) return;

    form.post(route("customer.orders.store"), {
        preserveScroll: true,
        onSuccess: (pageObj) => {
            const placedOrderId = pageObj.props?.flash?.placedOrderId || page.props.flash?.placedOrderId;
            if (placedOrderId) {
                launchPaymentModal(placedOrderId);
            }
        },
        onError: (errors) => {
            console.error("Error submitting order:", errors);
            const errorKeys = Object.keys(errors);
            if (errorKeys.length > 0) {
                const firstError = errors[errorKeys[0]];
                triggerToast(firstError || "Mohon periksa kembali form checkout.", "warning");

                const infoFields = ["recipient_name", "address", "province_id", "city_id", "district_id", "postal_code", "phone", "email"];
                const shippingFields = ["courier", "shipping_service"];

                if (errorKeys.some((k) => infoFields.includes(k))) {
                    currentStep.value = "information";
                } else if (errorKeys.some((k) => shippingFields.includes(k))) {
                    currentStep.value = "shipping";
                }
            }
        },
    });
};

const getProductImage = (product) => {
    if (product?.images && Array.isArray(product.images) && product.images.length > 0) {
        const first = product.images[0];
        return first.startsWith("/") ? first : `/storage/products/${first}`;
    }
    return "/img/products/default.jpg";
};

onMounted(fetchProvinces);
</script>

<template>

    <Head title="Checkout" />
    <ToastNotification :show="toast.show" :message="toast.message" :type="toast.type" @close="hideToast" />
    <!-- Empty Cart state -->
    <div v-if="!cartItems || cartItems.length === 0"
        class="flex flex-col items-center justify-center min-h-[60vh] px-4 text-center">
        <div class="w-20 h-20 mb-4 rounded-full bg-orange-100 flex items-center justify-center text-orange-500">
            <ShoppingCart :size="36" />
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Keranjang Belanja Kosong</h2>
        <p class="text-gray-500 max-w-md mb-6">Anda tidak memiliki produk di keranjang belanja untuk diproses ke
            checkout.</p>
        <Link :href="route('cart.show')"
            class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-full shadow-sm transition-colors">
            Kembali ke Keranjang
        </Link>
    </div>

    <form v-else @submit.prevent>
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
                                                <img :src="getProductImage(item.product)"
                                                    :alt="item.product?.name ?? 'Produk'"
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
                                            <div v-if="shippingCost > 0" class="flex justify-between text-gray-200">
                                                <h1>Shipping</h1>
                                                <h1>{{ formatPrice(shippingCost) }}</h1>
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
                            <FormInput v-model="form.recipient_name" type="text" placeholder="Nama lengkap penerima"
                                :error="form.errors.recipient_name" required />
                            <FormInput v-model="form.address" type="text" placeholder="Alamat lengkap"
                                :error="form.errors.address" required />
                            <FormSelect v-model="form.province_id" :options="regions.provinces" option-label="name"
                                option-value="id" placeholder="Pilih Provinsi" :error="form.errors.province_id"
                                required />
                            <FormSelect v-model="form.city_id" :options="regions.cities" option-label="name"
                                option-value="id" placeholder="Pilih Kota" :error="form.errors.city_id" required
                                :disabled="!form.province_id" />
                            <FormSelect v-model="form.district_id" :options="regions.districts" option-label="name"
                                option-value="id" placeholder="Pilih Kecamatan" :error="form.errors.district_id"
                                required :disabled="!form.city_id" />
                            <FormInput v-model="form.postal_code" type="text" placeholder="Kode pos (opsional)"
                                :error="form.errors.postal_code" />
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
                                    <span class="text-gray-900 font-medium">{{ form.recipient_name }}, {{ form.address
                                        }}, {{
                                            form.city_name }}, {{ form.province_name }} ({{ form.phone }})</span>
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
                                    form.city_name }}...</p>
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
                                    :class="isShippingSelected(option) ? 'border-orange-500 bg-orange-50/50 ring-1 ring-orange-500' : 'border-gray-200 hover:border-gray-300'">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="shipping_option" :checked="isShippingSelected(option)"
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
                                    <span class="text-gray-900 font-medium">{{ form.recipient_name }}, {{ form.address
                                        }}, {{
                                            form.city_name }}</span>
                                </div>
                                <button type="button" @click="goToStep('information')"
                                    class="text-xs font-semibold text-orange-600 hover:underline">Ubah</button>
                            </div>
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-gray-500 block text-xs">Metode Pengiriman</span>
                                    <span class="text-gray-900 font-medium">{{
                                        form.courier ? `${form.courier.toUpperCase()} - ${form.shipping_service}` : '-'
                                        }} ({{
                                            formatPrice(shippingCost) }})</span>
                                </div>
                                <button type="button" @click="goToStep('shipping')"
                                    class="text-xs font-semibold text-orange-600 hover:underline">Ubah</button>
                            </div>
                        </div>

                        <!-- Payment Notice -->
                        <div class="rounded-xl border border-orange-200 bg-orange-50/60 p-5 space-y-2">
                            <div class="flex items-center gap-2 text-orange-900 font-semibold text-base">
                                <ShieldCheck class="w-5 h-5 text-orange-600 flex-shrink-0" />
                                <span>Pembayaran Aman via Midtrans</span>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                Pesanan Anda akan dibuat terlebih dahulu, kemudian popup pembayaran resmi dari Midtrans akan terbuka secara otomatis untuk memilih metode pembayaran (GoPay, QRIS, Virtual Account, Kartu Kredit, dll).
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between pt-2 gap-4">
                            <button type="button" @click="goToStep('shipping')"
                                class="inline-flex items-center justify-center w-full sm:w-auto gap-1 text-sm font-medium text-gray-500 hover:text-gray-800 transition-colors">
                                <ChevronLeft :size="18" />Kembali ke Pengiriman
                            </button>
                            <button type="button" @click="submitOrder" :disabled="form.processing || isProcessingPayment"
                                class="inline-flex items-center justify-center w-full sm:w-auto gap-2 rounded-full bg-orange-500 py-3.5 px-8 text-sm font-bold text-white shadow-md transition-all hover:bg-orange-600 hover:shadow-lg active:scale-[0.98] disabled:opacity-50">
                                <span v-if="form.processing || isProcessingPayment" class="loading loading-spinner loading-xs mr-1"></span>
                                {{ form.processing ? 'Membuat Pesanan...' : isProcessingPayment ? 'Membuka Pembayaran...' : 'Bayar Sekarang' }}
                                <ChevronRight v-if="!form.processing && !isProcessingPayment" :size="18" />
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            <section
                class="md:flex flex-col items-center w-full md:w-1/2 px-6 sm:px-10 py-10 md:py-14 gap-12 hidden bg-orange-500 p-10">
                <div class="flex flex-col">
                    <div v-for="item in cartItems" :key="item.id"
                        class="flex flex-row justify-between items-center py-2 text-white">
                        <div class="flex items-center gap-4">
                            <img :src="getProductImage(item.product)" :alt="item.product?.name ?? 'Produk'"
                                class="w-16 h-16 object-cover rounded-2xl" />
                            <div class="flex flex-col">
                                <h1 class="font-bold">
                                    {{ item.product.name }} x
                                    {{ item.quantity }}
                                </h1>
                                <div class="flex flex-col text-gray-200">
                                    <span v-if="item.size">Size: {{ item.size }}</span>
                                    <span v-if="item.color" class="flex items-center">
                                        Color:
                                        <span class="inline-block w-4 h-4 rounded-full ml-1" :style="{
                                            backgroundColor: item.color,
                                        }"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <h1 class="lg:text-xl text-lg">
                            {{ formatPrice(item.price * item.quantity) }}
                        </h1>
                    </div>
                    <div class="flex flex-col mt-4 pt-4 border-t text-white text-xl">
                        <div class="flex justify-between">
                            <h1>Subtotal</h1>
                            <h1>{{ formatPrice(cartTotal) }}</h1>
                        </div>
                        <div class="flex justify-between">
                            <h1 v-if="shippingCost > 0">
                                Shipping
                            </h1>
                            <h1 v-if="shippingCost > 0">
                                {{ formatPrice(shippingCost) }}
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
            </section>
        </div>
    </form>
</template>

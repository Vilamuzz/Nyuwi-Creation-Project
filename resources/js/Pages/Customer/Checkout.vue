<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { computed, ref, onMounted, watch } from "vue";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Hero from "@/Components/Customer/Main/Hero.vue";

const props = defineProps({
    cartItems: Array,
});

const form = useForm({
    name: "",
    address: "",
    city: "",
    district: "",
    village: "",
    province: "",
    phone: "",
    note: "",
    payment_method: "", // Will be either 'digital_wallet' or 'cash_on_delivery'
    shipping_method: "",
    shipping_cost: 0, // Add shipping cost field
});

const showModal = ref(false);

const validateForm = () => {
    form.clearErrors();

    if (!form.name) form.setError("name", "Name is required");
    if (!form.address) form.setError("address", "Address is required");
    if (!form.city) form.setError("city", "City is required");
    if (!form.district) form.setError("district", "District is required");
    if (!form.village) form.setError("village", "Village is required");
    if (!form.province) form.setError("province", "Province is required");
    if (!form.phone) form.setError("phone", "Phone is required");
    if (!form.payment_method)
        form.setError("payment_method", "Payment method is required");
    if (!form.shipping_method)
        form.setError("shipping_method", "Shipping method is required");

    return Object.keys(form.errors).length === 0;
};

const openConfirmModal = () => {
    if (validateForm()) {
        showModal.value = true;
    }
};

const closeModal = () => {
    showModal.value = false;
};

// Di Checkout.vue, modifikasi fungsi confirmCheckout
const confirmCheckout = () => {
    if (!form.payment_method) {
        form.setError("payment_method", "Payment method is required");
        return;
    }

    form.shipping_cost = selectedShippingRate.value
        ? parseInt(selectedShippingRate.value.price)
        : 0;

    form.post(route("order.store"), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            if (form.payment_method === "digital_wallet") {
                localStorage.setItem("showPaymentInfo", "true");
                localStorage.setItem("paymentAmount", totalWithShipping.value);
            }
            router.visit(route("cart.show"));
        },
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
};

const cartTotal = computed(() => {
    if (!props.cartItems || props.cartItems.length === 0) return 0;
    return props.cartItems.reduce((total, item) => {
        return total + item.price * item.quantity;
    }, 0);
});

const provinces = ref([]);
const cities = ref([]);
const districts = ref([]);
const villages = ref([]);

const selectedProvince = ref(null);
const selectedCity = ref(null);
const selectedDistrict = ref(null);
const selectedVillage = ref(null);

const getProvinces = async () => {
    const response = await axios.get("/api/provinces");
    provinces.value = response.data;
};

const getCities = async (provinceId) => {
    if (!provinceId) return;
    const response = await axios.get(`/api/cities/${provinceId}`);
    cities.value = response.data;
    form.city = null;
    form.district = null;
    form.village = null;
};

const getDistricts = async (cityId) => {
    if (!cityId) return;
    const response = await axios.get(`/api/districts/${cityId}`);
    districts.value = response.data;
    form.district = null;
    form.village = null;
};

const getVillages = async (districtId) => {
    if (!districtId) return;
    const response = await axios.get(`/api/villages/${districtId}`);
    villages.value = response.data;
    form.village = null;
};

onMounted(() => {
    getProvinces();
});

// Add these functions to convert IDs to names before form submission
const setSelectedProvince = (provinceId) => {
    const province = provinces.value.find((p) => p.id === parseInt(provinceId));
    form.province = province ? province.name : "";
    getCities(provinceId);
};

// Add function to clean city name
const cleanCityName = (cityName) => {
    return cityName
        .replace(/KABUPATEN\s+/i, "") // Remove 'KABUPATEN '
        .replace(/KOTA\s+/i, ""); // Remove 'KOTA '
};

// Update setSelectedCity function
const setSelectedCity = (cityId) => {
    const city = cities.value.find((c) => c.id === parseInt(cityId));
    form.city = city ? city.name : "";
    getDistricts(cityId);

    if (form.city && form.shipping_method) {
        fetchShippingRates(form.city);
    }
};

const setSelectedDistrict = (districtId) => {
    const district = districts.value.find((d) => d.id === parseInt(districtId));
    form.district = district ? district.name : "";
    getVillages(districtId);
};

const setSelectedVillage = (villageId) => {
    const village = villages.value.find((v) => v.id === parseInt(villageId));
    form.village = village ? village.name : "";
};

// Add a computed property to check if GoSend is available
const isGoSendAvailable = computed(() => {
    return form.city?.toLowerCase().includes("klaten");
});

const shippingRates = ref([]);
const selectedShippingRate = ref(null);
const isLoadingRates = ref(false);

// Modify fetchShippingRates function
const fetchShippingRates = async (destination) => {
    try {
        const cleanDestination = cleanCityName(destination);
        isLoadingRates.value = true;

        const response = await axios.get(`https://api.binderbyte.com/v1/cost`, {
            params: {
                api_key:
                    "151a782863970433251abbcbf51fe253f4625de1eda00f7a49ba55d90e7419a5",
                courier: form.shipping_method?.toLowerCase(),
                origin: "klaten",
                destination: cleanDestination,
                weight: 1,
                volume: "100x100x100",
            },
        });

        if (response.data.status === 200) {
            shippingRates.value = response.data.data.costs;
        }
    } catch (error) {
        console.error("Error fetching shipping rates:", error);
    } finally {
        isLoadingRates.value = false;
    }
};

// Add watcher for shipping method changes
watch(
    () => form.shipping_method,
    (newMethod) => {
        if (form.city && newMethod) {
            fetchShippingRates(form.city);
        }
    }
);

// Add computed for total with shipping
const totalWithShipping = computed(() => {
    const subtotal = cartTotal.value;
    const shippingCost = selectedShippingRate.value
        ? parseInt(selectedShippingRate.value.price)
        : 0;
    return subtotal + shippingCost;
});
</script>
<template>
    <Head title="Checkout" />
    <CustomersLayout>
        <Hero title="Checkout" breadcrumb="Home > Cart > Checkout" />
        <form @submit.prevent="submitCheckout">
            <section class="mx-24 flex flex-row my-16 gap-x-8">
                <div class="felx flex-col space-y-4 w-1/2">
                    <h1 class="font-bold text-2xl">Billing details</h1>

                    <div class="flex flex-col space-y-2">
                        <label for="first_name">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="rounded-md border-gray-400"
                            required
                        />
                        <div
                            v-if="form.errors.name"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.name }}
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="address">Address</label>
                        <input
                            v-model="form.address"
                            type="text"
                            class="rounded-md border-gray-400"
                            required
                        />
                        <div
                            v-if="form.errors.address"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.address }}
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="province">Province</label>
                        <select
                            @change="setSelectedProvince($event.target.value)"
                            class="rounded-md border-gray-400"
                            required
                        >
                            <option value="">Select Province</option>
                            <option
                                v-for="province in provinces"
                                :key="province.id"
                                :value="province.id"
                            >
                                {{ province.name }}
                            </option>
                        </select>
                        <div
                            v-if="form.errors.province"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.province }}
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="city">City</label>
                        <select
                            @change="setSelectedCity($event.target.value)"
                            class="rounded-md border-gray-400"
                            required
                            :disabled="!form.province"
                        >
                            <option value="">Select City</option>
                            <option
                                v-for="city in cities"
                                :key="city.id"
                                :value="city.id"
                            >
                                {{ city.name }}
                            </option>
                        </select>
                        <div
                            v-if="form.errors.city"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.city }}
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="district">District</label>
                        <select
                            @change="setSelectedDistrict($event.target.value)"
                            class="rounded-md border-gray-400"
                            required
                            :disabled="!form.city"
                        >
                            <option value="">Select District</option>
                            <option
                                v-for="district in districts"
                                :key="district.id"
                                :value="district.id"
                            >
                                {{ district.name }}
                            </option>
                        </select>
                        <div
                            v-if="form.errors.district"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.district }}
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="village">Village</label>
                        <select
                            @change="setSelectedVillage($event.target.value)"
                            class="rounded-md border-gray-400"
                            required
                            :disabled="!form.district"
                        >
                            <option value="">Select Village</option>
                            <option
                                v-for="village in villages"
                                :key="village.id"
                                :value="village.id"
                            >
                                {{ village.name }}
                            </option>
                        </select>
                        <div
                            v-if="form.errors.village"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.village }}
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="phone">Phone</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="rounded-md border-gray-400"
                            required
                        />
                        <div
                            v-if="form.errors.phone"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.phone }}
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="note">Note</label>
                        <input
                            v-model="form.note"
                            type="text"
                            class="rounded-md border-gray-400"
                        />
                    </div>
                </div>
                <div class="flex flex-col w-1/2">
                    <div class="flex flex-row justify-between">
                        <h1 class="font-bold text-2xl">Product</h1>
                        <h1 class="font-bold text-2xl">Price</h1>
                    </div>
                    <div
                        v-for="item in cartItems"
                        :key="item.id"
                        class="flex flex-row justify-between py-2"
                    >
                        <div class="flex flex-col">
                            <h1 class="text-gray-400">
                                {{ item.product.name }} x {{ item.quantity }}
                            </h1>
                            <div class="text-sm text-gray-500">
                                <span v-if="item.size"
                                    >Size: {{ item.size }}</span
                                >
                                <span v-if="item.color" class="ml-2">
                                    Color:
                                    <span
                                        class="inline-block w-4 h-4 rounded-full ml-1"
                                        :style="{ backgroundColor: item.color }"
                                    ></span>
                                </span>
                            </div>
                        </div>
                        <h1 class="text-gray-400">
                            {{ formatPrice(item.price * item.quantity) }}
                        </h1>
                    </div>
                    <div
                        class="flex flex-row justify-between mt-4 pt-4 border-t"
                    >
                        <h1 class="font-bold">Total</h1>
                        <h1 class="font-bold">{{ formatPrice(cartTotal) }}</h1>
                    </div>
                    <div class="flex flex-col space-y-3">
                        <h1 class="font-bold text-2xl">Shipping Method</h1>
                        <div class="flex flex-col space-y-2">
                            <div class="space-x-2">
                                <input
                                    type="radio"
                                    id="jne"
                                    value="JNE"
                                    v-model="form.shipping_method"
                                    name="shipping_method"
                                    class="focus:ring-orange-500 h-4 w-4 text-orange-600 border-gray-300"
                                />
                                <label
                                    for="jne"
                                    class="text-sm font-medium text-gray-700"
                                    >JNE</label
                                >
                            </div>
                            <div
                                v-if="isLoadingRates"
                                class="flex items-center space-x-2 text-gray-500 ml-6"
                            >
                                <svg
                                    class="animate-spin h-5 w-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                <span>Loading shipping rates...</span>
                            </div>
                            <div
                                v-if="shippingRates.length > 0"
                                class="mt-4 ml-6"
                            >
                                <h3 class="font-semibold mb-2">
                                    Shipping Options
                                </h3>
                                <div class="space-y-2">
                                    <div
                                        v-for="rate in shippingRates"
                                        :key="rate.service"
                                        class="flex items-center space-x-2"
                                    >
                                        <input
                                            type="radio"
                                            :id="rate.service"
                                            :value="rate"
                                            v-model="selectedShippingRate"
                                            name="shipping_rate"
                                            class="focus:ring-orange-500 h-4 w-4 text-orange-600 border-gray-300"
                                        />
                                        <label
                                            :for="rate.service"
                                            class="flex justify-between w-full"
                                        >
                                            <span
                                                >{{ rate.service }} ({{
                                                    rate.estimated
                                                }})</span
                                            >
                                            <span>{{
                                                formatPrice(
                                                    parseInt(rate.price)
                                                )
                                            }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="space-x-2">
                                <input
                                    type="radio"
                                    id="GoSend"
                                    value="GoSend"
                                    v-model="form.shipping_method"
                                    name="shipping_method"
                                    :disabled="!isGoSendAvailable"
                                    class="focus:ring-orange-500 h-4 w-4 text-orange-600 border-gray-300 disabled:opacity-50"
                                />
                                <label
                                    for="GoSend"
                                    :class="[
                                        'text-sm font-medium',
                                        isGoSendAvailable
                                            ? 'text-gray-700'
                                            : 'text-gray-400',
                                    ]"
                                >
                                    Go Send
                                    {{
                                        !isGoSendAvailable
                                            ? "(Only available in Klaten)"
                                            : ""
                                    }}
                                </label>
                            </div>
                        </div>

                        <div
                            v-if="form.errors.shipping_method"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.shipping_method }}
                        </div>
                    </div>

                    <div class="flex flex-col space-y-3">
                        <h1 class="font-bold text-2xl">Payment Method</h1>
                        <div class="flex flex-col space-y-2">
                            <div class="space-x-2">
                                <input
                                    type="radio"
                                    id="digital_wallet"
                                    value="digital_wallet"
                                    v-model="form.payment_method"
                                    name="payment_method"
                                />
                                <label for="digital_wallet"
                                    >Digital Wallet (Dana)</label
                                >
                            </div>
                            <div class="space-x-2">
                                <input
                                    type="radio"
                                    id="cash_on_delivery"
                                    value="cash_on_delivery"
                                    v-model="form.payment_method"
                                    name="payment_method"
                                />
                                <label for="cash_on_delivery"
                                    >Cash on Delivery</label
                                >
                            </div>

                            <div
                                v-if="form.errors.payment_method"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ form.errors.payment_method }}
                            </div>
                            <div class="items-center flex">
                                <button
                                    type="button"
                                    @click="openConfirmModal"
                                    class="mx-auto w-1/2 mt-4 py-2 border border-black hover:border-transparent hover:text-white rounded-md hover:bg-orange-500 duration-150"
                                >
                                    Place Order
                                </button>
                                <div
                                    v-if="showModal"
                                    class="fixed inset-0 z-50 overflow-y-auto"
                                >
                                    <div
                                        class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                                    ></div>

                                    <div
                                        class="flex min-h-full items-center justify-center p-4"
                                    >
                                        <div
                                            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                                        >
                                            <div
                                                class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4"
                                            >
                                                <div
                                                    class="sm:flex sm:items-start"
                                                >
                                                    <div
                                                        class="mt-3 text-center sm:mt-0 sm:text-left"
                                                    >
                                                        <h3
                                                            class="text-lg font-bold leading-6 text-gray-900"
                                                        >
                                                            Confirm Order
                                                        </h3>
                                                        <div class="mt-2">
                                                            <p
                                                                class="text-sm text-gray-500"
                                                            >
                                                                Are you sure you
                                                                want to place
                                                                this order?
                                                                Total amount:
                                                                {{
                                                                    formatPrice(
                                                                        cartTotal
                                                                    )
                                                                }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
                                            >
                                                <button
                                                    type="button"
                                                    class="inline-flex w-full justify-center rounded-md bg-orange-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-600 sm:ml-3 sm:w-auto"
                                                    @click="confirmCheckout"
                                                    :disabled="form.processing"
                                                >
                                                    {{
                                                        form.processing
                                                            ? "Processing..."
                                                            : "Confirm Order"
                                                    }}
                                                </button>
                                                <button
                                                    type="button"
                                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                                    @click="closeModal"
                                                >
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="flex flex-row justify-between mt-4 pt-4 border-t"
                    >
                        <div>
                            <h1 class="font-bold">Subtotal</h1>
                            <h1
                                v-if="selectedShippingRate"
                                class="text-gray-600"
                            >
                                Shipping ({{ selectedShippingRate.service }})
                            </h1>
                            <h1 class="font-bold mt-2">Total</h1>
                        </div>
                        <div class="text-right">
                            <h1 class="font-bold">
                                {{ formatPrice(cartTotal) }}
                            </h1>
                            <h1
                                v-if="selectedShippingRate"
                                class="text-gray-600"
                            >
                                {{
                                    formatPrice(
                                        parseInt(selectedShippingRate.price)
                                    )
                                }}
                            </h1>
                            <h1 class="font-bold mt-2">
                                {{ formatPrice(totalWithShipping) }}
                            </h1>
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </CustomersLayout>
</template>

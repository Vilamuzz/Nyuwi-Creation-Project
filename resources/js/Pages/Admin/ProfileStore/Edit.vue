<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { ref, onMounted } from "vue";
import axios from "axios";

const props = defineProps({
    profile: Object,
    errors: Object,
});

// Create the form with the existing profile data
const form = useForm({
    name: props.profile.name,
    logo: null,
    address: props.profile.address,
    city: props.profile.city,
    phone: props.profile.phone,
    instagram: props.profile.instagram || "",
    facebook: props.profile.facebook || "",
    tiktok: props.profile.tiktok || "",
    admin_registration_code: "", // Add this field for admin registration code
    _method: "PUT",
});

// For logo preview
const logoPreview = ref(null);
const logoInput = ref(null);
const existingLogo = `/storage/${props.profile.logo}`;

// For region selection
const provinces = ref([]);
const cities = ref([]);
const selectedProvince = ref(null);
const isLoadingProvinces = ref(false);
const isLoadingCities = ref(false);

// Toast notification state
const showMessage = ref(false);
const message = ref("");
const messageType = ref("success");

// Handle logo file selection
const handleLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.logo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// Fetch provinces from API
const getProvinces = async () => {
    try {
        isLoadingProvinces.value = true;
        const response = await axios.get("/api/provinces");
        provinces.value = response.data.data;
    } catch (error) {
        console.error("Error fetching provinces:", error);
    } finally {
        isLoadingProvinces.value = false;
    }
};

// Fetch cities for a selected province
const getCities = async (provinceId) => {
    if (!provinceId) return;

    try {
        isLoadingCities.value = true;
        const response = await axios.get(`/api/regencies/${provinceId}`);
        cities.value = response.data.data;
    } catch (error) {
        console.error("Error fetching cities:", error);
    } finally {
        isLoadingCities.value = false;
    }
};

// Update form when a province is selected
const setSelectedProvince = (provinceId) => {
    selectedProvince.value = provinceId;
    getCities(provinceId);
    form.city = "";
};

// Update form when a city is selected
const setSelectedCity = (cityName) => {
    form.city = cityName;
};

// Submit the form
const updateProfileStore = () => {
    form.post(route("profile-store.update", props.profile.name), {
        preserveScroll: true,
        onSuccess: () => {
            // Reset file input
            if (logoInput.value) {
                logoInput.value.value = "";
            }

            // Show success message
            message.value = "Profile store updated successfully!";
            messageType.value = "success";
            showMessage.value = true;

            // Hide message after 3 seconds
            setTimeout(() => {
                showMessage.value = false;
            }, 3000);
        },
        onError: () => {
            // Show error message
            message.value =
                "Failed to update profile store. Please check the form for errors.";
            messageType.value = "error";
            showMessage.value = true;

            // Hide message after 3 seconds
            setTimeout(() => {
                showMessage.value = false;
            }, 3000);
        },
    });
};

// Fetch provinces on component mount
onMounted(() => {
    getProvinces();
});
</script>

<template>
    <Head title="Edit Store Profile" />

    <AdminLayout pageTitle="Edit Store Profile">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Store Profile
            </h2>
        </template>

        <!-- DaisyUI Toast Notification -->
        <div v-if="showMessage" class="toast toast-top toast-end z-50">
            <div
                class="alert"
                :class="
                    messageType === 'success' ? 'alert-success' : 'alert-error'
                "
            >
                <span
                    v-if="messageType === 'success'"
                    class="flex items-center"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="stroke-current shrink-0 h-6 w-6 mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    {{ message }}
                </span>
                <span v-else class="flex items-center">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="stroke-current shrink-0 h-6 w-6 mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    {{ message }}
                </span>
            </div>
        </div>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form
                        @submit.prevent="updateProfileStore"
                        class="space-y-6"
                    >
                        <!-- Store Name -->
                        <div>
                            <InputLabel for="name" value="Store Name" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                                autofocus
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.name"
                            />
                        </div>

                        <!-- Logo -->
                        <div>
                            <InputLabel for="logo" value="Store Logo" />

                            <div class="flex items-center mt-2 space-x-6">
                                <!-- Logo Preview -->
                                <div
                                    class="w-24 h-24 border rounded-md overflow-hidden bg-gray-100"
                                >
                                    <img
                                        v-if="logoPreview"
                                        :src="logoPreview"
                                        class="w-full h-full object-cover"
                                        alt="Logo preview"
                                    />
                                    <img
                                        v-else-if="existingLogo"
                                        :src="existingLogo"
                                        class="w-full h-full object-cover"
                                        alt="Current logo"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full flex items-center justify-center text-gray-400"
                                    >
                                        No logo
                                    </div>
                                </div>

                                <!-- File Input -->
                                <div>
                                    <input
                                        ref="logoInput"
                                        type="file"
                                        id="logo"
                                        @change="handleLogoChange"
                                        accept="image/jpeg,image/png,image/jpg,image/svg+xml"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                    />
                                    <div class="mt-1 text-sm text-gray-500">
                                        Upload a store logo image (JPEG, PNG,
                                        JPG, SVG)
                                    </div>
                                </div>
                            </div>
                            <InputError
                                class="mt-2"
                                :message="form.errors.logo"
                            />
                        </div>

                        <!-- Address -->
                        <div>
                            <InputLabel for="address" value="Address" />
                            <textarea
                                id="address"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="form.address"
                                rows="3"
                                required
                            ></textarea>
                            <InputError
                                class="mt-2"
                                :message="form.errors.address"
                            />
                        </div>

                        <div class="flex flex-row gap-x-2 w-full">
                            <!-- Province Selection -->
                            <div class="w-1/2">
                                <InputLabel for="province" value="Province" />
                                <select
                                    id="province"
                                    @change="
                                        setSelectedProvince($event.target.value)
                                    "
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    :disabled="isLoadingProvinces"
                                >
                                    <option value="" selected disabled>
                                        Select Province
                                    </option>
                                    <option
                                        v-for="province in provinces"
                                        :key="province.id"
                                        :value="province.id"
                                    >
                                        {{ province.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- City Selection -->
                            <div class="w-1/2">
                                <InputLabel for="city" value="City" />
                                <select
                                    id="city"
                                    @change="
                                        setSelectedCity(
                                            $event.target.options[
                                                $event.target.selectedIndex
                                            ].text
                                        )
                                    "
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    :disabled="
                                        !selectedProvince || isLoadingCities
                                    "
                                    required
                                >
                                    <option value="" selected disabled>
                                        {{
                                            selectedProvince
                                                ? "Select City"
                                                : "Select Province First"
                                        }}
                                    </option>
                                    <option
                                        v-for="city in cities"
                                        :key="city.id"
                                        :value="city.id"
                                        :selected="city.name === form.city"
                                    >
                                        {{ city.name }}
                                    </option>
                                </select>
                                <div
                                    v-if="
                                        !selectedProvince && !isLoadingProvinces
                                    "
                                    class="mt-1 text-sm text-gray-500"
                                >
                                    Please select a province first
                                </div>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.city"
                                />
                            </div>
                        </div>

                        <!-- Phone -->
                        <div>
                            <InputLabel for="phone" value="Phone Number" />
                            <TextInput
                                id="phone"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.phone"
                                required
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.phone"
                            />
                        </div>

                        <!-- Admin Registration Code -->
                        <div class="border-t pt-4">
                            <h3 class="text-lg font-medium mb-4">
                                Admin Settings
                            </h3>

                            <div>
                                <InputLabel
                                    for="admin_registration_code"
                                    value="Admin Registration Code"
                                />
                                <TextInput
                                    id="admin_registration_code"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="form.admin_registration_code"
                                    placeholder="Enter new admin registration code"
                                />
                                <div class="mt-1 text-sm text-gray-500">
                                    Leave empty to keep current admin
                                    registration code. This code is required for
                                    admin registration.
                                </div>
                                <InputError
                                    class="mt-2"
                                    :message="
                                        form.errors.admin_registration_code
                                    "
                                />
                            </div>
                        </div>

                        <!-- Social Media - Optional Fields -->
                        <div class="border-t pt-4">
                            <h3 class="text-lg font-medium mb-4">
                                Social Media Links (Optional)
                            </h3>

                            <!-- Instagram -->
                            <div class="mb-4">
                                <InputLabel for="instagram" value="Instagram" />
                                <TextInput
                                    id="instagram"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.instagram"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.instagram"
                                />
                            </div>

                            <!-- Facebook -->
                            <div class="mb-4">
                                <InputLabel for="facebook" value="Facebook" />
                                <TextInput
                                    id="facebook"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.facebook"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.facebook"
                                />
                            </div>

                            <!-- TikTok -->
                            <div>
                                <InputLabel for="tiktok" value="TikTok" />
                                <TextInput
                                    id="tiktok"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.tiktok"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.tiktok"
                                />
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end">
                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Update Store Profile
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";
import { LogOut, UserPen } from "lucide-vue-next";

// Accept title as a prop
defineProps({
    title: {
        type: String,
        default: "Admin Panel",
    },
});

// Dropdown state
const isDropdownOpen = ref(false);

// Toggle dropdown
const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

// Close dropdown when clicking outside
const closeDropdown = () => {
    isDropdownOpen.value = false;
};
</script>

<template>
    <nav class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <!-- Left side - Logo/Title -->
            <div class="flex items-center">
                <h1 class="text-xl font-semibold text-gray-800">
                    {{ title }}
                </h1>
            </div>

            <!-- Right side - Profile Circle -->
            <div class="flex items-center">
                <div class="relative">
                    <button
                        @click="toggleDropdown"
                        class="flex items-center justify-center w-8 h-8 bg-blue-500 rounded-full hover:bg-blue-600 transition-colors duration-200"
                    >
                        <span class="text-white text-sm font-medium">A</span>
                    </button>

                    <!-- Dropdown Menu -->
                    <div
                        v-if="isDropdownOpen"
                        @click="closeDropdown"
                        class="fixed inset-0 z-10"
                    ></div>

                    <div
                        v-if="isDropdownOpen"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-20"
                    >
                        <div class="py-1">
                            <Link
                                :href="route('profile.edit')"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                                @click="closeDropdown"
                            >
                                <div class="flex items-center">
                                    <UserPen class="w-4 h-4 mr-2" />
                                    Profile
                                </div>
                            </Link>
                            <hr class="my-1 border-gray-200" />
                            <Link
                                :href="route('logout')"
                                method="post"
                                
                                class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150"
                                @click="closeDropdown"
                            >
                                <div class="flex items-center">
                                    <LogOut class="w-4 h-4 mr-2" />
                                    Logout
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

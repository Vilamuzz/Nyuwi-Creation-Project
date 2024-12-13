<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const form = useForm({});

const downloadBackup = () => {
    form.post(route("admin.database.backup"), {
        preserveScroll: true,
        onError: (errors) => {
            alert("Backup gagal: " + errors.error);
        },
    });
};
</script>

<template>
    <Head title="Database" />
    <AdminLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-2xl font-semibold mb-4">
                            Database Backup
                        </h2>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium">
                                        Download Database Backup
                                    </h3>
                                    <p class="text-gray-600 text-sm mt-1">
                                        Download backup database dalam format
                                        SQL
                                    </p>
                                </div>

                                <button
                                    @click="downloadBackup"
                                    :disabled="form.processing"
                                    class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors duration-200"
                                >
                                    <span v-if="form.processing"
                                        >Memproses...</span
                                    >
                                    <span v-else>Download Backup</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

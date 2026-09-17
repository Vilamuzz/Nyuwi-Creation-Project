<script setup>
import { ref, watch, onMounted } from "vue";
import { CircleCheck, CircleX, CircleAlert, Info } from "lucide-vue-next";

const props = defineProps({
    message: {
        type: String,
        default: "",
    },
    type: {
        type: String,
        default: "info", // 'success', 'error', 'warning', 'info'
        validator: (value) =>
            ["success", "error", "warning", "info"].includes(value),
    },
    duration: {
        type: Number,
        default: 3000,
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close"]);

const isVisible = ref(false);

// Watch for show prop changes
watch(
    () => props.show,
    (newValue) => {
        if (newValue && props.message) {
            isVisible.value = true;

            // Auto-hide after duration
            if (props.duration > 0) {
                setTimeout(() => {
                    closeToast();
                }, props.duration);
            }
        } else {
            isVisible.value = false;
        }
    },
    { immediate: true }
);

const closeToast = () => {
    isVisible.value = false;
    emit("close");
};

const getAlertClass = () => {
    const classes = {
        success: "bg-green-50 text-green-900 border-green-200",
        error: "bg-red-50 text-red-900 border-red-200",
        warning: "bg-yellow-50 text-yellow-900 border-yellow-200",
        info: "bg-blue-50 text-blue-900 border-blue-200",
    };
    return classes[props.type] || "bg-blue-50 text-blue-900 border-blue-200";
};

const getIconComponent = () => {
    switch (props.type) {
        case "success":
            return CircleCheck;
        case "error":
            return CircleX;
        case "warning":
            return CircleAlert;
        default:
            return Info;
    }
};
</script>

<template>
    <div
        v-if="isVisible"
        class="fixed top-4 right-4 z-50 flex flex-col gap-2"
    >
        <div
            class="flex items-start gap-3 rounded-lg border px-4 py-3 shadow-lg"
            :class="getAlertClass()"
        >
            <component
                :is="getIconComponent()"
                class="mt-0.5 h-5 w-5 shrink-0"
            />
            <span class="text-sm">{{ message }}</span>
            <button
                @click="closeToast"
                class="ml-2 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-current opacity-70 transition hover:opacity-100 hover:bg-black/10 focus:outline-none focus:ring-2 focus:ring-current"
                aria-label="Close"
            >
                ✕
            </button>
        </div>
    </div>
</template>

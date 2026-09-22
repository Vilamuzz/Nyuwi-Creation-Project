<script setup>
import { ref, watch, onMounted } from "vue";
import { CircleCheck, CircleX, CircleAlert, Info, X, XIcon } from "lucide-vue-next";

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
    <Transition enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-[-8px] sm:translate-y-0 sm:translate-x-2"
        enter-to-class="opacity-100 translate-y-0 sm:translate-x-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0 sm:translate-x-0"
        leave-to-class="opacity-0 translate-y-[-8px] sm:translate-y-0 sm:translate-x-2">
        <div v-if="isVisible" class="fixed top-4 left-4 right-4 sm:left-auto sm:right-4 z-50 flex flex-col sm:max-w-md w-auto">
            <div class="flex items-center justify-between gap-3 rounded-xl border px-4 py-3 shadow-xl backdrop-blur-sm"
                :class="getAlertClass()">
                <component :is="getIconComponent()" class="h-5 w-5 shrink-0" />
                <span class="text-sm font-medium flex-1 break-words">{{ message }}</span>
                <button @click="closeToast"
                    class="inline-flex p-1 shrink-0 rounded-full opacity-70 transition hover:opacity-100 hover:bg-black/10 focus:outline-none"
                    aria-label="Close">
                    <XIcon :size="18" />
                </button>
            </div>
        </div>
    </Transition>
</template>

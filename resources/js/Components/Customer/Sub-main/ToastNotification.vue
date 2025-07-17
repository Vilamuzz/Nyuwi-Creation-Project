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
        success: "alert-success",
        error: "alert-error",
        warning: "alert-warning",
        info: "alert-info",
    };
    return classes[props.type] || "alert-info";
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
    <div v-if="isVisible" class="toast toast-top toast-end z-50">
        <div class="alert" :class="getAlertClass()">
            <component
                :is="getIconComponent()"
                class="stroke-current shrink-0 h-6 w-6"
            />
            <span>{{ message }}</span>
            <button
                @click="closeToast"
                class="btn btn-sm btn-circle btn-ghost ml-auto"
            >
                ✕
            </button>
        </div>
    </div>
</template>

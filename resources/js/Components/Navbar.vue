<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>
<template>
    <nav>
        <div class="navbar bg-base-100 fixed top-0 left-0 w-full z-50">
            <div class="navbar-start">
                <a class="btn btn-ghost text-xl">daisyUI</a>
            </div>
            <div class="navbar-center">
                <div class="form-control">
                    <div class="relative w-full md:w-96 lg:w-[640px]">
                        <!-- Magnifying glass icon -->
                        <span
                            class="absolute inset-y-0 left-0 flex items-center pl-3"
                        >
                            <img
                                src="../../../public/img/magnifying-glass.svg"
                                width="20px"
                                alt=""
                            />
                        </span>
                        <!-- Search input field -->
                        <input
                            type="text"
                            placeholder="Search"
                            class="input input-bordered w-full h-10 pl-10"
                        />
                    </div>
                </div>
            </div>
            <div class="navbar-end">
                <Link
                    ><button class="btn btn-primary btn-sm mx-2">
                        Sign up
                    </button></Link
                >

                <Link :href="route('login')"
                    ><button class="btn btn-neutral btn-sm">
                        Sign in
                    </button></Link
                >
            </div>
        </div>
    </nav>
</template>

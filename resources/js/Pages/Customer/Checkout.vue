<script setup>
import { Head } from "@inertiajs/vue3";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Hero from "@/Components/Customer/Main/Hero.vue";
</script>
<template>
    <CustomersLayout>
        <Hero />
        <section class="mx-24 flex flex-row my-16 gap-x-8">
            <div class="felx flex-col space-y-4 w-1/2">
                <h1 class="font-bold text-2xl">Billing details</h1>
                <div class="flex flex-row gap-x-4">
                    <div class="flex flex-col space-y-2 w-1/2">
                        <label for="first_name">First Name</label>
                        <input type="text" class="rounded-md border-gray-400" />
                    </div>
                    <div class="flex flex-col space-y-2 w-1/2">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="rounded-md border-gray-400" />
                    </div>
                </div>
                <div class="flex flex-col space-y-2">
                    <label for="address">Address</label>
                    <input type="text" class="rounded-md border-gray-400" />
                </div>
                <div class="flex flex-col space-y-2">
                    <label for="city">City</label>
                    <input type="text" class="rounded-md border-gray-400" />
                </div>
                <div class="flex flex-col space-y-2">
                    <label for="state">Province</label>
                    <input type="text" class="rounded-md border-gray-400" />
                </div>
                <div class="flex flex-col space-y-2">
                    <label for="phone">Phone</label>
                    <input type="text" class="rounded-md border-gray-400" />
                </div>
                <div class="flex flex-col space-y-2">
                    <label for="note">Note</label>
                    <input type="text" class="rounded-md border-gray-400" />
                </div>
            </div>
            <div class="flex flex-col w-1/2">
                <div class="flex flex-row justify-between">
                    <h1 class="font-bold text-2xl">Product</h1>
                    <h1 class="font-bold text-2xl">Price</h1>
                </div>
                <div class="flex flex-row justify-between">
                    <div>
                        <h1 class="text-gray-400">Product 1 x 1</h1>
                    </div>
                    <h1 class="text-gray-400">Rp 10.000</h1>
                </div>
                <div class="flex flex-row justify-between">
                    <h1 class="">Total</h1>
                    <h1 class="">Rp 10.000</h1>
                </div>
                <hr />
                <div class="flex flex-col space-y-3">
                    <h1 class="font-bold text-2xl">Payment Method</h1>
                    <div class="flex flex-col space-y-2">
                        <div class="space-x-2">
                            <input
                                type="radio"
                                id="credit_card"
                                name="payment_method"
                            />
                            <label for="credit_card">Credit Card</label>
                        </div>
                        <div class="space-x-2">
                            <input
                                type="radio"
                                id="digital_wallet"
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
                                name="payment_method"
                            />
                            <label for="cash_on_delivery"
                                >Cash on Delivery</label
                            >
                        </div>
                        <div class="items-center flex">
                            <button
                                class="mx-auto w-1/2 mt-4 py-2 border border-black hover:border-transparent hover:text-white rounded-md hover:bg-orange-500 duration-150"
                            >
                                Place Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </CustomersLayout>
</template>

<template>
    <div
        class="flex flex-col items-start w-[200px] bg-gray-200 rounded-md shadow-md"
    >
        <img
            :src="image"
            alt="Product Image"
            class="w-full h-48 object-cover rounded-t-md"
        />
        <div class="p-4">
            <h2 class="text-lg font-bold">{{ name }}</h2>
            <p class="text-gray-500"></p>
            <h2 class="text-lg font-bold">{{ price }}</h2>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        name: {
            type: String,
            required: true,
        },
        price: {
            type: String,
            required: true,
        },
        image: {
            type: String,
            default: "/img/background/category1.svg", // default jika tidak ada gambar
        },
    },
};
</script>

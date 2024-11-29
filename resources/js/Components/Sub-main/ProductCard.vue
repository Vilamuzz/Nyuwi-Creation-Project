<template>
    <div
        class="flex flex-col items-start w-[200px] bg-gray-200 rounded-md shadow-md"
    >
        <!-- Gambar produk -->
        <img
            :src="image"
            alt="Product Image"
            class="w-full h-48 object-cover rounded-t-md"
        />
        <!-- Detail produk -->
        <div class="p-4">
            <h2 class="text-lg font-bold">{{ name }}</h2>
            <p class="text-gray-500">{{ description }}</p>
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
        description: {
            type: String,
            default: "No description available",
        },
    },
};
</script>

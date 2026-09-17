<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Product from "@/Components/Customer/Sub-main/Product.vue";

const props = defineProps({
    newProducts: { type: Array, default: () => [] },
    featuredProducts: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const newProductsRail = ref(null);
const featuredProductsRail = ref(null);

const scrollRail = (rail, direction) => {
    rail?.scrollBy({ left: direction * 360, behavior: "smooth" });
};

const getCategoryName = (categoryId) => {
    const category = props.categories.find((item) => item.id === categoryId);
    return category?.name || "Uncategorized";
};

const formatPrice = (price) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);

const categoryFallbacks = {
    accessories: "/img/products/acc.jpg",
    decoration: "/img/products/deco.jpg",
    bucket: "/img/products/buck.jpg",
};

const categoryImage = (category) => {
    if (category.image) return `/storage/products/${category.image}`;
    const key = category.name?.toLowerCase() || "";
    const localFallback = Object.entries(categoryFallbacks).find(([name]) =>
        key.includes(name)
    );
    return localFallback?.[1] || "https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=900&q=80";
};

const handleImageError = (event) => {
    if (!event.target.src.includes("images.unsplash.com")) {
        event.target.src = "https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=900&q=80";
    }
};
</script>

<template>
    <Head title="Nyuwi Creation | Handmade pieces with meaning" />
    <CustomersLayout>
        <section
            class="relative isolate overflow-hidden bg-stone-950 text-white"
            aria-labelledby="hero-title"
        >
            <img
                src="/img/background/hero.svg"
                alt=""
                class="absolute inset-0 -z-20 h-full w-full object-cover opacity-40"
            />
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-stone-950 via-stone-950/80 to-orange-950/30"></div>
            <div class="mx-auto grid min-h-[620px] max-w-7xl items-center gap-12 px-6 py-24 lg:grid-cols-[1.1fr_.9fr] lg:px-8">
                <div class="max-w-2xl">
                    <p class="mb-5 text-sm font-semibold uppercase tracking-[0.3em] text-orange-300">Handmade with intention</p>
                    <h1 id="hero-title" class="text-5xl font-bold leading-tight sm:text-6xl lg:text-7xl">Small details. Lasting stories.</h1>
                    <p class="mt-6 max-w-xl text-lg leading-8 text-stone-200">Temukan perhiasan, dekorasi, dan kreasi handmade yang dibuat dengan hati untuk membuat momen sehari-hari terasa lebih berarti.</p>
                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <Link href="/shop" class="inline-flex min-h-12 items-center justify-center rounded-full bg-orange-500 px-7 font-semibold text-white transition hover:bg-orange-400">Belanja sekarang</Link>
                    </div>
                </div>
                <div class="hidden justify-self-end lg:block">
                    <div class="rounded-[2rem] border border-white/20 bg-white/10 p-4 shadow-2xl backdrop-blur-sm">
                        <img src="/img/products/buck.jpg" alt="Contoh kreasi handmade Nyuwi Creation" class="h-[420px] w-[340px] rounded-[1.5rem] object-cover" />
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-orange-50/70 px-6 py-20" aria-labelledby="category-title">
            <div class="mx-auto max-w-7xl">
                <div class="mb-10 max-w-2xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-orange-600">Our Expertises</p>
                    <h2 id="category-title" class="mt-3 text-3xl font-bold text-stone-900 sm:text-4xl">Jangan ragukan produk kami</h2>
                    <p class="mt-4 text-stone-600">Mulai dari kategori yang paling sesuai dengan ide dan momenmu.</p>
                </div>
                <div v-if="props.categories.length" class="relative">
                    <div ref="categoryRail" class="flex snap-x snap-mandatory gap-6 overflow-x-auto pb-4 pr-6 scrollbar-none">
                    <Link v-for="category in props.categories" :key="category.id" href="/shop" class="group w-[18rem] shrink-0 snap-start overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200 transition hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-orange-500 sm:w-[20rem]">
                        <img :src="categoryImage(category)" :alt="category.name" class="h-72 w-full object-cover transition duration-500 group-hover:scale-105" @error="handleImageError" />
                        <div class="flex items-center justify-between p-5"><h3 class="text-xl font-semibold text-stone-900">{{ category.name }}</h3><span class="text-orange-600" aria-hidden="true">→</span></div>
                    </Link>
                    </div>
                    <div class="mt-4 flex justify-end gap-3">
                        <button type="button" aria-label="Previous categories" class="rounded-full border border-stone-300 bg-white px-4 py-2 text-lg hover:bg-stone-100 focus:outline-none focus:ring-2 focus:ring-orange-500" @click="scrollRail(categoryRail, -1)">←</button>
                        <button type="button" aria-label="Next categories" class="rounded-full border border-stone-300 bg-white px-4 py-2 text-lg hover:bg-stone-100 focus:outline-none focus:ring-2 focus:ring-orange-500" @click="scrollRail(categoryRail, 1)">→</button>
                    </div>
                </div>
                <p v-else class="rounded-2xl bg-white p-8 text-stone-600 ring-1 ring-stone-200">Koleksi sedang disiapkan. Jelajahi semua produk untuk melihat pilihan terbaru.</p>
            </div>
        </section>

        <section class="px-6 py-20" aria-labelledby="new-products-title">
            <div class="mx-auto max-w-7xl">
                <div class="mb-10 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-orange-600">New Drops</p>
                        <h2 id="new-products-title" class="mt-3 text-3xl font-bold text-stone-900 sm:text-4xl">Kreasi terbaru kami</h2>
                    </div>
                    <Link href="/shop" class="font-semibold text-orange-600 hover:text-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500">Lihat semua produk <span aria-hidden="true">→</span></Link>
                </div>
                <div v-if="props.newProducts.length" class="relative">
                    <div ref="newProductsRail" class="flex snap-x snap-mandatory gap-6 overflow-x-auto pb-4 scrollbar-none">
                        <Product v-for="item in props.newProducts" :key="item.slug" class="w-[18rem] shrink-0 snap-start sm:w-[21rem]" :slug="item.slug" :name="item.name" :price="formatPrice(item.price)" :category="getCategoryName(item.category_id)" :image="item.image" :rating="item.average_rating || 0" :total-reviews="item.total_reviews || 0" :stock="item.stock" :colors="item.colors" :sizes="item.sizes" />
                    </div>
                    <div class="mt-4 flex justify-end gap-3">
                        <button type="button" aria-label="Previous new products" class="rounded-full border border-stone-300 bg-white px-4 py-2 text-lg hover:bg-stone-100 focus:outline-none focus:ring-2 focus:ring-orange-500" @click="scrollRail(newProductsRail, -1)">←</button>
                        <button type="button" aria-label="Next new products" class="rounded-full border border-stone-300 bg-white px-4 py-2 text-lg hover:bg-stone-100 focus:outline-none focus:ring-2 focus:ring-orange-500" @click="scrollRail(newProductsRail, 1)">→</button>
                    </div>
                </div>
                <div v-else class="rounded-2xl border border-dashed border-stone-300 p-12 text-center text-stone-600">Belum ada produk yang tersedia saat ini.</div>
            </div>
        </section>

        <section id="about" class="scroll-mt-24 bg-stone-100 px-6 py-20" aria-labelledby="about-title">
            <div class="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-2">
                <img src="/img/products/buck.jpg" alt="Kreasi handmade Nyuwi Creation" class="h-[420px] w-full rounded-3xl object-cover shadow-lg" />
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-orange-600">Cerita kami</p>
                    <h2 id="about-title" class="mt-3 text-3xl font-bold text-stone-900 sm:text-4xl">Benda sederhana, dibuat dengan penuh perhatian</h2>
                    <p class="mt-6 text-lg leading-8 text-stone-600">Nyuwi Creation percaya bahwa setiap benda buatan tangan menceritakan sebuah kisah tentang seni. Dengan semangat kreativitas, kami mengubah bahan-bahan sederhana menjadi keindahan yang bermakna—dari perhiasan elegan hingga karangan bunga yang menawan.</p>
                    <p class="mt-4 leading-7 text-stone-600">Setiap bagian dibuat dengan hati untuk membangkitkan kegembiraan dan menghadirkan sentuhan personal di ruang maupun momenmu.</p>
                </div>
            </div>
        </section>

        <section class="px-6 py-16" aria-label="Layanan Nyuwi Creation">
            <div class="mx-auto grid max-w-7xl gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="benefit in [{ title: 'Kualitas terjaga', text: 'Dibuat dari material pilihan', icon: '/img/icon/trophy.svg' }, { title: 'Perlindungan', text: 'Dukungan untuk pesananmu', icon: '/img/icon/check-badge.svg' }, { title: 'Pengiriman aman', text: 'Dikemas dengan penuh perhatian', icon: '/img/icon/boxheart.svg' }, { title: 'Bantuan langsung', text: 'Kami siap membantu', icon: '/img/icon/support.svg' }]" :key="benefit.title" class="flex gap-4 rounded-2xl border border-stone-200 bg-white p-5">
                    <img :src="benefit.icon" :alt="benefit.title" class="h-10 w-10" />
                    <div>
                        <h3 class="font-semibold text-stone-900">{{ benefit.title }}</h3>
                        <p class="mt-1 text-sm text-stone-600">{{ benefit.text }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-orange-50/60 px-6 py-20" aria-labelledby="featured-products-title">
            <div class="mx-auto max-w-7xl">
                <div class="mb-10 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-orange-600">Produk Unggulan</p>
                        <h2 id="featured-products-title" class="mt-3 text-3xl font-bold text-stone-900 sm:text-4xl">Favorit pelanggan</h2>
                    </div>
                    <Link href="/shop" class="font-semibold text-orange-600 hover:text-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500">Lihat semua produk <span aria-hidden="true">→</span></Link>
                </div>
                <div v-if="props.featuredProducts.length" class="relative">
                    <div ref="featuredProductsRail" class="flex snap-x snap-mandatory gap-6 overflow-x-auto pb-4 scrollbar-none">
                        <Product v-for="item in props.featuredProducts" :key="item.slug" class="w-[18rem] shrink-0 snap-start sm:w-[21rem]" :slug="item.slug" :name="item.name" :price="formatPrice(item.price)" :category="getCategoryName(item.category_id)" :image="item.image" :rating="item.average_rating || 0" :total-reviews="item.total_reviews || 0" :stock="item.stock" :colors="item.colors" :sizes="item.sizes" />
                    </div>
                    <div class="mt-4 flex justify-end gap-3">
                        <button type="button" aria-label="Previous featured products" class="rounded-full border border-stone-300 bg-white px-4 py-2 text-lg hover:bg-stone-100 focus:outline-none focus:ring-2 focus:ring-orange-500" @click="scrollRail(featuredProductsRail, -1)">←</button>
                        <button type="button" aria-label="Next featured products" class="rounded-full border border-stone-300 bg-white px-4 py-2 text-lg hover:bg-stone-100 focus:outline-none focus:ring-2 focus:ring-orange-500" @click="scrollRail(featuredProductsRail, 1)">→</button>
                    </div>
                </div>
                <div v-else class="rounded-2xl border border-dashed border-stone-300 p-12 text-center text-stone-600">Belum ada produk unggulan saat ini.</div>
            </div>
        </section>

        <section class="bg-orange-500 px-6 py-16 text-center text-white">
            <h2 class="text-3xl font-bold sm:text-4xl">Siap menemukan kreasi favoritmu?</h2>
            <p class="mx-auto mt-4 max-w-xl text-orange-50">Jelajahi koleksi Nyuwi dan temukan detail kecil yang membuat harimu lebih istimewa.</p>
            <Link href="/shop" class="mt-8 inline-flex min-h-12 items-center rounded-full bg-stone-950 px-8 font-semibold transition hover:bg-stone-800 focus:outline-none focus:ring-2 focus:ring-stone-950 focus:ring-offset-2 focus:ring-offset-orange-500">Jelajahi koleksi</Link>
        </section>
    </CustomersLayout>
</template>

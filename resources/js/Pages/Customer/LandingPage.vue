<script setup>
import { Head, Link } from "@inertiajs/vue3";
import CustomersLayout from "@/Layouts/CustomersLayout.vue";
import Product from "@/Components/Customer/Sub-main/Product.vue";

const props = defineProps({
    products: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

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

const categoryImage = (category) =>
    category.image
        ? `/storage/products/${category.image}`
        : "/img/products/default.jpg";
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
                        <Link href="/shop" class="inline-flex min-h-12 items-center justify-center rounded-full bg-orange-500 px-7 font-semibold text-white transition hover:bg-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:ring-offset-2 focus:ring-offset-stone-950">Belanja sekarang</Link>
                        <a href="#about" class="inline-flex min-h-12 items-center justify-center rounded-full border border-white/50 px-7 font-semibold text-white transition hover:border-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white">Kenali Nyuwi</a>
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
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-orange-600">Jelajahi koleksi</p>
                    <h2 id="category-title" class="mt-3 text-3xl font-bold text-stone-900 sm:text-4xl">Temukan sesuatu yang terasa personal</h2>
                    <p class="mt-4 text-stone-600">Mulai dari kategori yang paling sesuai dengan ide dan momenmu.</p>
                </div>
                <div v-if="props.categories.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Link v-for="category in props.categories.slice(0, 3)" :key="category.id" href="/shop" class="group overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200 transition hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <img :src="categoryImage(category)" :alt="category.name" class="h-64 w-full object-cover transition duration-500 group-hover:scale-105" @error="$event.target.src = '/img/products/default.jpg'" />
                        <div class="flex items-center justify-between p-5"><h3 class="text-xl font-semibold text-stone-900">{{ category.name }}</h3><span class="text-orange-600" aria-hidden="true">→</span></div>
                    </Link>
                </div>
                <p v-else class="rounded-2xl bg-white p-8 text-stone-600 ring-1 ring-stone-200">Koleksi sedang disiapkan. Jelajahi semua produk untuk melihat pilihan terbaru.</p>
            </div>
        </section>

        <section class="px-6 py-20" aria-labelledby="products-title">
            <div class="mx-auto max-w-7xl">
                <div class="mb-10 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div><p class="text-sm font-semibold uppercase tracking-[0.25em] text-orange-600">Pilihan untukmu</p><h2 id="products-title" class="mt-3 text-3xl font-bold text-stone-900 sm:text-4xl">Produk unggulan</h2></div>
                    <Link href="/shop" class="font-semibold text-orange-600 hover:text-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500">Lihat semua produk <span aria-hidden="true">→</span></Link>
                </div>
                <div v-if="props.products.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 lg:gap-6">
                    <Product v-for="item in props.products" :key="item.slug" :slug="item.slug" :name="item.name" :price="formatPrice(item.price)" :category="getCategoryName(item.category_id)" :image="item.image" :rating="item.average_rating || 0" :total-reviews="item.total_reviews || 0" />
                </div>
                <div v-else class="rounded-2xl border border-dashed border-stone-300 p-12 text-center text-stone-600">Belum ada produk yang tersedia saat ini.</div>
            </div>
        </section>

        <section id="about" class="scroll-mt-24 bg-stone-100 px-6 py-20" aria-labelledby="about-title">
            <div class="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-2">
                <img src="/img/products/buck.jpg" alt="Kreasi handmade Nyuwi Creation" class="h-[420px] w-full rounded-3xl object-cover shadow-lg" />
                <div><p class="text-sm font-semibold uppercase tracking-[0.25em] text-orange-600">Cerita kami</p><h2 id="about-title" class="mt-3 text-3xl font-bold text-stone-900 sm:text-4xl">Benda sederhana, dibuat dengan penuh perhatian</h2><p class="mt-6 text-lg leading-8 text-stone-600">Nyuwi Creation percaya bahwa setiap benda buatan tangan menceritakan sebuah kisah tentang seni. Dengan semangat kreativitas, kami mengubah bahan-bahan sederhana menjadi keindahan yang bermakna—dari perhiasan elegan hingga karangan bunga yang menawan.</p><p class="mt-4 leading-7 text-stone-600">Setiap bagian dibuat dengan hati untuk membangkitkan kegembiraan dan menghadirkan sentuhan personal di ruang maupun momenmu.</p></div>
            </div>
        </section>

        <section class="px-6 py-16" aria-label="Layanan Nyuwi Creation">
            <div class="mx-auto grid max-w-7xl gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="benefit in [{ title: 'Kualitas terjaga', text: 'Dibuat dari material pilihan', icon: '/img/icon/trophy.svg' }, { title: 'Perlindungan', text: 'Dukungan untuk pesananmu', icon: '/img/icon/check-badge.svg' }, { title: 'Pengiriman aman', text: 'Dikemas dengan penuh perhatian', icon: '/img/icon/boxheart.svg' }, { title: 'Bantuan langsung', text: 'Kami siap membantu', icon: '/img/icon/support.svg' }]" :key="benefit.title" class="flex gap-4 rounded-2xl border border-stone-200 bg-white p-5"><img :src="benefit.icon" :alt="benefit.title" class="h-10 w-10" /><div><h3 class="font-semibold text-stone-900">{{ benefit.title }}</h3><p class="mt-1 text-sm text-stone-600">{{ benefit.text }}</p></div></div>
            </div>
        </section>

        <section class="bg-orange-500 px-6 py-16 text-center text-white"><h2 class="text-3xl font-bold sm:text-4xl">Siap menemukan kreasi favoritmu?</h2><p class="mx-auto mt-4 max-w-xl text-orange-50">Jelajahi koleksi Nyuwi dan temukan detail kecil yang membuat harimu lebih istimewa.</p><Link href="/shop" class="mt-8 inline-flex min-h-12 items-center rounded-full bg-stone-950 px-8 font-semibold transition hover:bg-stone-800 focus:outline-none focus:ring-2 focus:ring-stone-950 focus:ring-offset-2 focus:ring-offset-orange-500">Jelajahi koleksi</Link></section>
    </CustomersLayout>
</template>

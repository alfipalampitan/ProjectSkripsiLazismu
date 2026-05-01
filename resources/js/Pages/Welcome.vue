<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>

    <Head title="Lazismu - Memberi Untuk Negeri" />

    <div class="bg-gray-50 font-sans text-gray-900 antialiased">
        <nav class="bg-white shadow-md sticky top-0 z-50">
            <div class="container mx-auto px-6 py-3 flex justify-between items-center">
                <img src="/images/Lazismu.png" alt="Logo" class="h-12">

                <div class="hidden md:flex space-x-8 text-gray-700 font-medium">
                    <a href="#" class="hover:text-orange-500 transition">Tentang</a>
                    <a href="#" class="hover:text-orange-500 transition">Program</a>
                    <a href="#" class="hover:text-orange-500 transition">Berita</a>
                    <a href="#" class="hover:text-orange-500 transition">Artikel</a>
                    <a href="#" class="hover:text-orange-500 transition">Publikasi</a>
                    <Link :href="route('donatur.index')">Daftar Donatur</Link>
                </div>

                <div class="flex items-center space-x-4">
                    <div v-if="canLogin" class="flex items-center space-x-3">
                        <Link v-if="$page.props.auth.user"
                            :href="$page.props.auth.user.role === 'admin' ? route('admin.dashboard') : route('staff.dashboard')"
                            class="text-sm text-gray-700 underline hover:text-orange-500 transition">
                            Dashboard
                        </Link>
                    </div>

                    <Link href="/pilih-program"
                        class="bg-[#f29222] text-white px-6 py-2 rounded-md font-bold hover:bg-orange-600 transition shadow-md inline-block">
                        Donasi Sekarang
                    </Link>
                    <i
                        class="fa-solid fa-magnifying-glass text-gray-500 cursor-pointer hover:text-gray-700 transition"></i>
                </div>
            </div>
        </nav>

        <header class="relative bg-[#f29222] text-white overflow-hidden">
            <div class="container mx-auto px-6 py-16 flex flex-col md:flex-row items-center relative z-10">
                <div class="md:w-1/2 mb-10 md:mb-0">
                    <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-4">
                        Donasi Lebih Mudah <br> Melalui Web Lazismu
                    </h1>
                    <p class="text-xl mb-8 font-light">Banyak Pilihan Fitur Pembayaran yang aman dan transparan.</p>
                    <div class="flex space-x-4">
                        <button
                            class="bg-white text-orange-600 px-8 py-3 rounded-full font-bold shadow-lg hover:bg-gray-100 transition duration-300">
                            Klik Disini Aja
                        </button>
                        <button
                            class="border-2 border-white text-white px-8 py-3 rounded-full font-bold hover:bg-white hover:text-orange-600 transition duration-300">
                            Lihat Daftar Rekening
                        </button>
                    </div>
                </div>
                <div class="md:w-1/2 flex justify-center">
                    <div class="relative w-full max-w-lg">
                        <img src="/images/Hero.jpg" alt="Hero Image" class="rounded-lg shadow-2xl">
                    </div>
                </div>
            </div>
            <div class="absolute top-0 right-0 opacity-10 pointer-events-none">
                <i class="fa-solid fa-mosque text-[20rem]"></i>
            </div>
        </header>

        <section class="py-20 bg-white">
            <div class="container mx-auto px-6 flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-1/2">
                    <div
                        class="aspect-video bg-gray-100 rounded-xl overflow-hidden shadow-xl flex items-center justify-center relative border border-gray-200">
                        <iframe class="w-full h-full" src="https://www.youtube.com/embed/dQw4w9WgXcQ"
                            title="YouTube video player" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                    </div>
                </div>
                <div class="md:w-1/2">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Tentang LAZISMU</h2>
                    <p class="text-orange-500 font-bold mb-4">#MemberiUntukNegeri</p>
                    <p class="text-gray-600 leading-relaxed mb-6 text-justify">
                        LAZISMU adalah lembaga zakat tingkat nasional yang berkhidmat dalam pemberdayaan masyarakat
                        melalui pendayagunaan secara produktif dana zakat, infaq, dan dana kedermawanan lainnya baik
                        dari perseorangan, lembaga, perusahaan dan instansi lainnya.
                    </p>
                    <p class="text-gray-600 leading-relaxed text-justify">
                        Didirikan pada tahun 2002 dan dikukuhkan legalitasnya hingga SK Menteri Agama No. 90 Tahun 2022,
                        mengelola dana zakat dan infaq melalui manajemen modern yang amanah dan transparan.
                    </p>
                </div>
            </div>
        </section>

        <a href="https://wa.me/yournumber" target="_blank"
            class="fixed bottom-6 right-6 bg-green-500 text-white p-4 rounded-full shadow-2xl hover:bg-green-600 transition-all z-50 hover:scale-110">
            <i class="fa-brands fa-whatsapp text-3xl"></i>
        </a>
    </div>
</template>
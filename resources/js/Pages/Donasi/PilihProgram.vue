<script setup>
import 'swiper/css';
import { ref } from 'vue';
import 'swiper/css/pagination';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, Pagination } from 'swiper/modules';

const daftarKategori = ref([
    { nama: 'Qurban', icon: 'fa-solid fa-cow' },
    { nama: 'Zakat', icon: 'fa-solid fa-hand-holding-dollar' },
    { nama: 'Infaq', icon: 'fa-solid fa-heart' },
    { nama: 'Wakaf', icon: 'fa-solid fa-mosque' },
    { nama: 'Pilar', icon: 'fa-solid fa-star' },
]);

const programTren = ref([
    { id: 1, judul: 'Infaq Sedekah Berbagi Kapanpun', gambar: '/images/infaq.jpg' },
    { id: 2, judul: 'Zakat Maal Bersihkan Harta', gambar: '/images/zakatmaal.jpg' },
    { id: 3, judul: 'Fidyah Pengganti Puasa', gambar: '/images/fidyah.jpg' },
    { id: 4, judul: 'Donasi Peduli Palestina', gambar: '/images/palestina.jpg' },
]);

const pilihKategori = (nama) => {
    // Logika filter atau pindah ke daftar spesifik kategori
    console.log("Memilih kategori: ", nama);
};

const bukaModalDonasi = (program) => {
    // Di sini panggil modal yang sudah kita buat sebelumnya di Transaksi.vue
    // Kamu bisa menggunakan Shared State atau emit untuk memunculkan modalnya
    alert("Buka form donasi untuk: " + program.judul);
};

//ini adalah kode untuk menginisialisasi modul Swiper yang kita gunakan di template
const modules = [Autoplay, Pagination];

// --- TAMBAH FOTO DI SINI ---
const bannerSlides = ref([
    {
        tag: 'Program Prioritas: Ramadhan 1447H',
        judul: "Sedekah Al-Qur'an Untuk Pelosok Negeri",
        deskripsi: "Satu huruf yang dibaca, pahalanya mengalir abadi untuk Anda.",
        gambar: "/images/Lazismu.png",
        warna: "from-orange-50 to-orange-100"
    },
    {
        tag: 'Zakat Maal',
        judul: "Sucikan Harta dengan Zakat Maal",
        deskripsi: "Bantu sesama dan bersihkan harta Anda melalui program Lazismu.",
        gambar: "/images/zakatmaal.jpg", // Sesuaikan nama file fotomu
        warna: "from-blue-50 to-blue-100"
    },
]);

const props = defineProps({
    programs: Array,
    kategoriAktif: String
});

const filterKategori = (namaKategori) => {
    // Ini akan memanggil route yang sama tapi dengan filter kategori
    router.get('/pilih-program', { kategori: namaKategori }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['programs', 'kategoriAktif']
    });
};


let interval = null;

onMounted(() => {
    // Jalankan pengecekan setiap 5 detik (5000ms)
    interval = setInterval(() => {
        router.reload({
            only: ['programs'], // Hanya ambil data 'programs' saja, biar ringan
            preserveScroll: true, // Jangan scroll ke atas kalau data update
            preserveState: true  // Jangan reset input atau state lain
        });
    }, 5000);
});

onUnmounted(() => {
    // Matikan interval kalau user pindah halaman agar tidak boros RAM
    if (interval) clearInterval(interval);
});
</script>

<template>

    <div class="max-w-7xl mx-auto px-4 pt-6 pb-12">
        <swiper :modules="modules" :slides-per-view="1" :loop="true" :autoplay="{ delay: 5000 }"
            :pagination="{ clickable: true }" class="rounded-[2.5rem] shadow-2xl border border-white overflow-hidden">
            <swiper-slide v-for="(slide, index) in bannerSlides" :key="index">
                <div
                    :class="`bg-gradient-to-br ${slide.warna} p-8 md:p-16 flex flex-col md:flex-row items-center gap-8`">

                    <div class="w-full md:w-1/2 space-y-6 text-center md:text-left">
                        <div
                            class="inline-flex items-center bg-orange-200/50 px-4 py-2 rounded-full text-orange-700 text-sm font-bold">
                            {{ slide.tag }}
                        </div>
                        <h1 class="text-4xl md:text-6xl font-black text-gray-800 leading-tight" v-html="slide.judul">
                        </h1>
                        <p class="text-gray-600 text-lg">{{ slide.deskripsi }}</p>
                        <button class="bg-orange-500 text-white px-10 py-4 rounded-2xl font-bold text-lg shadow-lg">
                            Donasi Sekarang
                        </button>
                    </div>

                    <div class="w-full md:w-1/2 flex justify-center relative">
                        <div class="absolute inset-0 bg-orange-300 rounded-full blur-[100px] opacity-20 animate-pulse">
                        </div>
                        <img :src="slide.gambar"
                            class="relative z-10 rounded-2xl w-full max-w-md drop-shadow-2xl animate-float" />
                    </div>

                </div>
            </swiper-slide>
        </swiper>
    </div>

    <div class="min-h-screen bg-gray-50 p-8">
        <div class="max-w-6xl mx-auto space-y-12">

            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold mb-6">Kategori Pilihan</h2>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <button v-for="cat in daftarKategori" :key="cat.nama" @click="filterKategori(cat.nama)"
                        :class="{ 'border-orange-500 bg-orange-50': kategoriAktif === cat.nama }"
                        class="flex flex-col items-center p-4 border rounded-2xl hover:border-orange-500 transition-all group">

                        <div
                            class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3 group-hover:bg-white">
                            <i :class="cat.icon" class="text-2xl text-orange-500"></i>
                        </div>
                        <span class="font-bold text-gray-700">{{ cat.nama }}</span>
                    </button>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-bold mb-6">Donasi Yang Sedang Tren</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div v-for="prog in programs" :key="prog.id"
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group hover:shadow-md transition">

                        <img :src="`/storage/${prog.gambar}`"
                            class="w-full h-40 object-cover group-hover:scale-105 transition duration-500">

                        <div class="p-4">
                            <span class="text-[10px] font-bold text-orange-500 uppercase">{{ prog.kategori }}</span>
                            <h3 class="font-bold text-gray-800 text-sm line-clamp-2 h-10 mb-2">{{ prog.judul }}</h3>

                            <div class="space-y-1">
                                <div class="flex justify-between items-center text-[10px] font-bold text-gray-500 mb-1">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-orange-400"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="text-gray-700">{{ prog.donatur_count || 0 }} Donatur</span>
                                    </div>

                                    <div class="text-right flex flex-col items-end">
                                        <span class="text-orange-500">Rp {{ Number(prog.terkumpul ||
                                            0).toLocaleString('id-ID') }}</span>
                                        <span class="text-[8px] text-gray-400">
                                            {{ Math.round(((prog.terkumpul || 0) / (prog.target_dana || 1)) * 100) }}%
                                            target
                                        </span>
                                    </div>
                                </div>

                                <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden shadow-inner">
                                    <div class="bg-orange-500 h-full rounded-full transition-all duration-1000 ease-out"
                                        :style="{
                                            width: (prog.terkumpul > 0)
                                                ? `${Math.max(1, Math.min(((prog.terkumpul / prog.target_dana) * 100), 100))}%`
                                                : '0%'
                                        }">
                                    </div>
                                </div>
                            </div>

                            <Link :href="route('program.show', prog.slug)"
                                class="w-full mt-4 block text-center bg-orange-500 hover:bg-orange-600 text-white py-2 rounded-xl text-xs font-bold transition">
                                Donasi Sekarang
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>



<style scoped>
.swiper-pagination-bullet-active {
    background: #f97316 !important;
    width: 25px !important;
    border-radius: 5px !important;
}

@keyframes float {
    0% {
        transform: translateY(0px);
    }

    50% {
        transform: translateY(-20px);
    }

    100% {
        transform: translateY(0px);
    }
}

.animate-float {
    animation: float 5s ease-in-out infinite;
}
</style>
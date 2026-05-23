<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import DonaturLayout from '@/Layouts/DonaturLayout.vue';

// Kategori pilihan tetap dipertahankan karena digunakan di Section 3
const daftarKategori = ref([
    { nama: 'Qurban', icon: 'fa-solid fa-cow' },
    { nama: 'Zakat', icon: 'fa-solid fa-hand-holding-dollar' },
    { nama: 'Infaq', icon: 'fa-solid fa-heart' },
    { nama: 'Wakaf', icon: 'fa-solid fa-mosque' },
    { nama: 'Pilar', icon: 'fa-solid fa-star' },
]);

const props = defineProps({
    programs: Object,
    semuaProgramMobile: Array,
    kategoriAktif: String,
    totalMasuk: Number,          // Mengambil finansial global dari dashboard logic
    totalKeluar: Number,         // Mengambil finansial global dari dashboard logic
    totalDonaturGlobal: Number   // Diambil dari perhitungan ProgramController
});

const filterKategori = (namaKategori) => {
    router.get('/pilih-program', { kategori: namaKategori }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['programs', 'kategoriAktif']
    });
};

// let interval = null;

// onMounted(() => {
//     interval = setInterval(() => {
//         // Ambil URL lengkap saat ini (termasuk ?page=2, ?kategori=Zakat, dll)
//         const currentUrl = window.location.pathname + window.location.search;

//         router.reload({
//             url: currentUrl, // Mengunci agar reload tetap di halaman aktif saat ini
//             only: ['programs'],
//             preserveScroll: true,
//             preserveState: true
//         });
//     }, 5000);
// });

// onUnmounted(() => {
//     if (interval) clearInterval(interval);
// });

// 1. Tambahkan state untuk mendeteksi loading data
const sedangLoading = ref(false);
let unregisterStartHook = null;
let unregisterFinishHook = null;

onMounted(() => {
    // Deteksi saat Inertia mulai mengambil data baru
    unregisterStartHook = router.on('start', () => {
        sedangLoading.value = true;
    });
    // Deteksi saat Inertia selesai memuat data
    unregisterFinishHook = router.on('finish', () => {
        sedangLoading.value = false;
    });
});

// Bersihkan hook saat komponen hancur agar tidak memori bocor
onUnmounted(() => {
    if (unregisterStartHook) unregisterStartHook();
    if (unregisterFinishHook) unregisterFinishHook();
});

// Fungsi pembantu Format Rupiah anti-NaN
const formatRupiah = (number) => {
    const value = parseFloat(number) || 0;
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};

const showAllMobile = ref(false);

// Mengambil data array murni dari objek pagination Laravel
const programItems = computed(() => props.programs.data || []);

// Logika pemotongan data program khusus untuk tampilan layar HP/Mobile
const displayedMobilePrograms = computed(() => {
    if (showAllMobile.value) {
        return programItems.value;
    }
    return programItems.value.slice(0, 2); // Hanya tampilkan 2 item pertama di awal
});
</script>

<template>
    <DonaturLayout>
        <div class="max-w-7xl mx-auto px-4 pt-10 pb-12">
            <!-- Container Utama Pembuka -->
            <div class="text-center max-w-3xl mx-auto mb-12 space-y-4">
                <!-- Ucapan Salam Islami dengan Efek Denyut Halus -->
                <div
                    class="inline-flex items-center gap-2 bg-orange-50/80 backdrop-blur px-5 py-2 rounded-full border border-orange-100 shadow-sm">
                    <span class="flex h-2 w-2 relative">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                    </span>
                    <p class="text-xs md:text-sm font-semibold text-orange-600 tracking-wide">
                        Assalamu'alaikum Warahmatullahi Wabarakatuh
                    </p>
                </div>

                <!-- Judul Utama Yang Kuat -->
                <h1 class="text-3xl md:text-5xl font-black text-gray-800 tracking-tight leading-tight pt-2">
                    Pilih Program <span class="text-orange-500 relative inline-block">Kebaikanmu<span
                            class="absolute bottom-1 left-0 w-full h-2 bg-orange-100 rounded-full -z-10"></span></span>
                </h1>

                <!-- Kalimat Deskripsi / Ajakan yang Hangat -->
                <p class="text-gray-500 text-sm md:text-lg font-medium leading-relaxed max-w-2xl mx-auto">
                    Langkah kecil Anda hari ini adalah senyum kebahagiaan bagi mereka yang membutuhkan.
                    Mari salurkan kepedulian Anda melalui program terbaik bersama <span
                        class="font-bold text-gray-700">LAZISMU</span>.
                </p>

                <!-- Garis Pembatas Estetik Kecil di Tengah -->
                <div class="flex justify-center pt-2">
                    <div class="w-12 h-1 bg-gradient-to-r from-orange-300 to-orange-500 rounded-full"></div>
                </div>
            </div>

            <!-- Section 2: Tiga Kotak Ringkasan Statistik -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-16">
                <!-- Kotak 1: Total Terkumpul -->
                <div class="bg-white p-6 rounded-3xl border-2 border-gray-100 flex items-center gap-4 shadow-sm">
                    <div class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-hand-holding-dollar text-orange-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase">Total Terkumpul</p>
                        <p class="text-xl font-black text-gray-800">{{ formatRupiah(props.totalMasuk) }}</p>
                    </div>
                </div>

                <!-- Kotak 2: Tersalurkan -->
                <div class="bg-white p-6 rounded-3xl border-2 border-gray-100 flex items-center gap-4 shadow-sm">
                    <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-heart-circle-check text-green-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase">Tersalurkan</p>
                        <p class="text-xl font-black text-gray-800">{{ formatRupiah(props.totalKeluar) }}</p>
                    </div>
                </div>

                <!-- Kotak 3: Total Donatur Keseluruhan -->
                <div class="bg-white p-6 rounded-3xl border-2 border-gray-100 flex items-center gap-4 shadow-sm">
                    <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-users text-blue-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase">Total Donatur</p>
                        <p class="text-xl font-black text-gray-800">
                            {{ (props.totalDonaturGlobal || 0).toLocaleString('id-ID') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section 3: Kategori Pilihan -->
            <div class="mb-12">
                <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <span class="w-2 h-8 bg-orange-500 rounded-full"></span>
                    Kategori Pilihan
                </h2>

                <div class="flex overflow-x-auto pb-4 gap-4 snap-x no-scrollbar">
                    <button v-for="cat in daftarKategori" :key="cat.nama" @click="filterKategori(cat.nama)"
                        :class="kategoriAktif === cat.nama ? 'border-orange-500 bg-orange-500 text-white shadow-orange-200' : 'bg-white border-gray-100 text-gray-700'"
                        class="flex-shrink-0 snap-start flex items-center gap-3 px-6 py-4 border-2 rounded-2xl transition-all shadow-sm group">
                        <i :class="[cat.icon, kategoriAktif === cat.nama ? 'text-white' : 'text-orange-500']"
                            class="text-xl"></i>
                        <span class="font-bold whitespace-nowrap">{{ cat.nama }}</span>
                    </button>
                </div>
            </div>

            <!-- Section 4: Program Unggulan -->
            <div class="space-y-6">
                <div class="flex justify-between items-end px-1 md:px-0">
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Program Unggulan</h2>
                        <p class="text-gray-500 text-xs md:text-sm">Mari salurkan kepedulian Anda melalui program
                            terbaik kami</p>
                    </div>
                    <!-- Tombol Lihat Semua hanya muncul di Desktop karena Mobile sudah bisa di-swipe rapi -->
                    <!-- Sesudah -->
                    <Link :href="route('pilih.program')" :preserve-scroll="true"
                        class="text-orange-500 font-bold text-sm hover:underline hidden md:block">Lihat Semua</Link>
                </div>

                <!-- ================= TAMPILAN 1: GRID DESKTOP / LAPTOP (TETAP SAMA) ================= -->
                <div :class="{ 'opacity-40 pointer-events-none': sedangLoading }"
                    class="hidden md:grid grid-cols-2 lg:grid-cols-4 gap-6 transition-all duration-300">
                    <Link v-for="prog in programItems" :key="'desktop-' + prog.id"
                        :href="route('program.show', prog.slug)"
                        class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl hover:-translate-y-1 transition-all duration-300 block">

                        <div class="relative overflow-hidden">
                            <img :src="`/storage/${prog.gambar}`"
                                class="w-full h-48 object-cover group-hover:scale-110 transition duration-500">
                            <div
                                class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-[10px] font-bold text-orange-500 uppercase shadow-sm">
                                {{ prog.kategori }}
                            </div>
                        </div>

                        <div class="p-5">
                            <h3
                                class="font-bold text-gray-800 text-base line-clamp-2 h-12 mb-4 leading-snug group-hover:text-orange-500 transition-colors">
                                {{ prog.judul }}
                            </h3>

                            <div class="space-y-3">
                                <div class="flex justify-between items-end">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Terkumpul</span>
                                        <span class="text-orange-500 font-black text-sm">
                                            Rp {{ Number(prog.terkumpul || 0).toLocaleString('id-ID') }}
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span v-if="prog.target_dana && parseInt(prog.target_dana) > 0"
                                            class="text-gray-800 font-black text-sm">
                                            {{ Math.round(((prog.terkumpul || 0) / (prog.target_dana || 1)) * 100) }}%
                                        </span>
                                        <span v-else
                                            class="text-green-600 font-bold text-[10px] uppercase tracking-wider bg-green-50 px-2 py-0.5 rounded">
                                            Terbuka
                                        </span>
                                    </div>
                                </div>

                                <div v-if="prog.target_dana && parseInt(prog.target_dana) > 0"
                                    class="w-full bg-gray-100 h-2 rounded-full overflow-hidden mt-1">
                                    <div class="bg-orange-500 h-full rounded-full transition-all duration-1000"
                                        :style="{ width: `${Math.min(((prog.terkumpul || 0) / (prog.target_dana || 1) * 100), 100)}%` }">
                                    </div>
                                </div>

                                <div v-else class="w-full bg-orange-500 h-[3px] rounded-full mt-1"></div>

                                <div class="flex justify-between items-center pt-2">
                                    <span class="text-[10px] font-bold text-gray-500 flex items-center gap-1">
                                        <i class="fa-solid fa-user-group text-orange-400"></i>
                                        {{ prog.donatur_count || 0 }} Donatur
                                    </span>
                                    <div
                                        class="bg-orange-500 group-hover:bg-orange-600 text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md shadow-orange-100">
                                        Donasi
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- ================= TAMPILAN 2: MOBILE MODERN SLIDER (DIROMBAK TOTAL) ================= -->
                <!-- Menggunakan flex-row, overflow-x-auto, snap-x agar bisa digeser halus menggunakan jempol -->
                <div class="flex md:hidden overflow-x-auto gap-5 pb-5 pt-1 px-1 snap-x snap-mandatory no-scrollbar">

                    <!-- SEKARANG MENGGUNAKAN props.semuaProgramMobile SEHINGGA DATA MUNCUL SEMUANYA -->
                    <Link v-for="prog in props.semuaProgramMobile" :key="'mobile-slide-' + prog.id"
                        :href="route('program.show', prog.slug)"
                        class="w-[82vw] sm:w-[60vw] flex-shrink-0 snap-center bg-white rounded-[2rem] border border-gray-100 shadow-md active:scale-[0.97] transition-all duration-200 block overflow-hidden">

                        <!-- Area Gambar -->
                        <div class="relative overflow-hidden">
                            <img :src="`/storage/${prog.gambar}`" class="w-full h-40 object-cover">
                            <div
                                class="absolute top-3 left-3 bg-white/95 backdrop-blur px-2.5 py-0.5 rounded-md text-[9px] font-extrabold text-orange-500 uppercase tracking-wide shadow-sm">
                                {{ prog.kategori }}
                            </div>
                        </div>

                        <!-- Konten Card Informasi Program -->
                        <div class="p-4 space-y-4">
                            <h3 class="font-bold text-gray-800 text-sm line-clamp-2 h-10 leading-tight">
                                {{ prog.judul }}
                            </h3>

                            <div class="space-y-3">
                                <div
                                    :class="prog.target_dana && parseInt(prog.target_dana) > 0 ? 'flex justify-between items-end' : 'flex justify-between items-end'">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                                            {{ prog.target_dana && parseInt(prog.target_dana) > 0 ? 'Terkumpul' :
                                            'Donasi Terkumpul' }}
                                        </span>
                                        <span class="text-gray-900 font-black text-sm sm:text-base">
                                            Rp {{ Number(prog.terkumpul || 0).toLocaleString('id-ID') }}
                                        </span>
                                    </div>

                                    <div class="text-right">
                                        <span v-if="prog.target_dana && parseInt(prog.target_dana) > 0"
                                            class="text-gray-800 font-black text-xs sm:text-sm">
                                            {{ Math.round(((prog.terkumpul || 0) / (prog.target_dana || 1)) * 100) }}%
                                        </span>
                                        <div v-else class="flex flex-col items-end">
                                            <span
                                                class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Donatur</span>
                                            <span class="text-gray-500 font-black text-sm sm:text-base">
                                                {{ prog.donatur_count || 0 }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="prog.target_dana && parseInt(prog.target_dana) > 0"
                                    class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-orange-500 h-full rounded-full"
                                        :style="{ width: `${Math.min(((prog.terkumpul / prog.target_dana) * 100), 100)}%` }">
                                    </div>
                                </div>

                                <div v-else class="w-full bg-orange-500 h-[3px] rounded-full mt-1"></div>

                                <div class="flex justify-between items-center pt-1">
                                    <div>
                                        <span v-if="prog.target_dana && parseInt(prog.target_dana) > 0"
                                            class="text-[10px] font-bold text-gray-500 flex items-center gap-1">
                                            <i class="fa-solid fa-user-group text-orange-400"></i>
                                            {{ prog.donatur_count || 0 }} Donatur
                                        </span>
                                        <span v-else
                                            class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                            {{ prog.kategori || 'Program' }}
                                        </span>
                                    </div>

                                    <div
                                        class="bg-orange-500 text-white px-5 py-2 rounded-xl text-xs font-bold tracking-wide shadow-sm active:bg-orange-600 transition cursor-pointer select-none">
                                        Donasi
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <!-- CARD TAMBAHAN DI PALING KANAN -->
                    <Link :href="route('pilih.program')"
                        class="w-[40vw] flex-shrink-0 snap-center bg-orange-50/50 rounded-[2rem] border-2 border-dashed border-orange-200 flex flex-col items-center justify-center gap-2 p-4 text-center active:scale-[0.97] transition-all">
                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-arrow-right text-orange-500"></i>
                        </div>
                        <span class="text-xs font-black text-orange-600">Lihat Semua<br>Program</span>
                    </Link>
                </div>
            </div>

            <!-- NAVIGATION PAGINATION (HANYA DI LAPTOP) -->
            <div v-if="programs.links && programs.links.length > 3"
                class="hidden md:flex justify-center items-center gap-2 mt-8">
                <template v-for="(link, k) in programs.links" :key="k">
                    <div v-if="link.url === null"
                        class="px-4 py-2 text-sm text-gray-400 bg-gray-50 border border-gray-100 rounded-xl cursor-not-allowed"
                        v-html="link.label" />

                    <!-- Link aktif yang bisa diklik untuk berpindah page tanpa reload -->
                    <Link v-else :href="link.url" :preserve-scroll="true" :preserve-state="true"
                        class="px-4 py-2 text-sm font-bold border rounded-xl transition-all duration-200" :class="{
                            'bg-orange-500 text-white border-orange-500 shadow-md shadow-orange-100': link.active,
                            'bg-white text-gray-600 border-gray-200 hover:border-orange-500 hover:text-orange-500': !link.active
                        }" v-html="link.label" />
                </template>
            </div>
        </div>
    </DonaturLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
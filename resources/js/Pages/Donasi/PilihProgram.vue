<script setup>
import 'swiper/css';
import { ref } from 'vue';
import 'swiper/css/pagination';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, Pagination } from 'swiper/modules';
import DonaturLayout from '@/Layouts/DonaturLayout.vue';

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


<<template>
    <DonaturLayout>
        <!-- Main Container mengikuti layout Gambar: Hero Tengah -->
        <div class="max-w-7xl mx-auto px-4 pt-10 pb-12">

            <!-- Section 1: Hero Welcome (Sesuai Gambar) -->
            <div class="text-center space-y-4 mb-10">
                <p class="text-gray-500 font-medium italic text-sm md:text-base">
                    Assalamu'alaikum Warahmatullahi Wabarakatuh
                </p>
                <h1 class="text-3xl md:text-5xl font-black text-gray-800 leading-tight">
                    Selamat datang di Portal Donasi <span class="text-orange-500">LAZIS Indonesia</span>.
                </h1>
                <p class="text-gray-600 max-w-2xl mx-auto text-sm md:text-lg">
                    Salurkan zakat, infaq, dan sedekah Anda dengan mudah dan transparan.
                </p>

                <!-- Tombol Aksi Ganda (Sesuai Gambar) -->
                <div class="flex flex-col sm:flex-row justify-center gap-4 pt-4">
                    <button
                        class="bg-orange-500 text-white px-8 py-3 rounded-xl font-bold shadow-lg hover:bg-orange-600 transition">
                        Donasi Sekarang
                    </button>
                    <button
                        class="bg-white border-2 border-gray-800 text-gray-800 px-8 py-3 rounded-xl font-bold hover:bg-gray-50 transition">
                        Hitung Zakat
                    </button>
                </div>
            </div>

            <!-- Section 2: Ringkasan Statistik (Sesuai Gambar: 3 Kotak) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-16">
                <div class="bg-white p-6 rounded-3xl border-2 border-gray-100 flex items-center gap-4 shadow-sm">
                    <div class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-hand-holding-dollar text-orange-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase">Total Terkumpul</p>
                        <p class="text-xl font-black text-gray-800">Rp 99.999.999</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl border-2 border-gray-100 flex items-center gap-4 shadow-sm">
                    <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-heart-circle-check text-green-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase">Tersalurkan</p>
                        <p class="text-xl font-black text-gray-800">Rp 99.999.999</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl border-2 border-gray-100 flex items-center gap-4 shadow-sm">
                    <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-users text-blue-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase">Total Donatur</p>
                        <p class="text-xl font-black text-gray-800">9.999</p>
                    </div>
                </div>
            </div>

            <!-- Section 3: Kategori Pilihan (Dibuat Horizontal Scroll untuk Mobile) -->
            <div class="mb-12">
                <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <span class="w-2 h-8 bg-orange-500 rounded-full"></span>
                    Kategori Pilihan
                </h2>

                <!-- Container Scroll -->
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

            <!-- Section 4: Program Unggulan (Sesuai Program Yang Sedang Tren) -->
            <div class="space-y-6">
                <div class="flex justify-between items-end">
                    <div>
                        <h2 class="text-2xl font-black text-gray-800">Program Unggulan</h2>
                        <p class="text-gray-500 text-sm">Ini ada beberapa program yang sedang berjalan di lembaga kami
                        </p>
                    </div>
                    <Link href="#" class="text-orange-500 font-bold text-sm hover:underline hidden md:block">Lihat Semua
                    </Link>
                </div>

                <!-- Layout Grid Program (Tetap 4 kolom di desktop, 1 di mobile) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="prog in programs" :key="prog.id"
                        class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl transition-all duration-300">

                        <div class="relative overflow-hidden">
                            <img :src="`/storage/${prog.gambar}`"
                                class="w-full h-48 object-cover group-hover:scale-110 transition duration-500">
                            <div
                                class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-[10px] font-bold text-orange-500 uppercase shadow-sm">
                                {{ prog.kategori }}
                            </div>
                        </div>

                        <div class="p-5">
                            <h3 class="font-bold text-gray-800 text-base line-clamp-2 h-12 mb-4 leading-snug">{{
                                prog.judul }}</h3>

                            <!-- Progress Bar Section -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-end">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Terkumpul</span>
                                        <span class="text-orange-500 font-black text-sm">Rp {{ Number(prog.terkumpul ||
                                            0).toLocaleString('id-ID') }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-gray-800 font-black text-sm">{{ Math.round(((prog.terkumpul ||
                                            0) / (prog.target_dana || 1)) * 100) }}%</span>
                                    </div>
                                </div>

                                <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-orange-500 h-full rounded-full transition-all duration-1000"
                                        :style="{ width: `${Math.min(((prog.terkumpul / prog.target_dana) * 100), 100)}%` }">
                                    </div>
                                </div>

                                <div class="flex justify-between items-center pt-2">
                                    <span class="text-[10px] font-bold text-gray-500 flex items-center gap-1">
                                        <i class="fa-solid fa-user-group text-orange-400"></i>
                                        {{ prog.donatur_count || 0 }} Donatur
                                    </span>
                                    <Link :href="route('program.show', prog.slug)"
                                        class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md shadow-orange-100">
                                        Donasi
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Lihat Semua di Mobile -->
                <button
                    class="w-full md:hidden py-4 border-2 border-gray-100 rounded-2xl text-gray-500 font-bold text-sm">
                    Lihat Semua Program
                </button>
            </div>
        </div>
    </DonaturLayout>
</template>

    <style scoped>

    /* Menghilangkan scrollbar tapi tetap bisa di-scroll */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
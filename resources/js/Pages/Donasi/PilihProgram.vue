<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import DonaturLayout from '@/Layouts/DonaturLayout.vue';

// FIX TAMPILAN KATEGORI: Ditambahkan properti 'label' untuk tulisan di layar tanpa underscore
const daftarKategori = ref([
    { nama: 'Qurban', label: 'Qurban', icon: 'fa-solid fa-cow' },
    { nama: 'Zakat', label: 'Zakat', icon: 'fa-solid fa-hand-holding-dollar' },
    { nama: 'Infaq_sodaqoh', label: 'Infaq Sodaqoh', icon: 'fa-solid fa-heart' }, // Tetap kirim 'Infaq_sodaqoh' ke DB, tapi tampil 'Infaq Sodaqoh'
    { nama: 'Wakaf', label: 'Wakaf', icon: 'fa-solid fa-mosque' },
    { nama: 'Pilar', label: 'Pilar', icon: 'fa-solid fa-star' },
]);

const props = defineProps({
    programs: Object,
    kategoriAktif: String,
    totalMasuk: Number,         
    totalKeluar: Number,         
    totalDonaturGlobal: Number   
});

const filterKategori = (namaKategori) => {
    // Logika toggle filter
    const kategoriKirim = props.kategoriAktif === namaKategori ? '' : namaKategori;

    router.get('/pilih-program', { kategori: kategoriKirim }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['programs', 'kategoriAktif']
    });
};

// State Loading
const sedangLoading = ref(false);
let unregisterStartHook = null;
let unregisterFinishHook = null;

onMounted(() => {
    unregisterStartHook = router.on('start', () => {
        sedangLoading.value = true;
    });
    unregisterFinishHook = router.on('finish', () => {
        sedangLoading.value = false;
    });
});

onUnmounted(() => {
    if (unregisterStartHook) unregisterStartHook();
    if (unregisterFinishHook) unregisterFinishHook();
});

// Format Rupiah
const formatRupiah = (number) => {
    const value = parseFloat(number) || 0;
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};

// Mengambil data array program hasil filter
const programItems = computed(() => props.programs.data || []);

// Fungsi helper untuk menghilangkan underscore pada teks kategori dari database
const formatNamaKategori = (text) => {
    if (!text) return '';
    // Mengubah 'Infaq_sodaqoh' menjadi 'Infaq sodaqoh' lalu diubah huruf kapital awalnya
    return text.replace(/_/g, ' ');
};
</script>

<template>
    <DonaturLayout>
        <div class="max-w-7xl mx-auto px-4 pt-10 pb-12">
            
            <div class="text-center max-w-3xl mx-auto mb-12 space-y-4">
                <div class="inline-flex items-center gap-2 bg-orange-50/80 backdrop-blur px-5 py-2 rounded-full border border-orange-100 shadow-sm">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                    </span>
                    <p class="text-xs md:text-sm font-semibold text-orange-600 tracking-wide">
                        Assalamu'alaikum Warahmatullahi Wabarakatuh
                    </p>
                </div>

                <h1 class="text-3xl md:text-5xl font-black text-gray-800 tracking-tight leading-tight pt-2">
                    Pilih Program <span class="text-orange-500 relative inline-block">Kebaikanmu<span class="absolute bottom-1 left-0 w-full h-2 bg-orange-100 rounded-full -z-10"></span></span>
                </h1>
                <p class="text-gray-500 text-sm md:text-lg font-medium leading-relaxed max-w-2xl mx-auto">
                    Langkah kecil Anda hari ini adalah senyum kebahagiaan bagi mereka yang membutuhkan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-16">
                <div class="bg-white p-6 rounded-3xl border-2 border-gray-100 flex items-center gap-4 shadow-sm">
                    <div class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-hand-holding-dollar text-orange-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase">Total Terkumpul</p>
                        <p class="text-xl font-black text-gray-800">{{ formatRupiah(props.totalMasuk) }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border-2 border-gray-100 flex items-center gap-4 shadow-sm">
                    <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-heart-circle-check text-green-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase">Tersalurkan</p>
                        <p class="text-xl font-black text-gray-800">{{ formatRupiah(props.totalKeluar) }}</p>
                    </div>
                </div>

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

            <div class="mb-12">
                <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <span class="w-2 h-8 bg-orange-500 rounded-full"></span>
                    Kategori Pilihan
                </h2>

                <div class="flex overflow-x-auto pb-4 gap-4 snap-x no-scrollbar">
                    <button v-for="cat in daftarKategori" :key="cat.nama" @click="filterKategori(cat.nama)"
                        :class="kategoriAktif === cat.nama ? 'border-orange-500 bg-orange-500 text-white shadow-orange-200' : 'bg-white border-gray-100 text-gray-700'"
                        class="flex-shrink-0 snap-start flex items-center gap-3 px-6 py-4 border-2 rounded-2xl transition-all shadow-sm group">
                        <i :class="[cat.icon, kategoriAktif === cat.nama ? 'text-white' : 'text-orange-500']" class="text-xl"></i>
                        <span class="font-bold whitespace-nowrap capitalize">{{ cat.label }}</span>
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <div class="flex justify-between items-end px-1 md:px-0">
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight">
                            {{ kategoriAktif ? `Program ${formatNamaKategori(kategoriAktif)}` : 'Program Unggulan' }}
                        </h2>
                        <p class="text-gray-500 text-xs md:text-sm">Mari salurkan kepedulian Anda melalui program terbaik kami</p>
                    </div>
                    <Link :href="route('pilih.program')" :preserve-scroll="true" class="text-orange-500 font-bold text-sm hover:underline hidden md:block">Lihat Semua</Link>
                </div>

                <div v-if="programItems.length === 0" class="text-center py-16 bg-gray-50 rounded-[2rem] border border-dashed border-gray-200">
                    <p class="text-gray-400 font-medium text-sm">Belum ada program untuk kategori "{{ formatNamaKategori(kategoriAktif) }}" saat ini.</p>
                </div>

                <div :class="{ 'opacity-40 pointer-events-none': sedangLoading }" class="hidden md:grid grid-cols-2 lg:grid-cols-4 gap-6 transition-all duration-300">
                    <Link v-for="prog in programItems" :key="'desktop-' + prog.id" :href="route('program.show', prog.slug)"
                        class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl hover:-translate-y-1 transition-all duration-300 block">
                        <div class="relative overflow-hidden">
                            <img :src="`/storage/${prog.gambar}`" class="w-full h-48 object-cover group-hover:scale-110 transition duration-500">
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-[10px] font-bold text-orange-500 uppercase shadow-sm">
                                {{ formatNamaKategori(prog.kategori) }}
                            </div>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-gray-800 text-base line-clamp-2 h-12 mb-4 leading-snug group-hover:text-orange-500 transition-colors">
                                {{ prog.judul }}
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-end">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Terkumpul</span>
                                        <span class="text-orange-500 font-black text-sm">
                                            {{ formatRupiah(prog.terkumpul) }}
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span v-if="prog.target_dana && parseInt(prog.target_dana) > 0" class="text-gray-800 font-black text-sm">
                                            {{ Math.round(((prog.terkumpul || 0) / (prog.target_dana || 1)) * 100) }}%
                                        </span>
                                        <span v-else class="text-green-600 font-bold text-[10px] uppercase tracking-wider bg-green-50 px-2 py-0.5 rounded">
                                            Terbuka
                                        </span>
                                    </div>
                                </div>
                                <div v-if="prog.target_dana && parseInt(prog.target_dana) > 0" class="w-full bg-gray-100 h-2 rounded-full overflow-hidden mt-1">
                                    <div class="bg-orange-500 h-full rounded-full transition-all duration-1000" :style="{ width: `${Math.min(((prog.terkumpul || 0) / (prog.target_dana || 1) * 100), 100)}%` }"></div>
                                </div>
                                <div v-else class="w-full bg-orange-500 h-[3px] rounded-full mt-1"></div>
                                <div class="flex justify-between items-center pt-2">
                                    <span class="text-[10px] font-bold text-gray-500 flex items-center gap-1">
                                        <i class="fa-solid fa-user-group text-orange-400"></i>
                                        {{ prog.donatur_count || 0 }} Donatur
                                    </span>
                                    <div class="bg-orange-500 group-hover:bg-orange-600 text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md shadow-orange-100">
                                        Donasi
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <div :class="{ 'opacity-40 pointer-events-none': sedangLoading }" class="flex md:hidden overflow-x-auto gap-5 pb-5 pt-1 px-1 snap-x snap-mandatory no-scrollbar transition-all duration-300">
                    <Link v-for="prog in programItems" :key="'mobile-slide-' + prog.id" :href="route('program.show', prog.slug)"
                        class="w-[82vw] sm:w-[60vw] flex-shrink-0 snap-center bg-white rounded-[2rem] border border-gray-100 shadow-md active:scale-[0.97] transition-all duration-200 block overflow-hidden">
                        <div class="relative overflow-hidden">
                            <img :src="`/storage/${prog.gambar}`" class="w-full h-40 object-cover">
                            <div class="absolute top-3 left-3 bg-white/95 backdrop-blur px-2.5 py-0.5 rounded-md text-[9px] font-extrabold text-orange-500 uppercase tracking-wide shadow-sm">
                                {{ formatNamaKategori(prog.kategori) }}
                            </div>
                        </div>
                        <div class="p-4 space-y-4">
                            <h3 class="font-bold text-gray-800 text-sm line-clamp-2 h-10 leading-tight">
                                {{ prog.judul }}
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-end">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                                            {{ prog.target_dana && parseInt(prog.target_dana) > 0 ? 'Terkumpul' : 'Donasi Terkumpul' }}
                                        </span>
                                        <span class="text-gray-900 font-black text-sm sm:text-base">
                                            {{ formatRupiah(prog.terkumpul) }}
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span v-if="prog.target_dana && parseInt(prog.target_dana) > 0" class="text-gray-800 font-black text-xs sm:text-sm">
                                            {{ Math.round(((prog.terkumpul || 0) / (prog.target_dana || 1)) * 100) }}%
                                        </span>
                                        <div v-else class="flex flex-col items-end">
                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Donatur</span>
                                            <span class="text-gray-500 font-black text-sm sm:text-base">{{ prog.donatur_count || 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="prog.target_dana && parseInt(prog.target_dana) > 0" class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-orange-500 h-full rounded-full" :style="{ width: `${Math.min(((prog.terkumpul / prog.target_dana) * 100), 100)}%` }"></div>
                                </div>
                                <div v-else class="w-full bg-orange-500 h-[3px] rounded-full mt-1"></div>
                                <div class="flex justify-between items-center pt-1">
                                    <div>
                                        <span v-if="prog.target_dana && parseInt(prog.target_dana) > 0" class="text-[10px] font-bold text-gray-500 flex items-center gap-1">
                                            <i class="fa-solid fa-user-group text-orange-400"></i>
                                            {{ prog.donatur_count || 0 }} Donatur
                                        </span>
                                        <span v-else class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                            {{ formatNamaKategori(prog.kategori) }}
                                        </span>
                                    </div>
                                    <div class="bg-orange-500 text-white px-5 py-2 rounded-xl text-xs font-bold tracking-wide shadow-sm active:bg-orange-600 transition cursor-pointer select-none">
                                        Donasi
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <Link :href="route('pilih.program')" class="w-[40vw] flex-shrink-0 snap-center bg-orange-50/50 rounded-[2rem] border-2 border-dashed border-orange-200 flex flex-col items-center justify-center gap-2 p-4 text-center active:scale-[0.97] transition-all">
                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-arrow-right text-orange-500"></i>
                        </div>
                        <span class="text-xs font-black text-orange-600">Lihat Semua<br>Program</span>
                    </Link>
                </div>
            </div>

            <div v-if="programs.links && programs.links.length > 3" class="hidden md:flex justify-center items-center gap-2 mt-8">
                <template v-for="(link, k) in programs.links" :key="k">
                    <div v-if="link.url === null" class="px-4 py-2 text-sm text-gray-400 bg-gray-50 border border-gray-100 rounded-xl cursor-not-allowed" v-html="link.label" />
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
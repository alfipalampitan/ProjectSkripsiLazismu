<script setup>
import DonaturLayout from '@/Layouts/DonaturLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Array,            // Data summary dari Controller
    totalSemuaSaldo: Number, // Ini "Saldo Bersih" dari Controller
    latestDonations: Array
});

// Tambahkan pengecekan array di dalam computed
const totalMasuk = computed(() => {
    // Jika props.stats ada dan merupakan array, jalankan reduce. Jika tidak, balikin 0.
    return Array.isArray(props.stats) 
        ? props.stats.reduce((acc, item) => acc + (Number(item.total_masuk) || Number(item.amount) || 0), 0) 
        : 0;
});

const totalKeluar = computed(() => {
    return Array.isArray(props.stats) 
        ? props.stats.reduce((acc, item) => acc + (Number(item.total_keluar) || 0), 0) 
        : 0;
});

const formatIDR = (val) => {
    return new Intl.NumberFormat('id-ID').format(val);
};
</script>

<template>
    <Head title="Transparansi Dana" />

    <DonaturLayout>
        <div class="space-y-8 p-6">
            <!-- Judul Halaman -->
            <div>
                <h1 class="text-3xl font-black text-gray-800">Laporan Transparansi</h1>
                <p class="text-gray-500 mt-1">Audit saldo masuk dan penyaluran dana donatur</p>
            </div>

            <!-- TIGA KOTAK UTAMA (SESUAI REQUEST) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- KOTAK PEMASUKAN (HIJAU) -->
                <div class="bg-white p-8 rounded-[2.5rem] border-b-8 border-green-500 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Dana Masuk</p>
                            <h2 class="text-3xl font-black text-green-600 mt-1">Rp {{ formatIDR(totalMasuk) }}</h2>
                        </div>
                        <div class="w-12 h-12 bg-green-50 text-green-500 rounded-2xl flex items-center justify-center">
                            <i class="fa-solid fa-arrow-down-long"></i>
                        </div>
                    </div>
                </div>

                <!-- KOTAK PENGELUARAN (MERAH) -->
                <div class="bg-white p-8 rounded-[2.5rem] border-b-8 border-red-500 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Penyaluran</p>
                            <h2 class="text-3xl font-black text-red-600 mt-1">Rp {{ formatIDR(totalKeluar) }}</h2>
                        </div>
                        <div class="w-12 h-12 bg-red-50 text-red-500 rounded-2xl flex items-center justify-center">
                            <i class="fa-solid fa-arrow-up-long"></i>
                        </div>
                    </div>
                </div>

                <!-- KOTAK SALDO BERSIH (ORANGE/GOLD) -->
                <div class="bg-orange-500 p-8 rounded-[2.5rem] shadow-xl shadow-orange-100 text-white overflow-hidden relative">
                    <div class="relative z-10">
                        <p class="text-[10px] font-black text-orange-100 uppercase tracking-widest">Saldo Bersih Saat Ini</p>
                        <h2 class="text-3xl font-black mt-1">Rp {{ formatIDR(totalSemuaSaldo) }}</h2>
                    </div>
                    <!-- Icon dekorasi di background -->
                    <i class="fa-solid fa-scale-balanced text-7xl absolute -right-4 -bottom-4 opacity-20"></i>
                </div>
            </div>

            <!-- DETAIL PER KATEGORI -->
            <div class="bg-white rounded-[3rem] p-8 border border-gray-100 shadow-sm">
                <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest mb-6">Detail Saldo per Program</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div v-for="item in stats" :key="item.name" class="p-4 rounded-3xl bg-gray-50 border border-gray-100 text-center">
                        <div class="text-orange-500 mb-2 text-lg">
                            <i :class="['fa-solid', item.icon]"></i>
                        </div>
                        <p class="text-[9px] font-bold text-gray-400 uppercase">{{ item.name }}</p>
                        <p class="text-sm font-black text-gray-800">Rp {{ formatIDR(item.amount) }}</p>
                    </div>
                </div>
            </div>

            <!-- TABEL TRANSAKSI (Sama seperti sebelumnya) -->
            <!-- ... bagian tabel ... -->
        </div>
    </DonaturLayout>
</template>
<!-- Resources/js/Pages/Admin/Laporan.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router} from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    rekapBulanan: Array,
    rekapKategori: Array,
    filters: Object // Pastikan ini ada untuk menerima status 'type' dari controller
});

// Fungsi inilah yang memicu perubahan data dari database
const updateFilter = (newType) => {
    router.get(route('laporan.index'), 
        { type: newType }, 
        { preserveState: true, replace: true }
    );
};

// Fungsi Format Rupiah
const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-6 px-4 md:px-8">

            <!-- Header dengan Filter -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-800">Laporan Keuangan</h1>
                    <p class="text-sm text-gray-500 font-medium">Ringkasan donasi masuk berdasarkan sistem.</p>
                </div>

                <!-- Filter Type Buttons -->
                <div class="flex bg-gray-100 p-1 rounded-2xl w-fit">
                    <button @click="updateFilter('all')"
                        :class="[filters.type === 'all' ? 'bg-white shadow-sm text-orange-600' : 'text-gray-500']"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase transition-all">
                        Semua
                    </button>
                    <button @click="updateFilter('cash')"
                        :class="[filters.type === 'cash' ? 'bg-white shadow-sm text-orange-600' : 'text-gray-500']"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase transition-all">
                        Cash
                    </button>
                    <button @click="updateFilter('transfer')"
                        :class="[filters.type === 'transfer' ? 'bg-white shadow-sm text-orange-600' : 'text-gray-500']"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase transition-all">
                        Transfer
                    </button>
                </div>
            </div>

            <!-- Section 1: Ringkasan Kategori (Gunakan data dari props langsung) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 mb-8">
                <div v-for="kat in rekapKategori" :key="kat.kategori"
                    class="bg-white p-5 md:p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-2">
                        <p class="text-gray-400 text-[10px] md:text-xs uppercase tracking-tighter font-black">{{
                            kat.kategori }}</p>
                        <i class="fa-solid fa-wallet text-gray-100 text-xl"></i>
                    </div>
                    <h3 class="text-xl md:text-2xl font-black text-gray-800">{{ formatRupiah(kat.total) }}</h3>
                </div>
            </div>

            <!-- Section 2: Tabel Rekap (Gunakan filteredRekapBulanan) -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                    <h2 class="font-bold text-gray-800">Rekapitulasi Per Bulan</h2>
                    <span
                        class="text-[10px] font-black px-3 py-1 bg-orange-50 text-orange-500 rounded-full uppercase tracking-widest">
                        Mode: {{ filters.type }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left min-w-[600px]">
                        <thead class="bg-gray-50 text-gray-400 uppercase text-[10px] font-black">
                            <tr>
                                <th class="p-5 tracking-widest">Bulan</th>
                                <th class="p-5 tracking-widest text-center">Jumlah Transaksi</th>
                                <th class="p-5 tracking-widest">Total Dana Masuk</th>
                                <th class="p-5 tracking-widest text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <!-- Ganti loop menjadi rekapBulanan karena filter diproses di Controller -->
                            <tr v-for="(lap, index) in rekapBulanan" :key="index"
                                class="hover:bg-gray-50/50 transition">
                                <td class="p-5">
                                    <div class="font-bold text-gray-700">{{ lap.bulan }}</div>

                                    <!-- JIKA MODE CASH/TRANSFER: Tampilkan detail tipenya -->
                                    <div v-if="filters.type !== 'all'">
                                        <span v-if="lap.type === 'cash'"
                                            class="text-[10px] font-bold text-orange-500 uppercase tracking-tighter">
                                            <i class="fa-solid fa-money-bill-wave mr-1"></i> Tunai
                                        </span>
                                        <span v-else
                                            class="text-[10px] font-bold text-blue-500 uppercase tracking-tighter">
                                            <i class="fa-solid fa-university mr-1"></i> Transfer
                                        </span>
                                    </div>

                                    <!-- JIKA MODE SEMUA: Tampilkan label gabungan -->
                                    <div v-else>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                            <i class="fa-solid fa-layer-group mr-1"></i> Gabungan
                                        </span>
                                    </div>
                                </td>

                                <td class="p-5 text-center">
                                    <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-xl text-xs font-black">
                                        {{ lap.total_donatur }} Donasi
                                    </span>
                                </td>

                                <td class="p-5">
                                    <div class="text-green-600 font-black">{{ formatRupiah(lap.total_dana) }}</div>
                                </td>

                                <td class="p-5 text-right">
                                    <!-- URL Download menggunakan filters.type agar PDF sinkron dengan filter saat ini -->
                                    <a :href="route('laporan.download', { bulan: lap.bulan, type: filters.type })"
                                        target="_blank"
                                        class="inline-flex items-center bg-gray-900 text-white hover:bg-orange-500 px-5 py-2.5 rounded-2xl text-xs font-bold transition-all">
                                        <i class="fa-solid fa-file-pdf mr-2"></i> PDF
                                    </a>
                                </td>
                            </tr>

                            <!-- State jika data kosong -->
                            <tr v-if="rekapBulanan.length === 0">
                                <td colspan="4" class="p-20 text-center text-gray-400 font-medium italic">
                                    Tidak ada data donasi untuk kategori ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
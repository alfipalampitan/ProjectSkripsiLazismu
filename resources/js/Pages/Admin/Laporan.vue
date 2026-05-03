<!-- Resources/js/Pages/Admin/Laporan.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    rekapBulanan: Array,
    rekapKategori: Array
});

// Fungsi Format Rupiah
const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
};
</script>

<template>
    <AuthenticatedLayout>
        <!-- Responsiveness: px-4 di mobile, px-8 di desktop. py-6 agar tidak terlalu jauh dari atas -->
        <div class="py-6 px-4 md:px-8">
            
            <!-- Header: Responsive alignment -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                <h1 class="text-xl md:text-2xl font-bold text-gray-800">Laporan Keuangan & Donatur</h1>
                <!-- Bisa tambah tombol filter di sini nanti -->
            </div>

            <!-- Section 1: Ringkasan Kategori -->
            <!-- grid-cols-1 di HP, grid-cols-3 di Desktop -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 mb-8">
                <div v-for="kat in rekapKategori" :key="kat.kategori"
                    class="bg-white p-5 md:p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <p class="text-gray-500 text-xs md:text-sm uppercase tracking-wider font-semibold mb-1">{{ kat.kategori }}</p>
                    <h3 class="text-xl md:text-2xl font-black text-orange-500">{{ formatRupiah(kat.total) }}</h3>
                </div>
            </div>

            <!-- Section 2: Tabel Rekap Bulanan Otomatis -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100">
                <div class="p-6 border-b border-gray-50">
                    <h2 class="font-bold text-gray-800">Rekapitulasi Per Bulan</h2>
                </div>

                <!-- Wrapper untuk Responsive Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left min-w-[600px]"> <!-- min-w memastikan tabel tidak gepeng di HP -->
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs font-bold">
                            <tr>
                                <th class="p-4 tracking-wider">Bulan</th>
                                <th class="p-4 tracking-wider text-center">Jumlah Transaksi</th>
                                <th class="p-4 tracking-wider">Total Dana Masuk</th>
                                <th class="p-4 tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-sm md:text-base">
                            <tr v-for="lap in rekapBulanan" :key="lap.bulan" class="hover:bg-gray-50 transition">
                                <td class="p-4">
                                    <div class="font-bold text-gray-700">{{ lap.bulan }}</div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs font-bold">
                                        {{ lap.total_donatur }} Donasi
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="text-green-600 font-black">{{ formatRupiah(lap.total_dana) }}</div>
                                </td>
                                <td class="p-4 text-right">
                                    <a :href="route('laporan.download', lap.bulan)" target="_blank"
                                        class="inline-flex items-center bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white px-4 py-2 rounded-xl text-xs font-bold transition-all">
                                        <i class="fa-solid fa-file-pdf mr-2"></i> PDF
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Keterangan jika di HP (opsional) -->
                <div class="md:hidden p-4 text-center border-t border-gray-50">
                    <p class="text-xs text-gray-400 italic font-medium">
                        <i class="fa-solid fa-arrows-left-right mr-1"></i> Geser tabel ke samping untuk melihat detail
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
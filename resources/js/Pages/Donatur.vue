<script setup>
import { Head, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';

// Mengambil data 'donatur' yang dikirim dari DonaturController
defineProps({
    donatur: Array
});

// --- FITUR AUTO-REFRESH ---
let interval = null;

onMounted(() => {
    // Cek data ke server setiap 5 detik (5000 ms)
    interval = setInterval(() => {
        router.reload({ 
            only: ['donatur'], // Hanya ambil data donatur, jangan refresh seluruh halaman
            preserveScroll: true // Biar posisi scroll user nggak loncat
        });
    }, 5000);
});

onUnmounted(() => {
    // Hentikan pengecekan kalau user pindah halaman biar nggak berat
    clearInterval(interval);
});

// Fungsi untuk format rupiah
const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
};

// Fungsi format tanggal
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};



</script>

<template>
    <Head title="Daftar Donatur" />

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Daftar Donatur LAZISMU</h2>
                    <p class="text-gray-600">Laporan rincian donasi masuk secara real-time.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Lengkap</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Kontak & Email</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Jenis</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nominal</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Keterangan</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in donatur" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ item.nama || item.user_name }}
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <div class="flex flex-col">
                                        <span class="font-semibold">{{ item.nomor_hp || '-' }}</span>
                                        <span class="text-xs text-gray-400">{{ item.email || '-' }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 rounded-md text-xs font-bold bg-orange-100 text-orange-700 uppercase">
                                        {{ item.jenis || 'Donasi' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-bold">
                                    {{ formatRupiah(item.amount) }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    {{ item.keterangan || '-' }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                    {{ formatDate(item.updated_at) }}
                                </td>
                            </tr>

                            <tr v-if="donatur.length === 0">
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">
                                    Belum ada donasi yang berhasil diselesaikan hari ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
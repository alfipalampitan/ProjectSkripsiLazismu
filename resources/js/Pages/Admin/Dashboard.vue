<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// 1. Sesuaikan Props dengan keys yang dikirim dari Controller
const props = defineProps({
    totalMasuk: Number,
    totalKeluar: Number,
    totalSemuaSaldo: Number,
    stats_kategori: Array,        // Data saldo per kategori
    pemasukan_program: Array,
    pengeluaran_pilar: Array,
    latestDonations: Array
});

// 2. Fungsi Format Rupiah
const formatRupiah = (number) => {
    const value = parseFloat(number) || 0;
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};
</script>

<template>

    <Head title="Dashboard Admin" />
    <AuthenticatedLayout>
        <div class="bg-gray-50 min-h-screen py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="stat in stats_kategori" :key="stat.name"
                        class="bg-white border-b-4 border-orange-500 rounded-xl shadow-sm p-6 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase">SALDO {{ stat.name }}</p>
                            <p class="text-2xl font-bold text-gray-800">{{ formatRupiah(stat.amount) }}</p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full text-orange-600">
                            <i :class="['fa-solid', stat.icon, 'text-2xl']"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md overflow-hidden border-2 border-orange-500">
                    <div class="bg-orange-500 px-6 py-4 flex justify-between items-center">
                        <h3 class="text-white font-bold text-lg">
                            <i class="fa-solid fa-wallet mr-2"></i> TOTAL KESELURUHAN SALDO LAZISMU
                        </h3>
                        <span class="text-white text-2xl font-black">{{ formatRupiah(totalSemuaSaldo) }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                </div>

                <div class="bg-white rounded-xl shadow-md overflow-hidden border-2 border-orange-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white border-b-4 border-red-500 rounded-xl shadow-sm p-6">
                        <p class="text-sm font-medium text-gray-500 uppercase">Total Pengeluaran</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">{{ formatRupiah(totalKeluar) }}</p>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h3 class="text-gray-800 font-bold">Pengeluaran per Pilar</h3>
                        </div>
                        <div class="max-h-40 overflow-y-auto">
                            <table class="w-full text-sm">
                                <tr v-for="pilar in pengeluaran_pilar" :key="pilar.pilar_terkait">
                                    <td class="px-6 py-2 text-gray-600">{{ pilar.pilar_terkait }}</td>
                                    <td class="px-6 py-2 text-right font-semibold">{{ formatRupiah(pilar.total) }}</td>
                                </tr>
                                <tr v-if="pengeluaran_pilar.length === 0">
                                    <td class="px-6 py-4 text-center text-gray-400">Belum ada data pengeluaran.</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                </div>

                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-gray-800 font-bold flex items-center">
                            <i class="fa-solid fa-clock-rotate-left text-orange-500 mr-2"></i> Transaksi Terakhir
                        </h3>
                        <Link :href="route('transaksi')" class="text-sm text-orange-600 font-semibold hover:underline">
                            Lihat Semua
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-sm uppercase">
                                    <th class="px-6 py-3 font-semibold">Nama</th>
                                    <th class="px-6 py-3 font-semibold">Kategori</th>
                                    <th class="px-6 py-3 font-semibold text-right">Jumlah</th>
                                    <th class="px-6 py-3 font-semibold text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="donatur in latestDonations" :key="donatur.id"
                                    class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-semibold">{{ donatur.user_name }}</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-xs font-medium">
                                            {{ donatur.kategori }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-green-600">{{
                                        formatRupiah(donatur.amount) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-green-500 text-xs font-bold uppercase italic">{{
                                            donatur.status }}</span>
                                    </td>
                                </tr>
                                <tr v-if="latestDonations?.length === 0">
                                    <td colspan="4" class="text-center py-10 text-gray-400">Belum ada transaksi masuk.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import StaffLayout from '@/Layouts/StaffLayout.vue';
import { Head } from '@inertiajs/vue3';

// Data Dummy (Nanti bisa diganti dengan data dari props/backend)
const stats = [
    { name: 'Zakat', amount: 'Rp. 12.500.000', icon: 'fa-hand-holding-heart' },
    { name: 'Infaq', amount: 'Rp. 8.200.000', icon: 'fa-hand-holding-dollar' },
    { name: 'Sodaqoh', amount: 'Rp. 5.750.000', icon: 'fa-heart' },
];

const bankDetails = [
    { bank: 'BSI', zakat: 'Rp. 5.000.000', infaq: 'Rp. 3.000.000', sodaqoh: 'Rp. 2.000.000' },
    { bank: 'Bank Muamalat', zakat: 'Rp. 4.000.000', infaq: 'Rp. 2.500.000', sodaqoh: 'Rp. 1.500.000' },
    { bank: 'Mandiri Syariah', zakat: 'Rp. 3.500.000', infaq: 'Rp. 2.700.000', sodaqoh: 'Rp. 2.250.000' },
];

const donaturDetails = [
    { nama: 'Hamba Allah', bank: 'BSI', jumlah: 'Rp. 1.000.000', keterangan: 'Zakat Mal' },
    { nama: 'Ahmad S.', bank: 'Mandiri', jumlah: 'Rp. 500.000', keterangan: 'Infaq Rutin' },
    { nama: 'Siti Aminah', bank: 'BCA', jumlah: 'Rp. 250.000', keterangan: 'Sodaqoh' },
];
</script>

<template>
    <Head title="Staff Dashboard" />

    <StaffLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-orange-600">
                Staff Panel <span class="text-gray-400 mx-2">|</span> Dashboard
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="stat in stats" :key="stat.name" 
                         class="bg-white border-b-4 border-orange-500 rounded-xl shadow-sm p-6 flex items-center justify-between hover:shadow-md transition">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase">{{ stat.name }}</p>
                            <p class="text-2xl font-bold text-gray-800">{{ stat.amount }}</p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full text-orange-600">
                             <i :class="['fa-solid', stat.icon, 'text-2xl']"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <div class="bg-orange-500 px-6 py-4">
                        <h3 class="text-white font-bold flex items-center">
                            <i class="fa-solid fa-building-columns mr-2"></i> Rincian Saldo Per Bank
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-sm uppercase">
                                    <th class="px-6 py-3 font-semibold">Bank</th>
                                    <th class="px-6 py-3 font-semibold text-center">Zakat</th>
                                    <th class="px-6 py-3 font-semibold text-center">Infaq</th>
                                    <th class="px-6 py-3 font-semibold text-center">Sodaqoh</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="item in bankDetails" :key="item.bank" class="hover:bg-orange-50 transition">
                                    <td class="px-6 py-4 font-medium text-gray-800">{{ item.bank }}</td>
                                    <td class="px-6 py-4 text-center text-gray-600">{{ item.zakat }}</td>
                                    <td class="px-6 py-4 text-center text-gray-600">{{ item.infaq }}</td>
                                    <td class="px-6 py-4 text-center text-gray-600">{{ item.sodaqoh }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-gray-800 font-bold flex items-center">
                            <i class="fa-solid fa-users text-orange-500 mr-2"></i> Rincian Donatur Terbaru
                        </h3>
                        <button class="text-sm text-orange-600 font-semibold hover:underline">Lihat Semua</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-sm uppercase">
                                    <th class="px-6 py-3 font-semibold">Nama Donatur</th>
                                    <th class="px-6 py-3 font-semibold">Bank</th>
                                    <th class="px-6 py-3 font-semibold">Jumlah</th>
                                    <th class="px-6 py-3 font-semibold">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <tr v-for="donatur in donaturDetails" :key="donatur.nama" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-semibold text-orange-600">{{ donatur.nama }}</td>
                                    <td class="px-6 py-4">{{ donatur.bank }}</td>
                                    <td class="px-6 py-4 font-bold text-gray-800">{{ donatur.jumlah }}</td>
                                    <td class="px-6 py-4">
                                        <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs">
                                            {{ donatur.keterangan }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </StaffLayout>
</template>

<style scoped>
/* Tambahkan FontAwesome jika belum ada di project */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
</style>
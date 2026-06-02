<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// 1. Tambahkan 3 props baru di dalam deklarasi defineProps
const props = defineProps({
    totalseluruhdonasi: Number,       // Total akumulasi dana masuk (all-time)
    totalSaldoDenganTarget: Number,  // Total terkumpul program dengan target dana
    totalSaldoTanpaTarget: Number,   // Total terkumpul program tanpa target dana          
    saldoLiveProgram: Number,      
    saldoLiveKasUmum: Number,
    totalSaldoLiveSekarang: Number,      
    pengeluaranTerikat: Number,    
    pengeluaranTidakTerikat: Number, 
    totalPengeluaran: Number,           
    stats_kategori: Array,         
    pemasukan_program: Array,
    pengeluaran_pilar: Array,
    latestDonations: Array,
});

// 2. Fungsi Format Rupiah
const formatRupiah = (number) => {
    const value = parseFloat(number) || 0;
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <Head title="Dashboard Admin" />
    <AuthenticatedLayout>
        <div class="bg-gray-50 min-h-screen py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">

                <!-- ==================== BARIS BARU: REKAPITULASI DANA TERKUMPUL (ALL-TIME) ==================== -->
                <div>
                    <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-3">Akumulasi Pencapaian Donasi (All-Time)</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Card: Total Saldo Terkumpul -->
                        <div class="bg-gradient-to-br from-purple-800 to-indigo-900 p-5 rounded-xl text-white shadow-sm relative overflow-hidden">
                            <p class="text-[10px] font-black uppercase tracking-widest text-purple-200">Total Seluruh Saldo Terkumpul</p>
                            <p class="text-2xl font-black mt-1">{{ formatRupiah(totalseluruhdonasi) }}</p>
                            <div class="absolute right-3 bottom-2 text-3xl opacity-15">💎</div>
                        </div>

                        <!-- Card: Total Saldo dengan Target Dana -->
                        <div class="bg-gradient-to-br from-amber-700 to-orange-800 p-5 rounded-xl text-white shadow-sm relative overflow-hidden">
                            <p class="text-[10px] font-black uppercase tracking-widest text-amber-200">Dana Terkumpul (Program Bertarget)</p>
                            <p class="text-2xl font-black mt-1">{{ formatRupiah(totalSaldoDenganTarget) }}</p>
                            <div class="absolute right-3 bottom-2 text-3xl opacity-15">🎯</div>
                        </div>

                        <!-- Card: Total Saldo tanpa Target Dana -->
                        <div class="bg-gradient-to-br from-cyan-800 to-blue-900 p-5 rounded-xl text-white shadow-sm relative overflow-hidden">
                            <p class="text-[10px] font-black uppercase tracking-widest text-cyan-200">Dana Terkumpul (Tanpa Target / Kas Umum)</p>
                            <p class="text-2xl font-black mt-1">{{ formatRupiah(totalSaldoTanpaTarget) }}</p>
                            <div class="absolute right-3 bottom-2 text-3xl opacity-15">📢</div>
                        </div>
                    </div>
                </div>

                <!-- ==================== BARIS 2: KAS & SALDO LIVE SEKARANG ==================== -->
                <div>
                    <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-3">Ikhtisar Saldo & Pemasukan (Ready to Use)</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Card 1: Total Akumulasi Masuk -->
                        <div class="bg-gradient-to-br from-emerald-800 to-teal-700 p-5 rounded-xl text-white shadow-sm relative overflow-hidden">
                            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-200">Total Akumulasi Dana Masuk</p>
                            <p class="text-2xl font-black mt-1">{{ formatRupiah(totalSaldoLiveSekarang) }}</p>
                            <div class="absolute right-3 bottom-2 text-3xl opacity-15">💰</div>
                        </div>

                        <!-- Card 2: Total Saldo Live Program -->
                        <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-5 rounded-xl text-white shadow-sm relative overflow-hidden">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Saldo Live Program (Terikat)</p>
                            <p class="text-2xl font-black mt-1">{{ formatRupiah(saldoLiveProgram) }}</p>
                            <div class="absolute right-3 bottom-2 text-3xl opacity-10">📁</div>
                        </div>

                        <!-- Card 3: Total Saldo Live Kas Umum -->
                        <div class="bg-gradient-to-br from-blue-900 to-indigo-800 p-5 rounded-xl text-white shadow-sm relative overflow-hidden">
                            <p class="text-[10px] font-black uppercase tracking-widest text-blue-200">Total Saldo Live Kas Umum</p>
                            <p class="text-2xl font-black mt-1">{{ formatRupiah(saldoLiveKasUmum) }}</p>
                            <div class="absolute right-3 bottom-2 text-3xl opacity-15">💼</div>
                        </div>
                    </div>
                </div>

                <!-- ==================== BARIS 3: PENGELUARAN ==================== -->
                <div>
                    <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-3">Ikhtisar Pengeluaran / Penyaluran</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Card 1: Pengeluaran Terikat -->
                        <div class="bg-white border-b-4 border-red-500 rounded-xl shadow-sm p-5 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pengeluaran Terikat</p>
                                <p class="text-xl font-black text-gray-800 mt-1">{{ formatRupiah(pengeluaranTerikat) }}</p>
                            </div>
                            <div class="bg-red-50 p-3 rounded-full text-red-500 text-xl">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i>
                            </div>
                        </div>

                        <!-- Card 2: Pengeluaran Tidak Terikat -->
                        <div class="bg-white border-b-4 border-orange-500 rounded-xl shadow-sm p-5 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pengeluaran Tidak Terikat</p>
                                <p class="text-xl font-black text-gray-800 mt-1">{{ formatRupiah(pengeluaranTidakTerikat) }}</p>
                            </div>
                            <div class="bg-orange-50 p-3 rounded-full text-orange-500 text-xl">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                        </div>

                        <!-- Card 3: Total Pengeluaran Seluruhnya -->
                        <div class="bg-white border-b-4 border-rose-700 rounded-xl shadow-sm p-5 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Seluruh Pengeluaran</p>
                                <p class="text-xl font-black text-rose-700 mt-1">{{ formatRupiah(totalPengeluaran) }}</p>
                            </div>
                            <div class="bg-rose-50 p-3 rounded-full text-rose-700 text-xl">
                                <i class="fa-solid fa-calculator"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200">

                <!-- ==================== BARIS 4: DETAIL PILAR & TRANSAKSI ==================== -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Pengeluaran per Pilar -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-fit">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="text-gray-800 font-bold text-sm">Pengeluaran per Pilar</h3>
                        </div>
                        <div class="max-h-60 overflow-y-auto">
                            <table class="w-full text-sm">
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="pilar in pengeluaran_pilar" :key="pilar.pilar_terkait" class="hover:bg-gray-50">
                                        <td class="px-6 py-3 text-gray-600 font-medium">{{ pilar.pilar_terkait }}</td>
                                        <td class="px-6 py-3 text-right font-bold text-gray-800">{{ formatRupiah(pilar.total) }}</td>
                                    </tr>
                                    <tr v-if="pengeluaran_pilar?.length === 0">
                                        <td class="px-6 py-8 text-center text-gray-400">Belum ada data pengeluaran pilar.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Transaksi Masuk Terakhir -->
                    <div class="md:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                        <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="text-gray-800 font-bold text-sm flex items-center">
                                <i class="fa-solid fa-clock-rotate-left text-orange-500 mr-2"></i> Transaksi Masuk Terakhir
                            </h3>
                            <Link :href="route('transaksi')" class="text-xs text-orange-600 font-semibold hover:underline">
                                Lihat Semua
                            </Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                        <th class="px-6 py-3 font-semibold">Nama Donatur</th>
                                        <th class="px-6 py-3 font-semibold">Kategori</th>
                                        <th class="px-6 py-3 font-semibold text-right">Jumlah</th>
                                        <th class="px-6 py-3 font-semibold text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="donatur in latestDonations" :key="donatur.id" class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 font-semibold text-gray-700">{{ donatur.user_name }}</td>
                                        <td class="px-6 py-4">
                                            <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full text-xs font-medium uppercase">
                                                {{ donatur.kategori }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-emerald-600">{{ formatRupiah(donatur.amount) }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider">
                                                {{ donatur.status }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="latestDonations?.length === 0">
                                        <td colspan="4" class="text-center py-12 text-gray-400">Belum ada transaksi masuk.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
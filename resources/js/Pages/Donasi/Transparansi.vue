<script setup>
import { Head } from '@inertiajs/vue3';
import DonaturLayout from '@/Layouts/DonaturLayout.vue'; // Sesuaikan jika ada DonaturLayout.vue

// Mengambil props dari endpoint publik transparansi
const props = defineProps({
    totalseluruhdonasi: Number,
    totalSaldoDenganTarget: Number,
    totalSaldoTanpaTarget: Number,
    totalSaldoLiveSekarang: Number,
    saldoLiveProgram: Number,
    saldoLiveKasUmum: Number,
    pengeluaranTerikat: Number,
    pengeluaranTidakTerikat: Number,
    totalPengeluaran: Number,
    pengeluaran_pilar: Array,
    latestDonations: Array,
});

// Format Rupiah
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

    <Head title="Transparansi Dana Real-Time" />
    <DonaturLayout>
        <div class="bg-white min-h-screen py-12 px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl space-y-12">

                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight sm:text-4xl">
                        Laporan Transparansi Keuangan Terbuka
                    </h1>
                    <p class="text-base text-gray-500">
                        Bentuk pertanggungjawaban penyaluran dana Zakat, Infaq, dan Sedekah secara berkala dan real-time
                        demi menjaga amanah para donatur.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div
                        class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Dana Diterima</p>
                            <h3 class="text-2xl font-black text-gray-900 mt-1">{{ formatRupiah(totalseluruhdonasi) }}
                            </h3>
                            <p class="text-[11px] text-emerald-600 font-semibold mt-1">▲ Terakumulasi All-Time</p>
                        </div>
                        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl text-2xl">💰</div>
                    </div>

                    <div
                        class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Dana Tersalurkan
                            </p>
                            <h3 class="text-2xl font-black text-gray-900 mt-1">{{ formatRupiah(totalPengeluaran) }}</h3>
                            <p class="text-[11px] text-red-600 font-semibold mt-1">▼ Didistribusikan ke Mustahik</p>
                        </div>
                        <div class="bg-red-50 text-red-600 p-4 rounded-xl text-2xl">💸</div>
                    </div>

                    <div class="bg-slate-900 rounded-xl p-6 shadow-md flex items-center justify-between text-white">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider text-opacity-80">Sisa
                                Kas Siap Salur</p>
                            <h3 class="text-2xl font-black text-white mt-1">{{ formatRupiah(totalSaldoLiveSekarang) }}
                            </h3>
                            <p class="text-[11px] text-cyan-300 font-semibold mt-1">● Saldo Aktif Saat Ini</p>
                        </div>
                        <div class="bg-slate-800 text-white p-4 rounded-xl text-2xl">💼</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 space-y-4">
                        <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider border-b pb-2">Komposisi
                            Sifat Alokasi</h3>
                        <div class="space-y-3">
                            <div
                                class="bg-white p-4 rounded-xl border border-gray-200 flex justify-between items-center">
                                <div>
                                    <p class="text-xs text-gray-400 font-medium">Pengeluaran Terikat (Program Tertentu)
                                    </p>
                                    <p class="text-lg font-bold text-gray-800 mt-0.5">{{
                                        formatRupiah(pengeluaranTerikat) }}</p>
                                </div>
                                <span
                                    class="bg-red-50 text-red-700 font-bold text-xs px-2.5 py-1 rounded-md uppercase">Terikat</span>
                            </div>
                            <div
                                class="bg-white p-4 rounded-xl border border-gray-200 flex justify-between items-center">
                                <div>
                                    <p class="text-xs text-gray-400 font-medium">Pengeluaran Tidak Terikat (Kas
                                        Umum/Operasional)</p>
                                    <p class="text-lg font-bold text-gray-800 mt-0.5">{{
                                        formatRupiah(pengeluaranTidakTerikat) }}</p>
                                </div>
                                <span
                                    class="bg-orange-50 text-orange-700 font-bold text-xs px-2.5 py-1 rounded-md uppercase">Bebas</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm h-fit">
                        <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider border-b pb-2 mb-4">
                            Penyaluran Berdasarkan Pilar</h3>
                        <div class="max-h-52 overflow-y-auto pr-2 space-y-3">
                            <div v-for="pilar in pengeluaran_pilar" :key="pilar.pilar_terkait"
                                class="flex justify-between items-center border-b border-gray-50 pb-2 text-sm">
                                <span class="text-gray-600 font-medium">{{ pilar.pilar_terkait }}</span>
                                <span class="font-bold text-gray-900">{{ formatRupiah(pilar.total) }}</span>
                            </div>
                            <div v-if="pengeluaran_pilar?.length === 0" class="text-center py-6 text-gray-400 text-xs">
                                Belum ada alokasi pengeluaran pilar terdata.
                            </div>
                        </div>
                    </div>

                </div>

                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50/70 border-b border-gray-200">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Audit Log Penerimaan Donasi
                            Terbaru</h3>
                    </div>
                    <div class="overflow-x-auto text-sm">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-gray-400 text-xs uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">Muzakki / Donatur</th>
                                    <th class="px-6 py-3 font-semibold">Kategori Dana</th>
                                    <th class="px-6 py-3 font-semibold text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-600">
                                <tr v-for="donatur in latestDonations" :key="donatur.id"
                                    class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-semibold text-gray-800">
                                        {{ donatur.user_name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-xs font-bold uppercase">
                                            {{ donatur.kategori }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-emerald-600">{{
                                        formatRupiah(donatur.amount) }}</td>
                                </tr>
                                <tr v-if="latestDonations?.length === 0">
                                    <td colspan="3" class="text-center py-10 text-gray-400">Belum ada riwayat donasi
                                        masuk terkini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </DonaturLayout>
</template>
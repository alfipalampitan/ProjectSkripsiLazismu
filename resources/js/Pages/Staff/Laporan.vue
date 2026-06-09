<script setup>
import StaffLayout from '@/Layouts/StaffLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    rekapBulanan: { type: Array, default: () => [] },
    rekapKategori: { type: Array, default: () => [] },
    rekapPengeluaran: { type: Array, default: () => [] },
    programs: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) }
});

// Tab Kontrol ('pemasukan' / 'pengeluaran')
const activeTab = ref('pemasukan');

// Fungsi pemicu refresh filter multi-parameter
const updateFilter = (key, value) => {
    // Ambil data filter saat ini, timpa dengan key filter yang baru diganti
    const currentFilters = {
        type: props.filters.type || 'all',
        program_id: props.filters.program_id || 'all',
        sifat_pengeluaran: props.filters.sifat_pengeluaran || 'all',
    };
    currentFilters[key] = value;

    router.get(route('staff.laporan.index'), currentFilters, {
        preserveState: true,
        replace: true
    });
};

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
};
</script>

<template>
    <StaffLayout>

        <Head title="Laporan Keuangan Admin" />
        <div class="py-6 px-4 md:px-8">

            <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-800">Laporan Kas Bulanan (Staff)</h1>
                    <p class="text-sm text-gray-500 font-medium">Rekapitulasi transparansi dana masuk dan keluar
                        Lazismu.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex bg-gray-100 p-1 rounded-2xl">
                        <button @click="activeTab = 'pemasukan'"
                            :class="[activeTab === 'pemasukan' ? 'bg-white shadow-sm text-orange-600' : 'text-gray-500']"
                            class="px-4 py-2 rounded-xl text-xs font-black uppercase transition-all">
                            <i class="fa-solid fa-arrow-down mr-1"></i> Pemasukan
                        </button>
                        <button @click="activeTab = 'pengeluaran'"
                            :class="[activeTab === 'pengeluaran' ? 'bg-white shadow-sm text-orange-600' : 'text-gray-500']"
                            class="px-4 py-2 rounded-xl text-xs font-black uppercase transition-all">
                            <i class="fa-solid fa-arrow-up mr-1"></i> Pengeluaran
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm mb-6">
                <div v-if="activeTab === 'pemasukan'" class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="w-full md:w-auto">
                        <span class="block text-[10px] font-black uppercase text-gray-400 mb-1">Metode Donasi</span>
                        <div class="flex bg-gray-100 p-1 rounded-xl w-fit">
                            <button v-for="t in ['all', 'cash', 'transfer']" :key="t" @click="updateFilter('type', t)"
                                :class="[filters.type === t ? 'bg-white text-orange-600 shadow-sm' : 'text-gray-500']"
                                class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase transition-all">
                                {{ t === 'all' ? 'Semua' : t }}
                            </button>
                        </div>
                    </div>
                    <div class="w-full md:flex-1">
                        <span class="block text-[10px] font-black uppercase text-gray-400 mb-1">Berdasarkan
                            Program</span>
                        <select :value="filters.program_id" @change="updateFilter('program_id', $event.target.value)"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 rounded-xl px-4 py-2 text-xs font-bold outline-none focus:border-orange-500">
                            <option value="all">Semua Program Kerja</option>
                            <option v-for="prog in programs" :key="prog.id" :value="prog.id">
                                {{ prog.judul }} ({{ prog.kategori }})
                            </option>
                        </select>
                    </div>
                </div>

                <div v-if="activeTab === 'pengeluaran'" class="w-full">
                    <select :value="filters.sifat_pengeluaran"
                        @change="updateFilter('sifat_pengeluaran', $event.target.value)"
                        class="w-full bg-gray-50 border border-gray-200 text-gray-700 rounded-xl px-4 py-2 text-xs font-bold outline-none focus:border-orange-500">
                        <option value="all">Semua Sifat Penyaluran</option>
                        <option value="Terikat">Terikat</option>
                        <option value="Tidak_Terikat">Tidak Terikat</option>
                    </select>
                </div>
            </div>

            <div v-if="activeTab === 'pemasukan'" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
                    <div v-for="kat in rekapKategori" :key="kat.kategori"
                        class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-start mb-2">
                            <p class="text-gray-400 text-[10px] uppercase font-black">{{ kat.kategori }}</p>
                            <i class="fa-solid fa-wallet text-gray-200 text-xl"></i>
                        </div>
                        <h3 class="text-xl md:text-2xl font-black text-gray-800">{{ formatRupiah(kat.total) }}</h3>
                    </div>
                </div>

                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                        <h2 class="font-bold text-gray-800">Rekapitulasi Donasi Masuk</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left min-w-[600px]">
                            <thead class="bg-gray-50 text-gray-400 uppercase text-[10px] font-black">
                                <tr>
                                    <th class="p-5">Bulan</th>
                                    <th class="p-5 text-center">Jumlah Transaksi</th>
                                    <th class="p-5">Total Dana Masuk</th>
                                    <th class="p-5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="(lap, index) in rekapBulanan" :key="index"
                                    class="hover:bg-gray-50/50 transition">
                                    <td class="p-5">
                                        <div class="font-bold text-gray-700">{{ lap.bulan }}</div>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase">
                                            Mode: {{ filters.type === 'all' ? 'Gabungan' : filters.type }}
                                        </span>
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
                                        <a :href="route('staff.laporan.download', { bulan: lap.bulan, type: filters.type, program_id: filters.program_id })"
                                            target="_blank"
                                            class="inline-flex items-center bg-gray-900 text-white hover:bg-orange-500 px-5 py-2.5 rounded-2xl text-xs font-bold transition-all">
                                            <i class="fa-solid fa-file-pdf mr-2"></i> PDF
                                        </a>
                                    </td>
                                </tr>
                                <tr v-if="rekapBulanan.length === 0">
                                    <td colspan="4" class="p-20 text-center text-gray-400 italic">Tidak ada data donasi
                                        untuk kriteria ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'pengeluaran'">
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50">
                        <h2 class="font-bold text-gray-800">Rekapitulasi Penyaluran Dana (Disbursement)</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left min-w-[600px]">
                            <thead class="bg-gray-50 text-gray-400 uppercase text-[10px] font-black">
                                <tr>
                                    <th class="p-5">Bulan</th>
                                    <th class="p-5 text-center">Jumlah Distribusi</th>
                                    <th class="p-5">Total Dana Keluar</th>
                                    <th class="p-5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="(lap, index) in rekapPengeluaran" :key="'out-' + index"
                                    class="hover:bg-gray-50/50 transition">
                                    <td class="p-5">
                                        <div class="font-bold text-gray-700">{{ lap.bulan }}</div>
                                        <span class="text-[10px] font-bold text-orange-500 uppercase">Sifat: {{
                                            filters.sifat_pengeluaran }}</span>
                                    </td>
                                    <td class="p-5 text-center">
                                        <span
                                            class="bg-orange-50 text-orange-600 px-3 py-1 rounded-xl text-xs font-black">
                                            {{ lap.total_kasus }} Kasus
                                        </span>
                                    </td>
                                    <td class="p-5">
                                        <div class="text-red-500 font-black">{{ formatRupiah(lap.total_dana) }}</div>
                                    </td>
                                    <td class="p-5 text-right">
                                        <a :href="route('staff.laporan.download_pengeluaran', { bulan: lap.bulan, sifat_pengeluaran: filters.sifat_pengeluaran })"
                                            target="_blank"
                                            class="inline-flex items-center bg-gray-900 text-white hover:bg-orange-500 px-5 py-2.5 rounded-2xl text-xs font-bold transition-all">
                                            <i class="fa-solid fa-file-pdf mr-2"></i> PDF
                                        </a>
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
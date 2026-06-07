<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    donatur: Array, // Seluruh data transaksi pemasukan/pengeluaran dari backend
});

// State Kontrol Modal Edit & Loading
const isModalOpen = ref(false);
const loading = ref(false);

// Form khusus Update Data (Inertia useForm)
const form = useForm({
    id: null,
    nama: '',
    nomor_hp: '',
    email: '',
    jenis: 'Pemasukan', // Pemasukan / Pengeluaran
    kategori: '',
    amount: '',
    keterangan: '',
    is_anonim: false,
});

const tutupModal = () => isModalOpen.value = false;

// Buka Modal Edit & Isi Form data terpilih
const editDonasi = (item) => {
    form.id = item.id;
    form.nama = item.nama || item.user_name || 'N/A';
    form.email = item.email || '';
    form.nomor_hp = item.nomor_hp || '';
    form.jenis = item.jenis || (item.tipe === 'keluar' ? 'Pengeluaran' : 'Pemasukan'); 
    form.kategori = item.kategori || 'Donasi';
    form.amount = item.amount;
    form.keterangan = item.keterangan || '';
    form.is_anonim = item.is_anonim || false;

    isModalOpen.value = true;
};

// Fungsi Update/Simpan Perubahan ke Backend
const updateDonasi = () => {
    loading.value = true;

    form.put(route('donasi.update', form.id), {
        onSuccess: () => {
            tutupModal();
            Swal.fire({
                title: 'Berhasil!',
                text: 'Data monitoring transaksi telah diperbarui.',
                icon: 'success',
                confirmButtonColor: '#f97316',
            });
        },
        onError: (err) => {
            console.error(err);
            Swal.fire('Gagal!', 'Terjadi kesalahan saat menyimpan perubahan.', 'error');
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

// Fungsi Hapus Transaksi (Destroy)
const deleteDonasi = (id) => {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: 'Data transaksi ini akan dihapus permanen dari rekaman data!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444', 
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('donasi.destroy', id), {
                onSuccess: () => {
                    Swal.fire('Terhapus!', 'Rekam transaksi berhasil dihapus.', 'success');
                },
                onError: () => {
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
                }
            });
        }
    });
};

// --- FITUR MONITORING & FILTER DATA ---
const searchQuery = ref('');
const filterAlur = ref('Semua Alur'); // Semua Alur / Pemasukan / Pengeluaran

// Fungsi Helper menghitung ringkasan statistik secara otomatis (Kalkulasi Frontend)
const totalPemasukan = computed(() => {
    if (!props.donatur) return 0;
    return props.donatur
        .filter(item => item.jenis === 'Pemasukan' || item.tipe !== 'keluar')
        .reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
});

const totalPengeluaran = computed(() => {
    if (!props.donatur) return 0;
    return props.donatur
        .filter(item => item.jenis === 'Pengeluaran' || item.tipe === 'keluar')
        .reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
});

const saldoBersih = computed(() => totalPemasukan.value - totalPengeluaran.value);

// Filter Utama Laporan
const filteredDonatur = computed(() => {
    if (!props.donatur) return [];

    return props.donatur.filter(item => {
        // 1. Logika Pencarian Nama / Email / Kategori
        const query = searchQuery.value.toLowerCase();
        const nama = (item.nama || item.user_name || 'hamba allah').toLowerCase();
        const email = (item.email || '').toLowerCase();
        const kategori = (item.kategori || '').toLowerCase();
        const matchSearch = nama.includes(query) || email.includes(query) || kategori.includes(query);

        // 2. Logika Filter Jenis Alur Dana (Pemasukan / Pengeluaran)
        const itemJenis = item.jenis || (item.tipe === 'keluar' ? 'Pengeluaran' : 'Pemasukan');
        const matchAlur = filterAlur.value === 'Semua Alur' || itemJenis === filterAlur.value;

        return matchSearch && matchAlur;
    });
});

// Format Rupiah
const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
};

// Format Tanggal
const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Monitoring Keuangan Admin" />

    <AuthenticatedLayout>
        <template #header>
            Monitoring Alur Keuangan (Maju/Mundur Dana)
        </template>

        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h1 class="text-2xl font-bold text-gray-800">Panel Monitoring Transaksi Keuangan</h1>
                <p class="text-gray-500 text-sm mt-1">Pantau seluruh alur dana masuk (donasi) dan dana keluar untuk program filantropi.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600 text-xl flex-shrink-0">
                        <i class="fa-solid fa-arrow-down-long"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Total Pemasukan (Donasi)</p>
                        <p class="text-lg font-black text-green-600 mt-0.5">{{ formatRupiah(totalPemasukan) }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-red-600 text-xl flex-shrink-0">
                        <i class="fa-solid fa-arrow-up-long"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Total Pengeluaran Laporan</p>
                        <p class="text-lg font-black text-red-600 mt-0.5">{{ formatRupiah(totalPengeluaran) }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center text-orange-500 text-xl flex-shrink-0">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Kalkulasi Saldo Bersih</p>
                        <p class="text-lg font-black text-gray-800 mt-0.5" :class="{'text-red-500': saldoBersih < 0}">
                            {{ formatRupiah(saldoBersih) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="relative w-full max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input v-model="searchQuery" type="text" placeholder="Cari nama donatur atau kategori program..."
                        class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-orange-500 focus:border-orange-500 outline-none transition-all text-sm" />
                </div>

                <div class="flex gap-2">
                    <select v-model="filterAlur"
                        class="bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 focus:ring-orange-500 outline-none">
                        <option value="Semua Alur">Semua Alur Transaksi</option>
                        <option value="Pemasukan">Pemasukan (Dana Masuk)</option>
                        <option value="Pengeluaran">Pengeluaran (Dana Keluar)</option>
                    </select>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 font-bold text-gray-700 uppercase text-xs tracking-wider">Tanggal</th>
                                <th class="px-6 py-4 font-bold text-gray-700 uppercase text-xs tracking-wider">Pihak Terkait / Donatur</th>
                                <th class="px-6 py-4 font-bold text-gray-700 uppercase text-xs tracking-wider">Alur Dana</th>
                                <th class="px-6 py-4 font-bold text-gray-700 uppercase text-xs tracking-wider">Kategori Program</th>
                                <th class="px-6 py-4 font-bold text-gray-700 uppercase text-xs tracking-wider">Nominal</th>
                                <th class="px-6 py-4 font-bold text-gray-700 uppercase text-xs tracking-wider">Keterangan</th>
                                <th class="px-6 py-4 font-bold text-gray-700 uppercase text-xs tracking-wider text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in filteredDonatur" :key="item.id" class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-6 py-4 text-gray-600 font-medium text-sm whitespace-nowrap">
                                    {{ formatDate(item.tanggal) }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-800">
                                        {{ item.is_anonim ? 'Hamba Allah' : (item.nama || item.user_name || 'N/A') }}
                                    </div>
                                    <div class="text-xs text-gray-400">{{ item.email || '-' }}</div>
                                </td>

                                <td class="px-6 py-4">
                                    <span :class="(item.jenis === 'Pengeluaran' || item.tipe === 'keluar') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-wider">
                                        {{ (item.jenis === 'Pengeluaran' || item.tipe === 'keluar') ? 'Keluar' : 'Masuk' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm font-semibold text-gray-700 capitalize">
                                    {{ (item.kategori || 'Donasi').replace('_', ' ') }}
                                </td>

                                <td class="px-6 py-4 font-bold text-sm" 
                                    :class="(item.jenis === 'Pengeluaran' || item.tipe === 'keluar') ? 'text-red-500' : 'text-green-600'">
                                    {{ (item.jenis === 'Pengeluaran' || item.tipe === 'keluar') ? '-' : '+' }} {{ formatRupiah(item.amount) }}
                                </td>

                                <td class="px-6 py-4 text-gray-500 text-sm max-w-xs truncate">
                                    {{ item.keterangan || '-' }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-3">
                                        <button @click="editDonasi(item)" class="text-blue-500 hover:text-blue-700 transition" title="Edit Data Laporan">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button @click="deleteDonasi(item.id)" class="text-red-400 hover:text-red-600 transition" title="Hapus Rekam Data">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="filteredDonatur.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">
                                    Tidak ada data alur transaksi yang cocok atau tersedia saat ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl flex flex-col max-h-[90vh] relative">
            
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">Koreksi Data Keuangan</h2>
                <button @click="tutupModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Pihak / Donatur</label>
                    <input v-model="form.nama" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-orange-500 text-sm" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Alur Transaksi</label>
                        <select v-model="form.jenis" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-orange-500">
                            <option value="Pemasukan">Pemasukan (Masuk)</option>
                            <option value="Pengeluaran">Pengeluaran (Keluar)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori Kebaikan</label>
                        <input v-model="form.kategori" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-orange-500" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nominal Dana (Rp)</label>
                    <input v-model="form.amount" type="number" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm bg-gray-50 font-bold" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan Keterangan</label>
                    <textarea v-model="form.keterangan" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-orange-500 outline-none"></textarea>
                </div>
            </div>

            <div class="p-6 border-t border-gray-100 flex gap-3 bg-gray-50 rounded-b-3xl">
                <button @click="tutupModal" class="flex-1 px-4 py-3 rounded-xl bg-white border border-gray-200 text-gray-600 font-bold hover:bg-gray-100 text-sm transition-all">
                    Batal
                </button>
                <button @click="updateDonasi" :disabled="loading" class="flex-1 px-4 py-3 rounded-xl bg-orange-500 text-white font-bold hover:bg-orange-600 text-sm shadow-lg shadow-orange-100 transition-all">
                    {{ loading ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </button>
            </div>
        </div>
    </div>
</template>
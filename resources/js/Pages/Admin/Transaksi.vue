<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// Data Dummy untuk tabel transaksi
const transaksi = ref([
    { tanggal: '12-04-2026', jenis: 'Zakat', kategori: 'Zakat Mal', keterangan: 'Pembayaran via BSI' },
    { tanggal: '13-04-2026', jenis: 'Infaq', kategori: 'Infaq Dakwah', keterangan: 'Hamba Allah' },
    { tanggal: '14-04-2026', jenis: 'Sodaqoh', kategori: 'Bantuan Bencana', keterangan: 'Transfer Mandiri' },
    { tanggal: '15-04-2026', jenis: 'Zakat', kategori: 'Zakat Fitrah', keterangan: 'Tunai di Kantor' },
    { tanggal: '16-04-2026', jenis: 'Infaq', kategori: 'Kemanusiaan', keterangan: 'Program Ramadhan' },
]);

// State untuk Modal & Form
const isModalOpen = ref(false);
const loading = ref(false);

const form = ref({
    nama: '',
    nomor_hp: '',
    email: '',
    nominal: '',
    jenis: 'Zakat',
    keterangan: '',
    is_anonim: false // State untuk checkbox anonim
});

const bukaModal = () => isModalOpen.value = true;
const tutupModal = () => isModalOpen.value = false;

const errors = ref({
    nama: '',
    nomor_hp: '',
    nominal: ''
});

// Fungsi Utama Midtrans
const prosesDonasi = async () => {

    // Reset errors setiap kali tombol diklik
    errors.value = { nama: '', nomor_hp: '', nominal: '' };

    let isValid = true;

    if (!form.value.is_anonim && !form.value.nama) {
        errors.value.nama = 'Nama wajib diisi jika tidak anonim';
        isValid = false;
    }
    if (!form.value.nomor_hp) {
        errors.value.nomor_hp = 'Nomor HP wajib diisi untuk konfirmasi';
        isValid = false;
    }
    if (!form.value.nominal || form.value.nominal < 10000) {
        errors.value.nominal = 'Minimal donasi adalah Rp 10.000';
        isValid = false;
    }
    if (!isValid) return; // Berhenti jika ada yang tidak valid

    loading.value = true;

    try {
        // Logika untuk menentukan nama yang dikirim ke server
        const namaDonatur = form.value.is_anonim ? 'Hamba Allah' : form.value.nama;

        // 1. Minta Snap Token dari Laravel
        const response = await axios.post('/payment/token', {
            total: form.value.nominal,
            jenis: form.value.jenis,
            keterangan: form.value.keterangan,
            nama: namaDonatur,
            nomor_hp: form.value.nomor_hp,
            email: form.value.email,
        });

        const token = response.data.token;

        // 2. Munculkan Snap Midtrans
        window.snap.pay(token, {
            onSuccess: function (result) {
                tutupModal(); // Tutup modal input donasi dulu

                Swal.fire({
                    title: 'Alhamdulillah! 🎉',
                    text: 'Pembayaran donasi kamu telah berhasil kami terima.',
                    icon: 'success',
                    confirmButtonText: 'Mantap',
                    confirmButtonColor: '#f97316', // Warna orange-500
                    customClass: {
                        popup: 'rounded-3xl', // Agar senada dengan desain modal kamu
                        confirmButton: 'rounded-xl px-10 py-3'
                    }
                }).then((result) => {
                    // Setelah user klik 'Mantap', baru reload atau redirect
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            },
            onPending: function (result) {
                Swal.fire({
                    title: 'Menunggu Pembayaran',
                    text: 'Segera selesaikan pembayaranmu ya!',
                    icon: 'info',
                    confirmButtonColor: '#f97316',
                });
            },
            onError: function (result) {
                Swal.fire({
                    title: 'Oops!',
                    text: 'Terjadi kesalahan saat memproses pembayaran.',
                    icon: 'error',
                    confirmButtonColor: '#ef4444', // Merah
                });
            },
            onClose: function () {
                console.log('User menutup popup tanpa menyelesaikan pembayaran');
            }
        });
    } catch (error) {
        console.error(error);
        alert("Terjadi kesalahan pada server.");
    } finally {
        loading.value = false;
    }
};

const tambahTransaksi = () => {
    // Logika di sini: bisa buka modal atau route ke halaman form
    console.log('Buka form tambah transaksi');
};

</script>

<template>

    <Head title="Daftar Transaksi" />

    <AuthenticatedLayout>
        <template #header>
            Daftar Transaksi
        </template>

        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Daftar Transaksi</h1>
                    <p class="text-gray-500 text-sm mt-1">Info tentang daftar transaksi di bawah ini :</p>
                </div>

                <button @click="bukaModal"
                    class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-orange-200 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Transaksi</span>
                </button>
            </div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="relative w-full md:w-1/3">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </span>
                    <input type="text" placeholder="Search..."
                        class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-orange-500 focus:border-orange-500 outline-none transition-all" />
                </div>

                <div class="flex gap-2">
                    <select
                        class="bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm text-gray-600 focus:ring-orange-500 outline-none">
                        <option>Semua Jenis</option>
                        <option>Zakat</option>
                        <option>Infaq</option>
                        <option>Sodaqoh</option>
                    </select>
                    <select
                        class="bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm text-gray-600 focus:ring-orange-500 outline-none">
                        <option>Semua Kategori</option>
                        <option>Kemanusiaan</option>
                        <option>Pendidikan</option>
                        <option>Dakwah</option>
                    </select>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 font-bold text-gray-700 uppercase text-xs tracking-wider">Tanggal
                                </th>
                                <th class="px-6 py-4 font-bold text-gray-700 uppercase text-xs tracking-wider">Jenis
                                </th>
                                <th class="px-6 py-4 font-bold text-gray-700 uppercase text-xs tracking-wider">Kategori
                                </th>
                                <th class="px-6 py-4 font-bold text-gray-700 uppercase text-xs tracking-wider">
                                    Keterangan</th>
                                <th
                                    class="px-6 py-4 font-bold text-gray-700 uppercase text-xs tracking-wider text-center">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(item, index) in transaksi" :key="index"
                                class="hover:bg-orange-50/50 transition-colors">
                                <td class="px-6 py-4 text-gray-600 font-medium">{{ item.tanggal }}</td>
                                <td class="px-6 py-4">
                                    <span :class="{
                                        'bg-orange-100 text-orange-700': item.jenis === 'Zakat',
                                        'bg-blue-100 text-blue-700': item.jenis === 'Infaq',
                                        'bg-green-100 text-green-700': item.jenis === 'Sodaqoh'
                                    }" class="px-3 py-1 rounded-lg text-xs font-bold uppercase">
                                        {{ item.jenis }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 italic font-semibold">{{ item.kategori }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ item.keterangan }}</td>
                                <td class="px-6 py-4 text-center">
                                    <button class="text-blue-500 hover:text-blue-700 mr-3">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button @click="form.nominal = 50000; prosesDonasi()"
                                        class="bg-blue-500 text-white px-3 py-1 rounded-lg text-xs font-bold">
                                        Bayar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
    <div v-if="isModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">

        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl flex flex-col max-h-[90vh] relative">

            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">Input Donasi Baru</h2>
                <button @click="tutupModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                        <input v-model="form.nama" type="text" :disabled="form.is_anonim"
                            :placeholder="form.is_anonim ? 'Hamba Allah' : 'Masukkan nama Anda'"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500 outline-none transition-all disabled:bg-gray-50 disabled:text-gray-400" />

                        <div class="flex items-center mt-2">
                            <input type="checkbox" id="anonim" v-model="form.is_anonim"
                                class="w-4 h-4 text-orange-500 border-gray-300 rounded focus:ring-orange-500 cursor-pointer">
                            <label for="anonim" class="ml-2 text-sm text-gray-600 cursor-pointer select-none">
                                Sembunyikan nama (Hamba Allah)
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nomor HP (WhatsApp)</label>
                        <input v-model="form.nomor_hp" type="tel"
                            @input="form.nomor_hp = form.nomor_hp.replace(/\D/g, '')" placeholder="0812"
                            :class="errors.nomor_hp ? 'border-red-500 focus:ring-red-500' : 'border-gray-200 focus:ring-orange-500'"
                            class="w-full px-4 py-3 rounded-xl border outline-none transition-all" />
                        <p v-if="errors.nomor_hp" class="text-red-500 text-xs mt-1 font-medium italic">
                            * {{ errors.nomor_hp }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                        <input v-model="form.email" type="email" placeholder="nama@email.com"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500 outline-none transition-all" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nominal (Rp)</label>
                        <input v-model="form.nominal" type="number" placeholder="Min. 10.000"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500 outline-none transition-all" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Jenis Donasi</label>
                        <select v-model="form.jenis"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500 outline-none">
                            <option value="Zakat">Zakat</option>
                            <option value="Infaq">Infaq</option>
                            <option value="Sodaqoh">Sodaqoh</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan</label>
                        <textarea v-model="form.keterangan" rows="2" placeholder="Tulis catatan..."
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500 outline-none"></textarea>
                    </div>
                </div>
            </div>

            <div class="p-6 border-t border-gray-100 flex gap-3 bg-gray-50 rounded-b-3xl">
                <button @click="tutupModal"
                    class="flex-1 px-4 py-3 rounded-xl bg-white border border-gray-200 text-gray-600 font-bold hover:bg-gray-100 transition-all">
                    Batal
                </button>
                <button @click="prosesDonasi" :disabled="loading"
                    class="flex-1 px-4 py-3 rounded-xl bg-orange-500 text-white font-bold hover:bg-orange-600 shadow-lg shadow-orange-200 transition-all disabled:opacity-50">
                    {{ loading ? 'Memproses...' : 'Bayar Sekarang' }}
                </button>
            </div>
        </div>
    </div>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
    donatur: Array,
});

// State untuk Modal & Form
const isModalOpen = ref(false);
const loading = ref(false);
const isEditing = ref(false);



// Ganti const form = ref(...) menjadi:
const form = useForm({
    id: null, // Tambahkan ID untuk keperluan edit/hapus
    nama: '',
    nomor_hp: '',
    email: '',
    nominal: '',
    jenis: 'Zakat',
    keterangan: '',
    is_anonim: false,
    // Tambahkan field yang mungkin ada di database kamu agar tidak undefined
    kategori: '',
    amount: ''
});

const bukaModal = () => {
    isEditing.value = false; // Reset ke mode tambah
    form.reset();
    isModalOpen.value = true;
};
const tutupModal = () => isModalOpen.value = false;

const editDonasi = (item) => {
    isEditing.value = true;

    // Masukkan data dari tabel ke dalam form
    form.id = item.id;
    form.nama = item.nama || item.user_name;
    form.email = item.email;
    form.nomor_hp = item.nomor_hp;
    form.kategori = item.kategori;
    form.amount = item.amount;
    form.keterangan = item.keterangan;

    // Buka modal edit
    isModalOpen.value = true;
};

// --- TAMBAHKAN FUNGSI INI ---
const updateDonasi = () => {
    loading.value = true;

    // Menggunakan Inertia untuk mengirim data ke Laravel
    form.put(route('donasi.update', form.id), {
        onSuccess: () => {
            tutupModal();
            Swal.fire({
                title: 'Berhasil!',
                text: 'Data donasi telah diperbarui.',
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

const errors = ref({
    nama: '',
    nomor_hp: '',
    nominal: ''
});

// Fungsi Utama Midtrans
const prosesDonasi = async () => {
    errors.value = { nama: '', nomor_hp: '', nominal: '' };
    let isValid = true;

    // Hapus semua .value di bawah ini
    if (!form.is_anonim && !form.nama) {
        errors.value.nama = 'Nama wajib diisi jika tidak anonim';
        isValid = false;
    }
    if (!form.nomor_hp) {
        errors.value.nomor_hp = 'Nomor HP wajib diisi untuk konfirmasi';
        isValid = false;
    }
    if (!form.nominal || form.nominal < 10000) {
        errors.value.nominal = 'Minimal donasi adalah Rp 10.000';
        isValid = false;
    }
    if (!isValid) return;

    loading.value = true;

    try {
        // Logika untuk menentukan nama yang dikirim ke server
        const namaDonatur = form.value.is_anonim ? 'Hamba Allah' : form.nama;

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

// 1. Fungsi Format Rupiah
const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
};

// 2. Fungsi Format Tanggal
const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

// --- LOGIKA HAPUS ---
const deleteDonasi = (id) => {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data donasi ini akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f97316', // Warna orange
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Pastikan route 'donasi.destroy' sesuai dengan nama route di web.php kamu
            form.delete(route('donasi.destroy', id), {
                onSuccess: () => {
                    Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                },
                onError: () => {
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
                }
            });
        }
    });
};

// 2. Pastikan variabel ini sudah dibuat
const searchQuery = ref('');
const filterJenis = ref('Semua Jenis');
const filteredDonatur = computed(() => {
    // 1. Pastikan props.donatur ada datanya
    if (!props.donatur) return [];

    return props.donatur.filter(item => {
        // --- LOGIKA PENCARIAN (Berdasarkan Nama/Email) ---
        // Kita ambil data nama/email dan ubah ke huruf kecil agar pencarian tidak sensitif huruf besar
        const query = searchQuery.value.toLowerCase();
        const nama = (item.nama || item.user_name || '').toLowerCase();
        const email = (item.email || '').toLowerCase();

        const matchSearch = nama.includes(query) || email.includes(query);

        // --- LOGIKA FILTER KATEGORI ---
        const matchKategori = filterJenis.value === 'Semua Jenis' || item.kategori === filterJenis.value;

        // --- GABUNGKAN KEDUA KONDISI ---
        // Hanya kembalikan data yang lolos pencarian DAN lolos filter kategori
        return matchSearch && matchKategori;
    });
});

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
                <div class="relative w-full max-w-md">
                    <!-- Icon Pencarian (Opsional agar lebih cantik) -->
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>

                    <!-- Input Search Utama -->
                    <input v-model="searchQuery" type="text" placeholder="Cari nama atau email..."
                        class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-orange-500 focus:border-orange-500 outline-none transition-all" />
                </div>

                <div class="flex gap-2">
                    <select v-model="filterJenis"
                        class="bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm text-gray-600 focus:ring-orange-500 outline-none">
                        <option value="Semua Jenis">Semua Jenis</option>
                        <option value="Zakat">Zakat</option>
                        <option value="Infaq">Infaq</option>
                        <option value="Wakaf">Wakaf</option>
                        <option value="Pilar">Pilar</option>
                        <option value="Qurban">Qurban</option>
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
                                <th class="px-6 py-4 font-bold text-gray-700 uppercase text-xs tracking-wider">Donatur
                                </th>
                                <th class="px-6 py-4 font-bold text-gray-700 uppercase text-xs tracking-wider">Jenis
                                </th>
                                <th class="px-6 py-4 font-bold text-gray-700 uppercase text-xs tracking-wider">Nominal
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
                            <!-- Iterasi menggunakan data 'donatur' -->
                            <tr v-for="item in filteredDonatur" :key="item.id"
                                class="hover:bg-orange-50/50 transition-colors">

                                <!-- Tanggal (menggunakan helper formatDate) -->
                                <td class="px-6 py-4 text-gray-600 font-medium text-sm">
                                    {{ formatDate(item.updated_at) }}
                                </td>

                                <!-- Nama Donatur -->
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-800">{{ item.nama || item.user_name }}</div>
                                    <div class="text-xs text-gray-400">{{ item.email || '-' }}</div>
                                </td>

                                <!-- Jenis / Kategori dengan Badge Dinamis -->
                                <td class="px-6 py-4">
                                    <span :class="{
                                        'bg-orange-100 text-orange-700': item.kategori === 'Zakat',
                                        'bg-blue-100 text-blue-700': item.kategori === 'Infaq',
                                        'bg-green-100 text-green-700': item.kategori === 'Shadaqah' || item.kategori === 'Sodaqoh',
                                        'bg-gray-100 text-gray-700': !['Zakat', 'Infaq', 'Sodaqoh', 'Shadaqah'].includes(item.kategori)
                                    }" class="px-3 py-1 rounded-lg text-xs font-bold uppercase">
                                        {{ item.kategori || 'Donasi' }}
                                    </span>
                                </td>

                                <!-- Nominal (menggunakan helper formatRupiah) -->
                                <td class="px-6 py-4 text-green-600 font-bold text-sm">
                                    {{ formatRupiah(item.amount) }}
                                </td>

                                <!-- Keterangan -->
                                <td class="px-6 py-4 text-gray-500 text-sm max-w-xs truncate">
                                    {{ item.keterangan || '-' }}
                                </td>

                                <!-- Aksi -->
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button @click="editDonasi(item)"
                                            class="text-blue-500 hover:text-blue-700 transition">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button @click="deleteDonasi(item.id)"
                                            class="text-red-500 hover:text-red-700 transition">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- State jika data kosong -->
                            <tr v-if="donatur.length === 0">
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400 italic">
                                    Belum ada data donasi yang tersedia.
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
                <!-- Judul dinamis berdasarkan status isEditing -->
                <h2 class="text-xl font-bold text-gray-800">
                    {{ isEditing ? 'Edit Data Donasi' : 'Input Donasi Baru' }}
                </h2>
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

                    <!-- Cari bagian input nominal kamu -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nominal Donasi</label>
                        <input v-model="form.nominal" type="number"
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500"
                            :class="{ 'bg-gray-100 cursor-not-allowed': isEditing }" :readonly="isEditing" />
                        <p v-if="isEditing" class="text-xs text-gray-500 mt-1">
                            *Nominal tidak dapat diubah untuk menjaga integritas data.
                        </p>
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

                <!-- Tombol ini akan memanggil fungsi yang berbeda & teks yang berbeda -->
                <button @click="isEditing ? updateDonasi() : prosesDonasi()" :disabled="loading"
                    class="flex-1 px-4 py-3 rounded-xl bg-orange-500 text-white font-bold hover:bg-orange-600 shadow-lg shadow-orange-200 transition-all disabled:opacity-50">
                    <span v-if="loading">Memproses...</span>
                    <span v-else>
                        {{ isEditing ? 'Simpan Perubahan' : 'Bayar Sekarang' }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>
<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

// 1. Setup Form Utama menggunakan Inertia Form Helper
const form = useForm({
    nama_penyaluran: '',
    pilar: '',
    skema_form: [
        { field_name: '', type: 'text', required: true } // Default baris pertama inputan
    ]
});

// 2. Fungsi Interaktif Form Builder (Tambah & Hapus Baris Inputan)
const tambahField = () => {
    form.skema_form.push({ field_name: '', type: 'text', required: true });
};

const hapusField = (index) => {
    if (form.skema_form.length > 1) {
        form.skema_form.splice(index, 1);
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Gagal',
            text: 'Minimal harus ada 1 inputan dalam formulir!',
            confirmButtonColor: '#EF4444'
        });
    }
};

// 3. Eksekusi Kirim Data ke Laravel Backend
const submitForm = () => {
    form.post(route('pilar-form.store'), {
        onStart: () => {
            Swal.fire({
                title: 'Menyimpan...',
                text: 'Sedang membuat template form pilar',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        },
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Template formulir dinamis pilar berhasil disimpan.',
                confirmButtonColor: '#10B981'
            });
        },
        onError: () => {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Pastikan semua nama field dan pilar sudah diisi dengan benar.',
                confirmButtonColor: '#EF4444'
            });
        }
    });
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 p-6">
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md p-6">
            
            <div class="flex justify-between items-center border-b pb-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Buat Formulir Dinamis Pilar</h1>
                    <p class="text-sm text-gray-500">Rancang form administrasi khusus untuk sub-program pilar Lazismu</p>
                </div>
                <Link :href="route('pilar-form.index')" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    Kembali
                </Link>
            </div>

            <form @submit.prevent="submitForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Program Penyaluran</label>
                        <input v-model="form.nama_penyaluran" type="text" placeholder="Contoh: Beasiswa Mentari / Peduli Guru" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-orange-500 focus:ring-orange-500" required />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Pilar Lazismu</label>
                        <select v-model="form.pilar" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-orange-500 focus:ring-orange-500" required>
                            <option value="" disabled>-- Pilih Pilar --</option>
                            <option value="pendidikan">Pendidikan</option>
                            <option value="ekonomi">Ekonomi</option>
                            <option value="kesehatan">Kesehatan</option>
                            <option value="sosial_dakwah">Sosial Dakwah</option>
                            <option value="kemanusiaan">Kemanusiaan</option>
                            <option value="lingkungan">Lingkungan</option>
                        </select>
                    </div>
                </div>

                <hr class="my-6 border-gray-200" />

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-700">Rancangan Baris Inputan (Fields)</h3>
                        <button type="button" @click="tambahField" class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition flex items-center gap-1">
                            <span>➕ Tambah Inputan Baru</span>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div v-for="(item, index) in form.skema_form" :key="index" class="flex flex-wrap md:flex-nowrap items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-200 shadow-sm">
                            
                            <span class="font-bold text-gray-400 bg-white px-2 py-1 rounded border shadow-sm text-sm">#{{ index + 1 }}</span>

                            <div class="flex-1 min-w-[200px]">
                                <label class="block text-xs text-gray-500 mb-1">Nama Field / Syarat Dokumen</label>
                                <input v-model="item.field_name" type="text" placeholder="Contoh: Jurusan / Fotocopy KTP" class="w-full text-sm border-gray-300 rounded-md focus:border-orange-500 focus:ring-orange-500" required />
                            </div>

                            <div class="w-full md:w-48">
                                <label class="block text-xs text-gray-500 mb-1">Tipe Inputan</label>
                                <select v-model="item.type" class="w-full text-sm border-gray-300 rounded-md focus:border-orange-500 focus:ring-orange-500">
                                    <option value="text">Teks (Huruf/Kalimat)</option>
                                    <option value="number">Angka / Nominal</option>
                                    <option value="file">File Upload (PDF/Gambar)</option>
                                </select>
                            </div>

                            <div class="w-full md:w-32">
                                <label class="block text-xs text-gray-500 mb-1">Sifat</label>
                                <select v-model="item.required" class="w-full text-sm border-gray-300 rounded-md focus:border-orange-500 focus:ring-orange-500">
                                    <option :value="true">Wajib</option>
                                    <option :value="false">Opsional</option>
                                </select>
                            </div>

                            <div class="pt-5">
                                <button type="button" @click="hapusField(index)" class="bg-red-100 text-red-600 hover:bg-red-200 p-2 rounded-lg transition" title="Hapus Inputan">
                                    🗑️
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t pt-4 mt-6">
                    <button type="submit" :disabled="form.processing" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 py-2.5 rounded-lg shadow transition disabled:opacity-50">
                        💾 Simpan Struktur Formulir
                    </button>
                </div>
            </form>

        </div>
    </div>
</template>
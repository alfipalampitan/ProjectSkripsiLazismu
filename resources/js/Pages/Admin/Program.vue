<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2'; // Import SweetAlert2

const form = useForm({
    judul: '',
    kategori: 'Zakat',
    target_dana: '',
    deskripsi: '',
    gambar: null,
});

const submit = () => {
    form.post(route('programs.store'), {
        // Jika proses upload/simpan berhasil
        onSuccess: () => {
            // 1. Munculkan Notifikasi
            Swal.fire({
                title: 'Berhasil!',
                text: 'Program donasi baru telah ditambahkan.',
                icon: 'success',
                confirmButtonColor: '#f97316', // Warna orange-500 sesuai tema kamu
            });

            // 2. Kosongkan Form kembali
            form.reset(); 
            
            // Khusus untuk input file, kita harus reset manual elemen DOM-nya
            // karena value input file tidak bisa diikat (bind) dua arah oleh v-model
            document.querySelector('input[type="file"]').value = '';
        },
        onError: (errors) => {
            // Opsional: Munculkan pesan jika ada validasi yang gagal
            Swal.fire({
                title: 'Gagal!',
                text: 'Pastikan semua data terisi dengan benar.',
                icon: 'error',
            });
        }
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto p-6 bg-white rounded-2xl shadow-sm mt-10">
            <h2 class="text-2xl font-bold mb-6">Tambah Program Donasi Baru</h2>
            
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Judul Program</label>
                        <input v-model="form.judul" type="text" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select v-model="form.kategori" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="Zakat">Zakat</option>
                            <option value="Infaq">Infaq</option>
                            <option value="Qurban">Qurban</option>
                            <option value="Wakaf">Wakaf</option>
                            <option value="Pilar">Pilar</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Target Dana (Rp)</label>
                        <input v-model="form.target_dana" type="number" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea v-model="form.deskripsi" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Banner Program</label>
                        <input type="file" @input="form.gambar = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100" />
                    </div>

                    <button type="submit" :disabled="form.processing" class="bg-orange-500 text-white py-3 rounded-xl font-bold hover:bg-orange-600 transition">
                        Simpan Program
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>


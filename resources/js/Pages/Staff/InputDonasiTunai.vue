<script setup>
import StaffLayout from '@/Layouts/StaffLayout.vue'; // Sesuaikan dengan nama layout staffmu
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    programs: Array,
});

const form = useForm({
    user_name: '',
    amount: '',
    kategori: '',
    nomor_hp: '',
    email: '',
    program_id: '',
    keterangan: '',
});

const categories = ['Zakat', 'Infaq', 'Pilar', 'Qurban', 'Wakaf'];

// LOGIKA OTOMATIS: 
// Pantau kalau program_id berubah
watch(() => form.program_id, (newId) => {
    // Cari data program yang dipilih dari array props.programs
    const selectedProgram = props.programs.find(p => p.id === newId);

    if (selectedProgram) {
        // Set kategori form sesuai kategori milik program tersebut
        form.kategori = selectedProgram.kategori;
    }
});

const submit = () => {
    form.post(route('donasi-tunai.store'), {
        onBefore: () => {
            // Opsional: Tampilkan loading saat proses simpan
            Swal.fire({
                title: 'Mohon Tunggu',
                html: 'Sedang menyimpan data donasi...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
        },
        onSuccess: () => {
            form.reset();
            // Notifikasi Sukses
            Swal.fire({
                icon: 'success',
                title: 'Alhamdulillah!',
                text: 'Donasi tunai telah berhasil dicatat ke dalam sistem.',
                confirmButtonColor: '#f97316', // Warna orange sesuai tema
                confirmButtonText: 'Mantap',
                timer: 3000, // Hilang otomatis dalam 3 detik
                timerProgressBar: true,
            });
        },
        onError: (errors) => {
            // Notifikasi jika ada error validasi
            Swal.fire({
                icon: 'error',
                title: 'Waduh...',
                text: 'Ada kesalahan saat menginput data. Cek kembali form Anda.',
                confirmButtonColor: '#f97316',
            });
        }
    });
};
</script>

<template>

    <Head title="Input Donasi Tunai" />

    <StaffLayout>
        <div class="max-w-4xl mx-auto py-8 px-4">
            <!-- Header Halaman -->
            <div class="mb-8">
                <h1 class="text-3xl font-black text-gray-800 tracking-tight">Penerimaan Donasi Tunai</h1>
                <p class="text-gray-500 font-medium">Input manual untuk donasi yang diterima secara langsung
                    (Cash/Offline).</p>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Sisi Kiri: Form Utama -->
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 space-y-5">

                        <!-- Nama Donatur -->
                        <div>
                            <label class="text-xs font-black uppercase text-gray-400 ml-2 tracking-widest">Nama
                                Donatur</label>
                            <input v-model="form.user_name" type="text"
                                placeholder="Masukkan nama donatur (Hamba Allah)"
                                class="w-full mt-1 px-6 py-4 rounded-2xl bg-gray-50 border-none focus:ring-2 focus:ring-orange-500 transition-all outline-none text-gray-700 font-semibold"
                                required>
                            <div v-if="form.errors.user_name" class="text-red-500 text-xs mt-1 ml-2">{{
                                form.errors.user_name }}</div>
                        </div>

                        <div>
                            <label class="text-xs font-black uppercase text-gray-400 ml-2">Pilih Program</label>
                            <select v-model="form.program_id"
                                class="w-full mt-1 px-6 py-4 rounded-2xl bg-gray-50 border-none focus:ring-2 focus:ring-orange-500 font-semibold"
                                required>
                                <option value="" disabled>-- Pilih Program --</option>
                                <option v-for="program in programs" :key="program.id" :value="program.id">
                                    {{ program.judul }}
                                </option>
                            </select>
                            <div v-if="form.errors.program_id" class="text-red-500 text-xs mt-1">{{
                                form.errors.program_id }}</div>
                        </div>

                        <!-- Nominal & Kategori -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-black uppercase text-gray-400 ml-2 tracking-widest">Nominal
                                    Donasi (Rp)</label>
                                <input v-model="form.amount" type="number" placeholder="Contoh: 100000"
                                    class="w-full mt-1 px-6 py-4 rounded-2xl bg-gray-50 border-none focus:ring-2 focus:ring-orange-500 transition-all outline-none text-gray-700 font-bold"
                                    required>
                            </div>
                            <!-- Tampilan Kategori (ReadOnly) -->
                            <div>
                                <label class="text-xs font-black uppercase text-gray-400 ml-2">Kategori
                                    Terdeteksi</label>
                                <div
                                    class="w-full mt-1 px-6 py-4 rounded-2xl bg-gray-100 text-gray-500 font-bold border-2 border-dashed border-gray-200">
                                    {{ form.kategori || 'Pilih program dulu...' }}
                                </div>
                                <!-- Hidden input supaya kategori tetap terkirim saat form submit -->
                                <input type="hidden" v-model="form.kategori">
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div>
                            <label class="text-xs font-black uppercase text-gray-400 ml-2 tracking-widest">Keterangan /
                                Pesan</label>
                            <textarea v-model="form.keterangan" rows="3"
                                placeholder="Contoh: Donasi untuk yatim piatu..."
                                class="w-full mt-1 px-6 py-4 rounded-2xl bg-gray-50 border-none focus:ring-2 focus:ring-orange-500 transition-all outline-none text-gray-700"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Sisi Kanan: Kontak & Submit -->
                <div class="space-y-6">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 space-y-5">
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-tighter">Kontak Donatur
                            (Opsional)</h3>

                        <div>
                            <label class="text-[10px] font-black uppercase text-gray-400 ml-2">WhatsApp / HP</label>
                            <input v-model="form.nomor_hp" type="text" placeholder="0812..."
                                class="w-full mt-1 px-5 py-3 rounded-xl bg-gray-50 border-none focus:ring-2 focus:ring-orange-500 text-sm">
                        </div>

                        <div>
                            <label class="text-[10px] font-black uppercase text-gray-400 ml-2">Email</label>
                            <input v-model="form.email" type="email" placeholder="example@mail.com"
                                class="w-full mt-1 px-5 py-3 rounded-xl bg-gray-50 border-none focus:ring-2 focus:ring-orange-500 text-sm">
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="space-y-3">
                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-orange-500 hover:bg-orange-600 text-white font-black py-5 rounded-[2rem] shadow-xl shadow-orange-100 transition-all active:scale-95 flex items-center justify-center gap-3 disabled:opacity-50">
                            <i class="fa-solid fa-cloud-arrow-up text-xl"></i>
                            SIMPAN DATA
                        </button>

                        <button type="button" @click="form.reset()"
                            class="w-full bg-gray-100 hover:bg-gray-200 text-gray-500 font-bold py-4 rounded-[2rem] transition-all text-sm">
                            Reset Form
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </StaffLayout>
</template>

<style scoped>
/* Menghilangkan spin button pada input number */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
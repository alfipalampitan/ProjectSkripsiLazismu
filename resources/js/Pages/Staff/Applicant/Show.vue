<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    applicant: Object,
    programs: Array
});

// Penarikan Nama Pemohon khusus untuk teks otomatis di form pencairan dana
const namaPemohon = computed(() => props.applicant.biodata_dinamis?.['Nama'] || props.applicant.biodata_dinamis?.['Nama Pemohon'] || 'Tanpa Nama');

const disburseForm = useForm({
    program_id: '',
    amount: props.applicant.biodata_dinamis?.['Nominal']?.replace(/[^0-9]/g, '') || '',
    judul_pengeluaran: `Pencairan ${props.applicant.pilar_form?.nama_penyaluran} an. ${namaPemohon.value}`,
    keterangan: ''
});

const submitPencairan = () => {
    disburseForm.post(route('staff.applicants.disburse', props.applicant.id), {
        onStart: () => { Swal.fire({ title: 'Memproses Otorisasi...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } }); },
        onSuccess: () => { Swal.fire({ icon: 'success', title: 'Verifikasi Berhasil', text: 'Dana kas berhasil dialokasikan.', confirmButtonColor: '#F97316' }); },
    });
};
</script>

<template>
    <div class="min-h-screen bg-slate-100 p-8 font-sans antialiased text-slate-900 tracking-normal text-base">
        <div class="max-w-7xl mx-auto space-y-6">

            <div
                class="bg-white rounded-xl border border-slate-300 p-6 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-left w-full">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">LEMBAR VERIFIKASI BERKAS
                        </h1>

                        <span
                            class="text-xs font-black bg-amber-500 text-white px-3 py-1 rounded uppercase tracking-widest border border-amber-600 shadow-sm animate-pulse"
                            v-if="applicant.status_permohonan === 'pending'">
                            {{ applicant.status_permohonan }}
                        </span>
                        <span
                            class="text-xs font-black bg-slate-900 text-white px-3 py-1 rounded uppercase tracking-widest border border-slate-950 shadow-sm"
                            v-else>
                            {{ applicant.status_permohonan }}
                        </span>
                    </div>
                    <p class="text-sm font-mono text-slate-500 font-bold mt-1">Nomor Registrasi Sistem: {{
                        applicant.nomor_permohonan }}</p>
                </div>
                <Link :href="route('staff.applicants.index')"
                    class="bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm px-6 py-3 rounded border border-slate-900 shadow transition w-full md:w-auto text-center uppercase tracking-wider shrink-0">
                    Kembali Ke Daftar Log
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <div class="lg:col-span-8 space-y-6">

                    <div class="bg-white rounded-xl border border-slate-300 p-6 shadow-sm space-y-4">
                        <div class="border-b-2 border-slate-200 pb-2">
                            <h2 class="text-base font-black text-slate-800 uppercase tracking-wide">I. REKAPAN DATA DAN
                                ATRIBUT ISIAN FORMULIR</h2>
                        </div>

                        <div v-if="Object.keys(applicant.biodata_dinamis || {}).length > 0"
                            class="overflow-hidden border border-slate-300 rounded">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-slate-800 text-white text-xs font-bold uppercase tracking-wider">
                                    <tr class="divide-x divide-slate-700">
                                        <th class="px-5 py-3.5 w-1/3">Nama Atribut / Borang</th>
                                        <th class="px-5 py-3.5 w-2/3">Isian Data Rekaman Hasil Input</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-300 text-sm font-semibold text-slate-700">
                                    <tr v-for="(val, key) in applicant.biodata_dinamis" :key="key"
                                        class="divide-x divide-slate-300 hover:bg-slate-50 transition">
                                        <td
                                            class="px-5 py-4 bg-slate-50/70 font-bold text-slate-600 uppercase text-xs tracking-wide">
                                            {{ key }}</td>
                                        <td
                                            class="px-5 py-4 font-black text-slate-900 text-base font-mono bg-white uppercase">
                                            {{ val }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center p-8 bg-slate-50 rounded border border-dashed border-slate-300" v-else>
                            <p class="text-sm text-slate-400 font-bold italic">Tidak ditemukan rekaman parameter atribut
                                pada permohonan ini.</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-slate-300 p-6 shadow-sm space-y-4">
                        <div class="border-b-2 border-slate-200 pb-2">
                            <h2 class="text-base font-black text-slate-800 uppercase tracking-wide">II. LAMPIRAN BUKTI
                                DOKUMEN FISIK</h2>
                        </div>

                        <div v-if="Object.keys(applicant.berkas_dinamis || {}).length > 0"
                            class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-for="(fileName, fieldName) in applicant.berkas_dinamis" :key="fieldName"
                                class="border border-slate-300 rounded p-4 bg-slate-50 space-y-4 shadow-inner">
                                <div class="border-b border-slate-300 pb-2 text-center">
                                    <span class="text-sm font-black text-slate-800 uppercase tracking-wide">{{ fieldName
                                        }}</span>
                                </div>

                                <div
                                    class="w-full h-64 bg-slate-200 rounded border border-slate-300 overflow-hidden shadow-sm">
                                    <img :src="`/storage/permohonan/${fileName}`" class="w-full h-full object-cover"
                                        alt="Preview Berkas">
                                </div>

                                <a :href="`/storage/permohonan/${fileName}`" target="_blank"
                                    class="block text-center bg-white hover:bg-orange-500 text-slate-800 hover:text-white font-bold py-3 rounded border border-slate-300 hover:border-orange-600 shadow-sm text-xs uppercase tracking-wider transition">
                                    Lihat File Ukuran Penuh 🔍
                                </a>
                            </div>
                        </div>
                        <div class="text-center p-8 bg-slate-50 rounded border border-dashed border-slate-300" v-else>
                            <p class="text-sm text-slate-400 font-bold italic">Tidak ada lampiran gambar/file berkas
                                fisik pada syarat ini.</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div
                        class="bg-white rounded-xl border border-slate-300 p-6 shadow-md sticky top-6 border-t-[10px] border-orange-500 space-y-5">
                        <div class="border-b border-slate-200 pb-2">
                            <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight">Otorisasi Finansial
                            </h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Pencatatan
                                Keluar Buku Kas</p>
                        </div>

                        <div v-if="applicant.status_permohonan !== 'pending'"
                            class="p-4 rounded text-center font-bold text-sm bg-slate-100 border border-slate-300 text-slate-600 uppercase tracking-wide">
                            🔒 Dokumen Terkunci: {{ applicant.status_permohonan }}
                        </div>

                        <form @submit.prevent="submitPencairan" class="space-y-4 text-sm font-semibold" v-else>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Program
                                    Alokasi Kas</label>
                                <select v-model="disburseForm.program_id"
                                    class="w-full text-sm border-slate-300 rounded bg-slate-50 p-3 font-bold text-slate-700 focus:border-orange-500 outline-none"
                                    required>
                                    <option value="" disabled>-- Pilih Kas Donasi --</option>
                                    <option v-for="prog in programs" :key="prog.id" :value="prog.id">{{ prog.judul }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Nominal
                                    Cair Aktual</label>
                                <div class="relative rounded shadow-sm">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 font-black text-slate-400">Rp</span>
                                    <input v-model="disburseForm.amount" type="number"
                                        class="w-full text-base border-slate-300 rounded bg-slate-50 p-3 pl-10 font-black text-slate-900 focus:border-orange-500 outline-none"
                                        required />
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Keterangan
                                    Memo Internal</label>
                                <textarea v-model="disburseForm.keterangan" rows="3"
                                    class="w-full text-sm border-slate-300 rounded bg-slate-50 p-3 font-medium text-slate-700 focus:border-orange-500 outline-none"
                                    placeholder="Alasan disposisi persetujuan dana..."></textarea>
                            </div>

                            <button type="submit"
                                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-black py-4 rounded shadow hover:shadow-md transition uppercase tracking-widest text-xs">
                                📑 Eksekusi & Cairkan Kas
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
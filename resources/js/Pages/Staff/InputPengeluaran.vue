<script setup>
import StaffLayout from '@/Layouts/StaffLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { watch } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({ programs: Array });

const form = useForm({
    sifat_pengeluaran: 'terikat', // Pilihan: 'terikat' atau 'tidak_terikat'
    judul_pengeluaran: '',
    program_id: '', // Null jika tidak terikat
    amount: '',
    pilar_terkait: 'Kemanusiaan',
    keterangan: '',
    applicant_id: null,
});

// Otomatis kosongkan program_id jika staff mengubah sifat menjadi tidak terikat
watch(() => form.sifat_pengeluaran, (newVal) => {
    if (newVal === 'tidak_terikat') {
        form.program_id = '';
    }
});

const submit = () => {
    form.post(route('pengeluaran.store'), {
        onStart: () => {
            Swal.fire({
                title: 'Memproses Pembukuan...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        },
        onSuccess: () => {
            form.reset();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Log pengeluaran berhasil dicatat sesuai sifat dana.',
                confirmButtonColor: '#F97316'
            });
        },
    });
};
</script>

<template>
    <Head title="Input Pengeluaran" />
    <StaffLayout>
        <div class="max-w-3xl mx-auto py-10 px-4">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div class="border-b border-slate-100 pb-3 mb-6">
                    <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">Form Penyaluran & Pengeluaran Kas</h2>
                    <p class="text-xs text-slate-400 font-semibold uppercase mt-0.5">Pencatatan distribusi dana terikat program maupun operasional amil</p>
                </div>
                
                <form @submit.prevent="submit" class="space-y-5 font-sans text-sm font-semibold">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wide">Sifat Alokasi Pengeluaran</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label :class="`flex flex-col p-3 border rounded-lg cursor-pointer transition ${form.sifat_pengeluaran === 'terikat' ? 'border-orange-500 bg-orange-50/50' : 'border-slate-200 bg-white'}`">
                                <div class="flex items-center gap-2">
                                    <input type="radio" v-model="form.sifat_pengeluaran" value="terikat" class="text-orange-500 focus:ring-orange-500">
                                    <span class="font-black text-slate-800 uppercase text-xs">Dana Terikat</span>
                                </div>
                                <span class="text-[11px] text-slate-400 font-medium mt-1">Dipotong langsung dari saldo spesifik program donasi terkait.</span>
                            </label>

                            <label :class="`flex flex-col p-3 border rounded-lg cursor-pointer transition ${form.sifat_pengeluaran === 'tidak_terikat' ? 'border-red-500 bg-red-50/50' : 'border-slate-200 bg-white'}`">
                                <div class="flex items-center gap-2">
                                    <input type="radio" v-model="form.sifat_pengeluaran" value="tidak_terikat" class="text-red-500 focus:ring-red-500">
                                    <span class="font-black text-slate-800 uppercase text-xs">Dana Tidak Terikat</span>
                                </div>
                                <span class="text-[11px] text-slate-400 font-medium mt-1">Diambil dari Kas Umum / Operasional Amil fleksibel.</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase">Judul Pengeluaran / Kegiatan</label>
                        <input type="text" v-model="form.judul_pengeluaran" class="w-full border-slate-300 rounded focus:border-orange-500 p-2.5 bg-slate-50 font-bold" placeholder="Contoh: Pembelian Ambulans Baru / Honor Da'i Pedalaman" required>
                    </div>

                    <div v-if="form.sifat_pengeluaran === 'terikat'">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase">Sumber Dompet Program (Dana Terikat)</label>
                        <select v-model="form.program_id" class="w-full border-slate-300 rounded focus:border-orange-500 p-2.5 bg-slate-50 font-bold" :required="form.sifat_pengeluaran === 'terikat'">
                            <option value="" disabled>-- Pilih Dompet Program Berorientasi Terikat --</option>
                            <option v-for="prog in programs" :key="prog.id" :value="prog.id">{{ prog.judul }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase">Nominal Uang Keluar</label>
                        <div class="relative rounded shadow-sm">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 font-black text-slate-400">Rp</span>
                            <input type="number" v-model="form.amount" class="w-full border-slate-300 rounded focus:border-orange-500 p-2.5 pl-9 bg-slate-50 font-black text-slate-900 text-base" placeholder="0" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase">Pilar Kategori Laporan bulanan</label>
                        <select v-model="form.pilar_terkait" class="w-full border-slate-300 rounded focus:border-orange-500 p-2.5 bg-slate-50 font-bold">
                            <option value="Pendidikan">Pendidikan</option>
                            <option value="Ekonomi">Ekonomi</option>
                            <option value="Kemanusiaan">Kemanusiaan</option>
                            <option value="Kesehatan">Kesehatan</option>
                            <option value="Dakwah">Dakwah</option>
                            <option value="Lingkungan">Lingkungan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase">Keterangan Tambahan / Memo</label>
                        <textarea v-model="form.keterangan" class="w-full border-slate-300 rounded focus:border-orange-500 p-2.5 bg-slate-50 font-medium" rows="3" placeholder="Nota transaksi internal..."></textarea>
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full bg-slate-900 hover:bg-slate-800 text-white py-3 rounded font-black uppercase tracking-widest text-xs transition shadow">
                        {{ form.processing ? 'Menyimpan Buku Kas...' : '💾 Eksekusi & Catat Pengeluaran' }}
                    </button>
                </form>
            </div>
        </div>
    </StaffLayout>
</template>
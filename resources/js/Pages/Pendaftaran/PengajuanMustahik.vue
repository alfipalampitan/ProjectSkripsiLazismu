<script setup>
import { ref, watch } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

// 1. Menerima skema form pilar yang sudah dibuat oleh staff
const props = defineProps({
    pilarForms: Array
});

const formTerpilih = ref(null);

// Form helper utama hanya memegang ID template pilar, sisanya ditampung di objek berkas_dinamis
const form = useForm({
    pilar_form_id: '',
    berkas_dinamis: {} 
});

// SENSOR GARING & KARAKTER SPESIAL: Disamakan 100% dengan Str::slug milik Laravel
const slugify = (text) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '') // Buang total garing (/) dan karakter aneh lainnya
        .replace(/[\s-]+/g, '_')     // Spasi dan minus diganti satu underscore
        .replace(/^_+|_+$/g, '');    // Bersihkan ujungnya
};

// Fungsi memformat ketikan nominal agar otomatis memunculkan pemisah titik (.)
const formatRupiah = (inputan, fieldName) => {
    let value = inputan.replace(/[^0-9]/g, ''); // Buang huruf/simbol
    const key = slugify(fieldName);
    
    if (value) {
        // Beri titik setiap 3 digit angka
        form.berkas_dinamis[key] = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    } else {
        form.berkas_dinamis[key] = '';
    }
};

// 2. Watcher: Membentuk ulang inputan secara realtime saat jenis program dipilih
watch(() => form.pilar_form_id, (newId) => {
    const pencarian = props.pilarForms.find(f => f.id === newId);
    if (pencarian) {
        formTerpilih.value = pencarian;
        form.berkas_dinamis = {}; // Reset data lama
        
        // Buat struktur inputan murni mengikuti skema form pilar dari database
        pencarian.skema_form.forEach(field => {
            const key = slugify(field.field_name);
            form.berkas_dinamis[key] = field.type === 'file' ? null : '';
        });
    } else {
        formTerpilih.value = null;
        form.berkas_dinamis = {};
    }
});

// Handle input file dinamis
const handleFileChange = (fieldName, event) => {
    const key = slugify(fieldName);
    form.berkas_dinamis[key] = event.target.files[0];
};

// 3. Submit Data Pengajuan
const submitPengajuan = () => {
    // Flatten / ratakan struktur objek agar bisa dikirim sebagai Multipart (mendukung file upload)
    const payload = {
        pilar_form_id: form.pilar_form_id,
        ...form.berkas_dinamis 
    };

    const uploadForm = useForm(payload);

    uploadForm.post(route('pengajuan-mustahik.store'), {
        onStart: () => {
            Swal.fire({
                title: 'Mengirim Pengajuan...',
                text: 'Sedang mengunggah berkas administrasi Anda ke sistem Lazismu.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        },
        onSuccess: (page) => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Terkirim!',
                text: page.props.flash?.success || 'Pengajuan Anda berhasil disimpan.',
                confirmButtonColor: '#10B981'
            });
            form.reset();
            formTerpilih.value = null;
        },
        onError: (errors) => {
            console.error(errors);
            Swal.fire({
                icon: 'error',
                title: 'Gagal Mengirim',
                text: 'Periksa kembali isian Anda. Pastikan semua dokumen wajib telah diunggah.',
                confirmButtonColor: '#EF4444'
            });
        }
    });
};

const formatPilar = (text) => {
    return text.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};
</script>

<template>
    <Head title="Formulir Pengajuan Mustahik" />

    <div class="min-h-screen bg-gradient-to-br from-orange-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            
            <div class="bg-gradient-to-r from-orange-500 to-amber-600 p-6 text-white text-center sm:text-left flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight">Formulir Pengajuan Dana Mustahik</h2>
                    <p class="text-orange-100 text-sm mt-1">Layanan pendaftaran bantuan dana pilar Lazismu secara mandiri.</p>
                </div>
                <div class="bg-white/20 px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider backdrop-blur-sm">
                    Portal Mustahik
                </div>
            </div>

            <form @submit.prevent="submitPengajuan" class="p-6 sm:p-8 space-y-6">
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Pilih Jenis Program Bantuan / Pilar <span class="text-red-500">*</span></label>
                    <select v-model="form.pilar_form_id" class="w-full border-gray-300 rounded-xl shadow-sm focus:border-orange-500 focus:ring-orange-500 transition text-sm py-3" required>
                        <option value="" disabled>-- Klik untuk melihat daftar program bantuan yang tersedia --</option>
                        <option v-for="pilar in pilarForms" :key="pilar.id" :value="pilar.id">
                            [{{ formatPilar(pilar.pilar) }}] - {{ pilar.nama_penyaluran }}
                        </option>
                    </select>
                </div>

                <div v-if="formTerpilih" class="bg-gray-50 p-6 rounded-2xl border border-gray-200 space-y-5 animate-fade-in">
                    <div class="border-b pb-3 mb-2">
                        <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            📋 Syarat Administrasi: <span class="text-orange-600 font-extrabold">{{ formTerpilih.nama_penyaluran }}</span>
                        </h3>
                        <p class="text-xs text-gray-400 mt-1">Silakan lengkapi seluruh field dan berkas khusus yang dipersyaratkan di bawah ini.</p>
                    </div>

                    <div v-for="field in formTerpilih.skema_form" :key="field.field_name" class="w-full">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            {{ field.field_name }} 
                            <span v-if="field.required" class="text-red-500" title="Wajib Diisi">*</span>
                            <span v-else class="text-xs text-gray-400 font-normal ml-1">(Opsional)</span>
                        </label>

                        <div v-if="field.type === 'nominal'" class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm">Rp</span>
                            </div>
                            <input 
                                type="text" 
                                v-model="form.berkas_dinamis[slugify(field.field_name)]"
                                @input="formatRupiah($event.target.value, field.field_name)"
                                placeholder="0"
                                class="w-full border-gray-300 rounded-xl pl-9 shadow-sm focus:border-orange-500 focus:ring-orange-500 transition text-sm"
                                :required="field.required" 
                            />
                        </div>

                        <input v-else-if="field.type === 'tel'"
                               type="tel"
                               v-model="form.berkas_dinamis[slugify(field.field_name)]"
                               placeholder="Contoh: 08123456789"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               class="w-full border-gray-300 rounded-xl shadow-sm focus:border-orange-500 focus:ring-orange-500 transition text-sm"
                               :required="field.required" />

                        <div v-else-if="field.type === 'file'" class="w-full">
                            <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-white hover:bg-gray-50 transition p-2 relative group">
                                <div class="flex flex-col items-center justify-center pt-2 pb-2">
                                    <p class="text-xs text-gray-500"><span class="font-semibold text-orange-500">Klik untuk cari file</span> atau drag berkas ke sini</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">Format valid: PDF, JPG, PNG (Maks. 2MB)</p>
                                </div>
                                <input type="file" 
                                       class="hidden" 
                                       @change="handleFileChange(field.field_name, $event)" 
                                       :required="field.required" 
                                       accept=".pdf,.jpg,.jpeg,.png" />
                            </label>
                            <p v-if="form.berkas_dinamis[slugify(field.field_name)]" class="text-xs text-emerald-600 font-semibold mt-1.5 flex items-center gap-1">
                                📎 Berkas Terpilih: {{ form.berkas_dinamis[slugify(field.field_name)].name }}
                            </p>
                        </div>

                        <input v-else
                               type="text"
                               v-model="form.berkas_dinamis[slugify(field.field_name)]"
                               :placeholder="'Masukkan ' + field.field_name + '...'"
                               class="w-full border-gray-300 rounded-xl shadow-sm focus:border-orange-500 focus:ring-orange-500 transition text-sm"
                               :required="field.required" />
                        
                        <p v-if="form.errors[slugify(field.field_name)]" class="text-xs text-red-500 mt-1">
                            {{ form.errors[slugify(field.field_name)] }}
                        </p>
                    </div>
                </div>

                <div v-if="formTerpilih" class="flex justify-end pt-4 border-t">
                    <button type="submit" :disabled="form.processing" class="w-full bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white font-bold px-8 py-3.5 rounded-xl shadow-md transition transform active:scale-95 disabled:opacity-50">
                        🚀 Kirim Berkas Pengajuan Mandiri
                    </button>
                </div>

            </form>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
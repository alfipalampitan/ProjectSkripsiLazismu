<script setup>
import StaffLayout from '@/Layouts/StaffLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';

// 1. Terima semua props gabungan dari controller backend
const props = defineProps({
    applicants: Array,       // Seluruh list permohonan dana masuk dari database
    programs: Array,         // List dompet program terikat (untuk form dan tabel dinamis)
    kasUmumList: Array,          // List saldo kas umum live
    stats: Object,           // Angka rekap atas: total_saldo, total_terikat, total_bebas
    pilarStats: Object,      // Total nominal pengeluaran bulanan per pilar lazismu
});

// State reaktif untuk memegang baris data aktif (Bisa Mustahik atau Program)
const selectedApplicant = ref(null);
const selectedProgram = ref(null);

const form = useForm({
    sifat_pengeluaran: 'terikat', // Default awal diubah ke terikat sesuai preferensi interaksi baru
    judul_pengeluaran: '',
    program_id: '',
    kategori_dana_umum: '',
    amount: '',
    pilar_terkait: 'Kemanusiaan',
    keterangan: '',
    applicant_id: null,
});

// ================= HANDLER MODE 🌐 TIDAK TERIKAT (MUSTAHIK) =================
const selectApplicant = (item) => {
    selectedApplicant.value = item;
    selectedProgram.value = null; // Reset sebelah

    form.applicant_id = item.id;
    form.program_id = '';
    form.pilar_terkait = item.pilar_form?.pilar || 'Kemanusiaan';

    const namaPemohon = item.biodata_dinamis?.['Nama'] || item.biodata_dinamis?.['Nama Pemohon'] || 'Mustahik';
    form.judul_pengeluaran = `Pencairan Dana Pilar ${formatPilar(form.pilar_terkait)} an. ${namaPemohon}`;
    form.amount = '';
};

// ================= HANDLER MODE 💼 TERIKAT PROGRAM (CAMPAIGN) =================
const selectProgram = (program) => {
    selectedProgram.value = program;
    selectedApplicant.value = null; // Reset sebelah

    form.program_id = program.id;
    form.applicant_id = null; // Tidak lewat permohonan individu
    form.pilar_terkait = program.pilar || 'Kemanusiaan'; // Sesuaikan pilar program jika ada di DB

    form.judul_pengeluaran = `Distribusi Penyaluran Dana Program: ${program.judul}`;
    form.amount = '';
};

// Reset otomatis data pilihan & form saat user mengganti Sifat Dana
watch(() => form.sifat_pengeluaran, (newVal) => {
    selectedApplicant.value = null;
    selectedProgram.value = null;
    form.judul_pengeluaran = '';
    form.amount = '';
    form.keterangan = '';

    if (newVal === 'tidak_terikat') {
        form.program_id = '';
    } else {
        form.kategori_dana_umum = '';
        form.applicant_id = null;
    }
});

const getStatusBadge = (status) => {
    switch (status?.toLowerCase()) {
        case 'pending': return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'disetujui': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'ditolak': return 'bg-red-100 text-red-700 border-red-200';
        default: return 'bg-gray-100 text-gray-700 border-gray-200';
    }
};

const formatPilar = (text) => {
    if (!text) return 'Umum';
    return text.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const submit = () => {
    // Validasi Penguncian Berdasarkan Sifat Dana di Sisi Client
    if (form.sifat_pengeluaran === 'tidak_terikat' && !form.applicant_id) {
        Swal.fire({
            icon: 'warning',
            title: 'Otorisasi Ditolak',
            text: 'Silakan pilih & periksa berkas salah satu pemohon (mustahik) pada tabel terlebih dahulu.'
        });
        return;
    }

    if (form.sifat_pengeluaran === 'terikat' && !form.program_id) {
        Swal.fire({
            icon: 'warning',
            title: 'Otorisasi Ditolak',
            text: 'Silakan pilih salah satu program kerja aktif di tabel kiri terlebih dahulu.'
        });
        return;
    }

    // Koreksi data sebelum dikirim agar ramah terhadap Validasi Laravel
    if (form.sifat_pengeluaran === 'tidak_terikat') {
        form.program_id = null; // Ubah "" menjadi null murni
    } else {
        form.kategori_dana_umum = null;
        form.applicant_id = null;
    }

    // Pastikan pilar menggunakan format huruf kapital di awal (PascalCase) agar grafik terbaca
    if (form.pilar_terkait) {
        form.pilar_terkait = form.pilar_terkait.charAt(0).toUpperCase() + form.pilar_terkait.slice(1).toLowerCase();
    }

    // Ganti target post ke 'pengeluaran.store' sesuai web.php Anda
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
            selectedApplicant.value = null;
            selectedProgram.value = null;
            Swal.fire({
                icon: 'success',
                title: 'Transaksi Berhasil',
                text: 'Sistem berhasil mengalokasikan saldo, mencatatkan buku kas, dan memperbarui status.',
                confirmButtonColor: '#F97316'
            });
        },
        onError: (errors) => {
            Swal.close();
            console.log("Error Validasi:", errors);

            let errorText = 'Terjadi kesalahan sistem, periksa kembali inputan Anda.';
            if (errors.amount) errorText = errors.amount;
            else if (errors.kategori_dana_umum) errorText = errors.kategori_dana_umum;
            else if (errors.program_id) errorText = errors.program_id;

            Swal.fire({
                icon: 'error',
                title: 'Gagal Memproses',
                text: errorText,
                confirmButtonColor: '#EF4444'
            });
        }
    });
};
</script>

<template>

    <Head title="Verifikasi & Pengeluaran Kas" />
    <StaffLayout>
        <div
            class="p-6 bg-slate-50 min-h-screen font-sans text-xs sm:text-sm selection:bg-orange-500 selection:text-white space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div
                    class="bg-gradient-to-br from-slate-900 to-slate-800 p-5 rounded-xl text-white shadow-sm relative overflow-hidden">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Saldo Live Kas</p>
                    <p class="text-2xl font-black mt-1">Rp{{ stats?.total_saldo?.toLocaleString('id-ID') ?? '0' }}</p>
                    <div class="absolute right-3 bottom-2 text-3xl opacity-10">💰</div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm border-l-4 border-l-orange-500">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Pengeluaran Terikat
                    </p>
                    <p class="text-2xl font-black text-slate-800 mt-1">Rp{{
                        stats?.total_terikat?.toLocaleString('id-ID') ?? '0' }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm border-l-4 border-l-red-500">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Pengeluaran Tidak
                        Terikat</p>
                    <p class="text-2xl font-black text-slate-800 mt-1">Rp{{ stats?.total_bebas?.toLocaleString('id-ID')
                        ?? '0' }}</p>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <div>
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">📊 Rincian Kluster
                        Pengeluaran Kas Per Pilar</h3>
                    <p class="text-[11px] text-slate-400 font-medium mt-0.5">Total akumulasi distribusi pendayagunaan
                        dana yang sukses dibukukan internal</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div v-for="(pilarLabel, key) in { Pendidikan: '🎓 Pendidikan', Ekonomi: '💼 Ekonomi', Kesehatan: '❤️ Kesehatan', Kemanusiaan: '🤝 Kemanusiaan', Dakwah: '🕌 Dakwah', Lingkungan: '🌿 Lingkungan' }"
                        :key="key"
                        class="p-3 rounded-xl border border-slate-100 bg-slate-50/70 flex flex-col justify-between shadow-sm">
                        <span
                            class="px-2 py-0.5 bg-slate-200 text-slate-800 font-extrabold text-[9px] rounded uppercase self-start">{{
                                pilarLabel }}</span>
                        <p class="text-xs font-black text-slate-800 mt-2">Rp{{
                            pilarStats?.[key]?.toLocaleString('id-ID') ?? '0' }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

                <div class="lg:col-span-2 space-y-6">

                    <div v-if="form.sifat_pengeluaran === 'terikat'"
                        class="bg-white rounded-xl shadow-md p-6 border border-slate-200 animate-fade-in">
                        <div class="border-b pb-4 mb-6">
                            <h1 class="text-xl font-black text-slate-900 uppercase tracking-tight text-orange-600">💼
                                Monitoring & Pencairan Program Kerja</h1>
                            <p class="text-xs text-slate-400 font-semibold uppercase mt-0.5">Pilih salah satu
                                program/campaign aktif di bawah ini untuk mendistribusikan anggaran keuangan khusus</p>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
                            <table class="w-full text-sm text-left text-slate-500">
                                <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                                    <tr class="font-bold">
                                        <th class="px-4 py-3.5 text-center w-12">No</th>
                                        <th class="px-4 py-3.5">Nama Program Kerja</th>
                                        <th class="px-4 py-3.5 text-right">Saldo Terkumpul</th>
                                        <th class="px-4 py-3.5 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white text-xs font-semibold">
                                    <tr v-for="(prog, index) in programs" :key="prog.id"
                                        :class="`hover:bg-slate-50 transition cursor-pointer ${selectedProgram?.id === prog.id ? 'bg-orange-50/60 font-bold' : ''}`"
                                        @click="selectProgram(prog)">
                                        <td class="px-4 py-4 text-center font-bold text-slate-900">{{ index + 1 }}</td>
                                        <td class="px-4 py-4 text-slate-800 font-black uppercase tracking-tight">
                                            {{ prog.judul }}
                                        </td>
                                        <td class="px-4 py-4 text-right text-emerald-600 font-bold font-mono text-sm">
                                            Rp{{ prog.terkumpul?.toLocaleString('id-ID') ?? '0' }}
                                        </td>
                                        <td class="px-4 py-4 text-center" @click.stop>
                                            <button @click="selectProgram(prog)"
                                                class="inline-flex items-center gap-1 bg-orange-500 hover:bg-orange-600 text-white font-black px-3 py-1.5 rounded text-[10px] uppercase transition shadow-sm">
                                                🎯 Pilih Program
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="programs.length === 0">
                                        <td colspan="4"
                                            class="px-6 py-12 text-center text-slate-400 font-bold uppercase bg-slate-50">
                                            📂 Belum ada program kerja terikat yang terdaftar.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-else class="bg-white rounded-xl shadow-md p-6 border border-slate-200 animate-fade-in">
                        <div class="border-b pb-4 mb-6">
                            <h1 class="text-xl font-black text-slate-900 uppercase tracking-tight text-blue-600">🌐
                                Verifikasi Permohonan Dana Pilar (Mustahik)</h1>
                            <p class="text-xs text-slate-400 font-semibold uppercase mt-0.5">Daftar asnaf mustahik
                                perorangan/lembaga yang masuk ke dalam antrean verifikasi dana pilar bebas</p>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm mb-6">
                            <table class="w-full text-sm text-left text-slate-500">
                                <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                                    <tr class="font-bold">
                                        <th class="px-4 py-3.5 text-center w-12">No</th>
                                        <th class="px-4 py-3.5">No. Permohonan</th>
                                        <th class="px-4 py-3.5">Nama Pemohon</th>
                                        <th class="px-4 py-3.5">Program & Pilar</th>
                                        <th class="px-4 py-3.5 text-center">Status</th>
                                        <th class="px-4 py-3.5 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white text-xs font-semibold">
                                    <tr v-for="(item, index) in applicants" :key="item.id"
                                        :class="`hover:bg-slate-50 transition cursor-pointer ${selectedApplicant?.id === item.id ? 'bg-blue-50/40 font-bold' : ''}`"
                                        @click="selectApplicant(item)">
                                        <td class="px-4 py-4 text-center font-bold text-slate-900">{{ index + 1 }}</td>
                                        <td class="px-4 py-4 font-mono font-bold text-slate-600 select-all">{{
                                            item.nomor_permohonan }}</td>
                                        <td class="px-4 py-4 text-slate-800 font-bold">{{ item.biodata_dinamis?.['Nama']
                                            || item.biodata_dinamis?.['Nama Pemohon'] || 'Tanpa Nama' }}</td>
                                        <td class="px-4 py-4">
                                            <div class="text-slate-800 font-medium truncate max-w-[160px]">{{
                                                item.pilar_form?.nama_penyaluran }}</div>
                                            <div class="text-[10px] text-orange-600 font-black uppercase">{{
                                                formatPilar(item.pilar_form?.pilar) }}</div>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span
                                                :class="`px-2.5 py-0.5 rounded-full text-[9px] font-black border uppercase tracking-wider ${getStatusBadge(item.status_permohonan)}`">
                                                {{ item.status_permohonan }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center" @click.stop>
                                            <button @click="selectApplicant(item)"
                                                class="inline-flex items-center gap-1 bg-slate-900 hover:bg-slate-800 text-white font-black px-3 py-1.5 rounded text-[10px] uppercase transition shadow-sm">
                                                🔍 Periksa & Cairkan
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="applicants.length === 0">
                                        <td colspan="6"
                                            class="px-6 py-12 text-center text-slate-400 font-bold uppercase bg-slate-50">
                                            📂 Belum ada berkas permohonan masuk yang terdaftar.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="selectedApplicant" class="space-y-6">
                            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                                <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider mb-3">I. REKAPAN
                                    DATA ATRIBUT FORMULIR</h3>
                                <div class="overflow-x-auto rounded-lg border border-slate-200">
                                    <table class="w-full text-left border-collapse text-xs">
                                        <thead>
                                            <tr class="bg-slate-900 text-white font-bold uppercase tracking-wider">
                                                <th class="p-2.5 w-1/3">Nama Atribut</th>
                                                <th class="p-2.5">Isian Data Rekaman</th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-bold text-slate-800 divide-y divide-slate-100">
                                            <tr>
                                                <td class="p-2.5 bg-slate-50 text-slate-500 uppercase">NIK / KTP
                                                    Mustahik</td>
                                                <td class="p-2.5 font-mono text-slate-900 text-sm tracking-wider">{{
                                                    selectedApplicant.biodata_dinamis?.['NIK'] || 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="p-2.5 bg-slate-50 text-slate-500 uppercase">Nama Lengkap</td>
                                                <td class="p-2.5 uppercase text-slate-900 font-black">{{
                                                    selectedApplicant.biodata_dinamis?.['Nama'] || 'Tanpa Nama' }}</td>
                                            </tr>
                                            <tr v-for="(val, key) in selectedApplicant.biodata_dinamis" :key="key">
                                                <td v-if="key !== 'Nama' && key !== 'NIK'"
                                                    class="p-2.5 bg-slate-50 text-slate-400 uppercase">{{ key }}</td>
                                                <td v-if="key !== 'Nama' && key !== 'NIK'" class="p-2.5 text-slate-700">
                                                    {{ val }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                                <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider mb-3">II. LAMPIRAN
                                    BUKTI DOKUMEN FISIK</h3>
                                <div v-if="selectedApplicant.berkas_dinamis && Object.keys(selectedApplicant.berkas_dinamis).length > 0"
                                    class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-for="(fileName, label) in selectedApplicant.berkas_dinamis" :key="label"
                                        class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 flex flex-col shadow-sm">
                                        <div
                                            class="bg-slate-900 text-white border-b border-slate-200 p-2 text-center text-[10px] font-black uppercase tracking-wider">
                                            📄 {{ label }}</div>
                                        <div class="p-3 flex items-center justify-center flex-grow bg-white">
                                            <img :src="`/storage/permohonan/${fileName}`" :alt="label"
                                                class="max-w-full h-auto rounded max-h-[200px] object-contain hover:scale-105 transition cursor-zoom-in"
                                                @click="window.open(`/storage/permohonan/${fileName}`, '_blank')" />
                                        </div>
                                    </div>
                                </div>
                                <div v-else
                                    class="text-slate-400 text-xs py-8 font-black uppercase tracking-wider text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                    📂 Berkas Fisik Lembar Lampiran Belum Diunggah</div>
                            </div>
                        </div>

                        <div v-else
                            class="bg-slate-100 border border-dashed border-slate-300 rounded-xl p-8 text-center text-slate-400 font-bold uppercase text-xs">
                            💡 Klik salah satu baris mustahik di atas untuk memeriksa berkas fisiknya.
                        </div>
                    </div>

                </div>

                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-md p-5 border border-slate-200 sticky top-6">
                        <div class="border-b border-slate-100 pb-2 mb-4">
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-tight">Otorisasi Finansial
                            </h2>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Sistem Manajemen
                                Buku Kas Pengeluaran</p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-4 text-xs font-bold text-slate-600">

                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1.5">Sifat Dana
                                    Pencairan</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="form.sifat_pengeluaran = 'terikat'"
                                        :class="`flex items-center justify-center gap-1 p-2 rounded border font-black transition text-[10px] uppercase ${form.sifat_pengeluaran === 'terikat' ? 'border-orange-500 bg-orange-50 text-orange-600 ring-1 ring-orange-500' : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50'}`">
                                        💼 Terikat Program
                                    </button>
                                    <button type="button" @click="form.sifat_pengeluaran = 'tidak_terikat'"
                                        :class="`flex items-center justify-center gap-1 p-2 rounded border font-black transition text-[10px] uppercase ${form.sifat_pengeluaran === 'tidak_terikat' ? 'border-blue-500 bg-blue-50 text-blue-600 ring-1 ring-blue-500' : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50'}`">
                                        🌐 Tidak Terikat
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Judul Agenda
                                    Pengeluaran</label>
                                <input type="text" v-model="form.judul_pengeluaran"
                                    class="w-full border-slate-200 rounded p-2.5 bg-slate-50 font-bold text-slate-800 text-xs shadow-inner"
                                    required placeholder="Pilih baris program/mustahik di tabel kiri...">
                            </div>

                            <div v-if="form.sifat_pengeluaran === 'terikat'">
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Target Dompet
                                    Terikat</label>
                                <select v-model="form.program_id" disabled
                                    class="w-full border-slate-200 rounded p-2.5 bg-slate-100 font-bold text-slate-500 text-xs cursor-not-allowed">
                                    <option value="">-- Terpilih Otomatis dari Tabel Kiri --</option>
                                    <option v-for="prog in programs" :key="prog.id" :value="prog.id">{{ prog.judul }}
                                    </option>
                                </select>
                            </div>

                            <div v-if="form.sifat_pengeluaran === 'tidak_terikat'">
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">
                                    Kategori Dana Tidak Terikat
                                </label>
                                <select v-model="form.kategori_dana_umum"
                                    class="w-full border-slate-200 rounded p-2.5 bg-white font-bold text-slate-800 text-xs"
                                    :required="form.sifat_pengeluaran === 'tidak_terikat'">
                                    <option value="" disabled>-- Pilih Jenis Alokasi Dana --</option>

                                    <option v-for="kas in kasUmumList" :key="kas.kategori" :value="kas.kategori">
                                        {{ kas.kategori.replace('_', ' ').toUpperCase() }} (Saldo: Rp{{
                                        kas.saldo?.toLocaleString('id-ID') }})
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Nominal Cair
                                    Aktual</label>
                                <div class="relative rounded shadow-sm">
                                    <span
                                        class="absolute left-2.5 top-1/2 -translate-y-1/2 font-black text-slate-400">Rp</span>
                                    <input type="number" v-model="form.amount"
                                        class="w-full border-slate-200 rounded p-2.5 pl-7 bg-white font-black text-slate-900 text-sm focus:ring-orange-500"
                                        placeholder="Masukkan Jumlah Anggaran..." required>
                                </div>
                                <p v-if="form.errors.amount" class="text-[11px] text-red-600 mt-1 font-bold">⚠️ {{
                                    form.errors.amount }}</p>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Keterangan
                                    Memo Internal</label>
                                <textarea v-model="form.keterangan"
                                    class="w-full border-slate-200 rounded p-2.5 bg-white text-xs font-medium text-slate-800"
                                    rows="2" placeholder="Nota deskripsi disposisi pencairan amil..."></textarea>
                            </div>

                            <button type="submit"
                                :disabled="form.processing || (form.sifat_pengeluaran === 'terikat' ? !selectedProgram : !selectedApplicant)"
                                class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2.5 rounded font-black uppercase tracking-wider text-[11px] transition shadow disabled:opacity-40">
                                {{ form.processing ? 'Menyimpan Buku Kas...' : '💾 Eksekusi & Catat Pengeluaran' }}
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </StaffLayout>
</template>
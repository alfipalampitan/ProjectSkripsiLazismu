<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2'; // Import SweetAlert2
import DonaturLayout from '@/Layouts/DonaturLayout.vue';

// 1. Terima data props dari ProgramController@kalkulatorZakat
const props = defineProps({
    programs: Array,
    errors: Object,
    auth: Object,
    system: Object
});

// State Utama
const currentTab = ref('penghasilan'); // 'penghasilan' atau 'harta'
const isLoading = ref(false);          // State loading tombol & overlay

// Konstanta Angka Nishob Dinamis (Mengambil dari setting harga emas admin via Props)
const NISHOB_PER_TAHUN = computed(() => {
    // Jika props.system.nishob_tahunan ada, pakai nilainya. Jika kosong/null, gunakan fallback default.
    return props.system?.nishob_tahunan || 81945667;
});

const NISHOB_PER_BULAN = computed(() => {
    return Math.round(NISHOB_PER_TAHUN.value / 12);
});

// Form State - Zakat Penghasilan
const formPenghasilan = ref({
    periode: 'bulan', // 'bulan' atau 'tahun'
    gaji: null,
    lain: null,
    hutang: null
});

// Form State - Zakat Harta
const formHarta = ref({
    tabungan: null,
    emas: null,
    properti: null
});

// Helper Format Rupiah
const formatRupiah = (value) => {
    if (!value) return '0';
    return new Intl.NumberFormat('id-ID').format(value);
};

// =========================================================
// HELPER FORMAT & PARSE INPUTAN (THOUSAND SEPARATOR)
// =========================================================
const formatInputan = (val) => {
    if (val === null || val === undefined || val === '') return '';
    const nomor_murni = String(val).replace(/\D/g, '');
    return nomor_murni.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

const parseInputan = (val) => {
    if (!val) return null; // kembalikan null jika kosong agar placeholder fungsi kembali bekerja
    return parseInt(String(val).replace(/\./g, ''), 10) || 0;
};

// Proxy Tampilan untuk Zakat Penghasilan
const displayGaji = computed({
    get: () => formatInputan(formPenghasilan.value.gaji),
    set: (val) => { formPenghasilan.value.gaji = parseInputan(val); }
});
const displayLain = computed({
    get: () => formatInputan(formPenghasilan.value.lain),
    set: (val) => { formPenghasilan.value.lain = parseInputan(val); }
});
const displayHutang = computed({
    get: () => formatInputan(formPenghasilan.value.hutang),
    set: (val) => { formPenghasilan.value.hutang = parseInputan(val); }
});

// Proxy Tampilan untuk Zakat Harta
const displayTabungan = computed({
    get: () => formatInputan(formHarta.value.tabungan),
    set: (val) => { formHarta.value.tabungan = parseInputan(val); }
});
const displayEmas = computed({
    get: () => formatInputan(formHarta.value.emas),
    set: (val) => { formHarta.value.emas = parseInputan(val); }
});
const displayProperti = computed({
    get: () => formatInputan(formHarta.value.properti),
    set: (val) => { formHarta.value.properti = parseInputan(val); }
});

// 1. Hitung Total Harta / Pendapatan Bersih
const totalHarta = computed(() => {
    if (currentTab.value === 'penghasilan') {
        const gaji = formPenghasilan.value.gaji || 0;
        const lain = formPenghasilan.value.lain || 0;
        const hutang = formPenghasilan.value.hutang || 0;
        const total = (gaji + lain) - hutang;
        return total < 0 ? 0 : total;
    } else {
        const tabungan = formHarta.value.tabungan || 0;
        const emas = formHarta.value.emas || 0;
        const properti = formHarta.value.properti || 0;
        return tabungan + emas + properti;
    }
});

// 2. Tentukan Batas Nishob Aktif
const nishobAktif = computed(() => {
    if (currentTab.value === 'penghasilan' && formPenghasilan.value.periode === 'bulan') {
        return NISHOB_PER_BULAN.value;
    }
    return NISHOB_PER_TAHUN.value;
});

// 3. Status Apakah Wajib Zakat
const isWajibZakat = computed(() => {
    if (totalHarta.value === 0) return null;
    return totalHarta.value >= nishobAktif.value;
});

// 4. Hitung Jumlah Zakat yang Harus Dibayar
const jumlahZakat = computed(() => {
    if (isWajibZakat.value) {
        return Math.round(totalHarta.value * 0.025); // Tarif Zakat 2.5%
    }
    return 0;
});

// =========================================================
// LOGIKA MENCARI ID PROGRAM SUNGGURAN (0 TYPO)
// =========================================================
const targetProgramId = computed(() => {
    if (!props.programs || !Array.isArray(props.programs) || props.programs.length === 0) {
        return null;
    }

    if (currentTab.value === 'penghasilan') {
        const prog = props.programs.find(p => {
            if (!p) return false;
            const namaString = String(p.judul || p.name || '').toLowerCase();
            return namaString.includes('penghasilan') || namaString.includes('profesi');
        });

        if (prog) return prog.id;
        const fallback = props.programs.find(p => String(p.id) === '3');
        return fallback ? fallback.id : props.programs[0].id;

    } else {
        const prog = props.programs.find(p => {
            if (!p) return false;
            const namaString = String(p.judul || p.name || '').toLowerCase();
            return namaString.includes('maal') || namaString.includes('harta') || namaString.includes('mal');
        });

        if (prog) return prog.id;
        const fallback = props.programs.find(p => String(p.id) === '1');
        return fallback ? fallback.id : props.programs[0].id;
    }
});

// Fungsi Reset Form saat pindah Tab
const switchTab = (tab) => {
    currentTab.value = tab;
};

// =========================================================
// INTEGRASI MENEMBAK PAYMENTCONTROLLER (MIDTRANS) & SWAL
// =========================================================
const tunaikanZakat = async () => {
    console.log("Isi props.programs:", props.programs);
    console.log("ID Program yang terkunci:", targetProgramId.value);
    console.log("Nominal Zakat:", jumlahZakat.value);
    console.log("Data User Auth:", props.auth?.user);

    if (jumlahZakat.value <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Belum Wajib Zakat',
            text: 'Nominal hitungan masih kosong atau harta Anda belum mencapai batas nishob.',
            confirmButtonColor: '#f97316'
        });
        return;
    }

    if (jumlahZakat.value < 10000) {
        Swal.fire({
            icon: 'info',
            title: 'Minimal Transaksi',
            text: 'Maaf, minimal nominal pembayaran Zakat melalui gateway adalah Rp 10.000',
            confirmButtonColor: '#f97316'
        });
        return;
    }

    if (!targetProgramId.value) {
        Swal.fire({
            icon: 'error',
            title: 'Sistem Gagal Mengunci ID',
            html: `Data <b>props.programs</b> yang diterima dari Laravel kosong atau tidak terbaca.<br><br>
                   <small class="text-slate-500">Cek ZakatController kamu apakah sudah melempar data 'programs' atau belum.</small>`,
            confirmButtonColor: '#ef4444'
        });
        return;
    }

    const konfirmasi = await Swal.fire({
        title: 'Konfirmasi Zakat',
        html: `Apakah Anda yakin ingin menunaikan zakat sebesar <br><b class="text-xl text-orange-600">Rp ${formatRupiah(jumlahZakat.value)}</b>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f97316',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Tunaikan Sekarang!',
        cancelButtonText: 'Batal'
    });

    if (!konfirmasi.isConfirmed) return;

    isLoading.value = true;

    Swal.fire({
        title: 'Menghubungkan ke Midtrans...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        const response = await axios.post('/payment/token', {
            program_id: targetProgramId.value,
            total: jumlahZakat.value,
            nominal: jumlahZakat.value,
            amount: jumlahZakat.value,
            nama: props.auth?.user?.name ? props.auth.user.name.replace(/[^a-zA-Z\s]/g, "") : 'Hamba Allah',
            email: props.auth?.user?.email || 'hambaallah@gmail.com',
            nomor_hp: props.auth?.user?.phone || props.auth?.user?.nomor_hp || '081234567890',
            keterangan: `Zakat ${currentTab.value === 'penghasilan' ? 'Penghasilan' : 'Maal'} terhitung otomatis.`,
        });

        Swal.close();

        if (response.data && response.data.token) {
            window.snap.pay(response.data.token, {
                onSuccess: function (result) {
                    Swal.fire({ icon: 'success', title: 'Alhamdulillah!', text: 'Zakat Anda berhasil ditunaikan.', confirmButtonColor: '#10b981' })
                        .then(() => { router.visit('/transparansi'); });
                },
                onPending: function (result) {
                    Swal.fire({ icon: 'info', title: 'Transaksi Pending', text: 'Silakan segera tuntaskan pembayaran.', confirmButtonColor: '#f97316' })
                        .then(() => { router.visit('/transparansi'); });
                },
                onError: function (result) {
                    Swal.fire({ icon: 'error', title: 'Pembayaran Gagal', text: 'Terjadi kegagalan pada server gateway.', confirmButtonColor: '#ef4444' });
                },
                onClose: function () {
                    Swal.fire({ icon: 'info', title: 'Dibatalkan', text: 'Anda menutup jendela pop-up pembayaran.', confirmButtonColor: '#64748b' });
                }
            });
        }
    } catch (error) {
        Swal.close();
        console.error("Detail Error Axios:", error);

        const validasiPesan = error.response?.data?.errors
            ? Object.values(error.response.data.errors).flat().join('<br>')
            : (error.response?.data?.message || 'Gagal memproses pembuatan tagihan ke server Midtrans.');

        Swal.fire({
            icon: 'error',
            title: 'Gagal Memproses',
            html: `<div class="text-left bg-slate-100 p-3 rounded text-xs text-red-600 font-mono">${validasiPesan}</div>`,
            confirmButtonColor: '#ef4444'
        });
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <DonaturLayout>
    <div class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col items-center py-12 px-4">

        <div class="text-center mb-10 max-w-xl">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 uppercase">Kalkulator Zakat</h1>
            <p class="text-slate-500 mt-2 text-sm">Yuk hitung berapa zakat yang harus kamu keluarkan tahun ini dengan mudah dan transparan.</p>
        </div>

        <div class="w-full max-w-5xl bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-7 flex flex-col gap-6">

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Pilih Jenis Zakat</label>
                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" @click="switchTab('penghasilan')" :class="[
                            'flex flex-col items-center justify-center p-4 rounded-xl border-2 transition-all duration-200',
                            currentTab === 'penghasilan' ? 'border-orange-500 bg-orange-50/50 text-orange-600' : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'
                        ]">
                            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm font-semibold">Penghasilan</span>
                        </button>

                        <button type="button" @click="switchTab('harta')" :class="[
                            'flex flex-col items-center justify-center p-4 rounded-xl border-2 transition-all duration-200',
                            currentTab === 'harta' ? 'border-orange-500 bg-orange-50/50 text-orange-600' : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'
                        ]">
                            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm font-semibold">Harta (Maal)</span>
                        </button>
                    </div>
                </div>

                <hr class="border-slate-100">

                <div v-if="currentTab === 'penghasilan'" class="space-y-5 animate-fadeIn">
                    <div class="flex items-center gap-6 mb-2">
                        <label class="flex items-center text-sm font-medium text-slate-700 cursor-pointer">
                            <input type="radio" v-model="formPenghasilan.periode" value="bulan" class="w-4 h-4 text-orange-500 focus:ring-orange-500 border-slate-300 mr-2"> Perbulan
                        </label>
                        <label class="flex items-center text-sm font-medium text-slate-700 cursor-pointer">
                            <input type="radio" v-model="formPenghasilan.periode" value="tahun" class="w-4 h-4 text-orange-500 focus:ring-orange-500 border-slate-300 mr-2"> Pertahun
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Pendapatan / Gaji Bulanan</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-sm">Rp</div>
                            <input type="text" v-model="displayGaji" class="block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 text-sm" placeholder="0">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Pendapatan Lain Bulanan (Opsional)</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-sm">Rp</div>
                            <input type="text" v-model="displayLain" class="block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 text-sm" placeholder="0">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Hutang / Cicilan Bulanan (Opsional)</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-sm">Rp</div>
                            <input type="text" v-model="displayHutang" class="block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 text-sm" placeholder="0">
                        </div>
                    </div>
                </div>

                <div v-else-if="currentTab === 'harta'" class="space-y-5 animate-fadeIn">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Uang Tunai / Tabungan / Deposito</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-sm">Rp</div>
                            <input type="text" v-model="displayTabungan" class="block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 text-sm" placeholder="0">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Emas, Perak, atau Permata (Nilai Uang)</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-sm">Rp</div>
                            <input type="text" v-model="displayEmas" class="block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 text-sm" placeholder="0">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Properti & Kendaraan (Investasi / Non-Pokok)</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-sm">Rp</div>
                            <input type="text" v-model="displayProperti" class="block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 text-sm" placeholder="0">
                        </div>
                    </div>
                </div>

            </div>

            <div class="lg:col-span-5 bg-slate-50 rounded-2xl p-6 border border-slate-100 flex flex-col justify-between h-fit gap-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Nilai Zakat</h3>
                    <p class="text-xs text-slate-500 mt-1">Berikut hasil perhitungan real-time berdasarkan data yang Anda masukkan.</p>

                    <div :class="[
                        'mt-5 p-3 rounded-xl text-center font-medium text-sm border transition-all duration-300',
                        isWajibZakat === null ? 'bg-slate-100 border-slate-200 text-slate-500' :
                            isWajibZakat ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-600'
                    ]">
                        <span v-if="isWajibZakat === null">Masukkan nominal untuk melihat status nishob</span>
                        <span v-else-if="isWajibZakat">Anda Wajib Membayar Zakat</span>
                        <span v-else>Tidak Wajib Membayar Zakat, Tapi Bisa Berinfak</span>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Total Harta / Pendapatan</label>
                            <div class="flex items-center justify-between font-semibold text-slate-800 bg-white px-3 py-2.5 rounded-lg border border-slate-200">
                                <span>Rp</span>
                                <span>{{ formatRupiah(totalHarta) }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Ambang Batas (Nishob)</label>
                            <div class="flex items-center justify-between text-slate-600 bg-slate-200/50 px-3 py-2.5 rounded-lg border border-slate-200 text-sm">
                                <span>Rp</span>
                                <span>{{ formatRupiah(nishobAktif) }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Jumlah Zakat yang Harus Dibayar</label>
                            <div class="flex items-center justify-between font-bold text-lg text-slate-900 bg-white px-3 py-2.5 rounded-lg border-2 border-slate-200">
                                <span>Rp</span>
                                <span class="text-orange-600">{{ formatRupiah(jumlahZakat) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <button @click="tunaikanZakat" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3.5 rounded-xl transition-colors duration-150">
                    Tunaikan Zakat Sekarang
                </button>
            </div>

        </div>
    </div>
    </DonaturLayout>
</template>

<style scoped>
.animate-fadeIn {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
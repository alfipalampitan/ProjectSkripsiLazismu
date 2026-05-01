<script setup>
import { ref, reactive, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    isOpen: Boolean,
    program: Object // Menerima object program secara dinamis
});

const emit = defineEmits(['close']);
const loading = ref(false);

const form = reactive({
    nominal: '',
    nama: '',
    nomor_hp: '',
    is_anonim: false,
    keterangan: ''
});

// Reset form saat modal ditutup/dibuka
watch(() => props.isOpen, (val) => {
    if (!val) {
        form.nominal = '';
        form.nama = '';
        form.is_anonim = false;
    }
});

const submitDonasi = async () => {
    if (!form.nominal || form.nominal < 10000) return alert('Minimal donasi Rp 10.000');

    loading.value = true;
    try {
        const response = await axios.post('/payment/token', {
            program_id: props.program.id, // ID Program masuk secara otomatis
            total: form.nominal,
            nama: form.is_anonim ? 'Hamba Allah' : form.nama,
            nomor_hp: form.nomor_hp,
            keterangan: form.keterangan
        });

        window.snap.pay(response.data.token, {
            onSuccess: (result) => { location.reload(); },
            onPending: (result) => { alert('Menunggu pembayaran...'); },
            onError: (result) => { alert('Pembayaran gagal!'); }
        });
    } catch (error) {
        console.error(error);
        alert('Gagal memproses transaksi.');
    } finally {
        loading.value = false;
    }
};

// Di dalam <script setup>
const presets = [50000, 100000, 200000, 500000];

// Fungsi untuk memformat angka menjadi format ribuan (titik)
const formatRupiah = (angka) => {
    if (!angka) return '';
    const stringAngka = angka.toString().replace(/[^0-9]/g, '');
    return stringAngka.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

// Fungsi saat input diketik
const handleInput = (e) => {
    let value = e.target.value.replace(/[^0-9]/g, ''); // Hapus semua selain angka
    form.nominal = value; // Simpan angka murni ke state form
};

// Fungsi untuk tombol preset agar tetap singkron
const setNominal = (amount) => {
    form.nominal = amount.toString();
};
</script>

<template>
    <Transition name="fade">
        <div v-if="isOpen"
            class="fixed inset-0 z-[99] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">

            <div
                class="bg-white w-full max-w-lg rounded-[2.5rem] overflow-hidden shadow-2xl flex flex-col animate-modal">

                <div class="bg-orange-500 p-6 text-white relative">
                    <h3 class="text-xl font-bold">Infaq & Sedekah</h3>
                    <p class="text-sm opacity-90 line-clamp-1">{{ program?.judul || 'Pilih Program' }}</p>

                    <button @click="$emit('close')"
                        class="absolute top-6 right-6 hover:rotate-90 transition-all duration-300">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <div class="p-8 space-y-5 overflow-y-auto max-h-[65vh]">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3">Nominal Donasi</label>

                        <div class="grid grid-cols-2 gap-2 mb-4">
                            <button v-for="amount in presets" :key="amount" @click="setNominal(amount)" type="button"
                                :class="[
                                    'py-3 px-2 rounded-xl border-2 font-bold transition-all text-sm',
                                    form.nominal == amount.toString()
                                        ? 'border-orange-500 bg-orange-50 text-orange-600'
                                        : 'border-gray-100 bg-gray-50 text-gray-500 hover:border-orange-200'
                                ]">
                                Rp {{ amount.toLocaleString('id-ID') }}
                            </button>
                        </div>

                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-gray-400">Rp</span>
                            <input type="text" :value="formatRupiah(form.nominal)" @input="handleInput"
                                class="w-full pl-12 pr-4 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-orange-500 font-bold text-lg"
                                placeholder="Masukkan jumlah">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-2xl">
                        <input type="checkbox" v-model="form.is_anonim" id="anonim"
                            class="w-5 h-5 text-orange-500 border-gray-300 rounded">
                        <label for="anonim" class="text-sm font-semibold text-gray-600 cursor-pointer">Sembunyikan nama
                            (Hamba Allah)</label>
                    </div>

                    <Transition name="slide">
                        <div v-if="!form.is_anonim">
                            <input v-model="form.nama" type="text" placeholder="Nama Lengkap Anda"
                                class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-orange-500">
                        </div>
                    </Transition>

                    <input v-model="form.nomor_hp" type="text" placeholder="Nomor WhatsApp (Aktif)"
                        class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-orange-500">

                    <textarea v-model="form.keterangan" placeholder="Tulis doa atau pesan khusus..."
                        class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-orange-500 h-24"></textarea>
                </div>

                <div class="p-8 pt-0">
                    <button @click="submitDonasi" :disabled="loading"
                        class="w-full bg-orange-500 hover:bg-orange-600 text-white py-5 rounded-2xl font-black text-lg shadow-lg transition-all active:scale-95 flex items-center justify-center gap-3">
                        <i v-if="loading" class="fa-solid fa-circle-notch animate-spin"></i>
                        {{ loading ? 'Menyiapkan Pembayaran...' : 'Donasi Sekarang' }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.animate-modal {
    animation: zoomIn 0.3s ease-out;
}

@keyframes zoomIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
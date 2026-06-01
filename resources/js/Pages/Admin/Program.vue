<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';
import Swal from 'sweetalert2'; // Import SweetAlert2
import { ref, computed, watch } from 'vue';

const props = defineProps({
    programs: Array,
});

// State untuk Modal
const isModalOpen = ref(false);
const isEditing = ref(false);

// State tambahan untuk melacak opsi target dana di UI modal
const tipeTarget = ref('pakai_target'); 

const form = useForm({
    judul: '',
    kategori: 'zakat',
    target_dana: '',
    deskripsi: '',
    gambar: null,
});

// Watcher untuk memantau jika staf memilih 'tanpa_target', kita set nominal ke 0 agar aman di DB
watch(tipeTarget, (newVal) => {
    if (newVal === 'tanpa_target') {
        form.target_dana = 0;
    } else if (newVal === 'pakai_target' && form.target_dana === 0) {
        form.target_dana = '';
    }
});

// Pencarian & Filter
const searchQuery = ref('');
const selectedKategori = ref('Semua Kategori');

const filteredPrograms = computed(() => {
    return props.programs.filter(p => {
        const matchSearch = p.judul.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchKategori = selectedKategori.value === 'Semua Kategori' || p.kategori === selectedKategori.value;
        return matchSearch && matchKategori;
    });
});

// Fungsi Modal
const openModal = (program = null) => {
    if (program) {
        isEditing.value = true;
        form.id = program.id;
        form.judul = program.judul;
        form.kategori = program.kategori;

        // Memastikan penentuan tipe target saat edit dibuka
        if (program.target_dana && parseInt(program.target_dana, 10) > 0) {
            tipeTarget.value = 'pakai_target';
            form.target_dana = parseInt(program.target_dana, 10);
        } else {
            tipeTarget.value = 'tanpa_target';
            form.target_dana = 0;
        }

        form.deskripsi = program.deskripsi;
    } else {
        isEditing.value = false;
        tipeTarget.value = 'pakai_target';
        form.reset();
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.post(route('programs.update_manual', form.id), {
            forceFormData: true,
            onSuccess: () => {
                closeModal();
                Swal.fire('Berhasil!', 'Program diperbarui.', 'success');
            },
            onError: () => Swal.fire('Gagal!', 'Periksa kembali inputan Anda.', 'error')
        });
    } else {
        form.post(route('programs.store'), {
            onSuccess: () => {
                closeModal();
                Swal.fire('Berhasil!', 'Program ditambahkan.', 'success');
            },
            onError: (errors) => {
                console.log(errors);
                Swal.fire('Gagal!', 'Pastikan semua data terisi (termasuk gambar).', 'error');
            }
        });
    }
};

const hapusProgram = (id) => {
    Swal.fire({
        title: 'Hapus Program?',
        text: "Data yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('programs.destroy', id));
        }
    });
};

// Fungsi 1: Untuk merender teks format Rupiah di daftar card program (Read-Only)
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

// Fungsi 2: Pembantu khusus untuk menampilkan format titik di dalam kotak input secara aman
const formatTampilanInput = (val) => {
    if (!val || val === 0) return '';
    return new Intl.NumberFormat('id-ID').format(val);
};

// Fungsi 3: Dipicu saat staf mengetik nominal uang di input target dana
const formatInputUang = (event) => {
    let value = event.target.value.replace(/\D/g, ""); // Hapus semua non-angka

    if (value) {
        event.target.value = new Intl.NumberFormat("id-ID").format(parseInt(value));
        form.target_dana = parseInt(value); // Simpan angka bersih murni ke data form Inertia
    } else {
        event.target.value = "";
        form.target_dana = tipeTarget.value === 'tanpa_target' ? 0 : "";
    }
};
</script>

<template>
    <Head title="Manajemen Program" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Header & Tombol Tambah -->
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-extrabold text-gray-900">Daftar Program</h2>
                        <p class="text-gray-500">Info dan Manajemen Daftar Program Donasi</p>
                    </div>
                    <button @click="openModal()"
                        class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition flex items-center">
                        <i class="fa-solid fa-plus mr-2"></i> Tambah Program
                    </button>
                </div>

                <!-- Search & Filter Bar -->
                <div class="bg-white p-4 rounded-2xl shadow-sm mb-8 flex flex-col md:flex-row gap-4">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input v-model="searchQuery" type="text" placeholder="Cari judul program..."
                            class="pl-10 w-full rounded-xl border-gray-200 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    <select v-model="selectedKategori"
                        class="rounded-xl border-gray-200 focus:ring-orange-500 w-full md:w-64">
                        <option>Semua Kategori</option>
                        <option>Zakat</option>
                        <option>Infaq</option>
                        <option>Qurban</option>
                        <option>Wakaf</option>
                        <option>Pilar</option>
                    </select>
                </div>

                <!-- Grid Daftar Program -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="program in filteredPrograms" :key="program.id"
                        class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition">
                        <div class="h-48 bg-gray-200 relative">
                            <img v-if="program.gambar" :src="`/storage/${program.gambar}`"
                                class="w-full h-full object-cover">
                            <div
                                class="absolute top-3 left-3 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-orange-600 shadow-sm uppercase">
                                {{ program.kategori }}
                            </div>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-lg text-gray-800 mb-2 truncate">{{ program.judul }}</h3>
                            <p class="text-sm text-gray-500 line-clamp-2 mb-4">{{ program.deskripsi }}</p>

                            <div class="flex justify-between items-center mb-5">
                                <span class="text-xs text-gray-400">Target Dana:</span>
                                <!-- Logika Tampilan: Jika target_dana kosong atau 0, render sebagai Tanpa Target -->
                                <span v-if="program.target_dana && parseInt(program.target_dana) > 0" class="text-sm font-bold text-gray-700">
                                    {{ formatRupiah(program.target_dana) }}
                                </span>
                                <span v-else class="text-sm font-bold text-green-600 bg-green-50 px-2.5 py-1 rounded-lg">
                                    Tanpa Target (Open)
                                </span>
                            </div>

                            <div class="flex gap-2">
                                <button @click="openModal(program)"
                                    class="flex-1 bg-blue-50 text-blue-600 py-2 rounded-lg font-bold hover:bg-blue-100 transition">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                </button>
                                <button @click="hapusProgram(program.id)"
                                    class="flex-1 bg-red-50 text-red-600 py-2 rounded-lg font-bold hover:bg-red-100 transition">
                                    <i class="fa-solid fa-trash mr-1"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL FORM PROGRAM -->
                <div v-if="isModalOpen"
                    class="fixed inset-0 z-[99] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div
                        class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all max-h-[90vh] flex flex-col">

                        <div class="h-2 bg-orange-500 flex-shrink-0"></div>

                        <!-- Header Modal -->
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center flex-shrink-0">
                            <h3 class="text-xl font-bold text-gray-800">
                                {{ isEditing ? 'Edit Program' : 'Tambah Program Baru' }}
                            </h3>
                            <button @click="closeModal"
                                class="text-gray-400 hover:text-orange-500 hover:rotate-90 transition-all duration-300">
                                <i class="fa-solid fa-xmark text-2xl"></i>
                            </button>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submit" class="p-6 space-y-4 overflow-y-auto">

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Judul Program</label>
                                <input v-model="form.judul" type="text"
                                    class="w-full border-gray-200 rounded-xl focus:ring-orange-500 transition"
                                    required />
                            </div>

                            <!-- INPUT DROPDOWN BARU: Memilih Tipe Target Dana -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Kebutuhan Target Dana</label>
                                <select v-model="tipeTarget"
                                    class="w-full border-gray-200 rounded-xl focus:ring-orange-500">
                                    <option value="pakai_target">Menggunakan Target Nominal (Contoh: Bencana, Pembangunan)</option>
                                    <option value="tanpa_target">Tanpa Target Dana / Terbuka (Contoh: Zakat Maal, Infaq)</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                                <div class="sm:col-span-4">
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Kategori</label>
                                    <select v-model="form.kategori"
                                        class="w-full border-gray-200 rounded-xl focus:ring-orange-500">
                                        <option value="zakat">Zakat</option>
                                        <option value="infaq_sodaqoh">Infaq/Sodaqoh</option>
                                        <option value="Qurban">Qurban</option>
                                        <option value="Wakaf">Wakaf</option>
                                        <option value="Pilar">Pilar</option>
                                    </select>
                                </div>
                                
                                <!-- Input Target Dana: Otomatis tersembunyi/tidak wajib jika memilih 'tanpa_target' -->
                                <div class="sm:col-span-8 transition-all duration-300">
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Target Dana (Rp)</label>
                                    <input 
                                        :value="formatTampilanInput(form.target_dana)" 
                                        @input="formatInputUang"
                                        type="text" 
                                        class="w-full border-gray-200 rounded-xl focus:ring-orange-500 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
                                        :disabled="tipeTarget === 'tanpa_target'"
                                        :required="tipeTarget === 'pakai_target'" 
                                        :placeholder="tipeTarget === 'tanpa_target' ? 'Otomatis Tanpa Target' : 'Contoh: 10.000.000'" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Deskripsi Singkat</label>
                                <textarea v-model="form.deskripsi" rows="3"
                                    class="w-full border-gray-200 rounded-xl focus:ring-orange-500 transition"
                                    placeholder="Tulis deskripsi program..."></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Banner / Gambar</label>
                                <input type="file" @input="form.gambar = $event.target.files[0]"
                                    class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer" />
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="pt-4 flex flex-col sm:flex-row gap-3">
                                <button type="button" @click="closeModal"
                                    class="order-2 sm:order-1 flex-1 py-3 border border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition">
                                    Batal
                                </button>
                                <button type="submit" :disabled="form.processing"
                                    class="order-1 sm:order-2 flex-1 py-3 bg-orange-500 text-white rounded-xl font-bold hover:bg-orange-600 shadow-lg shadow-orange-200 transition-all duration-300 disabled:cursor-not-allowed"
                                    :class="{ 'animate-pulse bg-orange-400': form.processing }">
                                    {{ form.processing ? 'Memproses...' : (isEditing ? 'Update' : 'Simpan') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
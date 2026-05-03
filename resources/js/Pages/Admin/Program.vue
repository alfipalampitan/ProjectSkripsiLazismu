<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';
import Swal from 'sweetalert2'; // Import SweetAlert2
import { ref, computed } from 'vue';

const props = defineProps({
    programs: Array,
});

// State untuk Modal
const isModalOpen = ref(false);
const isEditing = ref(false);

const form = useForm({
    judul: '',
    kategori: 'Zakat',
    target_dana: '',
    deskripsi: '',
    gambar: null,
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
        form.target_dana = program.target_dana;
        form.deskripsi = program.deskripsi;
    } else {
        isEditing.value = false;
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
        // Mode EDIT: Gunakan POST dengan spoofing _method PUT untuk upload file
        form.post(route('programs.update_manual', form.id), {
            forceFormData: true, 
            onSuccess: () => {
                closeModal();
                Swal.fire('Berhasil!', 'Program diperbarui.', 'success');
            },
            onError: () => Swal.fire('Gagal!', 'Periksa kembali inputan Anda.', 'error')
        });
    } else {
        // Mode TAMBAH
        form.post(route('programs.store'), {
            onSuccess: () => {
                closeModal();
                Swal.fire('Berhasil!', 'Program ditambahkan.', 'success');
            },
            onError: (errors) => {
                console.log(errors); // Cek di console log browser untuk detail error
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

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
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
                    <button @click="openModal()" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition flex items-center">
                        <i class="fa-solid fa-plus mr-2"></i> Tambah Program
                    </button>
                </div>

                <!-- Search & Filter Bar -->
                <div class="bg-white p-4 rounded-2xl shadow-sm mb-8 flex flex-col md:flex-row gap-4">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input v-model="searchQuery" type="text" placeholder="Cari judul program..." class="pl-10 w-full rounded-xl border-gray-200 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    <select v-model="selectedKategori" class="rounded-xl border-gray-200 focus:ring-orange-500 w-full md:w-64">
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
                    <div v-for="program in filteredPrograms" :key="program.id" class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition">
                        <div class="h-48 bg-gray-200 relative">
                            <img v-if="program.gambar" :src="`/storage/${program.gambar}`" class="w-full h-full object-cover">
                            <div class="absolute top-3 left-3 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-orange-600 shadow-sm uppercase">
                                {{ program.kategori }}
                            </div>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-lg text-gray-800 mb-2 truncate">{{ program.judul }}</h3>
                            <p class="text-sm text-gray-500 line-clamp-2 mb-4">{{ program.deskripsi }}</p>
                            
                            <div class="flex justify-between items-center mb-5">
                                <span class="text-xs text-gray-400">Target Dana:</span>
                                <span class="text-sm font-bold text-gray-700">{{ formatRupiah(program.target_dana) }}</span>
                            </div>

                            <div class="flex gap-2">
                                <button @click="openModal(program)" class="flex-1 bg-blue-50 text-blue-600 py-2 rounded-lg font-bold hover:bg-blue-100 transition">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                </button>
                                <button @click="hapusProgram(program.id)" class="flex-1 bg-red-50 text-red-600 py-2 rounded-lg font-bold hover:bg-red-100 transition">
                                    <i class="fa-solid fa-trash mr-1"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Form (Tambah/Edit) -->
                <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden">
                        <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="text-xl font-bold text-gray-800">{{ isEditing ? 'Edit Program' : 'Tambah Program Baru' }}</h3>
                            <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                        </div>
                        
                        <form @submit.prevent="submit" class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Program</label>
                                    <input v-model="form.judul" type="text" required class="w-full rounded-xl border-gray-200 focus:ring-orange-500 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori</label>
                                    <select v-model="form.kategori" class="w-full rounded-xl border-gray-200 focus:ring-orange-500 shadow-sm">
                                        <option value="Zakat">Zakat</option>
                                        <option value="Infaq">Infaq</option>
                                        <option value="Qurban">Qurban</option>
                                        <option value="Wakaf">Wakaf</option>
                                        <option value="Pilar">Pilar</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Target Dana (Rp)</label>
                                    <input v-model="form.target_dana" type="number" required class="w-full rounded-xl border-gray-200 focus:ring-orange-500 shadow-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi</label>
                                    <textarea v-model="form.deskripsi" rows="3" class="w-full rounded-xl border-gray-200 focus:ring-orange-500 shadow-sm"></textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Banner/Gambar Program</label>
                                    <input type="file" @input="form.gambar = $event.target.files[0]" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer" />
                                </div>
                            </div>

                            <div class="mt-8 flex gap-3">
                                <button type="button" @click="closeModal" class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl font-bold hover:bg-gray-200 transition">Batal</button>
                                <button type="submit" :disabled="form.processing" class="flex-1 bg-orange-500 text-white py-3 rounded-xl font-bold hover:bg-orange-600 transition disabled:opacity-50">
                                    {{ isEditing ? 'Simpan Perubahan' : 'Simpan Program' }}
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


<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2'; // Import SweetAlert2 untuk notifikasi

defineProps({
    users: Array,
});

// State untuk membuka/tutup modal
const showModal = ref(false);
const isEditing = ref(false);
const editId = ref(null)

// Inisialisasi Form Helper Inertia
const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'staff', // Default role
    jabatan: '',
    alamat: '',
});

// Fungsi untuk membuka modal tambah (reset state)
const openAddModal = () => {
    isEditing.value = false;
    editId.value = null;
    form.reset();
    showModal.value = true;
};

// Fungsi untuk membuka modal edit (isi data user)
const openEditModal = (user) => {
    isEditing.value = true;
    editId.value = user.id;

    form.name = user.name;
    form.email = user.email;
    form.role = user.role;
    form.jabatan = user.jabatan;
    form.alamat = user.alamat;
    form.password = ''; // Kosongkan password agar tidak terlihat

    showModal.value = true;
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('pengguna.update', editId.value), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data pengguna telah diperbarui.',
                    confirmButtonColor: '#f97316',
                });
            },
        });
    } else {
        form.post(route('pengguna.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Pengguna baru berhasil ditambahkan.',
                    confirmButtonColor: '#f97316',
                });
            },
        });
    }
};

// Fungsi Hapus dengan SweetAlert2
const deleteUser = (id) => {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data pengguna ini akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f97316', // Warna orange-500 sesuai tema kamu
        cancelButtonColor: '#6b7280', // Warna gray-500
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true, // Tombol batal di kiri, hapus di kanan
        borderRadius: '1.5rem', // Membuat sudut melengkung sesuai desain modal kamu
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('pengguna.destroy', id), {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Pengguna telah dihapus.',
                        icon: 'success',
                        confirmButtonColor: '#f97316',
                    });
                },
                onError: () => {
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat menghapus data.',
                        icon: 'error',
                        confirmButtonColor: '#f97316',
                    });
                }
            });
        }
    });
};


</script>

<template>
    <Head title="Kelola Pengguna" />
    <AuthenticatedLayout>
        <template #header>Kelola Pengguna</template>

        <div class="space-y-6">
            <!-- Header Section: Stack di mobile, row di desktop -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white p-4 md:p-6 rounded-2xl shadow-sm border border-gray-100 gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-800">Daftar Pengguna</h1>
                    <p class="text-gray-500 text-xs md:text-sm">Kelola akses Admin dan Staff Keuangan</p>
                </div>
                <!-- Tombol Full Width di Mobile -->
                <button @click="openAddModal"
                    class="w-full sm:w-auto bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-orange-200">
                    <i class="fa-solid fa-user-plus mr-2"></i> Tambah Pengguna
                </button>
            </div>

            <!-- MODAL: Penyesuaian max-height dan padding mobile -->
            <div v-if="showModal"
                class="fixed inset-0 z-[99] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all max-h-[90vh] flex flex-col">
                    <div class="h-2 bg-orange-500 flex-shrink-0"></div>
                    
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center flex-shrink-0">
                        <h3 class="text-xl font-bold text-gray-800">
                            {{ isEditing ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}
                        </h3>
                        <button @click="showModal = false" class="text-gray-400 hover:text-orange-500 hover:rotate-90 transition-all duration-300">
                            <i class="fa-solid fa-xmark text-2xl"></i>
                        </button>
                    </div>

                    <!-- Form dengan scroll jika konten panjang -->
                    <form @submit.prevent="submit" class="p-6 space-y-4 overflow-y-auto">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap</label>
                            <input v-model="form.name" type="text"
                                class="w-full border-gray-200 rounded-xl focus:ring-orange-500 transition" required />
                        </div>

                        <!-- Grid 1 Kolom di Mobile, 2 di Desktop -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                                <input v-model="form.email" type="email"
                                    class="w-full border-gray-200 rounded-xl focus:ring-orange-500" required />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Role</label>
                                <select v-model="form.role"
                                    class="w-full border-gray-200 rounded-xl focus:ring-orange-500">
                                    <option value="admin">Admin</option>
                                    <option value="staff">Staff Keuangan</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Jabatan</label>
                            <input v-model="form.jabatan" type="text"
                                class="w-full border-gray-200 rounded-xl focus:ring-orange-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Alamat</label>
                            <input v-model="form.alamat" type="text"
                                class="w-full border-gray-200 rounded-xl focus:ring-orange-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                            <input v-model="form.password" type="password" 
                                class="w-full border-gray-200 rounded-xl focus:ring-orange-500" 
                                :required="!isEditing" />
                        </div>

                        <div class="pt-4 flex flex-col sm:flex-row gap-3">
                            <button type="button" @click="showModal = false"
                                class="order-2 sm:order-1 flex-1 py-3 border border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition">Batal</button>
                            <button type="submit" :disabled="form.processing"
                                class="order-1 sm:order-2 flex-1 py-3 bg-orange-500 text-white rounded-xl font-bold hover:bg-orange-600 shadow-lg shadow-orange-200 transition">
                                {{ isEditing ? 'Update' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TABEL: Gunakan overflow-x-auto agar bisa di-swipe -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase">Nama & Email</th>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase">Role</th>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase">Jabatan</th>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase">Alamat</th>
                                <th class="p-4 text-xs font-bold text-gray-500 uppercase text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50/50 transition">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold uppercase text-xs md:text-base flex-shrink-0">
                                            {{ user.name.charAt(0) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-gray-800 text-sm md:text-base truncate">{{ user.name }}</div>
                                            <div class="text-[10px] md:text-xs text-gray-500 truncate">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span :class="{
                                        'px-2 py-1 rounded-full text-[10px] md:text-xs font-bold whitespace-nowrap': true,
                                        'bg-blue-100 text-blue-600': user.role === 'admin',
                                        'bg-green-100 text-green-600': user.role === 'staff'
                                    }">
                                        {{ user.role === 'staff' ? 'Staff' : 'Admin' }}
                                    </span>
                                </td>
                                <td class="p-4 text-xs md:text-sm text-gray-600">{{ user.jabatan || '-' }}</td>
                                <td class="p-4 text-xs md:text-sm text-gray-600 max-w-[150px] md:max-w-[200px] truncate">{{ user.alamat || '-' }}</td>
                                <td class="p-4">
                                    <div class="flex justify-center gap-1">
                                        <button @click="openEditModal(user)" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button @click="deleteUser(user.id)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Petunjuk Swipe di Mobile -->
                <div class="sm:hidden p-3 bg-gray-50 text-[10px] text-center text-gray-400 border-t">
                    <i class="fa-solid fa-arrows-left-right mr-1"></i> Geser untuk melihat detail tabel
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
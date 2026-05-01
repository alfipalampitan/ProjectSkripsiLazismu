<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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
        // Menggunakan PUT untuk update
        form.put(route('pengguna.update', editId.value), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        // Menggunakan POST untuk simpan baru
        form.post(route('pengguna.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

// Fungsi Hapus
const deleteUser = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
        form.delete(route('pengguna.destroy', id));
    }
};
</script>

<template>
    <Head title="Kelola Pengguna" />
    <AuthenticatedLayout>
        <template #header>Kelola Pengguna</template>

        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Daftar Pengguna</h1>
                    <p class="text-gray-500 text-sm">Kelola akses Admin dan Staff Keuangan</p>
                </div>
                <!-- Tombol Tambah memanggil fungsi openAddModal -->
                <button @click="openAddModal"
                    class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-orange-200">
                    <i class="fa-solid fa-user-plus mr-2"></i> Tambah Pengguna
                </button>
            </div>

            <!-- MODAL (Desain Asli dengan Warna Oranye di Atas) -->
            <div v-if="showModal"
                class="fixed inset-0 z-[99] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
                    <!-- Aksen Oranye di Atas Modal -->
                    <div class="h-2 bg-orange-500"></div>
                    
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-800">
                            {{ isEditing ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}
                        </h3>
                        <!-- Tombol X dengan animasi hover dan fungsi tutup yang benar -->
                        <button @click="showModal = false" class="text-gray-400 hover:text-orange-500 hover:rotate-90 transition-all duration-300">
                            <i class="fa-solid fa-xmark text-2xl"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-4 overflow-y-auto max-h-[70vh]">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap</label>
                            <input v-model="form.name" type="text" pattern="^[a-zA-Z\s]+$"
                                title="Hanya diperbolehkan huruf dan spasi" minlength="8"
                                class="w-full border-gray-200 rounded-xl focus:ring-orange-500 transition" required />
                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                                <input v-model="form.email" type="email"
                                    class="w-full border-gray-200 rounded-xl focus:ring-orange-500" required />
                                <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
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
                            <input v-model="form.jabatan" type="text" placeholder="Contoh: Bendahara Lazismu"
                                class="w-full border-gray-200 rounded-xl focus:ring-orange-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Alamat</label>
                            <input v-model="form.alamat" type="text" placeholder="Contoh: Jl. Merdeka No. 123"
                                class="w-full border-gray-200 rounded-xl focus:ring-orange-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                            <input v-model="form.password" type="password" 
                                class="w-full border-gray-200 rounded-xl focus:ring-orange-500" 
                                :required="!isEditing" />
                            <p v-if="isEditing" class="text-[10px] text-gray-500 mt-1">
                                *Kosongkan jika tidak ingin mengubah password
                            </p>
                            <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                        </div>

                        <div class="pt-4 flex gap-3">
                            <button type="button" @click="showModal = false"
                                class="flex-1 py-3 border border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition">Batal</button>
                            <button type="submit" :disabled="form.processing"
                                class="flex-1 py-3 bg-orange-500 text-white rounded-xl font-bold hover:bg-orange-600 shadow-lg shadow-orange-200 transition">
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>{{ isEditing ? 'Update User' : 'Simpan User' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TABEL DAFTAR PENGGUNA -->
        <div class="bg-white mt-6 rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Nama & Email</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Jabatan</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Alamat</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50/50 transition">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold uppercase">
                                    {{ user.name.charAt(0) }}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-800">{{ user.name }}</div>
                                    <div class="text-xs text-gray-500">{{ user.email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <span :class="{
                                'px-3 py-1 rounded-full text-xs font-bold': true,
                                'bg-blue-100 text-blue-600': user.role === 'admin',
                                'bg-green-100 text-green-600': user.role === 'staff'
                            }">
                                {{ user.role === 'staff' ? 'Staff Keuangan' : 'Admin' }}
                            </span>
                        </td>
                        <td class="p-4 text-sm text-gray-600">{{ user.jabatan || '-' }}</td>
                        <td class="p-4 text-sm text-gray-600 max-w-[200px] truncate">{{ user.alamat || '-' }}</td>
                        <td class="p-4">
                            <div class="flex gap-2">
                                <button @click="openEditModal(user)" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button @click="deleteUser(user.id)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="users.length === 0">
                        <td colspan="5" class="p-8 text-center text-gray-400 italic">Belum ada data pengguna.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>
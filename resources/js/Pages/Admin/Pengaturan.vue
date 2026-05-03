<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    settings: Object, // Asumsi data setting dikirim dari controller
});

const form = useForm({
    nama_organisasi: props.settings?.site_name || '',
    alamat: props.settings?.site_address || '',
    nomor_telepon: props.settings?.site_phone || '',
    logo: null,
});

const logoPreview = ref(null);

const handleLogoChange = (e) => {
    const file = e.target.files[0];
    form.logo = file;
    if (file) {
        logoPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('settings.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Opsional: Hapus preview lokal setelah berhasil agar 
            // tampilan mengambil data terbaru dari $page.props.system.logo
            logoPreview.value = null; 
            
            Swal.fire('Berhasil', 'Logo sistem telah diperbarui!', 'success');
        },
    });
};

</script>

<template>

    <Head title="Pengaturan Sistem" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h2 class="text-3xl font-extrabold text-gray-900">Pengaturan Sistem</h2>
                    <p class="text-gray-500">Info Tentang Pengaturan Sistem</p>
                </div>

                <!-- Form Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-gray-100">
                    <form @submit.prevent="submit" class="p-8 space-y-6">

                        <!-- Nama Organisasi -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Organisasi</label>
                            <input v-model="form.nama_organisasi" type="text"
                                class="w-full rounded-2xl border-gray-200 focus:ring-orange-500 focus:border-orange-500 shadow-sm transition"
                                placeholder="Masukkan nama organisasi...">
                        </div>

                        <!-- Alamat -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Alamat</label>
                            <textarea v-model="form.alamat" rows="3"
                                class="w-full rounded-2xl border-gray-200 focus:ring-orange-500 focus:border-orange-500 shadow-sm transition"
                                placeholder="Alamat lengkap..."></textarea>
                        </div>

                        <!-- Nomor Telepon -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nomor Telepon</label>
                            <input v-model="form.nomor_telepon" type="text"
                                class="w-full rounded-2xl border-gray-200 focus:ring-orange-500 focus:border-orange-500 shadow-sm transition"
                                placeholder="0812...">
                        </div>

                        <!-- Change Icon / Logo -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Change Icon</label>
                            <div class="flex items-center gap-4">
                                <!-- Preview Box -->
                                <div
                                    class="w-20 h-20 border-2 border-dashed border-gray-200 rounded-2xl flex items-center justify-center overflow-hidden bg-gray-50">
                                    <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-cover" />
                                    <i v-else class="fa-solid fa-image text-gray-300 text-2xl"></i>
                                </div>

                                <!-- Custom File Button -->
                                <label
                                    class="cursor-pointer bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-xl text-sm font-bold transition">
                                    Pilih
                                    <input type="file" @change="handleLogoChange" class="hidden" accept="image/*" />
                                </label>
                            </div>
                        </div>

                        <!-- Tombol Submit kamu di sini -->
                        <div class="pt-4">
                            <button type="submit" :disabled="form.processing"
                                class="w-full bg-orange-500 hover:bg-orange-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-orange-200 transition disabled:opacity-50">
                                {{ form.processing ? 'Sedang Menyimpan...' : 'Simpan Perubahan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import StaffLayout from '@/Layouts/StaffLayout.vue';

// 1. Menerima data dari Laravel Backend
const props = defineProps({
    pilarForms: Array // List formulir pilar yang sudah tersimpan di database
});

// State untuk mengontrol buka/tutup masing-masing Modal
const isModalOpen = ref(false);
const isModalEditOpen = ref(false);
const idFormYangDiedit = ref(null);

// =========================================================
// [FITUR A] SETUP FORM CREATE
// =========================================================
const form = useForm({
    nama_penyaluran: '',
    pilar: '',
    skema_form: [
        { field_name: '', type: 'text', required: true } // Default baris pertama
    ]
});

const tambahField = () => {
    form.skema_form.push({ field_name: '', type: 'text', required: true });
};

const hapusField = (index) => {
    if (form.skema_form.length > 1) {
        form.skema_form.splice(index, 1);
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Gagal',
            text: 'Minimal harus ada 1 inputan dalam formulir!',
            confirmButtonColor: '#EF4444'
        });
    }
};

const tutupDanResetModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    form.post(route('pilar-form.store'), {
        onStart: () => {
            Swal.fire({
                title: 'Menyimpan...',
                text: 'Sedang membuat template form pilar',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        },
        onSuccess: () => {
            tutupDanResetModal();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Template formulir dinamis pilar berhasil disimpan.',
                confirmButtonColor: '#10B981'
            });
        },
        onError: () => {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Pastikan semua nama field dan pilar sudah diisi dengan benar.',
                confirmButtonColor: '#EF4444'
            });
        }
    });
};


// =========================================================
// [FITUR B] SETUP FORM EDIT MODAL
// =========================================================
const formEdit = useForm({
    nama_penyaluran: '',
    pilar: '',
    skema_form: []
});

// Memicu modal edit muncul dan menyuntikkan data lama dari card
const bukaModalEdit = (item) => {
    idFormYangDiedit.value = item.id;
    formEdit.nama_penyaluran = item.nama_penyaluran;
    formEdit.pilar = item.pilar;
    // Deep clone array skema_form agar ketikan di modal tidak merusak card sebelum disave
    formEdit.skema_form = JSON.parse(JSON.stringify(item.skema_form || []));
    isModalEditOpen.value = true;
};

const tambahFieldEdit = () => {
    formEdit.skema_form.push({ field_name: '', type: 'text', required: true });
};

const hapusFieldEdit = (index) => {
    if (formEdit.skema_form.length > 1) {
        formEdit.skema_form.splice(index, 1);
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Gagal',
            text: 'Minimal harus ada 1 inputan dalam formulir!',
            confirmButtonColor: '#EF4444'
        });
    }
};

const tutupDanResetModalEdit = () => {
    isModalEditOpen.value = false;
    formEdit.reset();
    formEdit.clearErrors();
    idFormYangDiedit.value = null;
};

const submitFormEdit = () => {
    formEdit.put(route('pilar-form.update', idFormYangDiedit.value), {
        onStart: () => {
            Swal.fire({
                title: 'Memperbarui...',
                text: 'Sedang memperbarui skema form pilar',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        },
        onSuccess: () => {
            tutupDanResetModalEdit();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Struktur skema pilar berhasil diperbarui.',
                confirmButtonColor: '#10B981'
            });
        },
        onError: () => {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Pastikan semua isian data sudah benar.',
                confirmButtonColor: '#EF4444'
            });
        }
    });
};


// =========================================================
// [FITUR C] EKSEKUSI DELETE DATA (DESTROY)
// =========================================================
const formDelete = useForm({});

const eksekusiHapusForm = (id, namaPenyaluran) => {
    Swal.fire({
        title: 'Apakah Anda Yakin?',
        text: `Formulir "${namaPenyaluran}" akan dihapus permanen dari sistem!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            formDelete.delete(route('pilar-form.destroy', id), {
                onStart: () => {
                    Swal.fire({
                        title: 'Menghapus...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                },
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: 'Formulir pilar berhasil dihapus.',
                        confirmButtonColor: '#10B981'
                    });
                }
            });
        }
    });
};


// 4. MAPPING VISUAL PILAR (Ikon & Warna Tema Dinamis)
const dapatkanBadgePilar = (pilar) => {
    const daftarPilar = {
        pendidikan: { teks: 'Pendidikan', icon: '🎓', bg: 'bg-blue-50 text-blue-700 border-blue-200' },
        ekonomi: { teks: 'Ekonomi', icon: '💼', bg: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
        kesehatan: { teks: 'Kesehatan', icon: '🏥', bg: 'bg-red-50 text-red-700 border-red-200' },
        sosial_dakwah: { teks: 'Sosial Dakwah', icon: '🕌', bg: 'bg-purple-50 text-purple-700 border-purple-200' },
        kemanusiaan: { teks: 'Kemanusiaan', icon: '⛑️', bg: 'bg-amber-50 text-amber-700 border-amber-200' },
        lingkungan: { teks: 'Lingkungan', icon: '🌱', bg: 'bg-teal-50 text-teal-700 border-teal-200' },
    };
    return daftarPilar[pilar] || { teks: pilar, icon: '📋', bg: 'bg-gray-50 text-gray-700 border-gray-200' };
};

// Fungsi mengubah nama type menjadi label yang mudah dibaca di halaman utama staff
const dapatkanLabelType = (type) => {
    const listType = {
        text: 'Teks Biasa',
        nominal: 'Nominal Rp',
        nik_nbm: 'NIK / NBM',
        tel: 'No. WhatsApp',
        alamat: 'Alamat Lengkap',
        file: 'Berkas Dokumen'
    };
    return listType[type] || type;
};

// Modifikasi fungsi SLUGIFY agar aman untuk tracking key object form tanpa merusak value inputan asli
const slugify = (text) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '') // Hanya untuk key database / data-state tracker
        .replace(/[\s-]+/g, '_')
        .replace(/^_+|_+$/g, '');
};
</script>

<template>
    <StaffLayout>
    <div class="min-h-screen bg-gray-50 p-6">
        <div class="max-w-6xl mx-auto">

            <div
                class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Master Formulir Pilar</h1>
                    <p class="text-sm text-gray-500 mt-1">Kelola skema administrasi pengajuan dana untuk setiap pilar
                        penyaluran Lazismu.</p>
                </div>
                <button @click="isModalOpen = true"
                    class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:shadow transition flex items-center gap-2 text-sm">
                    <span>➕ Rancang Form Baru</span>
                </button>
            </div>

            <div v-if="pilarForms.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="item in pilarForms" :key="item.id"
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col justify-between hover:border-orange-200 transition-all duration-200 group">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold border flex items-center gap-1.5"
                                :class="dapatkanBadgePilar(item.pilar).bg">
                                <span>{{ dapatkanBadgePilar(item.pilar).icon }}</span>
                                {{ dapatkanBadgePilar(item.pilar).teks }}
                            </span>
                            <span :class="item.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                                class="text-[10px] uppercase font-bold px-2 py-0.5 rounded">
                                {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-gray-800 group-hover:text-orange-600 transition truncate">
                            {{ item.nama_penyaluran }}
                        </h3>

                        <div
                            class="mt-4 bg-gray-50 rounded-xl p-3 border border-gray-100 text-xs text-gray-600 space-y-1.5">
                            <div class="font-semibold text-gray-400 uppercase tracking-wider text-[10px] mb-1">
                                Daftar Syarat Berkas ({{ item.skema_form?.length || 0 }} Field):
                            </div>
                            <div v-for="(field, idx) in item.skema_form?.slice(0, 3)" :key="idx"
                                class="flex justify-between items-center">
                                <span class="truncate max-w-[150px] text-gray-700">• {{ field.field_name }}</span>
                                <span
                                    class="font-mono text-[10px] bg-white px-1.5 py-0.5 rounded border text-gray-500 font-medium">
                                    {{ dapatkanLabelType(field.type) }}
                                </span>
                            </div>
                            <p v-if="item.skema_form?.length > 3"
                                class="text-[11px] text-orange-500 italic font-medium pt-1">
                                + {{ item.skema_form.length - 3 }} Syarat Administrasi Lainnya...
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-2 border-t pt-4 mt-5">
                        <button @click="bukaModalEdit(item)"
                            class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-700 text-center font-semibold py-2 rounded-xl text-xs border transition">
                            📝 Edit Skema
                        </button>
                        <button @click="eksekusiHapusForm(item.id, item.nama_penyaluran)"
                            class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-2 rounded-xl text-xs border border-red-200 transition"
                            title="Hapus Template">
                            🗑️
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white rounded-2xl border border-dashed border-gray-200 p-12 text-center">
                <span class="text-4xl block mb-3">📋</span>
                <h3 class="text-lg font-bold text-gray-700">Belum Ada Struktur Formulir</h3>
                <p class="text-sm text-gray-400 mt-1 max-w-sm mx-auto">Silakan klik tombol "Rancang Form Baru" di atas
                    untuk membuat konfigurasi formulir mustahik pertama Anda.</p>
            </div>

            <div v-if="isModalOpen"
                class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center p-4">
                <div
                    class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto animate-fade-in">

                    <div class="flex justify-between items-center px-6 py-4 border-b sticky top-0 bg-white z-10">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Rancang Formulir Dinamis</h2>
                            <p class="text-xs text-gray-500">Form ini akan langsung merubah UI halaman depan secara otomatis.</p>
                        </div>
                        <button @click="tutupDanResetModal"
                            class="text-gray-400 hover:text-gray-600 font-bold text-xl p-1">✕</button>
                    </div>

                    <form @submit.prevent="submitForm" class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Nama Program Penyaluran <span class="text-red-500">*</span></label>
                                <input v-model="form.nama_penyaluran" type="text"
                                    placeholder="Contoh: Beasiswa Mentari / Peduli Guru"
                                    class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-orange-500 focus:ring-orange-500"
                                    required />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Pilih Pilar Lazismu <span class="text-red-500">*</span></label>
                                <select v-model="form.pilar"
                                    class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-orange-500 focus:ring-orange-500"
                                    required>
                                    <option value="" disabled>-- Pilih Pilar --</option>
                                    <option value="pendidikan">🎓 Pendidikan</option>
                                    <option value="ekonomi">💼 Ekonomi</option>
                                    <option value="kesehatan">🏥 Kesehatan</option>
                                    <option value="sosial_dakwah">🕌 Sosial Dakwah</option>
                                    <option value="kemanusiaan">⛑️ Kemanusiaan</option>
                                    <option value="lingkungan">🌱 Lingkungan</option>
                                </select>
                            </div>
                        </div>

                        <hr class="border-gray-100" />

                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700">Rancangan Baris Inputan (Fields)</h3>
                                <button type="button" @click="tambahField"
                                    class="bg-orange-50 text-orange-600 hover:bg-orange-100 font-bold px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1 border border-orange-200">
                                    <span>➕ Tambah Inputan</span>
                                </button>
                            </div>

                            <div class="space-y-3 max-h-[40vh] overflow-y-auto pr-2">
                                <div v-for="(item, index) in form.skema_form" :key="index"
                                    class="flex flex-wrap md:flex-nowrap items-center gap-3 p-3.5 bg-gray-50 rounded-xl border border-gray-200 shadow-sm relative">
                                    <span
                                        class="font-bold text-gray-400 bg-white px-2 py-1 rounded-md border text-xs shadow-inner">#{{ index + 1 }}</span>
                                    <div class="flex-1 min-w-[180px]">
                                        <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Nama Syarat Dokumen / Isian</label>
                                        <input v-model="item.field_name" type="text"
                                            placeholder="Contoh: Nomor NIK / No Anggota NBM / Alamat Rumah"
                                            class="w-full text-xs border-gray-300 rounded-lg focus:border-orange-500 focus:ring-orange-500"
                                            required />
                                    </div>
                                    <div class="w-full md:w-56">
                                        <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Tipe Inputan</label>
                                        <select v-model="item.type"
                                            class="w-full text-xs border-gray-300 rounded-lg focus:border-orange-500 focus:ring-orange-500">
                                            <option value="text">Teks Biasa (Huruf / Kata)</option>
                                            <option value="alamat">Alamat Lengkap (Bisa huruf, angka & / . , -)</option>
                                            <option value="nik_nbm">NIK / NBM / No.Rek (Angka Murni Tanpa Titik)</option>
                                            <option value="nominal">Angka Nominal Rupiah (Otomatis Titik Ribuan)</option>
                                            <option value="tel">Nomor HP / WA (Bisa Angka Awalan 0)</option>
                                            <option value="file">File Upload (PDF/Gambar)</option>
                                        </select>
                                    </div>
                                    <div class="w-full md:w-28">
                                        <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Sifat</label>
                                        <select v-model="item.required"
                                            class="w-full text-xs border-gray-300 rounded-lg focus:border-orange-500 focus:ring-orange-500">
                                            <option :value="true">Wajib</option>
                                            <option :value="false">Opsional</option>
                                        </select>
                                    </div>
                                    <div class="pt-4">
                                        <button type="button" @click="hapusField(index)"
                                            class="bg-red-50 text-red-600 hover:bg-red-100 p-2 rounded-lg border border-red-100 transition"
                                            title="Hapus">
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 border-t pt-4">
                            <button type="button" @click="tutupDanResetModal"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold px-4 py-2 rounded-xl text-sm transition">Batal</button>
                            <button type="submit" :disabled="form.processing"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2 rounded-xl shadow transition disabled:opacity-50">
                                💾 Simpan Struktur Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="isModalEditOpen"
                class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center p-4">
                <div
                    class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto animate-fade-in">

                    <div class="flex justify-between items-center px-6 py-4 border-b sticky top-0 bg-white z-10">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Edit Skema Formulir</h2>
                            <p class="text-xs text-gray-500">Perubahan data syarat ini akan langsung mengubah kolom isian pendaftaran publik.</p>
                        </div>
                        <button @click="tutupDanResetModalEdit"
                            class="text-gray-400 hover:text-gray-600 font-bold text-xl p-1">✕</button>
                    </div>

                    <form @submit.prevent="submitFormEdit" class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Nama Program Penyaluran <span class="text-red-500">*</span></label>
                                <input v-model="formEdit.nama_penyaluran" type="text"
                                    class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-orange-500 focus:ring-orange-500"
                                    required />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Pilih Pilar Lazismu <span class="text-red-500">*</span></label>
                                <select v-model="formEdit.pilar"
                                    class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-orange-500 focus:ring-orange-500"
                                    required>
                                    <option value="pendidikan">🎓 Pendidikan</option>
                                    <option value="ekonomi">💼 Ekonomi</option>
                                    <option value="kesehatan">🏥 Kesehatan</option>
                                    <option value="sosial_dakwah">🕌 Sosial Dakwah</option>
                                    <option value="kemanusiaan">⛑️ Kemanusiaan</option>
                                    <option value="lingkungan">🌱 Lingkungan</option>
                                </select>
                            </div>
                        </div>

                        <hr class="border-gray-100" />

                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700">Rancangan Baris Inputan (Fields)</h3>
                                <button type="button" @click="tambahFieldEdit"
                                    class="bg-orange-50 text-orange-600 hover:bg-orange-100 font-bold px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1 border border-orange-200">
                                    <span>➕ Tambah Inputan</span>
                                </button>
                            </div>

                            <div class="space-y-3 max-h-[40vh] overflow-y-auto pr-2">
                                <div v-for="(item, index) in formEdit.skema_form" :key="index"
                                    class="flex flex-wrap md:flex-nowrap items-center gap-3 p-3.5 bg-white rounded-xl border border-orange-100 shadow-sm relative">
                                    <span
                                        class="font-bold text-orange-500 bg-orange-50 px-2 py-1 rounded-md border border-orange-200 text-xs shadow-inner">#{{ index + 1 }}</span>
                                    <div class="flex-1 min-w-[180px]">
                                        <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Nama Syarat Dokumen / Isian</label>
                                        <input v-model="item.field_name" type="text"
                                            class="w-full text-xs border-gray-300 rounded-lg focus:border-orange-500 focus:ring-orange-500"
                                            required />
                                    </div>
                                    <div class="w-full md:w-56">
                                        <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Tipe Inputan</label>
                                        <select v-model="item.type"
                                            class="w-full text-xs border-gray-300 rounded-lg focus:border-orange-500 focus:ring-orange-500">
                                            <option value="text">Teks Biasa (Huruf / Kata)</option>
                                            <option value="alamat">Alamat Lengkap (Bisa huruf, angka & / . , -)</option>
                                            <option value="nik_nbm">NIK / NBM / No.Rek (Angka Murni Tanpa Titik)</option>
                                            <option value="nominal">Angka Nominal Rupiah (Otomatis Titik Ribuan)</option>
                                            <option value="tel">Nomor HP / WA (Bisa Angka Awalan 0)</option>
                                            <option value="file">File Upload (PDF/Gambar)</option>
                                        </select>
                                    </div>
                                    <div class="w-full md:w-28">
                                        <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Sifat</label>
                                        <select v-model="item.required"
                                            class="w-full text-xs border-gray-300 rounded-lg focus:border-orange-500 focus:ring-orange-500">
                                            <option :value="true">Wajib</option>
                                            <option :value="false">Opsional</option>
                                        </select>
                                    </div>
                                    <div class="pt-4">
                                        <button type="button" @click="hapusFieldEdit(index)"
                                            class="bg-red-50 text-red-600 hover:bg-red-100 p-2 rounded-lg border border-red-100 transition"
                                            title="Hapus">
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 border-t pt-4">
                            <button type="button" @click="tutupDanResetModalEdit"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold px-4 py-2 rounded-xl text-sm transition">Batal</button>
                            <button type="submit" :disabled="formEdit.processing"
                                class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-5 py-2 rounded-xl shadow transition disabled:opacity-50">
                                🆙 Perbarui Struktur Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
    </StaffLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
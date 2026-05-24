<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    applicants: Array
});

const getStatusBadge = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'disetujui':
            return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'ditolak':
            return 'bg-red-100 text-red-700 border-red-200';
        default:
            return 'bg-gray-100 text-gray-700 border-gray-200';
    }
};

const formatPilar = (text) => {
    return text.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 p-6">
        <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-md p-6">
            
            <div class="border-b pb-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Verifikasi Permohonan Dana Pilar</h1>
                <p class="text-sm text-gray-500">Daftar mustahik dan lembaga yang mengajukan dana bantuan melalui program pilar</p>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-4 text-center w-12">No</th>
                            <th class="px-6 py-4">No. Permohonan</th>
                            <th class="px-6 py-4">Nama Pemohon</th>
                            <th class="px-6 py-4">Program & Pilar</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="(item, index) in applicants" :key="item.id" class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-center font-medium text-gray-900">{{ index + 1 }}</td>
                            <td class="px-6 py-4 font-mono font-bold text-gray-600">{{ item.nomor_permohonan }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-800"> {{ item.biodata_dinamis?.['Nama'] || item.biodata_dinamis?.['Nama Pemohon'] || 'Tanpa Nama' }}</td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800">{{ item.pilar_form?.nama_penyaluran }}</div>
                                <div class="text-xs text-orange-600 font-semibold">{{ formatPilar(item.pilar_form?.pilar) }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span :class="`px-3 py-1 rounded-full text-xs font-bold border ${getStatusBadge(item.status_permohonan)}`">
                                    {{ item.status_permohonan.toUpperCase() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <Link :href="route('staff.applicants.show', item.id)" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium px-4 py-1.5 rounded-lg text-xs transition shadow-sm">
                                    🔍 Periksa & Cairkan
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="applicants.length === 0">
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                Belum ada berkas permohonan masuk yang terdaftar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</template>
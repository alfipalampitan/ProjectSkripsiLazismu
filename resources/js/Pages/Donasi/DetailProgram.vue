<script setup>
import { ref } from 'vue';
import ModalDonasi from '@/Components/ModalDonasi.vue'; // Impor komponennya

const props = defineProps({
    program: Object,
    donatur: Array
});

const isModalOpen = ref(false);

</script>

<template>
    <div class="max-w-4xl mx-auto p-4 md:p-8 bg-gray-50 min-h-screen pb-24 md:pb-8">
        <div class="relative group overflow-hidden rounded-3xl shadow-lg mb-8">
            <img :src="`/storage/${program.gambar}`"
                class="w-full h-64 md:h-96 object-cover transition-transform duration-500 group-hover:scale-105">
            <div class="absolute top-4 left-4">
                <span
                    class="bg-orange-500 text-white text-xs font-bold px-4 py-2 rounded-full uppercase tracking-wider shadow-lg">
                    {{ program.kategori }}
                </span>
            </div>
        </div>

        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-6 mb-10">
            <div class="flex-1">
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight mb-4">
                    {{ program.judul }}
                </h1>

                <div class="space-y-4 mb-8">
                    <div class="flex justify-between items-end">
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">Dana
                                Terkumpul</span>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black text-orange-500">
                                    Rp {{ Number(program.terkumpul || 0).toLocaleString('id-ID') }}
                                </span>
                                <span class="text-sm text-gray-400 font-medium">
                                    dari Rp {{ Number(program.target_dana || 0).toLocaleString('id-ID') }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-black text-gray-800">
                                {{ Math.round(((program.terkumpul || 0) / (program.target_dana || 1)) * 100) }}%
                            </span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden shadow-inner">
                        <div class="bg-orange-500 h-full rounded-full transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(249,115,22,0.3)]"
                            :style="{ width: `${Math.min(((program.terkumpul || 0) / (program.target_dana || 1)) * 100, 100)}%` }">
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-sm">
                        <div class="flex items-center gap-2 text-gray-600">
                            <i class="fa-solid fa-users text-orange-400"></i>
                            <span class="font-bold">{{ donatur.length }}</span>
                            <span>Donatur telah bergabung</span>
                        </div>
                        <div class="text-orange-600 font-bold bg-orange-50 px-3 py-1 rounded-lg">
                            {{ program.kategori }}
                        </div>
                    </div>
                </div>


            </div>

            <div class="hidden md:block">
                <button @click="isModalOpen = true"
                    class="bg-orange-500 hover:bg-orange-600 text-white px-10 py-5 rounded-2xl font-bold text-xl shadow-xl hover:shadow-orange-200 transition-all active:scale-95">
                    Donasi Sekarang
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-7 xl:col-span-6 space-y-8">
                <div class="bg-white p-6 md:p-10 rounded-[2.5rem] shadow-sm border border-gray-100">
                    <h2 class="text-2xl font-bold mb-8 text-gray-800 flex items-center">
                        <span class="w-2 h-8 bg-orange-500 rounded-full mr-4"></span>
                        Tentang Program
                    </h2>
                    <div class="text-gray-600 leading-relaxed text-lg whitespace-pre-line px-2">
                        {{ program.deskripsi }}
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 xl:col-span-6">
                <div class="bg-white p-6 md:p-8 rounded-[2.5rem] shadow-sm border border-gray-100 sticky top-6">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-xl font-bold text-gray-800">Donatur Terbaru</h2>
                        <span class="bg-orange-50 text-orange-600 text-xs font-bold px-3 py-1 rounded-full">
                            {{ donatur.length }} Orang
                        </span>
                    </div>

                    <div v-if="donatur.length > 0" class="space-y-4">
                        <div v-for="d in donatur" :key="d.id"
                            class="group flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 hover:bg-orange-50/50 border border-transparent hover:border-orange-100">

                            <div class="relative">
                                <div
                                    class="w-14 h-14 bg-gradient-to-br from-orange-100 to-orange-200 text-orange-600 rounded-2xl flex items-center justify-center font-extrabold text-xl shadow-sm group-hover:rotate-6 transition-transform">
                                    {{ (d.user_name || 'H').charAt(0).toUpperCase() }}
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p
                                    class="font-bold text-gray-900 truncate group-hover:text-orange-600 transition-colors">
                                    {{ d.user_name }}
                                </p>
                                <div class="flex items-center gap-2 mt-1">
                                    <i class="fa-regular fa-clock text-[10px] text-gray-400"></i>
                                    <p class="text-xs text-gray-400 italic">
                                        {{ new Date(d.created_at).toLocaleDateString('id-ID', {
                                            day: 'numeric', month:
                                        'short' }) }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-right">
                                <p class="text-orange-600 font-black text-base">
                                    Rp{{ Number(d.jumlah || 0).toLocaleString('id-ID') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-12 px-4">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-heart-pulse text-gray-200 text-2xl"></i>
                        </div>
                        <p class="text-gray-400 text-sm italic">Belum ada donatur.<br>Jadilah yang pertama berbagi
                            kebaikan!</p>
                    </div>

                    <button v-if="donatur.length > 5"
                        class="w-full mt-6 py-3 text-sm font-bold text-gray-400 hover:text-orange-500 transition-colors border-t border-gray-50">
                        Lihat Semua Donatur
                    </button>
                </div>
            </div>
        </div>

        <div
            class="fixed bottom-0 left-0 right-0 p-4 bg-white/80 backdrop-blur-md border-t border-gray-100 md:hidden z-50">
            <button @click="isModalOpen = true"
                class="w-full bg-orange-500 text-white py-4 rounded-2xl font-bold text-lg shadow-lg active:scale-[0.98] transition-transform">
                Donasi Sekarang
            </button>
        </div>

        <ModalDonasi :isOpen="isModalOpen" :program="program" @close="isModalOpen = false" />
    </div>
</template>

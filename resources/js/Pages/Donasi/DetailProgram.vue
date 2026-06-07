<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3'; // 👈 Impor Link dari Inertia untuk navigasi back
import ModalDonasi from '@/Components/ModalDonasi.vue';

const props = defineProps({
    program: Object,
    donatur: Array
});

const isModalOpen = ref(false);
</script>

<template>
    <div class="max-w-6xl mx-auto p-4 md:p-8 bg-gray-50 min-h-screen pt-20 md:pt-8 pb-24 md:pb-8 relative">
        
        <div class="fixed md:absolute top-4 left-4 z-50 md:z-10">
            <Link :href="route('pilih.program')" 
                class="flex items-center justify-center bg-white/90 backdrop-blur-md md:bg-white text-gray-700 hover:text-orange-500 w-11 h-11 rounded-xl shadow-md border border-gray-100 hover:border-orange-100 transition-all active:scale-95 group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 transition-transform group-hover:-translate-x-0.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </Link>
        </div>

        <div class="bg-white rounded-[2.5rem] p-4 md:p-6 shadow-sm border border-gray-100 flex flex-col md:flex-row gap-6 md:gap-8 mb-8">
            
            <div class="w-full md:w-1/2 relative overflow-hidden rounded-2xl md:rounded-3xl shadow-inner bg-gray-100 flex items-center">
                <img :src="`/storage/${program.gambar}`"
                    class="w-full h-56 sm:h-72 md:h-[350px] object-cover transition-transform duration-500 hover:scale-105">
                <div class="absolute top-4 left-4">
                    <span class="bg-orange-500 text-white text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-wider shadow-md ml-14 md:ml-0">
                        {{ program.kategori }}
                    </span>
                </div>
            </div>

            <div class="w-full md:w-1/2 flex flex-col justify-between py-2">
                <div>
                    <span class="text-xs font-bold text-orange-500 uppercase tracking-widest block mb-1">
                        {{ program.kategori }}
                    </span>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 leading-tight mb-3">
                        {{ program.judul }}
                    </h1>
                    
                    <p class="text-xs md:text-sm text-gray-500 line-clamp-3 mb-6 leading-relaxed">
                        {{ program.deskripsi }}
                    </p>
                </div>

                <div class="space-y-4 bg-gray-50/50 p-4 rounded-2xl border border-gray-100/50">
                    <div class="flex justify-between items-end">
                        <div class="space-y-1">
                            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">
                                {{ program.target_dana && parseInt(program.target_dana) > 0 ? 'Dana Terkumpul' : 'Dana Terus Dikumpul' }}
                            </span>
                            
                            <div class="flex flex-col sm:flex-row sm:items-baseline gap-1 sm:gap-2">
                                <span class="text-2xl md:text-3xl font-black text-gray-900 leading-none">
                                    Rp {{ Number(program.terkumpul || 0).toLocaleString('id-ID') }}
                                </span>
                                <span v-if="program.target_dana && parseInt(program.target_dana) > 0" class="text-xs text-gray-400 font-medium">
                                    dari Rp {{ Number(program.target_dana || 0).toLocaleString('id-ID') }}
                                </span>
                            </div>
                        </div>

                        <div v-if="program.target_dana && parseInt(program.target_dana) > 0" class="text-right">
                            <span class="text-xl font-black text-orange-500">
                                {{ Math.round(((program.terkumpul || 0) / (program.target_dana || 1)) * 100) }}%
                            </span>
                        </div>
                    </div>

                    <div v-if="program.target_dana && parseInt(program.target_dana) > 0" class="w-full bg-gray-200/70 h-2.5 rounded-full overflow-hidden shadow-inner">
                        <div class="bg-orange-500 h-full rounded-full transition-all duration-1000 ease-out"
                            :style="{ width: `${Math.min(((program.terkumpul || 0) / (program.target_dana || 1)) * 100, 100)}%` }">
                        </div>
                    </div>
                    
                    <div v-else class="w-full bg-orange-500 h-[3.5px] rounded-full mt-1 shadow-sm"></div>

                    <div class="flex justify-between items-center text-xs text-gray-500 pt-0.5">
                        <div class="flex items-center gap-1.5 font-semibold">
                            <span class="text-gray-900 font-black text-sm">{{ donatur.length }}</span>
                            <span class="text-gray-400">Donatur</span>
                        </div>
                        <div v-if="!(program.target_dana && parseInt(program.target_dana) > 0)" class="text-gray-400 font-medium italic">
                            Open Ended
                        </div>
                    </div>
                </div>

                <div class="hidden md:block mt-6">
                    <button @click="isModalOpen = true"
                        class="w-full bg-orange-500 hover:bg-orange-600 text-white py-4 rounded-xl font-extrabold text-base shadow-md hover:shadow-orange-200 transition-all active:scale-[0.99]">
                        Tunalkan Sekarang
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 items-start">
            
            <div class="lg:col-span-7 space-y-8">
                <div class="bg-white p-6 md:p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold mb-6 text-gray-800 flex items-center">
                        <span class="w-1.5 h-6 bg-orange-500 rounded-full mr-3"></span>
                        Deskripsi
                    </h2>
                    <div class="text-gray-600 leading-relaxed text-base whitespace-pre-line px-1">
                        {{ program.deskripsi }}
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="bg-white p-6 md:p-8 rounded-[2.5rem] shadow-sm border border-gray-100 sticky top-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-gray-800">Donatur</h2>
                        <span class="bg-orange-50 text-orange-600 text-xs font-bold px-3 py-1 rounded-full">
                            {{ donatur.length }} Orang
                        </span>
                    </div>

                    <div v-if="donatur.length > 0" class="space-y-4 max-h-[400px] overflow-y-auto pr-1 custom-scrollbar">
                        <div v-for="d in donatur" :key="d.id"
                            class="group flex items-center gap-3 p-3 rounded-xl transition-all duration-300 hover:bg-gray-50 border border-transparent hover:border-gray-100">

                            <div>
                                <div class="w-11 h-11 bg-gradient-to-br from-orange-100 to-orange-200 text-orange-600 rounded-xl flex items-center justify-center font-black text-base shadow-sm">
                                    {{ (d.user_name || 'H').charAt(0).toUpperCase() }}
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-900 truncate text-sm">
                                    {{ d.user_name }}
                                </p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <p class="text-[11px] text-gray-400">
                                        {{ new Date(d.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-right">
                                <p class="text-gray-900 font-extrabold text-sm">
                                    Rp{{ Number(d.jumlah || 0).toLocaleString('id-ID') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-12 px-4">
                        <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fa-solid fa-heart-pulse text-gray-200 text-xl"></i>
                        </div>
                        <p class="text-gray-400 text-xs italic">Belum ada donatur.<br>Jadilah yang pertama berbagi kebaikan!</p>
                    </div>

                    <button v-if="donatur.length > 5"
                        class="w-full mt-4 pt-4 text-xs font-bold text-gray-400 hover:text-orange-500 transition-colors border-t border-gray-100">
                        Look Semua Donatur
                    </button>
                </div>
            </div>
        </div>

        <div class="fixed bottom-0 left-0 right-0 p-4 bg-white/90 backdrop-blur-md border-t border-gray-100 md:hidden z-50">
            <button @click="isModalOpen = true"
                class="w-full bg-orange-500 text-white py-3.5 rounded-xl font-bold text-base shadow-md active:scale-[0.98] transition-transform">
                Tunalkan Sekarang
            </button>
        </div>

        <ModalDonasi :isOpen="isModalOpen" :program="program" @close="isModalOpen = false" />
    </div>
</template>
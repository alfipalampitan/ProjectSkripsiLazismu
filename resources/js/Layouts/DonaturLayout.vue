<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3'; // Import usePage untuk cek URL aktif

const isMobileMenuOpen = ref(false);

// Gunakan URL path yang sesuai dengan route di Laravel kamu
const navLinks = [
    { name: 'Donasi Sekarang', icon: 'fa-hand-holding-heart', url: '/pilih-program', component: 'Donasi/PilihProgram' },
    { name: 'Transparansi Dana', icon: 'fa-chart-pie', url: '/transparansi', component: 'Donasi/Transparansi' },
    { name: 'Kalkulator Zakat', icon: 'fa-calculator', url: '/zakat', component: 'Donasi/Kalkulator' },
];

// Fungsi untuk mengecek apakah menu sedang aktif
const isActive = (link) => {
    // Jika pakai Ziggy (disarankan):
    // return route().current(link.routeName); 

    // Jika pakai URL path (lebih aman jika route name belum diatur):
    return usePage().url === link.url || usePage().component === link.component;
};
</script>

<template>
    <div class="min-h-screen bg-white flex flex-col md:flex-row">
        
        <!-- SIDEBAR NAV -->
        <aside class="w-full md:w-72 bg-white border-r border-gray-100 flex flex-col md:sticky md:top-0 md:h-screen z-50">
            <div class="p-6 flex items-center justify-between border-b border-gray-50 md:h-24">
                <Link href="/" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center shadow-lg shadow-orange-200">
                        <i class="fa-solid fa-heart text-white"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xl font-black text-gray-800 leading-none tracking-tight">LAZIS<span class="text-orange-500">MU</span></span>
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Portal Donasi</span>
                    </div>
                </Link>
                <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="md:hidden text-gray-500 p-2">
                    <i :class="['fa-solid text-2xl', isMobileMenuOpen ? 'fa-xmark' : 'fa-bars-staggered']"></i>
                </button>
            </div>

            <!-- Navigasi Utama -->
            <nav :class="[isMobileMenuOpen ? 'block' : 'hidden', 'md:block flex-grow p-6 space-y-3']">
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 ml-2">Menu Utama</div>
                
                <Link v-for="link in navLinks" :key="link.name" :href="link.url"
                    class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 group"
                    :class="[
                        isActive(link) 
                        ? 'bg-orange-500 text-white shadow-lg shadow-orange-200 translate-x-2' 
                        : 'text-gray-600 hover:bg-orange-50 hover:text-orange-500'
                    ]">
                    
                    <div class="w-8 flex justify-center">
                        <i :class="[
                            'fa-solid text-lg transition-colors', 
                            link.icon,
                            isActive(link) ? 'text-white' : 'text-orange-500 group-hover:text-orange-600'
                        ]"></i>
                    </div>
                    <span class="font-bold text-sm">{{ link.name }}</span>
                </Link>
            </nav>

            <!-- Bantuan -->
            <div :class="[isMobileMenuOpen ? 'block' : 'hidden', 'md:block p-6 border-t border-gray-50']">
                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                    <p class="text-xs text-gray-500 font-bold mb-3 uppercase tracking-tight">Butuh Bantuan?</p>
                    <a href="#" class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition-all active:scale-95 shadow-md">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                        Chat Admin
                    </a>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 flex flex-col min-w-0">
            <header class="h-20 bg-white/80 backdrop-blur-md border-b border-gray-50 px-6 md:px-10 flex items-center justify-between sticky top-0 z-40 hidden md:flex">
                <div class="relative w-full max-w-md">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                    <input type="text" placeholder="Cari program kebaikan..." class="w-full pl-12 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-orange-100 transition-all outline-none">
                </div>
            </header>

            <div class="flex-1 overflow-x-hidden p-6 md:p-10">
                <slot />
            </div>
        </main>
    </div>
</template>

<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
}
</style>
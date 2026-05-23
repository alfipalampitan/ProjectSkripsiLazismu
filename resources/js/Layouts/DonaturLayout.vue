<script setup>
import { ref, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// 1. Logic Persistensi Menu (Menyimpan status terakhir di perangkat)
const getInitialMenuState = () => {
    if (typeof window === 'undefined') return false;
    const savedState = localStorage.getItem('donatur_menu_open');
    return savedState !== null ? JSON.parse(savedState) : window.innerWidth > 768;
};

const isMobileMenuOpen = ref(getInitialMenuState());

// Simpan status menu ke storage setiap kali diubah oleh pengguna
watch(isMobileMenuOpen, (newValue) => {
    localStorage.setItem('donatur_menu_open', JSON.stringify(newValue));
});

const navLinks = [
    { name: 'Donasi Sekarang', icon: 'fa-hand-holding-heart', url: '/pilih-program', component: 'Donasi/PilihProgram' },
    { name: 'Transparansi Dana', icon: 'fa-chart-pie', url: '/transparansi', component: 'Donasi/Transparansi' },
    { name: 'Kalkulator Zakat', icon: 'fa-calculator', url: '/zakat', component: 'Donasi/Kalkulator' },
];

const isActive = (link) => {
    return usePage().url === link.url || usePage().component === link.component;
};

const page = usePage();

// 2. Auto-close menu di mobile saat donatur berhasil berpindah halaman
watch(() => page.url, () => {
    if (window.innerWidth <= 768) {
        isMobileMenuOpen.value = false;
    }
});

onMounted(() => {
    window.addEventListener('resize', () => {
        if (window.innerWidth <= 768) {
            isMobileMenuOpen.value = false;
        } else {
            isMobileMenuOpen.value = getInitialMenuState();
        }
    });
});
</script>

<template>
    <div class="min-h-screen bg-[#F3F4F6] flex relative overflow-hidden">
        
        <!-- Overlay Mobile (Gaya Staff Layout: Latar belakang hitam transparan saat menu HP aktif) -->
        <transition name="fade">
            <div 
                v-if="isMobileMenuOpen" 
                @click="isMobileMenuOpen = false" 
                class="fixed inset-0 bg-gray-900/50 z-[60] md:hidden backdrop-blur-sm transition-opacity"
            ></div>
        </transition>

        <!-- SIDEBAR NAV -->
        <aside 
            :class="[
                'bg-white shadow-2xl md:shadow-none border-r border-gray-100 flex flex-col z-[70]',
                'fixed inset-y-0 left-0 transform md:sticky md:top-0 md:h-screen transition-all duration-500',
                isMobileMenuOpen ? 'translate-x-0 w-72' : '-translate-x-full md:translate-x-0 md:w-20'
            ]"
        >
            <!-- Logo Area & Tombol Silang khusus Mobile -->
            <div class="p-6 flex items-center justify-between border-b border-gray-50 h-20 md:h-24">
                <Link href="/" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center shadow-lg shadow-orange-200">
                        <i class="fa-solid fa-heart text-white"></i>
                    </div>
                    <!-- Sembunyikan teks LAZISMU jika posisi menu di desktop sedang mengecil (md:w-20) -->
                    <div v-if="isMobileMenuOpen" class="flex flex-col transition-all">
                        <span class="text-xl font-black text-gray-800 leading-none tracking-tight">LAZIS<span class="text-orange-500">MU</span></span>
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Portal Donasi</span>
                    </div>
                </Link>
                
                <!-- Tombol Close khusus Mobile -->
                <button @click="isMobileMenuOpen = false" class="md:hidden text-gray-500 p-2 focus:outline-none">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </div>

            <!-- Navigasi Utama -->
            <nav class="flex-grow p-4 space-y-3 overflow-y-auto custom-scrollbar">
                <div v-if="isMobileMenuOpen" class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 ml-2">Menu Utama</div>
                
                <Link v-for="link in navLinks" :key="link.name" :href="link.url"
                    class="flex items-center gap-4 p-3.5 rounded-2xl transition-all duration-300 group"
                    :class="[
                        isActive(link) 
                        ? 'bg-orange-500 text-white shadow-lg shadow-orange-200 translate-x-1' 
                        : 'text-gray-600 hover:bg-orange-50 hover:text-orange-500'
                    ]">
                    
                    <div class="w-8 flex justify-center flex-shrink-0">
                        <i :class="[
                            'fa-solid text-lg transition-colors', 
                            link.icon,
                            isActive(link) ? 'text-white' : 'text-orange-500 group-hover:text-orange-600'
                        ]"></i>
                    </div>
                    <span v-if="isMobileMenuOpen" class="font-bold text-sm transition-all truncate">{{ link.name }}</span>
                </Link>
            </nav>

            <!-- Bantuan (Otomatis sembunyi kalau menu menciut agar tidak merusak layout) -->
            <div v-if="isMobileMenuOpen" class="p-6 border-t border-gray-50 transition-all">
                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                    <p class="text-xs text-gray-500 font-bold mb-3 uppercase tracking-tight">Butuh Bantuan?</p>
                    <a href="#" class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition-all active:scale-95 shadow-md">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                        Chat Admin
                    </a>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
            
            <!-- Header Mobile & Desktop (Posisi Tombol Hamburger yang Sudah Diperbaiki) -->
            <header class="bg-white shadow-sm border-b border-gray-50 h-16 md:h-20 flex items-center justify-between px-4 md:px-10 z-50">
                <div class="flex items-center gap-4">
                    <!-- Tombol Hamburger Utama (Sekarang diletakkan di luar aside, dijamin 100% bisa diklik di PC/HP) -->
                    <button @click="isMobileMenuOpen = !isMobileMenuOpen" 
                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 text-gray-500 hover:bg-orange-50 hover:text-orange-500 transition-all focus:outline-none z-[80] relative">
                        <i :class="['fa-solid transition-transform duration-500 text-lg', isMobileMenuOpen ? 'fa-bars-staggered' : 'fa-bars']"></i>
                    </button>
                    
                    <!-- Judul Slot Header -->
                    <h2 class="font-black text-gray-800 text-sm md:text-lg tracking-tight truncate">
                        <slot name="header" />
                    </h2>
                </div>
                
                <!-- Fitur Cari Khusus Layar Lebar (Desktop) -->
                <div class="relative w-full max-w-md hidden md:block">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                    <input type="text" placeholder="Cari program kebaikan..." class="w-full pl-12 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-orange-100 transition-all outline-none">
                </div>
            </header>

            <!-- Halaman Utama Utama (Scrollable Mandiri) -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 md:p-10 scroll-smooth bg-white">
                <div class="max-w-7xl mx-auto">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #E5E7EB;
    border-radius: 10px;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
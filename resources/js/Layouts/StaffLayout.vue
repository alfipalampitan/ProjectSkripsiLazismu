<script setup>
import { ref, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

// 1. Logic Persistensi Sidebar (Sesuai permintaan Abang)
const getInitialSidebarState = () => {
    if (typeof window === 'undefined') return true;
    const savedState = localStorage.getItem('sidebar_open');
    return savedState !== null ? JSON.parse(savedState) : window.innerWidth > 768;
};

const isSidebarOpen = ref(getInitialSidebarState());

// Simpan status ke storage setiap kali berubah
watch(isSidebarOpen, (newValue) => {
    localStorage.setItem('sidebar_open', JSON.stringify(newValue));
});

// 2. Menu Items Khusus Staff
const menuItems = [
    { name: 'Dashboard', icon: 'fa-chart-pie', route: 'staff.dashboard' },
    { name: 'Donasi Masuk', icon: 'fa-hand-holding-dollar', route: 'donasi-tunai.index' }, // Sesuaikan nama route-nya
    { name: 'Input Pengeluaran', icon: 'fa-file-signature', route: '#' }, // Contoh route nanti
    { name: 'Laporan Saya', icon: 'fa-clipboard-list', route: '#' },
];

const page = usePage();

// 3. Auto-close sidebar di mobile saat pindah halaman
watch(() => page.url, () => {
    if (window.innerWidth <= 768) {
        isSidebarOpen.value = false;
    }
});

onMounted(() => {
    window.addEventListener('resize', () => {
        if (window.innerWidth <= 768) {
            isSidebarOpen.value = false;
        } else {
            // Balik ke state terakhir di storage kalau balik ke desktop
            isSidebarOpen.value = getInitialSidebarState();
        }
    });
});
</script>

<template>
    <div class="min-h-screen bg-[#F3F4F6] flex relative overflow-hidden">
        
        <!-- Overlay Mobile (Persis permintaan Abang) -->
        <transition name="fade">
            <div 
                v-if="isSidebarOpen" 
                @click="isSidebarOpen = false" 
                class="fixed inset-0 bg-gray-900/50 z-[60] md:hidden backdrop-blur-sm transition-opacity"
            ></div>
        </transition>

        <!-- Sidebar -->
        <aside 
            :class="[
                'bg-white shadow-2xl transition-all duration-500 flex flex-col z-[70]',
                'fixed inset-y-0 left-0 transform md:relative md:translate-x-0',
                isSidebarOpen ? 'translate-x-0 w-72' : '-translate-x-full md:translate-x-0 md:w-20'
            ]"
        >
            <!-- Logo Area -->
            <div class="p-6 flex items-center justify-center border-b border-gray-50 h-20 md:h-24">
                <Link :href="route('staff.dashboard')" class="flex items-center">
                    <div v-if="isSidebarOpen" class="text-2xl font-black text-orange-500 tracking-tighter flex items-center">
                        <div class="w-10 h-10 bg-orange-500 rounded-xl mr-3 flex items-center justify-center shadow-lg shadow-orange-200">
                             <i class="fa-solid fa-users-gear text-white text-sm"></i>
                        </div>
                        STAFF<span class="text-gray-800">MU</span>
                    </div>
                    <div v-else class="hidden md:flex w-12 h-12 bg-orange-500 rounded-2xl items-center justify-center shadow-lg shadow-orange-200 transition-all hover:rotate-6">
                        <span class="text-white font-black text-xl">S</span>
                    </div>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="mt-6 flex-grow px-4 space-y-1.5 overflow-y-auto custom-scrollbar">
                <div v-if="isSidebarOpen" class="px-4 mb-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Menu Staff</div>
                
                <Link v-for="item in menuItems" :key="item.name" 
                    :href="item.route === '#' ? '#' : route(item.route)"
                    :class="[
                        route().current(item.route) 
                        ? 'bg-orange-500 text-white shadow-lg shadow-orange-200' 
                        : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600',
                        'flex items-center p-3.5 rounded-2xl transition-all duration-300 group relative'
                    ]">
                    <i :class="['fa-solid', item.icon, 'text-lg w-10 text-center transition-transform group-hover:scale-110']"></i>
                    <span v-if="isSidebarOpen" class="font-bold text-sm tracking-tight">{{ item.name }}</span>
                </Link>
            </nav>

            <!-- User Info & Logout (Bottom Area) -->
            <div class="p-4 border-t border-gray-50">
                <Link :href="route('logout')" method="post" as="button"
                    class="w-full flex items-center p-3.5 rounded-2xl text-red-500 hover:bg-red-50 transition-all font-bold group">
                    <i class="fa-solid fa-right-from-bracket text-lg w-10 text-center"></i>
                    <span v-if="isSidebarOpen" class="text-sm">Keluar Sistem</span>
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
            
            <!-- Header -->
            <header class="bg-white shadow-sm h-16 md:h-20 flex items-center justify-between px-4 md:px-10 z-50">
                <div class="flex items-center">
                    <button @click="isSidebarOpen = !isSidebarOpen" 
                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 text-gray-500 hover:bg-orange-50 hover:text-orange-500 transition-all focus:outline-none">
                        <i :class="['fa-solid transition-transform duration-500', isSidebarOpen ? 'fa-bars-staggered' : 'fa-bars']"></i>
                    </button>
                    <h2 class="ml-4 font-black text-gray-800 text-sm md:text-lg tracking-tight truncate">
                        <slot name="header" />
                    </h2>
                </div>
                
                <div class="flex items-center">
                    <Dropdown align="right" width="56">
                        <template #trigger>
                            <button class="flex items-center group focus:outline-none">
                                <div class="text-right mr-3 hidden sm:block">
                                    <p class="text-xs font-black text-gray-800 leading-none">{{ $page.props.auth.user.name }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase mt-1 tracking-widest">Staff Amil</p>
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center border-2 border-white shadow-sm overflow-hidden group-hover:border-orange-200 transition-all">
                                    <i class="fa-solid fa-user text-gray-400"></i>
                                </div>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Edit Profil</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-500">Logout</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Main Scrollable Section -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 md:p-10 scroll-smooth bg-[#F8FAFC]">
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
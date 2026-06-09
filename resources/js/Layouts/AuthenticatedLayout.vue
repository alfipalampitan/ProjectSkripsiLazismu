<script setup>
import { ref, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

// 1. Logic Persistensi Sidebar (Sesuai kode Abang)
const getInitialSidebarState = () => {
    if (typeof window === 'undefined') return true;
    const savedState = localStorage.getItem('sidebar_open');
    return savedState !== null ? JSON.parse(savedState) : window.innerWidth > 768;
};

const isSidebarOpen = ref(getInitialSidebarState());

watch(isSidebarOpen, (newValue) => {
    localStorage.setItem('sidebar_open', JSON.stringify(newValue));
});

// 2. Menu Items Admin
const menuItems = [
    { name: 'Dashboard', icon: 'fa-chart-line', route: 'admin.dashboard' },
    { name: 'Transaksi', icon: 'fa-exchange-alt', route: 'transaksi' },
    { name: 'Kelola Pengguna', icon: 'fa-users', route: 'pengguna' },
    { name: 'Program Donasi', icon: 'fa-hand-holding-heart', route: 'programs.index' },
    { name: 'Laporan', icon: 'fa-file-invoice', route: 'admin.laporan.index' },
    { name: 'Pengaturan', icon: 'fa-cog', route: 'settings.index' },
];

const page = usePage();

// 3. Auto-close di mobile
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
            isSidebarOpen.value = getInitialSidebarState();
        }
    });
});
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC] flex relative overflow-hidden font-jakarta">
        
        <!-- Overlay Mobile (Smooth Transition) -->
        <transition name="fade">
            <div 
                v-if="isSidebarOpen" 
                @click="isSidebarOpen = false" 
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[60] md:hidden transition-opacity"
            ></div>
        </transition>

        <!-- Sidebar -->
        <aside 
            :class="[
                'bg-white shadow-2xl transition-all duration-500 flex flex-col z-[70]',
                'fixed inset-y-0 left-0 transform md:relative md:translate-x-0',
                isSidebarOpen ? 'translate-x-0 w-72' : '-translate-x-full md:translate-x-0 md:w-24'
            ]"
        >
            <!-- Logo Area -->
            <div class="p-6 flex items-center justify-center border-b border-gray-50 h-20 md:h-24">
                <Link :href="route('admin.dashboard')" class="flex items-center">
                    <div v-if="isSidebarOpen" class="text-2xl font-black text-orange-500 tracking-tighter flex items-center">
                        <div class="w-10 h-10 bg-orange-500 rounded-xl mr-3 flex items-center justify-center shadow-lg shadow-orange-200">
                             <i class="fa-solid fa-shield-halved text-white text-sm"></i>
                        </div>
                        ADMIN<span class="text-gray-800">MU</span>
                    </div>
                    <div v-else class="hidden md:flex w-12 h-12 bg-orange-500 rounded-2xl items-center justify-center shadow-lg shadow-orange-200 transition-all hover:rotate-12">
                        <span class="text-white font-black text-xl">A</span>
                    </div>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="mt-6 flex-grow px-4 space-y-1.5 overflow-y-auto custom-scrollbar">
                <div v-if="isSidebarOpen" class="px-4 mb-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Management</div>
                
                <Link v-for="item in menuItems" :key="item.name" 
                    :href="item.route === '#' ? '#' : route(item.route)"
                    :class="[
                        item.route !== '#' && route().current(item.route) 
                        ? 'bg-gradient-to-tr from-orange-500 to-orange-400 text-white shadow-md shadow-orange-200' 
                        : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600',
                        'flex items-center p-3.5 rounded-2xl transition-all duration-300 group relative overflow-hidden'
                    ]">
                    <!-- Active Line Indicator -->
                    <div v-if="item.route !== '#' && route().current(item.route)" class="absolute left-0 w-1 h-6 bg-white rounded-r-full"></div>
                    
                    <i :class="['fa-solid', item.icon, 'text-lg w-10 text-center transition-transform group-hover:scale-110']"></i>
                    <span v-if="isSidebarOpen" class="font-bold text-sm tracking-tight">{{ item.name }}</span>
                </Link>
            </nav>

            <!-- Bottom Section -->
            <div class="p-4 border-t border-gray-50">
                <Link :href="route('logout')" method="post" as="button"
                    class="w-full flex items-center p-3.5 rounded-2xl text-red-500 hover:bg-red-50 transition-all font-bold group">
                    <i class="fa-solid fa-power-off text-lg w-10 text-center group-hover:-rotate-12 transition-transform"></i>
                    <span v-if="isSidebarOpen" class="ml-1 text-sm tracking-tight">Keluar Sistem</span>
                </Link>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
            
            <!-- Header -->
            <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 h-16 md:h-20 flex items-center justify-between px-4 md:px-10 z-50">
                <div class="flex items-center">
                    <button @click="isSidebarOpen = !isSidebarOpen" 
                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 text-gray-500 hover:bg-orange-50 hover:text-orange-500 transition-all shadow-sm focus:outline-none">
                        <i :class="['fa-solid transition-transform duration-500', isSidebarOpen ? 'fa-xmark' : 'fa-bars-staggered']"></i>
                    </button>
                    
                    <div class="ml-4 md:ml-6 flex flex-col">
                        <h2 class="font-black text-gray-800 text-sm md:text-lg tracking-tight leading-none truncate max-w-[150px] md:max-w-none">
                            <slot name="header" />
                        </h2>
                        <span class="hidden md:block text-[10px] text-orange-500 font-bold uppercase mt-1 tracking-widest">Administrator Panel</span>
                    </div>
                </div>
                
                <!-- Profile Section -->
                <div class="flex items-center space-x-2 md:space-x-6">
                    <Dropdown align="right" width="56">
                        <template #trigger>
                            <button class="flex items-center group focus:outline-none">
                                <div class="text-right mr-3 hidden sm:block">
                                    <p class="text-sm font-black text-gray-800 leading-none">{{ $page.props.auth.user.name }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-tighter">Super Admin</p>
                                </div>
                                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl md:rounded-2xl bg-gradient-to-tr from-gray-50 to-gray-100 flex items-center justify-center border-2 border-white shadow-sm overflow-hidden group-hover:border-orange-200 transition-all">
                                    <i class="fa-solid fa-user-shield text-gray-400"></i>
                                </div>
                            </button>
                        </template>
                        <template #content>
                            <div class="px-4 py-3 border-b border-gray-50 md:hidden">
                                <p class="text-xs font-black text-gray-800">{{ $page.props.auth.user.name }}</p>
                            </div>
                            <DropdownLink :href="route('profile.edit')">
                                <i class="fa-solid fa-circle-user mr-2 opacity-50"></i> Profil Saya
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-500">
                                <i class="fa-solid fa-power-off mr-2 opacity-50"></i> Keluar
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 md:p-10 bg-[#F8FAFC] scroll-smooth">
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

.font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif; }

/* Scrollbar Style */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }

/* Animations */
.fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Fix layout shift on mobile */
@media (max-width: 768px) {
    body { overflow-x: hidden; }
}
</style>
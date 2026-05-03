<script setup>
import { ref, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

// 1. Ambil status terakhir dari localStorage, jika tidak ada baru pakai default
// Kita gunakan fungsi pembantu agar tidak error saat SSR (Server Side Rendering)
const getInitialSidebarState = () => {
    if (typeof window === 'undefined') return true;
    const savedState = localStorage.getItem('sidebar_open');
    // Jika ada di storage, pakai itu. Jika tidak, cek lebar layar.
    return savedState !== null ? JSON.parse(savedState) : window.innerWidth > 768;
};

const isSidebarOpen = ref(getInitialSidebarState());

// 2. Pantau perubahan isSidebarOpen dan simpan ke localStorage
watch(isSidebarOpen, (newValue) => {
    localStorage.setItem('sidebar_open', JSON.stringify(newValue));
});

const menuItems = [
    { name: 'Dashboard', icon: 'fa-chart-line', route: 'admin.dashboard' },
    { name: 'Transaksi', icon: 'fa-exchange-alt', route: 'transaksi' },
    { name: 'Kelola Pengguna', icon: 'fa-users', route: 'pengguna' },
    { name: 'Program Donasi', icon: 'fa-hand-holding-heart', route: 'programs.index' },
    { name: 'Laporan', icon: 'fa-file-invoice', route: 'laporan.index' },
    { name: 'Pengaturan', icon: 'fa-cog', route: 'settings.index' },
];

const page = usePage();
watch(() => page.url, () => {
    // Khusus mobile, kita tetap ingin sidebar otomatis tutup saat pindah halaman
    if (window.innerWidth <= 768) {
        isSidebarOpen.value = false;
    }
});

onMounted(() => {
    window.addEventListener('resize', () => {
        // Jika layar berubah ke ukuran desktop, biarkan mengikuti state terakhir
        // Tapi jika ke mobile, biasanya lebih baik ditutup
        if (window.innerWidth <= 768) {
            isSidebarOpen.value = false;
        }
    });
});
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex relative">
        
        <!-- Overlay untuk Mobile: Muncul saat sidebar terbuka di layar kecil -->
        <div 
            v-if="isSidebarOpen" 
            @click="isSidebarOpen = false" 
            class="fixed inset-0 bg-gray-900/50 z-40 md:hidden transition-opacity"
        ></div>

        <!-- Sidebar -->
        <aside 
            :class="[
                'bg-white shadow-xl transition-all duration-300 flex flex-col z-50',
                // Logika Mobile: Fixed position & Slide effect
                'fixed inset-y-0 left-0 transform md:relative md:translate-x-0',
                isSidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full md:translate-x-0 md:w-20'
            ]"
        >
            <div class="p-6 flex items-center justify-center border-b border-gray-100 h-16">
                <Link :href="route('admin.dashboard')" class="flex items-center">
                    <div v-if="isSidebarOpen" class="text-2xl font-black text-orange-500 tracking-tighter">
                        LAZIS<span class="text-gray-800">MU</span>
                    </div>
                    <div v-else class="text-2xl font-black text-orange-500 text-center">L</div>
                </Link>
            </div>

            <nav class="mt-6 flex-grow px-4 space-y-2 overflow-y-auto">
                <Link v-for="item in menuItems" :key="item.name" 
                    :href="item.route === '#' ? '#' : route(item.route)"
                    :class="[
                        route().current(item.route) 
                        ? 'bg-orange-500 text-white shadow-lg shadow-orange-300' 
                        : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600',
                        'flex items-center p-3 rounded-xl transition-all duration-200 group'
                    ]">
                    <i :class="['fa-solid', item.icon, 'text-lg w-8 text-center']"></i>
                    <span v-if="isSidebarOpen" class="font-semibold ml-3">{{ item.name }}</span>
                </Link>
            </nav>

            <div class="p-4 border-t border-gray-100">
                <Link :href="route('logout')" method="post" as="button"
                    class="w-full flex items-center p-3 rounded-xl text-red-500 hover:bg-red-50 transition-all font-bold">
                    <i class="fa-solid fa-right-from-bracket text-lg w-8 text-center"></i>
                    <span v-if="isSidebarOpen" class="ml-3">Logout</span>
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 md:px-8 z-30">
                <div class="flex items-center">
                    <button @click="isSidebarOpen = !isSidebarOpen" class="text-gray-500 hover:text-orange-500 transition-colors p-2 focus:outline-none">
                        <i class="fa-solid fa-bars-staggered text-xl"></i>
                    </button>
                    <h2 class="ml-2 md:ml-4 font-semibold text-gray-800 truncate">
                        <slot name="header" />
                    </h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="relative ms-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none">
                                        <span class="hidden sm:inline">{{ $page.props.auth.user.name }}</span>
                                        <i class="fa-solid fa-circle-user text-xl sm:hidden text-orange-500"></i>
                                        <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 md:p-8">
                <slot />
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

/* Mencegah scroll pada body saat sidebar mobile terbuka */
.overflow-hidden {
    overflow: hidden;
}
</style>
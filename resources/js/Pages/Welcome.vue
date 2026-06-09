<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="Lazismu - Memberi Untuk Negeri" />

    <div class="bg-gray-50 font-sans text-gray-900 antialiased flex flex-col min-h-screen">
        
        <nav class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <img :src="$page.props.system.logo" alt="Logo" class="h-12" />
                    
                </div>

                <div class="hidden md:flex space-x-8 text-gray-600 font-semibold">
                    <a href="#" class="hover:text-[#f29222] transition-colors duration-200">Tentang</a>
                    <a href="#" class="hover:text-[#f29222] transition-colors duration-200">Program</a>
                    <a href="#" class="hover:text-[#f29222] transition-colors duration-200">Berita</a>
                    <a href="#" class="hover:text-[#f29222] transition-colors duration-200">Artikel</a>
                    <a href="#" class="hover:text-[#f29222] transition-colors duration-200">Publikasi</a>
                </div>

                <div class="flex items-center space-x-4">
                    <div v-if="canLogin" class="flex items-center space-x-3">
                        <Link v-if="$page.props.auth.user"
                            :href="$page.props.auth.user.role === 'admin' ? route('admin.dashboard') : route('staff.dashboard')"
                            class="text-sm font-medium text-gray-600 underline hover:text-[#f29222] transition-colors">
                            Dashboard
                        </Link>
                    </div>

                    <Link href="/pilih-program"
                        class="bg-[#f29222] text-white px-6 py-2.5 rounded-lg font-bold hover:bg-orange-600 transition-all duration-200 shadow-md hover:shadow-lg inline-block">
                        Donasi Sekarang
                    </Link>
                    <i class="fa-solid fa-magnifying-glass text-gray-400 cursor-pointer hover:text-[#f29222] transition-colors text-lg"></i>
                </div>
            </div>
        </nav>

        <header class="bg-white text-gray-800 py-20 border-b border-gray-100">
            <div class="container mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-12">
                <div class="md:w-1/2 space-y-6">
                    <div class="inline-block px-3 py-1 bg-orange-50 text-[#f29222] rounded-full text-sm font-bold tracking-wide">
                        Edukasi Zakat
                    </div>
                    <h1 class="text-4xl md:text-5xl font-black text-gray-900 leading-tight">
                        Zakat <span class="text-[#f29222]">Penghasilan</span>
                    </h1>
                    <p class="text-lg font-medium text-gray-600">Dua cara menghitung zakat penghasilan:</p>
                    
                    <ul class="space-y-4 text-gray-700 font-medium">
                        <li class="flex items-start p-4 bg-gray-50 rounded-xl border-l-4 border-[#f29222]">
                            <span class="mr-3 font-bold text-[#f29222] text-lg">1.</span>
                            <span class="text-base md:text-lg">Penghasilan keseluruhan <strong class="text-gray-900">x 2,5%</strong></span>
                        </li>
                        <li class="flex items-start p-4 bg-gray-50 rounded-xl border-l-4 border-[#f29222]">
                            <span class="mr-3 font-bold text-[#f29222] text-lg">2.</span>
                            <span class="text-base md:text-lg">(Penghasilan keseluruhan &ndash; pengeluaran) <strong class="text-gray-900">x 2,5%</strong></span>
                        </li>
                    </ul>
                </div>
                
                <div class="md:w-1/2 w-full flex justify-center">
                    <div class="relative w-full max-w-lg">
                        <div class="absolute -top-4 -left-4 w-full h-full border-2 border-[#f29222] rounded-2xl pointer-events-none opacity-20"></div>
                        <img src="/images/Hero.jpg" alt="Hero Image" class="rounded-2xl shadow-xl w-full object-cover h-72 md:h-80 relative z-10 border border-gray-100">
                    </div>
                </div>
            </div>
        </header>

        <section class="py-20 bg-gray-50">
            <div class="container mx-auto px-6 text-center">
                <span class="text-sm font-bold tracking-widest text-[#f29222] uppercase">Layanan Kemudahan</span>
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mt-2 mb-3">Masih Bingung Untuk Berzakat?</h2>
                <p class="text-gray-500 max-w-xl mx-auto mb-16 font-medium">Untuk membantu kamu, silakan klik menu layanan digital kami di bawah ini.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16 text-left">
                    
                    <div class="border border-gray-100 rounded-2xl p-8 bg-white shadow-sm hover:shadow-md hover:border-orange-200 transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <div class="w-14 h-14 rounded-xl bg-orange-50 flex items-center justify-center mb-6 group-hover:bg-[#f29222] transition-colors duration-300">
                                <i class="fa-solid fa-comments text-2xl text-[#f29222] group-hover:text-white transition-colors duration-300"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">Konsultasi</h3>
                            <p class="text-gray-600 text-sm leading-relaxed mb-6">Sampaikan pertanyaan kamu secara langsung kepada tim layanan syariah kami.</p>
                        </div>
                        <a href="#" class="text-[#f29222] font-bold text-sm inline-flex items-center group-hover:text-orange-600 transition-colors">
                            Selengkapnya <i class="fa-solid fa-arrow-right ml-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>

                    <div class="border border-gray-100 rounded-2xl p-8 bg-white shadow-sm hover:shadow-md hover:border-orange-200 transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <div class="w-14 h-14 rounded-xl bg-orange-50 flex items-center justify-center mb-6 group-hover:bg-[#f29222] transition-colors duration-300">
                                <i class="fa-solid fa-calculator text-2xl text-[#f29222] group-hover:text-white transition-colors duration-300"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">Kalkulator Zakat</h3>
                            <p class="text-gray-600 text-sm leading-relaxed mb-6">Hitung kewajiban nisab dan tunaikan zakat kamu secara cepat, akurat dan tepat.</p>
                        </div>
                        <a href="#" class="text-[#f29222] font-bold text-sm inline-flex items-center group-hover:text-orange-600 transition-colors">
                            Selengkapnya <i class="fa-solid fa-arrow-right ml-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>

                    <div class="border border-gray-100 rounded-2xl p-8 bg-white shadow-sm hover:shadow-md hover:border-orange-200 transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <div class="w-14 h-14 rounded-xl bg-orange-50 flex items-center justify-center mb-6 group-hover:bg-[#f29222] transition-colors duration-300">
                                <i class="fa-solid fa-heart text-2xl text-[#f29222] group-hover:text-white transition-colors duration-300"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">Laman Donasi</h3>
                            <p class="text-gray-600 text-sm leading-relaxed mb-6">Beragam pilihan program kebaikan penyaluran dengan tema yang transparan.</p>
                        </div>
                        <a href="#" class="text-[#f29222] font-bold text-sm inline-flex items-center group-hover:text-orange-600 transition-colors">
                            Selengkapnya <i class="fa-solid fa-arrow-right ml-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>

                </div>

                <div class="flex justify-center">
                    <button class="bg-gray-900 text-white px-10 py-3.5 rounded-xl font-bold hover:bg-gray-800 transition-colors duration-200 shadow-md">
                        Kunjungi Pusat Layanan
                    </button>
                </div>
            </div>
        </section>

        <footer class="bg-white text-gray-700 pt-16 pb-8 mt-auto border-t border-gray-200">
            <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                
                <div class="space-y-4">
                    <div class="flex items-center space-x-3 max-w-max">
                        <img :src="$page.props.system.logo" alt="Logo Footer" class="h-10">
                        <span class="text-gray-900 font-black text-xl tracking-wider">LAZISMU</span>
                    </div>
                    <p class="text-sm text-gray-500 text-justify leading-relaxed">
                        LAZISMU adalah lembaga zakat nasional dengan SK Menag No. 90 Tahun 2022, yang berkhidmat dalam pemberdayaan masyarakat melalui pendayagunaan dana zakat, infaq, wakaf.
                    </p>
                </div>

                <div>
                    <h4 class="text-gray-900 font-bold text-base uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">Alamat</h4>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li class="flex items-start">
                            <i class="fa-solid fa-location-dot mt-1 mr-3 text-[#f29222]"></i>
                            <span>Jl. Pondok Metro</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fa-solid fa-envelope mr-3 text-[#f29222]"></i>
                            <a href="mailto:info@lazismu.kotabjm.or.id" class="hover:text-[#f29222] transition-colors">info@lazismu.kotabjm.or.id</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fa-solid fa-phone mr-3 text-[#f29222]"></i>
                            <span>08-9999-9999</span>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-gray-900 font-bold text-base uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">Selengkapnya</h4>
                    <ul class="space-y-2 text-sm text-gray-600 font-medium">
                        <li><a href="#" class="hover:text-[#f29222] transition-colors">FAQ</a></li>
                        <li><a href="#" class="hover:text-[#f29222] transition-colors">Laman Donasi</a></li>
                        <li><a href="#" class="hover:text-[#f29222] transition-colors">Publikasi</a></li>
                        <li><a href="#" class="hover:text-[#f29222] transition-colors">Ketentuan Layanan</a></li>
                        <li><a href="#" class="hover:text-[#f29222] transition-colors">Kebijakan Privasi</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-gray-900 font-bold text-base uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">Media Sosial</h4>
                    <div class="grid grid-cols-3 gap-3 max-w-[150px]">
                        <a href="#" class="w-10 h-10 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-[#f29222] hover:text-white hover:border-[#f29222] transition-all duration-200"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-[#f29222] hover:text-white hover:border-[#f29222] transition-all duration-200"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-[#f29222] hover:text-white hover:border-[#f29222] transition-all duration-200"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-[#f29222] hover:text-white hover:border-[#f29222] transition-all duration-200"><i class="fa-brands fa-youtube"></i></a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-[#f29222] hover:text-white hover:border-[#f29222] transition-all duration-200"><i class="fa-brands fa-tiktok"></i></a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-[#f29222] hover:text-white hover:border-[#f29222] transition-all duration-200"><i class="fa-brands fa-linkedin-in"></i></a>
                    </div>
                </div>

            </div>

            <div class="border-t border-gray-100 text-center pt-8 text-xs text-gray-400 container mx-auto px-6 font-medium">
                &copy; 2026 LAZISMU. All Rights Reserved.
            </div>
        </footer>

        <a href="https://wa.me/yournumber" target="_blank"
            class="fixed bottom-6 right-6 bg-green-500 text-white p-4 rounded-full shadow-2xl hover:bg-green-600 transition-all z-50 hover:scale-110">
            <i class="fa-brands fa-whatsapp text-3xl"></i>
        </a>
    </div>
</template>
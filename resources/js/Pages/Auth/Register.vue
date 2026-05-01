<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="relative min-h-screen bg-slate-50 flex items-center justify-center p-4 sm:p-6 overflow-hidden">
        
        <div class="absolute -top-24 -left-24 w-64 h-64 bg-orange-100 rounded-full blur-3xl opacity-60"></div>
        <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-orange-200 rounded-full blur-3xl opacity-50"></div>

        <Head title="Register" />

        <div class="relative w-full max-w-lg">
            <div class="bg-white rounded-3xl shadow-xl shadow-orange-900/5 border border-slate-100 overflow-hidden">
                
                <div class="h-2 w-full bg-gradient-to-r from-orange-400 to-orange-600"></div>

                <div class="p-8 sm:p-12">
                    <div class="mb-8 flex flex-col items-center">
                        <div class="w-16 h-16 bg-orange-500 rounded-2xl flex items-center justify-center shadow-lg shadow-orange-500/30 -rotate-3 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <h1 class="text-3xl font-black text-slate-800 tracking-tight text-center">Buat Akun Baru</h1>
                        <p class="text-slate-500 mt-2 text-center font-medium">Gabung sekarang dan mulai petualanganmu!</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div class="group">
                            <InputLabel for="name" value="Nama Lengkap" class="text-slate-700 font-semibold mb-1 ml-1" />
                            <div class="relative transition-all duration-300 group-focus-within:transform group-focus-within:scale-[1.01]">
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="block w-full px-4 py-3.5 bg-slate-50 border-slate-200 text-slate-900 focus:ring-orange-500 focus:border-orange-500 rounded-2xl transition-all shadow-sm"
                                    v-model="form.name"
                                    placeholder="Nama Anda"
                                    required
                                    autofocus
                                    autocomplete="name"
                                />
                            </div>
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="group">
                            <InputLabel for="email" value="Email" class="text-slate-700 font-semibold mb-1 ml-1" />
                            <div class="relative transition-all duration-300 group-focus-within:transform group-focus-within:scale-[1.01]">
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="block w-full px-4 py-3.5 bg-slate-50 border-slate-200 text-slate-900 focus:ring-orange-500 focus:border-orange-500 rounded-2xl transition-all shadow-sm"
                                    v-model="form.email"
                                    placeholder="email@contoh.com"
                                    required
                                    autocomplete="username"
                                />
                            </div>
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div class="group">
                            <InputLabel for="password" value="Password" class="text-slate-700 font-semibold mb-1 ml-1" />
                            <div class="relative transition-all duration-300 group-focus-within:transform group-focus-within:scale-[1.01]">
                                <TextInput
                                    id="password"
                                    type="password"
                                    class="block w-full px-4 py-3.5 bg-slate-50 border-slate-200 text-slate-900 focus:ring-orange-500 focus:border-orange-500 rounded-2xl transition-all shadow-sm"
                                    v-model="form.password"
                                    placeholder="••••••••"
                                    required
                                    autocomplete="new-password"
                                />
                            </div>
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="group">
                            <InputLabel for="password_confirmation" value="Konfirmasi Password" class="text-slate-700 font-semibold mb-1 ml-1" />
                            <div class="relative transition-all duration-300 group-focus-within:transform group-focus-within:scale-[1.01]">
                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    class="block w-full px-4 py-3.5 bg-slate-50 border-slate-200 text-slate-900 focus:ring-orange-500 focus:border-orange-500 rounded-2xl transition-all shadow-sm"
                                    v-model="form.password_confirmation"
                                    placeholder="••••••••"
                                    required
                                    autocomplete="new-password"
                                />
                            </div>
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>

                        <div class="pt-4">
                            <PrimaryButton
                                class="w-full h-14 flex items-center justify-center bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-lg font-bold rounded-2xl shadow-lg shadow-orange-500/40 transition-all hover:-translate-y-1 active:translate-y-0"
                                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                                :disabled="form.processing"
                            >
                                <span v-if="!form.processing">Daftar Sekarang</span>
                                <span v-else class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Mendaftarkan...
                                </span>
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <div class="bg-slate-50 p-6 border-t border-slate-100 text-center">
                    <p class="text-slate-600 text-sm font-medium">
                        Sudah punya akun? 
                        <Link :href="route('login')" class="text-orange-600 font-bold hover:underline underline-offset-4">
                            Masuk di sini
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Biar konsisten dengan Login */
input:focus {
    transform: translateY(-1px);
    box-shadow: 0 10px 15px -3px rgba(249, 115, 22, 0.1);
}
</style>
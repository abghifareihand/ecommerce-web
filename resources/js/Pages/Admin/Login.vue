<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Input from '../../Components/Input.vue';
import Button from '../../Components/Button.vue';

const form = useForm({
    email: 'admin@example.com',
    password: 'password',
    remember: false,
});

const submit = () => {
    form.post('/admin/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Admin Login" />

    <div class="min-h-screen bg-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Brand Logo -->
            <div class="flex justify-center">
                <Link href="/" class="flex items-center gap-3 group">
                    <div class="relative h-12 w-12 rounded-xl overflow-hidden bg-white border border-slate-200/80 shadow-md shadow-emerald-600/20 group-hover:scale-105 transition-transform flex items-center justify-center shrink-0">
                        <img
                            v-if="$page.props.store?.logo_url"
                            :src="$page.props.store.logo_url"
                            :alt="$page.props.store?.name || 'Store Logo'"
                            class="w-full h-full object-cover object-center"
                        />
                        <div v-else class="w-full h-full bg-emerald-600 text-white flex items-center justify-center font-bold">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                    </div>
                </Link>
            </div>

            <h2 class="mt-4 text-center text-2xl font-extrabold text-slate-900 tracking-tight">
                Admin Login
            </h2>
            <p class="mt-1 text-center text-xs text-slate-500">
                Portal Administrasi {{ $page.props.store?.name || 'EcoStore' }}
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4">
            <div class="bg-white py-8 px-6 sm:px-10 shadow-xl shadow-slate-200/50 rounded-2xl border border-slate-200/80">
                <!-- Development Hint Banner -->
                <div class="mb-6 p-3 rounded-xl bg-emerald-50 border border-emerald-200/60 text-xs text-emerald-800">
                    <p class="font-bold mb-1">Development credentials:</p>
                    <p>Email: <span class="font-mono bg-white px-1.5 py-0.5 rounded text-emerald-900">admin@example.com</span></p>
                    <p class="mt-0.5">Password: <span class="font-mono bg-white px-1.5 py-0.5 rounded text-emerald-900">password</span></p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <Input
                        id="email"
                        label="Email Address"
                        type="email"
                        v-model="form.email"
                        :error="form.errors.email"
                        placeholder="admin@example.com"
                        required
                    />

                    <Input
                        id="password"
                        label="Password"
                        type="password"
                        v-model="form.password"
                        :error="form.errors.password"
                        placeholder="••••••••"
                        required
                    />

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-slate-600">
                            <input
                                type="checkbox"
                                v-model="form.remember"
                                class="rounded border-slate-300 accent-emerald-600 text-emerald-600 focus:ring-emerald-500 h-4 w-4 cursor-pointer"
                            />
                            Remember me
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <Button
                            type="submit"
                            variant="primary"
                            :loading="form.processing"
                            :disabled="form.processing"
                            class="w-full justify-center"
                        >
                            Login to Admin
                        </Button>
                    </div>
                </form>

                <div class="mt-6 text-center border-t border-slate-100 pt-6">
                    <Link
                        href="/"
                        class="text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors"
                    >
                        &larr; Back to storefront
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

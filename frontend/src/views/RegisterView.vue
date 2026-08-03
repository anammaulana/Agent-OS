<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import AuthLayout from '../layouts/AuthLayout.vue'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const errors = ref({})
const generalError = ref('')

async function submit() {
    errors.value = {}
    generalError.value = ''

    try {
        await authStore.register(form)
        await router.push('/dashboard')
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors ?? {}
            generalError.value =
                error.response.data.message ??
                'Data registrasi belum valid.'
            return
        }

        generalError.value =
            'Registrasi gagal. Silakan coba kembali.'
    }
}
</script>

<template>
    <AuthLayout title="Buat akun AgentOS" subtitle="Mulai membuat dan mengelola AI agent dengan provider milik kamu.">
        <form class="space-y-5" @submit.prevent="submit">
            <div v-if="generalError" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ generalError }}
            </div>

            <div>
                <label for="name" class="mb-2 block text-sm font-medium text-slate-700">
                    Nama
                </label>

                <input id="name" v-model.trim="form.name" type="text" autocomplete="name" required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-slate-950"
                    placeholder="Nama lengkap" />

                <p v-if="errors.name" class="mt-2 text-sm text-red-600">
                    {{ errors.name[0] }}
                </p>
            </div>

            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-slate-700">
                    Email
                </label>

                <input id="email" v-model.trim="form.email" type="email" autocomplete="email" required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-slate-950"
                    placeholder="nama@email.com" />

                <p v-if="errors.email" class="mt-2 text-sm text-red-600">
                    {{ errors.email[0] }}
                </p>
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-slate-700">
                    Password
                </label>

                <input id="password" v-model="form.password" type="password" autocomplete="new-password" required
                    minlength="8"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-slate-950"
                    placeholder="Minimal 8 karakter" />

                <p v-if="errors.password" class="mt-2 text-sm text-red-600">
                    {{ errors.password[0] }}
                </p>
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700">
                    Konfirmasi password
                </label>

                <input id="password_confirmation" v-model="form.password_confirmation" type="password"
                    autocomplete="new-password" required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-slate-950"
                    placeholder="Ulangi password" />
            </div>

            <button type="submit" :disabled="authStore.loading"
                class="w-full rounded-xl bg-slate-950 px-4 py-3 font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60">
                {{
                    authStore.loading
                        ? 'Membuat akun...'
                        : 'Daftar'
                }}
            </button>

            <p class="text-center text-sm text-slate-500">
                Sudah memiliki akun?

                <RouterLink to="/login" class="font-semibold text-slate-950 hover:underline">
                    Masuk
                </RouterLink>
            </p>
        </form>
    </AuthLayout>
</template>
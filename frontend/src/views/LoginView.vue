<script setup>
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AuthLayout from '../layouts/AuthLayout.vue'
import { useAuthStore } from '../stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
    email: '',
    password: '',
})

const errors = ref({})
const generalError = ref('')

async function submit() {
    errors.value = {}
    generalError.value = ''

    try {
        await authStore.login(form)

        const destination =
            typeof route.query.redirect === 'string'
                ? route.query.redirect
                : '/dashboard'

        await router.push(destination)
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors ?? {}
            generalError.value =
                error.response.data.message ??
                'Email atau password tidak valid.'
            return
        }

        generalError.value =
            'Terjadi kesalahan. Silakan coba kembali.'
    }
}
</script>

<template>
    <AuthLayout title="Masuk ke AgentOS" subtitle="Kelola agent AI dan provider milik organisasi kamu.">
        <form class="space-y-5" @submit.prevent="submit">
            <div v-if="generalError" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ generalError }}
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

                <input id="password" v-model="form.password" type="password" autocomplete="current-password" required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-slate-950"
                    placeholder="Masukkan password" />

                <p v-if="errors.password" class="mt-2 text-sm text-red-600">
                    {{ errors.password[0] }}
                </p>
            </div>

            <button type="submit" :disabled="authStore.loading"
                class="w-full rounded-xl bg-slate-950 px-4 py-3 font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60">
                {{
                    authStore.loading
                        ? 'Sedang masuk...'
                        : 'Masuk'
                }}
            </button>

            <p class="text-center text-sm text-slate-500">
                Belum memiliki akun?

                <RouterLink to="/register" class="font-semibold text-slate-950 hover:underline">
                    Daftar
                </RouterLink>
            </p>
        </form>
    </AuthLayout>
</template>
<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

async function logout() {
    await authStore.logout()
    await router.push('/login')
}
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-950 font-bold text-white">
                        A
                    </div>

                    <div>
                        <p class="font-bold text-slate-950">
                            AgentOS
                        </p>

                        <p class="text-xs text-slate-500">
                            AI Agent Platform
                        </p>
                    </div>
                </div>

                <button type="button"
                    class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                    @click="logout">
                    Keluar
                </button>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-6 py-10">
            <section class="rounded-3xl bg-slate-950 p-8 text-white">
                <p class="text-sm font-medium text-slate-300">
                    Selamat datang
                </p>

                <h1 class="mt-2 text-3xl font-bold">
                    {{
                        authStore.user?.name ??
                    'Pengguna AgentOS'
                    }}
                </h1>

                <p class="mt-3 max-w-2xl leading-7 text-slate-300">
                    Fondasi autentikasi AgentOS sudah berjalan.
                    Tahap berikutnya adalah organisasi,
                    multi-tenant, role, dan tenant isolation.
                </p>
            </section>

            <section class="mt-8 grid gap-5 md:grid-cols-3">
                <article class="rounded-2xl border border-slate-200 bg-white p-6">
                    <p class="text-sm text-slate-500">
                        Authentication
                    </p>

                    <p class="mt-2 text-xl font-bold text-emerald-600">
                        Aktif
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6">
                    <p class="text-sm text-slate-500">
                        Database
                    </p>

                    <p class="mt-2 text-xl font-bold text-slate-950">
                        SQLite
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6">
                    <p class="text-sm text-slate-500">
                        Frontend
                    </p>

                    <p class="mt-2 text-xl font-bold text-slate-950">
                        Vue 3
                    </p>
                </article>
            </section>
        </main>
    </div>
</template>
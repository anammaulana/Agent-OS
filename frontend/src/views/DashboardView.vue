<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useOrganizationStore } from '../stores/organization'
const router = useRouter()
const authStore = useAuthStore()
const organizationStore = useOrganizationStore()
onMounted(async () => {
    await organizationStore.fetchOrganizations()
})
async function switchOrganization(event) {
    const organization = organizationStore.organizations.find((item) => String(item.id) === event.target.value,)
    organizationStore.setActiveOrganization(organization)
}
async function logout() {
    await authStore.logout()
    organizationStore.setActiveOrganization(null)
    await router.push('/login')
} </script>

<template>
    <div class="min-h-screen bg-slate-100">
        <header class="border-b bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-950 font-bold text-white">
                        A </div>
                    <div>
                        <p class="font-bold"> AgentOS </p>
                        <p class="text-xs text-slate-500"> AI Agent Platform </p>
                    </div>
                </div>
                <div class="flex items-center gap-3"> <select v-if="organizationStore.organizations.length"
                        :value="organizationStore.activeOrganization?.id" class="rounded-xl border px-4 py-2"
                        @change="switchOrganization">
                        <option v-for="organization in organizationStore.organizations" :key="organization.id"
                            :value="organization.id"> {{ organization.name }} </option>
                    </select> <button class="rounded-xl border px-4 py-2" @click="logout"> Keluar </button> </div>
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-6 py-10">
            <section v-if="!organizationStore.loading && !organizationStore.hasOrganization"
                class="rounded-3xl border bg-white p-10 text-center">
                <h1 class="text-3xl font-bold"> Buat organization pertama </h1>
                <p class="mt-3 text-slate-500"> Organization diperlukan sebelum membuat provider dan AI agent. </p>
                <RouterLink to="/organizations/create"
                    class="mt-6 inline-flex rounded-xl bg-slate-950 px-5 py-3 font-semibold text-white"> Buat
                    organization </RouterLink>
            </section> <template v-else>
                <section class="rounded-3xl bg-slate-950 p-8 text-white">
                    <p class="text-sm text-slate-300"> Organization aktif </p>
                    <h1 class="mt-2 text-3xl font-bold"> {{ organizationStore.activeOrganization?.name }} </h1>
                    <p class="mt-3 text-slate-300"> Role kamu: <strong> {{ organizationStore.currentRole }} </strong>
                    </p>
                </section>
                <section class="mt-8 grid gap-5 md:grid-cols-3">
                    <RouterLink to="/organization/members" class="rounded-2xl border bg-white p-6">
                        <p class="text-sm text-slate-500"> Organization </p>
                        <p class="mt-2 text-xl font-bold"> Kelola anggota </p>
                    </RouterLink>
                    <article class="rounded-2xl border bg-white p-6">
                        <p class="text-sm text-slate-500"> Provider AI </p>
                        <p class="mt-2 text-xl font-bold"> Sprint berikutnya </p>
                    </article>
                    <article class="rounded-2xl border bg-white p-6">
                        <p class="text-sm text-slate-500"> AI Agent </p>
                        <p class="mt-2 text-xl font-bold"> Belum tersedia </p>
                    </article>
                </section>
                <RouterLink to="/organizations/create" class="mt-6 inline-flex text-sm font-semibold"> + Buat
                    organization lain </RouterLink>
            </template>
        </main>
    </div>
</template>
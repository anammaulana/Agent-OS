<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useOrganizationStore } from '../stores/organization'
const organizationStore = useOrganizationStore()

const form = reactive({
    email: '',
    role: 'member',
})
const message = ref('')
const errorMessage = ref('')
onMounted(async () => {
    await organizationStore.fetchMembers()
})
async function addMember() {
    message.value = ''
    errorMessage.value = ''
    try {
        const response =
            await organizationStore.addMember(form)

        message.value = response.message
        form.email = ''
        form.role = 'member'
    } catch (error) {
        errorMessage.value = error.response?.data?.message ??
            'Gagal menambahkan anggota.'
    }
} async function changeRole(member, role) {
    message.value = ''
    errorMessage.value = ''
    try {
        const response = await organizationStore.updateMember(member.id, role,)
        message.value = response.message
    } catch (error)
    {
        errorMessage.value = error.response?.data?.message ??
            'Gagal mengubah role.'
    }
}

async function removeMember(member) {
    const confirmed = window.confirm(`Hapus ${member.user.name} dari organization?`,)
    if (!confirmed) { return }
    try {
        const response = await organizationStore.removeMember(member.id)
        message.value = response.message
    } catch (error) {
        errorMessage.value = error.response?.data?.message ?? 'Gagal menghapus anggota.'
    }
} </script>
<template>
    <main class="mx-auto max-w-6xl px-6 py-10">
        <div class="mb-8">
            <RouterLink to="/dashboard" class="text-sm font-semibold text-slate-500"> ← Dashboard </RouterLink>
            <h1 class="mt-4 text-3xl font-bold"> Anggota organization </h1>
        </div>
        <div v-if="message" class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-emerald-700"> {{ message }} </div>
        <div v-if="errorMessage" class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-red-700"> {{ errorMessage }} </div>
        <section v-if="organizationStore.canManageOrganization" class="mb-8 rounded-2xl border bg-white p-6">
            <h2 class="font-bold"> Tambah anggota </h2>
            <form class="mt-5 grid gap-4 md:grid-cols-[1fr_180px_auto]" @submit.prevent="addMember"> <input
                    v-model.trim="form.email" type="email" required class="rounded-xl border px-4 py-3"
                    placeholder="email anggota" /> <select v-model="form.role" class="rounded-xl border px-4 py-3">
                    <option value="admin"> Admin </option>
                    <option value="member"> Member </option>
                    <option value="viewer"> Viewer </option>
                </select> <button class="rounded-xl bg-slate-950 px-5 py-3 font-semibold text-white"> Tambahkan
                </button> </form>
        </section>
        <section class="overflow-hidden rounded-2xl border bg-white">
            <table class="w-full text-left">
                <thead class="bg-slate-100 text-sm">
                    <tr>
                        <th class="px-5 py-4"> Pengguna </th>
                        <th class="px-5 py-4"> Role </th>
                        <th class="px-5 py-4"> Aksi </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="member in organizationStore.members" :key="member.id" class="border-t">
                        <td class="px-5 py-4">
                            <p class="font-semibold"> {{ member.user.name }} </p>
                            <p class="text-sm text-slate-500"> {{ member.user.email }} </p>
                        </td>
                        <td class="px-5 py-4"> <select :value="member.role"
                                :disabled="!organizationStore.canManageOrganization || (organizationStore.currentRole === 'admin' && member.role === 'owner')"
                                class="rounded-lg border px-3 py-2" @change=" changeRole(member, $event.target.value,)">
                                <option value="owner"> Owner </option>
                                <option value="admin"> Admin </option>
                                <option value="member"> Member </option>
                                <option value="viewer"> Viewer </option>
                            </select> </td>
                        <td class="px-5 py-4"> <button v-if="organizationStore.canManageOrganization"
                                class="text-sm font-semibold text-red-600" @click="removeMember(member)"> Hapus
                            </button> </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>
</template>
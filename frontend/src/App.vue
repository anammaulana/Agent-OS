<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()

onMounted(async () => {
  if (authStore.token) {
    try {
      await authStore.fetchUser()
    } catch {
      // Session invalid sudah ditangani auth store.
    }
  } else {
    authStore.initialized = true
  }
})
</script>

<template>
  <RouterView />
</template>
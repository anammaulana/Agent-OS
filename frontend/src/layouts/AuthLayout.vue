<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },

    subtitle: {
        type: String,
        default: '',
    },
})
</script>

<template>
    <main class="flex min-h-screen items-center justify-center bg-slate-100 px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <RouterLink to="/" class="inline-flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950 text-lg font-bold text-white">
                        A
                    </div>

                    <span class="text-2xl font-bold text-slate-950">
                        AgentOS
                    </span>
                </RouterLink>
            </div>

            <section class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-slate-950">
                        {{ title }}
                    </h1>

                    <p v-if="subtitle" class="mt-2 text-sm leading-6 text-slate-500">
                        {{ subtitle }}
                    </p>
                </div>

                <slot />
            </section>
        </div>
    </main>
</template>
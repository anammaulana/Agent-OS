<template>
    <main class="flex min-h-screen items-center justify-center bg-slate-100 px-6">
        <div class="text-center">
            <p class="text-sm font-semibold text-slate-500">
                ERROR 404
            </p>

            <h1 class="mt-3 text-4xl font-bold text-slate-950">
                Halaman tidak ditemukan
            </h1>

            <p class="mt-3 text-slate-500">
                Halaman yang kamu cari tidak tersedia.
            </p>

            <RouterLink to="/dashboard"
                class="mt-6 inline-flex rounded-xl bg-slate-950 px-5 py-3 font-semibold text-white">
                Kembali ke dashboard
            </RouterLink>
        </div>
    </main>
</template>
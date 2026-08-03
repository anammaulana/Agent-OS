import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(
            localStorage.getItem('auth_user') ?? 'null',
        ),

        token: localStorage.getItem('access_token'),

        loading: false,

        initialized: false,
    }),

    getters: {
        isAuthenticated: (state) => Boolean(state.token),
    },

    actions: {
        saveSession(user, token) {
            this.user = user
            this.token = token

            localStorage.setItem(
                'auth_user',
                JSON.stringify(user),
            )

            localStorage.setItem(
                'access_token',
                token,
            )
        },

        clearSession() {
            this.user = null
            this.token = null

            localStorage.removeItem('auth_user')
            localStorage.removeItem('access_token')
        },

        async register(payload) {
            this.loading = true

            try {
                const response = await api.post(
                    '/auth/register',
                    payload,
                )

                const { user, token } = response.data.data

                this.saveSession(user, token)

                return response.data
            } finally {
                this.loading = false
            }
        },

        async login(payload) {
            this.loading = true

            try {
                const response = await api.post(
                    '/auth/login',
                    payload,
                )

                const { user, token } = response.data.data

                this.saveSession(user, token)

                return response.data
            } finally {
                this.loading = false
            }
        },

        async fetchUser() {
            if (!this.token) {
                this.initialized = true
                return null
            }

            try {
                const response = await api.get('/auth/me')

                this.user = response.data.data

                localStorage.setItem(
                    'auth_user',
                    JSON.stringify(this.user),
                )

                return this.user
            } catch (error) {
                this.clearSession()
                throw error
            } finally {
                this.initialized = true
            }
        },

        async logout() {
            try {
                if (this.token) {
                    await api.post('/auth/logout')
                }
            } finally {
                this.clearSession()
            }
        },
    },
})
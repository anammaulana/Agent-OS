import axios from 'axios'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    timeout: 30000,

    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
})

api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('access_token')
        const organizationId = localStorage.getItem('organization_id')

        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }

        if (organizationId) {
            config.headers['X-Organization-ID'] = organizationId
        }

        return config
    },

    (error) => Promise.reject(error),
)

api.interceptors.response.use(
    (response) => response,

    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('access_token')
            localStorage.removeItem('auth_user')
            localStorage.removeItem('organization_id')

            if (
                window.location.pathname !== '/login' &&
                window.location.pathname !== '/register'
            ) {
                window.location.href = '/login'
            }
        }

        return Promise.reject(error)
    },
)

export default api
import { createRouter, createWebHistory } from 'vue-router'
import CreateOrganizationView from '../views/CreateOrganizationView.vue'
import DashboardView from '../views/DashboardView.vue'
import LoginView from '../views/LoginView.vue'
import NotFoundView from '../views/NotFoundView.vue'
import OrganizationMembersView from '../views/OrganizationMembersView.vue'
import RegisterView from '../views/RegisterView.vue'

const routes = [
    {
        path: '/',
        redirect: '/dashboard',
    },
    {
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: {
            guestOnly: true,
        },
    },
    {
        path: '/register',
        name: 'register',
        component: RegisterView,
        meta: {
            guestOnly: true,
        },
    },
    {
        path: '/organizations/create',
        name: 'organization-create',
        component: CreateOrganizationView,
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: DashboardView,
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/organization/members',
        name: 'organization-members',
        component: OrganizationMembersView,
        meta: {
            requiresAuth: true,
            requiresOrganization: true,
        },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFoundView,
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach((to) => {
    const token = localStorage.getItem('access_token')
    const organizationId =
        localStorage.getItem('organization_id')

    if (to.meta.requiresAuth && !token) {
        return {
            name: 'login',
            query: {
                redirect: to.fullPath,
            },
        }
    }

    if (to.meta.guestOnly && token) {
        return {
            name: 'dashboard',
        }
    }

    if (
        to.meta.requiresOrganization &&
        !organizationId
    ) {
        return {
            name: 'organization-create',
        }
    }

    return true
})

export default router
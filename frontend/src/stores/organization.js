import { defineStore } from 'pinia'
import api from '../services/api'

export const useOrganizationStore = defineStore('organization', {
    state: () => ({
        organizations: [],
        activeOrganization: null,
        members: [],
        loading: false,
    }),

    getters: {
        hasOrganization: (state) =>
            Boolean(state.activeOrganization),

        currentRole: (state) =>
            state.activeOrganization?.current_user_role ?? null,

        canManageOrganization() {
            return ['owner', 'admin'].includes(this.currentRole)
        },

        isOwner() {
            return this.currentRole === 'owner'
        },
    },

    actions: {
        async fetchOrganizations() {
            this.loading = true

            try {
                const response = await api.get('/organizations')

                this.organizations = response.data.data

                const savedId =
                    localStorage.getItem('organization_id')

                const selected =
                    this.organizations.find(
                        (organization) =>
                            String(organization.id) === String(savedId),
                    ) ??
                    this.organizations[0] ??
                    null

                this.setActiveOrganization(selected)

                return this.organizations
            } finally {
                this.loading = false
            }
        },

        setActiveOrganization(organization) {
            this.activeOrganization = organization
            this.members = []

            if (organization) {
                localStorage.setItem(
                    'organization_id',
                    String(organization.id),
                )
            } else {
                localStorage.removeItem('organization_id')
            }
        },

        async createOrganization(payload) {
            const response = await api.post(
                '/organizations',
                payload,
            )

            await this.fetchOrganizations()

            const organization = this.organizations.find(
                (item) =>
                    String(item.id) ===
                    String(response.data.data.id),
            )

            this.setActiveOrganization(
                organization ?? response.data.data,
            )

            return response.data
        },

        async fetchCurrentOrganization() {
            const response = await api.get('/organization')
            const organization = response.data.data

            const index = this.organizations.findIndex(
                (item) => item.id === organization.id,
            )

            if (index >= 0) {
                this.organizations[index] = {
                    ...this.organizations[index],
                    ...organization,
                }
            }

            this.activeOrganization = {
                ...this.activeOrganization,
                ...organization,
            }

            return organization
        },

        async updateOrganization(payload) {
            const response = await api.put(
                '/organization',
                payload,
            )

            await this.fetchOrganizations()

            const organization = this.organizations.find(
                (item) =>
                    String(item.id) ===
                    String(response.data.data.id),
            )

            this.setActiveOrganization(
                organization ?? response.data.data,
            )

            return response.data
        },

        async fetchMembers() {
            const response = await api.get(
                '/organization/members',
            )

            this.members = response.data.data

            return this.members
        },

        async addMember(payload) {
            const response = await api.post(
                '/organization/members',
                payload,
            )

            await this.fetchMembers()

            return response.data
        },

        async updateMember(memberId, role) {
            const response = await api.patch(
                `/organization/members/${memberId}`,
                { role },
            )

            await this.fetchMembers()

            return response.data
        },

        async removeMember(memberId) {
            const response = await api.delete(
                `/organization/members/${memberId}`,
            )

            await this.fetchMembers()

            return response.data
        },

        async leaveOrganization() {
            const response = await api.post(
                '/organization/leave',
            )

            await this.fetchOrganizations()

            return response.data
        },
    },
})
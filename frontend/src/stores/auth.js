import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || null,
    user: (() => {
      try {
        return JSON.parse(localStorage.getItem('user') || 'null')
      } catch {
        return null
      }
    })(),
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    setToken(token) {
      this.token = token
      localStorage.setItem('token', token)
    },

    clearToken() {
      this.token = null
      localStorage.removeItem('token')
    },

    setUser(user) {
      this.user = user
      localStorage.setItem('user', JSON.stringify(user))
    },

    clearUser() {
      this.user = null
      localStorage.removeItem('user')
    },

    async logout(router = null) {
      try {
        if (this.token) {
          await api.post('/auth/logout')
        }
      } catch (error) {
        console.error('Logout request failed:', error)
      } finally {
        this.clearToken()
        this.clearUser()

        if (router) {
          router.push('/login')
        }
      }
    },
  },
})
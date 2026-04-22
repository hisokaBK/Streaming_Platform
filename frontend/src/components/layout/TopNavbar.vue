<template>
  <nav
    class="fixed left-0 top-0 z-[60] flex w-full items-center justify-between bg-black/80 px-4 py-4 shadow-[0_20px_40px_rgba(0,0,0,0.6)] backdrop-blur-xl sm:px-6"
  >
    <div class="flex items-center gap-3 sm:gap-4">
      <button
        type="button"
        class="rounded-full p-2 text-zinc-400 transition-all duration-300 hover:bg-white/10 hover:text-white active:scale-90"
        @click="$emit('toggle-sidebar')"
      >
        <span class="material-symbols-outlined">menu</span>
      </button>

      <span
        class="bg-gradient-to-br from-fuchsia-500 to-purple-600 bg-clip-text font-headline text-xl font-black tracking-tighter text-transparent sm:text-2xl"
      >
        HSKn
      </span>
    </div>

    <div class="flex items-center gap-2 sm:gap-4">
      <RouterLink
        :to="notificationLink"
        class="relative rounded-full p-2 text-zinc-400 transition-all duration-300 hover:bg-white/10 hover:text-white active:scale-90"
      >
        <span class="material-symbols-outlined">notifications</span>

        <span
          v-if="isAuthenticated && hasUnreadNotifications"
          class="absolute right-1.5 top-1.5 h-2.5 w-2.5 rounded-full bg-primary ring-2 ring-black"
        ></span>
      </RouterLink>

      <RouterLink
        v-if="isAdmin"
        to="/admin/dashboard"
        class="rounded-full border border-primary/20 bg-primary/10 px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-primary transition hover:bg-primary/20 md:hidden"
      >
        Admin
      </RouterLink>

      <RouterLink :to="profileLink" class="block shrink-0">
        <template v-if="avatarUrl">
          <img
            :src="avatarUrl"
            alt="Authenticated user avatar"
            class="h-10 w-10 rounded-full border-2 border-primary/30 object-cover object-center"
          />
        </template>

        <template v-else>
          <div
            class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-primary/30 bg-zinc-900 text-sm font-bold uppercase text-white"
          >
            {{ userInitials }}
          </div>
        </template>
      </RouterLink>
    </div>
  </nav>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'

defineEmits(['toggle-sidebar'])

const authStore = useAuthStore()

const authProfile = ref(null)
const hasUnreadNotifications = ref(false)

const isAuthenticated = computed(() => !!authStore.user)
const isAdmin = computed(() => authStore.user?.role === 'admin')

const APP_URL = (import.meta.env.VITE_APP_URL || 'http://localhost:8000').replace(/\/$/, '')
const STORAGE_BASE = `${APP_URL}/storage`

const buildStorageUrl = (path) => {
  if (!path || typeof path !== 'string') return null

  const value = path.trim()
  if (!value) return null

  if (value.startsWith('http://nginx/storage/')) {
    return value.replace('http://nginx/storage', STORAGE_BASE)
  }

  if (value.startsWith('https://nginx/storage/')) {
    return value.replace('https://nginx/storage', STORAGE_BASE)
  }

  if (value.startsWith('http://app/storage/')) {
    return value.replace('http://app/storage', STORAGE_BASE)
  }

  if (value.startsWith('https://app/storage/')) {
    return value.replace('https://app/storage', STORAGE_BASE)
  }

  if (value.startsWith('http://localhost/storage/')) {
    return value.replace('http://localhost/storage', STORAGE_BASE)
  }

  if (value.startsWith('https://localhost/storage/')) {
    return value.replace('https://localhost/storage', STORAGE_BASE)
  }

  if (value.startsWith('http://') || value.startsWith('https://')) {
    return value
  }

  const clean = value.replace(/^\/+/, '')

  if (clean.startsWith('storage/')) {
    return `${APP_URL}/${clean}`
  }

  return `${STORAGE_BASE}/${clean}`
}

const avatarUrl = computed(() => {
  const avatar = authProfile.value?.avatar
  return avatar ? buildStorageUrl(avatar) : null
})

const userInitials = computed(() => {
  const name = authProfile.value?.user?.name || authStore.user?.name || 'Guest'

  return name
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase()
})

const profileLink = computed(() => {
  return isAuthenticated.value ? '/profile' : '/login'
})

const notificationLink = computed(() => {
  return isAuthenticated.value ? '/notifications' : '/login'
})

const loadAuthProfile = async () => {
  if (!isAuthenticated.value) {
    authProfile.value = null
    return
  }

  try {
    const response = await api.get('/profile/profile')
    authProfile.value = response.data?.data?.profile || null
  } catch (error) {
    console.error('Failed to load authenticated profile for navbar', error)
    authProfile.value = null
  }
}

const loadNotificationsState = async () => {
  if (!isAuthenticated.value) {
    hasUnreadNotifications.value = false
    return
  }

  try {
    const response = await api.get('/notification/notifications?page=1')
    const notifications = response.data?.data?.data || response.data?.data || []

    hasUnreadNotifications.value = notifications.some(
      (notification) => notification.is_read === false
    )
  } catch (error) {
    console.error('Failed to load notifications state for navbar', error)
    hasUnreadNotifications.value = false
  }
}

onMounted(async () => {
  await Promise.all([
    loadAuthProfile(),
    loadNotificationsState(),
  ])
})
</script>

<style scoped>
.material-symbols-outlined {
  font-variation-settings:
    'FILL' 0,
    'wght' 400,
    'GRAD' 0,
    'opsz' 24;
}
</style>
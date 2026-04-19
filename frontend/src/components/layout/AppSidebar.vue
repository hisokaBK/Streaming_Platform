<template>
    <div
      v-if="mobileOpen"
      class="fixed inset-0 top-[72px] z-40 bg-black/60 backdrop-blur-sm md:hidden"
      @click="$emit('close')"
    ></div>

    <aside
      :class="[
        'fixed left-0 top-[72px] z-50 flex h-[calc(100vh-72px)] flex-col bg-zinc-900 shadow-2xl transition-all duration-300',
        mobileOpen ? 'translate-x-0' : '-translate-x-full',
        collapsed ? 'w-64 md:w-20' : 'w-64',
        'md:translate-x-0'
      ]"
    >
      <div class="flex-1 space-y-2 overflow-y-auto px-3 py-4">
        <RouterLink
          v-for="item in visibleLinks"
          :key="item.name"
          :to="item.to"
          class="flex items-center rounded-xl px-4 py-3 text-sm font-inter transition-all duration-200"
          :class="isActive(item.to)
            ? 'border-r-4 border-fuchsia-500 bg-fuchsia-500/10 font-semibold text-fuchsia-500'
            : 'text-zinc-500 hover:bg-white/5 hover:text-zinc-100'"
          @click="handleItemClick"
        >
          <span class="material-symbols-outlined shrink-0">{{ item.icon }}</span>
          <span v-if="!collapsed" class="ml-3">{{ item.name }}</span>
        </RouterLink>
      </div>

      <div class="mb-6 px-3">
        <RouterLink
          to="/streams/create"
          class="flex w-full items-center justify-center rounded-full bg-gradient-to-br from-primary to-secondary py-3 font-bold text-on-primary-fixed shadow-lg shadow-primary/20 transition-all hover:scale-[1.02] active:scale-95"
          @click="handleItemClick"
        >
          <span v-if="collapsed" class="material-symbols-outlined">radio_button_checked</span>
          <span v-else>Start Stream</span>
        </RouterLink>
      </div>

      <div class="space-y-2 border-t border-white/5 px-3 py-4">
        <button
          type="button"
          class="flex w-full items-center rounded-xl px-4 py-3 text-sm text-zinc-500 transition-all duration-200 hover:bg-white/5 hover:text-zinc-100"
          @click="handleLogout"
        >
          <span class="material-symbols-outlined shrink-0">logout</span>
          <span v-if="!collapsed" class="ml-3">Logout</span>
        </button>
      </div>
    </aside>

</template>

<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false,
  },
  mobileOpen: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close'])

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const isAdmin = computed(() => {
  return authStore.user?.role === 'admin'
})

const links = [
  { name: 'Home', to: '/', icon: 'home' },
  { name: 'Streams', to: '/streams', icon: 'sensors' },
  { name: 'Videos', to: '/videos', icon: 'video_library' },
  { name: 'Messages', to: '/messages', icon: 'chat' },
  { name: 'Notifications', to: '/notifications', icon: 'notifications' },
]

const visibleLinks = computed(() => {
  const items = [...links]

  if (isAdmin.value) {
    items.push({
      name: 'Dashboard',
      to: '/admin/dashboard',
      icon: 'dashboard',
    })
  }

  return items
})

const isActive = (path) => {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}

const closeOnMobile = () => {
  if (window.innerWidth < 768) {
    emit('close')
  }
}

const handleItemClick = () => {
  closeOnMobile()
}

const handleLogout = async () => {
  await authStore.logout(router)
  closeOnMobile()
}
</script>
<template>
  <aside
    :class="[
      'fixed left-0 top-[72px] z-40 hidden h-[calc(100vh-72px)] bg-zinc-900 shadow-2xl transition-all duration-300 md:flex md:flex-col',
      collapsed ? 'w-20' : 'w-64'
    ]"
  >
    <div class="flex-1 space-y-2 px-3 py-4">
      <RouterLink
        v-for="item in links"
        :key="item.name"
        :to="item.to"
        class="flex items-center rounded-xl px-4 py-3 text-sm font-inter transition-all duration-200"
        :class="isActive(item.to)
          ? 'border-r-4 border-fuchsia-500 bg-fuchsia-500/10 font-semibold text-fuchsia-500'
          : 'text-zinc-500 hover:bg-white/5 hover:text-zinc-100'"
      >
        <span class="material-symbols-outlined shrink-0">{{ item.icon }}</span>
        <span v-if="!collapsed" class="ml-3">{{ item.name }}</span>
      </RouterLink>
    </div>

    <div class="space-y-2 border-t border-white/5 px-3 py-4">
      <RouterLink
        to="/"
        class="flex items-center rounded-xl px-4 py-3 text-sm text-zinc-500 transition-all duration-200 hover:bg-white/5 hover:text-zinc-100"
      >
        <span class="material-symbols-outlined shrink-0">arrow_back</span>
        <span v-if="!collapsed" class="ml-3">Back to App</span>
      </RouterLink>

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
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

defineProps({
  collapsed: {
    type: Boolean,
    default: false,
  },
})

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const links = [
  { name: 'Statistics', to: '/admin/dashboard', icon: 'dashboard' },
  { name: 'Users', to: '/admin/users', icon: 'groups' },
  { name: 'Categories', to: '/admin/categories', icon: 'category' },
]

const isActive = (path) => route.path.startsWith(path)

const handleLogout = async () => {
  await authStore.logout(router)
}
</script>
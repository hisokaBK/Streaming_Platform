<template>
  <aside
    :class="[
      'fixed left-0 top-0 z-40 hidden h-screen bg-zinc-900 pt-24 shadow-2xl transition-all duration-300 md:flex md:flex-col',
      collapsed ? 'w-20' : 'w-64'
    ]"
  >
    <div class="flex-1 px-3 space-y-2">
      <RouterLink
        v-for="item in links"
        :key="item.name"
        :to="item.to"
        class="flex items-center rounded-xl px-4 py-3 text-sm font-inter transition-all duration-200"
        :class="isActive(item.to)
          ? 'bg-fuchsia-500/10 text-fuchsia-500 border-r-4 border-fuchsia-500 font-semibold'
          : 'text-zinc-500 hover:bg-white/5 hover:text-zinc-100'"
      >
        <span class="material-symbols-outlined shrink-0">{{ item.icon }}</span>
        <span v-if="!collapsed" class="ml-3">{{ item.name }}</span>
      </RouterLink>
    </div>

    <div class="px-3 mb-6">
      <RouterLink
        to="/streams/create"
        class="flex w-full items-center justify-center rounded-full bg-gradient-to-br from-primary to-secondary py-3 font-bold shadow-lg shadow-primary/20 transition-all hover:scale-[1.02] active:scale-95"
        :class="collapsed ? 'px-0 text-on-primary-fixed' : 'text-on-primary-fixed'"
      >
        <span v-if="collapsed" class="material-symbols-outlined">radio_button_checked</span>
        <span v-else>Start Stream</span>
      </RouterLink>
    </div>

    <div class="border-t border-white/5 px-3 py-4 space-y-2">
      <RouterLink
        to="/settings"
        class="flex items-center rounded-xl px-4 py-3 text-sm text-zinc-500 transition-all duration-200 hover:bg-white/5 hover:text-zinc-100"
      >
        <span class="material-symbols-outlined shrink-0">settings</span>
        <span v-if="!collapsed" class="ml-3">Settings</span>
      </RouterLink>

      <button
        type="button"
        class="flex w-full items-center rounded-xl px-4 py-3 text-sm text-zinc-500 transition-all duration-200 hover:bg-white/5 hover:text-zinc-100"
      >
        <span class="material-symbols-outlined shrink-0">logout</span>
        <span v-if="!collapsed" class="ml-3">Logout</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { RouterLink, useRoute } from 'vue-router'

const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false,
  },
})

const route = useRoute()

const links = [
  { name: 'Home', to: '/', icon: 'home' },
  { name: 'Streams', to: '/streams', icon: 'sensors' },
  { name: 'Videos', to: '/videos', icon: 'video_library' },
  { name: 'Messages', to: '/messages', icon: 'chat' },
  { name: 'Notifications', to: '/notifications', icon: 'notifications' },
]

const isActive = (path) => {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}
</script>
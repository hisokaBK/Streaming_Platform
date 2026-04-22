<template>
  <footer class="border-t border-outline-variant/10 bg-surface-container-lowest px-4 py-12 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
      <div class="grid grid-cols-1 gap-10 sm:gap-12 md:grid-cols-2 xl:grid-cols-4">
        <div class="md:col-span-2 xl:col-span-1">
          <span
            class="bg-gradient-to-br from-fuchsia-500 to-purple-700 bg-clip-text font-headline text-xl font-black tracking-tighter text-transparent sm:text-2xl"
          >
            HISOKA NOIR
          </span>

          <p
            class="mt-5 max-w-sm text-xs font-bold uppercase leading-relaxed tracking-wider text-on-surface-variant sm:mt-6"
          >
            Live streaming platform built with Laravel, Vue, LiveKit and real-time interaction.
          </p>
        </div>

        <div>
          <h5 class="mb-5 text-[10px] font-black uppercase tracking-[0.2em] text-primary sm:mb-6">
            Platform
          </h5>

          <ul class="space-y-3 sm:space-y-4">
            <li>
              <RouterLink to="/" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Home
              </RouterLink>
            </li>
            <li>
              <RouterLink to="/streams" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Streams
              </RouterLink>
            </li>
            <li>
              <RouterLink to="/videos" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Videos
              </RouterLink>
            </li>
            <li>
              <RouterLink
                :to="isAuthenticated ? '/streams/create' : '/register'"
                class="text-sm text-on-surface-variant transition-colors hover:text-on-surface"
              >
                {{ isAuthenticated ? 'Start Stream' : 'Get Started' }}
              </RouterLink>
            </li>
          </ul>
        </div>

        <div>
          <h5 class="mb-5 text-[10px] font-black uppercase tracking-[0.2em] text-primary sm:mb-6">
            Account
          </h5>

          <ul class="space-y-3 sm:space-y-4">
            <li v-if="isAuthenticated">
              <RouterLink to="/profile" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Profile
              </RouterLink>
            </li>
            <li v-if="isAuthenticated">
              <RouterLink to="/messages" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Messages
              </RouterLink>
            </li>
            <li v-if="isAuthenticated">
              <RouterLink to="/notifications" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Notifications
              </RouterLink>
            </li>
            <li v-if="!isAuthenticated">
              <RouterLink to="/login" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Login
              </RouterLink>
            </li>
            <li v-if="!isAuthenticated">
              <RouterLink to="/register" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Register
              </RouterLink>
            </li>
            <li v-if="isAdmin">
              <RouterLink to="/admin/dashboard" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Dashboard
              </RouterLink>
            </li>
          </ul>
        </div>

        <div>
          <h5 class="mb-5 text-[10px] font-black uppercase tracking-[0.2em] text-primary sm:mb-6">
            Connect
          </h5>

          <div class="flex flex-wrap gap-3 sm:gap-4">
            <RouterLink
              to="/streams"
              class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container text-on-surface transition-colors hover:bg-primary/20"
            >
              <span class="material-symbols-outlined text-sm">live_tv</span>
            </RouterLink>

            <RouterLink
              to="/videos"
              class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container text-on-surface transition-colors hover:bg-primary/20"
            >
              <span class="material-symbols-outlined text-sm">video_library</span>
            </RouterLink>

            <RouterLink
              :to="isAuthenticated ? '/messages' : '/login'"
              class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container text-on-surface transition-colors hover:bg-primary/20"
            >
              <span class="material-symbols-outlined text-sm">chat</span>
            </RouterLink>
          </div>
        </div>
      </div>

      <div
        class="mt-10 flex flex-col gap-4 border-t border-outline-variant/10 pt-6 sm:mt-14 sm:pt-8 lg:flex-row lg:items-center lg:justify-between"
      >
        <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">
          © 2026 HISOKA NOIR. ALL RIGHTS RESERVED. YOUCODE
        </p>

        <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:gap-6 lg:gap-8">
          <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-600">
            Laravel + Vue + LiveKit
          </span>
          <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-600">
            Status: Operational
          </span>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const isAuthenticated = computed(() => !!authStore.user)
const isAdmin = computed(() => authStore.user?.role === 'admin')
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
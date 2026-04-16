<template>
  <div
    v-if="open"
    class="fixed inset-0 z-[70] flex items-center justify-center bg-black/70 px-4 backdrop-blur-sm"
    @click.self="$emit('close')"
  >
    <div class="w-full max-w-xl rounded-3xl border border-white/10 bg-surface-container p-6 shadow-2xl">
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h3 class="font-headline text-2xl font-bold text-on-surface">{{ title }}</h3>
          <p class="text-sm text-on-surface-variant">{{ users.length }} users</p>
        </div>

        <button
          type="button"
          class="rounded-full p-2 text-zinc-400 transition hover:bg-white/10 hover:text-white"
          @click="$emit('close')"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <div v-if="loading" class="space-y-3">
        <div
          v-for="n in 5"
          :key="n"
          class="h-16 animate-pulse rounded-2xl bg-surface-container-high"
        ></div>
      </div>

      <div v-else-if="users.length === 0" class="rounded-2xl bg-surface-container-low p-8 text-center text-on-surface-variant">
        No users found.
      </div>

      <div v-else class="max-h-[420px] space-y-3 overflow-y-auto pr-1">
        <div
          v-for="user in users"
          :key="user.id"
          class="flex items-center justify-between rounded-2xl bg-surface-container-low p-4 transition hover:bg-surface-container-high"
        >
          <div class="flex items-center gap-4">
            <img
              :src="user.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name)"
              :alt="user.name"
              class="h-12 w-12 rounded-full object-cover"
            />
            <div>
              <h4 class="text-sm font-bold text-on-surface">{{ user.name }}</h4>
              <p class="text-xs text-zinc-500">{{ user.email }}</p>
            </div>
          </div>

          <RouterLink
            :to="`/profile/${user.id}`"
            class="rounded-full border border-white/10 px-4 py-2 text-xs font-bold uppercase tracking-wider text-primary transition hover:bg-primary/10"
            @click="$emit('close')"
          >
            View
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'

defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '',
  },
  users: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['close'])
</script>
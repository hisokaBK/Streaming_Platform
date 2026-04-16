<template>
  <nav
    class="sticky top-0 z-50 flex w-full items-center justify-between bg-black/80 px-6 py-4 shadow-[0_20px_40px_rgba(0,0,0,0.6)] backdrop-blur-xl"
  >
    <div class="flex items-center gap-4">
      <button
        type="button"
        class="rounded-full p-2 text-zinc-400 transition-all duration-300 hover:bg-white/10 hover:text-white active:scale-90"
        @click="$emit('toggle-sidebar')"
      >
        <span class="material-symbols-outlined">menu</span>
      </button>

      <span
        class="bg-gradient-to-br from-fuchsia-500 to-purple-600 bg-clip-text font-headline text-2xl font-black tracking-tighter text-transparent"
      >
        HSKn
      </span>
    </div>

    <div class="hidden items-center gap-6 md:flex">
      <RouterLink
        class="font-manrope tracking-tight text-zinc-400 transition-colors hover:text-zinc-100"
        to="/"
      >
        Discover
      </RouterLink>

      <RouterLink
        class="font-manrope tracking-tight text-zinc-400 transition-colors hover:text-zinc-100"
        to="/streams"
      >
        Live
      </RouterLink>

      <RouterLink
        class="font-manrope tracking-tight text-zinc-400 transition-colors hover:text-zinc-100"
        to="/videos"
      >
        Videos
      </RouterLink>
    </div>

    <div class="flex items-center gap-4">
      <RouterLink
        to="/notifications"
        class="rounded-full p-2 text-zinc-400 transition-all duration-300 hover:bg-white/10 hover:text-white active:scale-90"
      >
        <span class="material-symbols-outlined">notifications</span>
      </RouterLink>

      <RouterLink to="/profile" class="block">
        <img
          :src="avatarUrl"
          alt="User profile avatar"
          class="h-10 w-10 rounded-full border-2 border-primary/30 object-cover object-center"
        />
      </RouterLink>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'

const props = defineProps({
  profile: {
    type: Object,
    default: null,
  },
})

defineEmits(['toggle-sidebar'])

const buildStorageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http')) return path

  return `http://localhost:8000/storage/${path}`
}

const avatarUrl = computed(() => {
  const avatar = props.profile?.avatar

  if (avatar) {
    return buildStorageUrl(avatar)
  }

  const name = props.profile?.user?.name || 'User'
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=111111&color=ffffff&size=256`
})
</script>
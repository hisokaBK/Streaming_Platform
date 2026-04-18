<template>
  <div class="dark">
    <div class="min-h-screen bg-background text-on-background antialiased selection:bg-primary selection:text-on-primary">
      <TopNavbar @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed" />

      <div class="flex min-h-screen">
        <AppSidebar :collapsed="sidebarCollapsed" />

        <main
          :class="[
            'min-h-screen flex-1 px-8 pb-32 pt-3 transition-all duration-300',
            sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'
          ]"
        >
          <div class="mx-auto max-w-7xl">
            <!-- Page Header -->
            <div class="mb-12 flex flex-col justify-between gap-8 md:flex-row md:items-end">
              <div>
                <h1 class="neon-magenta-glow mb-4 font-headline text-5xl font-black tracking-tighter md:text-6xl">
                  Live Now
                </h1>
                <p class="max-w-lg font-body text-on-surface-variant">
                  Discover the hottest streamers across the globe in real-time. Exclusive content, immersive experiences.
                </p>
              </div>
            </div>

            <!-- Categories -->
            <div class="no-scrollbar mb-10 overflow-x-auto pb-2">
              <div class="flex w-max items-center gap-4">
                <button
                  type="button"
                  class="rounded-full border px-5 py-2.5 text-sm font-semibold transition-all"
                  :class="selectedCategory === 'all'
                    ? 'border-primary/20 bg-primary/10 text-primary'
                    : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-bright'"
                  @click="changeCategory('all')"
                >
                  <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">apps</span>
                    All
                  </span>
                </button>

                <button
                  v-for="category in categories"
                  :key="category.id"
                  type="button"
                  class="rounded-full px-5 py-2.5 text-sm font-semibold transition-all"
                  :class="selectedCategory === category.id
                    ? 'border border-primary/20 bg-primary/10 text-primary'
                    : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-bright'"
                  @click="changeCategory(category.id)"
                >
                  <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">sell</span>
                    {{ category.name }}
                  </span>
                </button>
              </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
              <div
                v-for="n in 6"
                :key="n"
                class="animate-pulse"
              >
                <div class="mb-4 aspect-video rounded-lg bg-surface-container-high shadow-2xl"></div>
                <div class="flex gap-4">
                  <div class="h-12 w-12 rounded-full bg-surface-container-high"></div>
                  <div class="flex-1">
                    <div class="mb-2 h-4 w-3/4 rounded bg-surface-container-high"></div>
                    <div class="mb-3 h-3 w-1/2 rounded bg-surface-container-high"></div>
                    <div class="mb-3 flex gap-2">
                      <div class="h-6 w-16 rounded-full bg-surface-container-high"></div>
                      <div class="h-6 w-16 rounded-full bg-surface-container-high"></div>
                    </div>
                    <div class="h-3 w-full rounded bg-surface-container-high"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty -->
            <div
              v-else-if="streams.length === 0"
              class="rounded-3xl bg-surface-container-low p-12 text-center shadow-xl"
            >
              <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary">
                <span class="material-symbols-outlined text-3xl">live_tv</span>
              </div>
              <h2 class="mb-2 font-headline text-2xl font-bold text-white">
                No streams found
              </h2>
              <p class="text-on-surface-variant">
                There are no streams available for this category yet.
              </p>
            </div>

            <!-- Stream Grid -->
            <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
              <RouterLink
                v-for="stream in streams"
                :key="stream.id"
                :to="`/streams/${stream.id}`"
                class="group cursor-pointer"
              >
                <div class="relative mb-4 aspect-video overflow-hidden rounded-lg shadow-2xl transition-transform duration-300 group-hover:-translate-y-2">
                  <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-tertiary/10"></div>
                  <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>

                  <div class="absolute left-4 top-4 flex items-center gap-2">
                    <span
                      class="rounded px-3 py-1 text-[10px] font-black uppercase tracking-widest"
                      :class="stream.status === 'live'
                        ? 'bg-error text-white animate-pulse'
                        : 'bg-surface-container-high text-on-surface-variant'"
                    >
                      <span class="mr-1 inline-block h-1.5 w-1.5 rounded-full bg-white"></span>
                      {{ stream.status }}
                    </span>

                    <span class="rounded bg-black/60 px-3 py-1 text-[10px] font-bold tracking-widest text-white backdrop-blur-md">
                      {{ stream.current_viewers || 0 }} VIEWERS
                    </span>
                  </div>

                  <div class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity group-hover:opacity-100">
                    <span class="material-symbols-outlined text-6xl text-white opacity-80">
                      play_circle
                    </span>
                  </div>
                </div>

                <div class="flex gap-4">
                  <img
                    :src="getAvatar(stream.user?.avatar, stream.user?.name)"
                    :alt="stream.user?.name || 'Avatar'"
                    class="h-12 w-12 flex-shrink-0 rounded-full border-2 border-surface-container-highest object-cover"
                  />

                  <div class="min-w-0 flex-1">
                    <h3 class="truncate font-headline text-lg font-bold text-white transition-colors group-hover:text-primary">
                      {{ stream.title }}
                    </h3>

                    <p class="mb-2 text-sm font-medium text-on-surface-variant">
                      {{ stream.user?.name || 'Unknown streamer' }}
                    </p>

                    <p class="mb-3 line-clamp-2 text-sm text-zinc-500">
                      {{ stream.description || 'No description provided.' }}
                    </p>

                    <div class="mb-4 flex flex-wrap gap-2">
                      <span
                        v-for="category in stream.categories || []"
                        :key="category.id"
                        class="rounded-full bg-surface-container px-3 py-1 text-[10px] font-bold uppercase tracking-tighter text-zinc-400 outline outline-1 outline-outline-variant/15"
                      >
                        {{ category.name }}
                      </span>
                    </div>

                    <div class="flex items-center gap-4 text-xs font-medium text-zinc-500">
                      <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">forum</span>
                        {{ stream.comments_count || 0 }}
                      </span>

                      <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">favorite</span>
                        {{ stream.reactions_count || 0 }}
                      </span>

                      <span class="ml-auto">
                        {{ formatStartedAt(stream.started_at, stream.created_at) }}
                      </span>
                    </div>
                  </div>
                </div>
              </RouterLink>
            </div>

            <!-- Pagination -->
            <div v-if="meta.last_page > 1" class="mt-20 flex justify-center">
              <nav class="flex items-center gap-2 rounded-full bg-surface-container-low p-2 shadow-lg">
                <button
                  type="button"
                  class="flex h-10 w-10 items-center justify-center rounded-full text-on-surface-variant transition-all hover:bg-surface-bright disabled:opacity-40"
                  :disabled="meta.current_page === 1"
                  @click="goToPage(meta.current_page - 1)"
                >
                  <span class="material-symbols-outlined">chevron_left</span>
                </button>

                <button
                  v-for="page in visiblePages"
                  :key="page"
                  type="button"
                  class="flex h-10 w-10 items-center justify-center rounded-full font-bold transition-all"
                  :class="page === meta.current_page
                    ? 'bg-primary text-on-primary shadow-[0_0_15px_rgba(246,128,255,0.4)]'
                    : 'text-on-surface-variant hover:bg-surface-bright hover:text-white'"
                  @click="goToPage(page)"
                >
                  {{ page }}
                </button>

                <button
                  type="button"
                  class="flex h-10 items-center justify-center rounded-full px-6 text-xs font-bold uppercase tracking-widest text-on-surface-variant transition-all hover:bg-surface-bright hover:text-white disabled:opacity-40"
                  :disabled="meta.current_page === meta.last_page"
                  @click="goToPage(meta.current_page + 1)"
                >
                  Next
                  <span class="material-symbols-outlined ml-2 text-sm">chevron_right</span>
                </button>
              </nav>
            </div>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import TopNavbar from '@/components/layout/TopNavbar.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'

const sidebarCollapsed = ref(false)

const loading = ref(false)
const streams = ref([])
const categories = ref([])
const selectedCategory = ref('all')

const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
})

const buildStorageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http')) return path
  return `http://localhost:8000/storage/${path}`
}

const getAvatar = (avatar, name = 'User') => {
  if (avatar) {
    return buildStorageUrl(avatar)
  }

  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=111111&color=ffffff&size=256`
}

const extractCategoriesFromStreams = (streamsList) => {
  const map = new Map()

  streamsList.forEach((stream) => {
    ;(stream.categories || []).forEach((category) => {
      if (!map.has(category.id)) {
        map.set(category.id, category)
      }
    })
  })

  return Array.from(map.values())
}

const loadStreams = async (page = 1) => {
  loading.value = true

  try {
    let response

    if (selectedCategory.value === 'all') {
      response = await api.get(`/stream/streams?page=${page}`)
    } else {
      response = await api.get(`/stream/streams/category/${selectedCategory.value}?page=${page}`)
    }

    streams.value = response.data?.data || []
    meta.value = response.data?.meta || {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
    }

    const extracted = extractCategoriesFromStreams(streams.value)

    if (page === 1 && categories.value.length === 0) {
      categories.value = extracted
    } else {
      const merged = new Map(categories.value.map((item) => [item.id, item]))
      extracted.forEach((item) => merged.set(item.id, item))
      categories.value = Array.from(merged.values())
    }
  } catch (error) {
    console.error('Failed to load streams', error)
    streams.value = []
  } finally {
    loading.value = false
  }
}

const changeCategory = async (categoryId) => {
  selectedCategory.value = categoryId
  await loadStreams(1)
}

const goToPage = async (page) => {
  if (page < 1 || page > meta.value.last_page) return
  await loadStreams(page)
}

const formatStartedAt = (startedAt, createdAt) => {
  const date = startedAt || createdAt
  if (!date) return 'Unknown time'

  const d = new Date(date)
  return d.toLocaleDateString()
}

const visiblePages = computed(() => {
  const current = meta.value.current_page
  const last = meta.value.last_page

  const start = Math.max(1, current - 1)
  const end = Math.min(last, current + 1)

  const pages = []
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

onMounted(() => {
  loadStreams()
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

.neon-magenta-glow {
  text-shadow:
    0 0 20px rgba(246, 128, 255, 0.4),
    0 0 40px rgba(246, 128, 255, 0.2);
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}

.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
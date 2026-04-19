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
                  Replays
                </h1>
                <p class="max-w-lg font-body text-on-surface-variant">
                  Explore replay videos, highlights, and the best moments captured from past live streams.
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
              v-else-if="videos.length === 0"
              class="rounded-3xl bg-surface-container-low p-12 text-center shadow-xl"
            >
              <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary">
                <span class="material-symbols-outlined text-3xl">smart_display</span>
              </div>
              <h2 class="mb-2 font-headline text-2xl font-bold text-white">
                No videos found
              </h2>
              <p class="text-on-surface-variant">
                There are no replay videos available for this category yet.
              </p>
            </div>

            <!-- Video Grid -->
            <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
              <RouterLink
                v-for="video in videos"
                :key="video.id"
                :to="`/videos/${video.id}`"
                class="group cursor-pointer"
              >
                <div class="relative mb-4 aspect-video overflow-hidden rounded-lg shadow-2xl transition-transform duration-300 group-hover:-translate-y-2">
                  <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-tertiary/10"></div>
                  <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>

                  <template v-if="video.url">
                    <video
                      :src="video.url"
                      class="h-full w-full object-cover"
                      muted
                      preload="metadata"
                    ></video>
                  </template>

                  <div v-else class="absolute inset-0 bg-surface-container-high"></div>

                  <div class="absolute left-4 top-4 flex items-center gap-2">
                    <span
                      class="rounded px-3 py-1 text-[10px] font-black uppercase tracking-widest bg-surface-container-high text-on-surface-variant"
                    >
                      <span class="mr-1 inline-block h-1.5 w-1.5 rounded-full bg-zinc-400"></span>
                      Replay
                    </span>

                    <span
                      v-if="video.stream?.status"
                      class="rounded bg-black/60 px-3 py-1 text-[10px] font-bold tracking-widest text-white backdrop-blur-md"
                    >
                      {{ video.stream.status }}
                    </span>
                  </div>

                  <div
                    v-if="video.duration"
                    class="absolute bottom-4 right-4 rounded bg-black/70 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-white backdrop-blur-md"
                  >
                    {{ formatDuration(video.duration) }}
                  </div>

                  <div class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity group-hover:opacity-100">
                    <span class="material-symbols-outlined text-6xl text-white opacity-80">
                      play_circle
                    </span>
                  </div>
                </div>

                <div class="flex gap-4">
                  <RouterLink
                    :to="`/profile/${video.user?.id}`"
                    class="h-12 w-12 flex-shrink-0"
                    @click.stop
                  >
                    <img
                      :src="getAvatar(video.user?.avatar, video.user?.name)"
                      :alt="video.user?.name || 'Avatar'"
                      class="h-12 w-12 rounded-full border-2 border-surface-container-highest object-cover"
                    />
                  </RouterLink>

                  <div class="min-w-0 flex-1">
                    <h3 class="truncate font-headline text-lg font-bold text-white transition-colors group-hover:text-primary">
                      {{ video.title }}
                    </h3>

                    <RouterLink
                      :to="`/profile/${video.user?.id}`"
                      class="mb-2 block text-sm font-medium text-on-surface-variant transition hover:text-primary"
                      @click.stop
                    >
                      {{ video.user?.name || 'Unknown creator' }}
                    </RouterLink>

                    <p class="mb-3 line-clamp-2 text-sm text-zinc-500">
                      {{ video.description || 'No description provided.' }}
                    </p>

                    <div class="mb-4 flex flex-wrap gap-2">
                      <span
                        v-for="category in video.categories || []"
                        :key="category.id"
                        class="rounded-full bg-surface-container px-3 py-1 text-[10px] font-bold uppercase tracking-tighter text-zinc-400 outline outline-1 outline-outline-variant/15"
                      >
                        {{ category.name }}
                      </span>
                    </div>

                    <div class="flex items-center gap-4 text-xs font-medium text-zinc-500">
                      <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">forum</span>
                        {{ video.comments_count || 0 }}
                      </span>

                      <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">favorite</span>
                        {{ video.stream?.reactions_count || 0 }}
                      </span>

                      <span class="ml-auto">
                        {{ formatCreatedAt(video.created_at) }}
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
const videos = ref([])
const categories = ref([])
const selectedCategory = ref('all')

const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
})

const normalizeCollection = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.data)) return payload.data.data
  return []
}

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

const loadCategories = async () => {
  try {
    const response = await api.get('/categories')
    categories.value = normalizeCollection(response.data)
  } catch (error) {
    console.error('Failed to load categories', error)
    categories.value = []
  }
}

const loadVideos = async (page = 1) => {
  loading.value = true

  try {
    let response

    if (selectedCategory.value === 'all') {
      response = await api.get(`/video/videos?page=${page}`)
    } else {
      response = await api.get(`/video/videos/category/${selectedCategory.value}?page=${page}`)
    }

    videos.value = normalizeCollection(response.data?.data || response.data)
    meta.value = response.data?.meta || {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
    }
  } catch (error) {
    console.error('Failed to load videos', error)
    videos.value = []
  } finally {
    loading.value = false
  }
}

const changeCategory = async (categoryId) => {
  selectedCategory.value = categoryId
  await loadVideos(1)
}

const goToPage = async (page) => {
  if (page < 1 || page > meta.value.last_page) return
  await loadVideos(page)
}

const formatCreatedAt = (date) => {
  if (!date) return 'Unknown time'
  return new Date(date).toLocaleDateString()
}

const formatDuration = (value) => {
  const totalSeconds = Number(value || 0)

  if (!Number.isFinite(totalSeconds) || totalSeconds <= 0) {
    return '00:00'
  }

  const hours = Math.floor(totalSeconds / 3600)
  const minutes = Math.floor((totalSeconds % 3600) / 60)
  const seconds = Math.floor(totalSeconds % 60)

  if (hours > 0) {
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
  }

  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
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

onMounted(async () => {
  await Promise.all([loadCategories(), loadVideos()])
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
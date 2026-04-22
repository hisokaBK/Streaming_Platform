<template>
  <div class="dark">
    <div class="min-h-screen bg-background text-on-background antialiased selection:bg-primary selection:text-on-primary">
      <TopNavbar @toggle-sidebar="handleSidebarToggle" />

      <div class="flex min-h-screen">
        <AppSidebar
          :collapsed="sidebarCollapsed"
          :mobile-open="mobileSidebarOpen"
          @close="mobileSidebarOpen = false"
        />

        <main
          :class="[
            'min-w-0 flex-1 px-4 pb-27 pt-4 transition-all duration-300 sm:px-6 lg:px-8 pt-[72px]',
            sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'
          ]"
        >
          <div class="mx-auto max-w-7xl">
            <div class="mb-12 flex flex-col justify-between gap-8 md:flex-row md:items-end">
              <div>
                <h1 class="neon-magenta-glow mb-4 font-headline text-4xl font-black tracking-tighter sm:text-5xl md:text-6xl">
                  Live Now
                </h1>
                <p class="max-w-lg font-body text-sm text-on-surface-variant sm:text-base">
                  Discover the hottest streamers across the globe in real-time. Exclusive content, immersive experiences.
                </p>
              </div>
            </div>

            <div class="no-scrollbar mb-6 overflow-x-auto pb-2">
              <div class="flex w-max items-center gap-3 sm:gap-4">
                <button
                  type="button"
                  class="rounded-full border px-4 py-2.5 text-sm font-semibold transition-all sm:px-5"
                  :class="selectedStatus === 'all'
                    ? 'border-primary/20 bg-primary/10 text-primary'
                    : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-bright'"
                  @click="changeStatus('all')"
                >
                  <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">filter_alt</span>
                    All Status
                  </span>
                </button>

                <button
                  type="button"
                  class="rounded-full border px-4 py-2.5 text-sm font-semibold transition-all sm:px-5"
                  :class="selectedStatus === 'live'
                    ? 'border-primary/20 bg-primary/10 text-primary'
                    : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-bright'"
                  @click="changeStatus('live')"
                >
                  <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">live_tv</span>
                    Live Now
                  </span>
                </button>

                <button
                  type="button"
                  class="rounded-full border px-4 py-2.5 text-sm font-semibold transition-all sm:px-5"
                  :class="selectedStatus === 'ended'
                    ? 'border-primary/20 bg-primary/10 text-primary'
                    : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-bright'"
                  @click="changeStatus('ended')"
                >
                  <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">history</span>
                    Ended
                  </span>
                </button>
              </div>
            </div>

            <div class="no-scrollbar mb-10 overflow-x-auto pb-2">
              <div class="flex w-max items-center gap-3 sm:gap-4">
                <button
                  type="button"
                  class="rounded-full border px-4 py-2.5 text-sm font-semibold transition-all sm:px-5"
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
                  class="rounded-full px-4 py-2.5 text-sm font-semibold transition-all sm:px-5"
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

            <div
              v-else-if="streams.length === 0"
              class="rounded-3xl bg-surface-container-low p-8 text-center shadow-xl sm:p-12"
            >
              <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary">
                <span class="material-symbols-outlined text-3xl">live_tv</span>
              </div>
              <h2 class="mb-2 font-headline text-2xl font-bold text-white">
                No streams found
              </h2>
              <p class="text-on-surface-variant">
                There are no streams available for this filter yet.
              </p>
            </div>

            <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
              <RouterLink
                v-for="stream in streams"
                :key="stream.id"
                :to="`/streams/${stream.id}`"
                class="group cursor-pointer"
              >
                <div class="relative mb-4 aspect-video overflow-hidden rounded-lg shadow-2xl transition-transform duration-300 group-hover:-translate-y-2">
                  <img
                    :src="getThumbnail(stream.thumbnail_url || stream.thumbnail, stream.title)"
                    :alt="stream.title || 'Stream thumbnail'"
                    class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                  />

                  <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-tertiary/10"></div>
                  <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/20 to-transparent"></div>

                  <div class="absolute left-4 top-4 flex flex-wrap items-center gap-2">
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
                    :src="getAvatar(stream.user?.avatar_url || stream.user?.avatar, stream.user?.name)"
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

                    <p class="mb-3 line-clamp-2 text-sm text-zinc-400">
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

                    <div class="flex flex-wrap items-center gap-3 text-xs font-medium text-zinc-500 sm:gap-4">
                      <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">forum</span>
                        {{ stream.comments_count || 0 }}
                      </span>

                      <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">favorite</span>
                        {{ stream.reactions_count || 0 }}
                      </span>

                      <span class="sm:ml-auto">
                        {{ formatStartedAt(stream.started_at, stream.created_at) }}
                      </span>
                    </div>
                  </div>
                </div>
              </RouterLink>
            </div>

            <div v-if="meta.last_page > 1" class="mt-16 flex justify-center sm:mt-20">
              <nav class="flex max-w-full flex-wrap items-center justify-center gap-2 rounded-3xl bg-surface-container-low p-2 shadow-lg">
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
                  class="flex h-10 items-center justify-center rounded-full px-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant transition-all hover:bg-surface-bright hover:text-white disabled:opacity-40 sm:px-6"
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
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import TopNavbar from '@/components/layout/TopNavbar.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'

const sidebarCollapsed = ref(false)
const mobileSidebarOpen = ref(false)

const loading = ref(false)
const streams = ref([])
const categories = ref([])
const selectedCategory = ref('all')
const selectedStatus = ref('all')

const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
})

const APP_URL = (import.meta.env.VITE_APP_URL || 'http://localhost:8000').replace(/\/$/, '')
const STORAGE_BASE = `${APP_URL}/storage`

const normalizeCollection = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.data)) return payload.data.data
  if (Array.isArray(payload?.data?.categories)) return payload.data.categories
  if (Array.isArray(payload?.categories)) return payload.categories
  return []
}

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

const getAvatar = (avatar, name = 'User') => {
  const fixedAvatar = buildStorageUrl(avatar)

  if (fixedAvatar) {
    return fixedAvatar
  }

  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=111111&color=ffffff&size=256`
}

const getThumbnail = (thumbnail, title = 'Stream') => {
  const fixedThumbnail = buildStorageUrl(thumbnail)

  if (fixedThumbnail) {
    return fixedThumbnail
  }

  return `https://ui-avatars.com/api/?name=${encodeURIComponent(title)}&background=111111&color=ffffff&size=512`
}

const handleSidebarToggle = () => {
  if (window.innerWidth < 768) {
    mobileSidebarOpen.value = !mobileSidebarOpen.value
    return
  }

  sidebarCollapsed.value = !sidebarCollapsed.value
}

const handleResize = () => {
  if (window.innerWidth >= 768) {
    mobileSidebarOpen.value = false
  }
}

watch(mobileSidebarOpen, (isOpen) => {
  if (window.innerWidth < 768) {
    document.body.style.overflow = isOpen ? 'hidden' : ''
  }
})

const loadCategories = async () => {
  try {
    const response = await api.get('/categories')

    categories.value = normalizeCollection(response.data).map((category) => ({
      id: String(category.id),
      name: category.name,
    }))
  } catch (error) {
    console.error('Failed to load categories', {
      status: error.response?.status,
      data: error.response?.data,
    })
    categories.value = []
  }
}

const loadStreams = async (page = 1) => {
  loading.value = true

  try {
    const params = { page }

    if (selectedCategory.value !== 'all') {
      params.category_id = selectedCategory.value
    }

    if (selectedStatus.value !== 'all') {
      params.status = selectedStatus.value
    }

    const response = await api.get('/stream/streams', { params })

    streams.value = Array.isArray(response.data?.data) ? response.data.data : normalizeCollection(response.data)
    meta.value = response.data?.meta || {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
    }
  } catch (error) {
    console.error('Failed to load streams', error)
    streams.value = []
    meta.value = {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
    }
  } finally {
    loading.value = false
  }
}

const changeCategory = async (categoryId) => {
  selectedCategory.value = String(categoryId)
  await loadStreams(1)
}

const changeStatus = async (status) => {
  selectedStatus.value = status
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

onMounted(async () => {
  handleResize()
  window.addEventListener('resize', handleResize)

  await Promise.all([
    loadCategories(),
    loadStreams(),
  ])
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
  document.body.style.overflow = ''
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
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
            'min-w-0 flex-1 overflow-x-hidden pt-[80px] transition-all duration-300',
            sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'
          ]"
        >
          <!-- Hero Section -->
          <section class="relative flex min-h-[700px] items-center overflow-hidden px-4 sm:px-6 lg:min-h-[870px] lg:px-16">
            <div class="absolute inset-0 z-0">
              <img
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuB3JAgeJJDfVCBak5NoRLozoR2y7gQLurMhTsAwvGMlowRFZN4476Qc_Rnb1Rt-hu3vy4Ey9ZVyGYINagJ8iE7Z9t3vMCdvzmv14QDEZexna7PMCV2-dMSnvk1bBHh2JQgsdoXf6vq2COxT-bx5HCVdLMKY-WY3FO69V9WC8nGw46by_gZKvvwmGYHsSmkx4m2yfoYtxNw5oNkb4au_UsEnuYzAU34ij3tv-4pWdHc6wzAUW0Ig1LU2f_NJmYDj4pQ9BlMSkTcCESQ"
                alt="Cinematic digital streaming setup"
                class="h-full w-full object-cover opacity-40 mix-blend-luminosity"
              />
              <div class="absolute inset-0 bg-gradient-to-r from-background via-background/60 to-transparent"></div>
              <div class="absolute inset-0 bg-gradient-to-t from-background to-transparent"></div>
            </div>

            <div class="relative z-10 max-w-4xl">
              <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-tertiary-container/30 bg-tertiary-container/20 px-3 py-1">
                <span class="h-2 w-2 animate-pulse rounded-full bg-tertiary"></span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-tertiary">
                  Live Now: Global Invitational
                </span>
              </div>

              <h1
                class="text-glow-magenta mb-6 font-headline text-4xl font-black leading-[0.9] tracking-tighter sm:text-5xl md:text-6xl lg:mb-8 lg:text-8xl"
              >
                THE DIGITAL <br />
                <span class="bg-neon-gradient bg-clip-text text-transparent">NIGHTCLUB</span>
              </h1>

              <p class="mb-8 max-w-2xl text-base leading-relaxed text-on-surface-variant sm:text-lg lg:mb-10 lg:text-xl">
                Welcome to the next evolution of live entertainment. Experience ultra-low latency, 4K HDR fidelity, and a community that never sleeps.
              </p>

              <div class="flex flex-wrap gap-4">
                <button
                  type="button"
                  class="flex items-center gap-3 rounded-full bg-neon-gradient px-6 py-3 text-sm font-bold text-on-primary transition-transform hover:scale-105 sm:px-8 sm:py-4"
                >
                  <span
                    class="material-symbols-outlined"
                    style="font-variation-settings: 'FILL' 1;"
                  >
                    play_arrow
                  </span>
                  Start Streaming
                </button>

                <button
                  type="button"
                  class="rounded-full border border-outline-variant/30 bg-surface-container-highest/80 px-6 py-3 text-sm font-bold text-on-surface backdrop-blur-md transition-colors hover:bg-surface-bright sm:px-8 sm:py-4"
                >
                  Explore Live
                </button>
              </div>
            </div>
          </section>

          <!-- Categories -->
          <section class="mt-16 px-4 sm:px-6 lg:mt-20 lg:px-16">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
              <h2 class="font-headline text-2xl font-black uppercase tracking-tight text-primary">
                Pulse Check
              </h2>

              <button
                type="button"
                class="flex items-center gap-2 text-sm font-bold text-on-surface-variant transition-colors hover:text-on-surface"
              >
                View All Categories
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
              </button>
            </div>

            <div v-if="categoriesLoading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
              <div
                v-for="n in 5"
                :key="`category-skeleton-${n}`"
                class="animate-pulse rounded-lg border border-outline-variant/10 bg-surface-container px-5 py-4"
              >
                <div class="flex items-center gap-4">
                  <div class="h-10 w-10 rounded-full bg-surface-container-high"></div>
                  <div class="h-4 w-24 rounded bg-surface-container-high"></div>
                </div>
              </div>
            </div>

            <div v-else-if="categories.length" class="flex flex-wrap gap-4">
              <div
                v-for="category in categories"
                :key="category.id"
                class="group relative flex cursor-pointer items-center gap-4 rounded-lg border border-outline-variant/10 bg-surface-container px-5 py-4 transition-all duration-300 hover:bg-surface-container-high"
              >
                <div
                  :class="getCategoryIconClass(category.name)"
                  class="flex h-10 w-10 items-center justify-center rounded-full transition-transform group-hover:scale-110"
                >
                  <span class="material-symbols-outlined">{{ getCategoryIcon(category.name) }}</span>
                </div>
                <span class="font-headline text-sm font-bold">{{ category.name }}</span>
              </div>
            </div>

            <div
              v-else
              class="rounded-2xl border border-outline-variant/10 bg-surface-container p-6 text-sm text-on-surface-variant"
            >
              No categories available right now.
            </div>
          </section>

          <!-- Featured Live Streams -->
          <section class="mt-20 px-4 sm:px-6 lg:mt-24 lg:px-16">
            <h2 class="mb-10 flex items-center gap-3 font-headline text-2xl font-black tracking-tight sm:text-3xl lg:mb-12">
              <span class="h-8 w-2 rounded-full bg-primary"></span>
              LIVE NOW
            </h2>

            <div v-if="streamsLoading" class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
              <div
                v-for="n in 3"
                :key="`stream-skeleton-${n}`"
                class="animate-pulse"
              >
                <div class="mb-4 aspect-video rounded-lg bg-surface-container-high shadow-xl"></div>
                <div class="flex gap-4">
                  <div class="h-12 w-12 rounded-full bg-surface-container-high"></div>
                  <div class="min-w-0 flex-1">
                    <div class="mb-2 h-4 w-3/4 rounded bg-surface-container-high"></div>
                    <div class="mb-2 h-3 w-1/2 rounded bg-surface-container-high"></div>
                    <div class="h-3 w-2/3 rounded bg-surface-container-high"></div>
                  </div>
                </div>
              </div>
            </div>

            <div
              v-else-if="liveStreams.length === 0"
              class="rounded-2xl border border-outline-variant/10 bg-surface-container p-6 text-sm text-on-surface-variant"
            >
              No live streams found.
            </div>

            <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
              <RouterLink
                v-for="stream in liveStreams"
                :key="stream.id"
                :to="`/streams/${stream.id}`"
                class="group flex cursor-pointer flex-col gap-4"
              >
                <div
                  class="relative aspect-video overflow-hidden rounded-lg bg-surface-container-highest shadow-xl transition-transform duration-300 group-hover:-translate-y-2"
                >
                  <img
                    :src="getStreamThumbnail(stream)"
                    :alt="stream.title"
                    class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                  />

                  <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                    <span class="rounded-sm bg-red-600 px-2 py-1 text-[10px] font-black uppercase tracking-tighter text-white">
                      {{ stream.status || 'Live' }}
                    </span>
                    <span class="rounded-sm bg-black/60 px-2 py-1 text-[10px] font-bold uppercase tracking-tighter text-white backdrop-blur-md">
                      {{ stream.current_viewers || 0 }} Viewers
                    </span>
                  </div>

                  <div
                    class="absolute inset-0 flex items-center justify-center bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 transition-opacity group-hover:opacity-100"
                  >
                    <span
                      class="scale-90 rounded-full bg-white px-6 py-2 text-xs font-bold uppercase tracking-widest text-black transition-transform group-hover:scale-100"
                    >
                      Watch Live
                    </span>
                  </div>
                </div>

                <div class="flex gap-4">
                  <div class="h-12 w-12 shrink-0 overflow-hidden rounded-full border-2 border-primary/30">
                    <img
                      :src="getUserAvatar(stream.user)"
                      :alt="stream.user?.name || 'Streamer'"
                      class="h-full w-full object-cover"
                    />
                  </div>

                  <div class="flex min-w-0 flex-col">
                    <h3 class="line-clamp-1 font-bold text-on-surface transition-colors group-hover:text-primary">
                      {{ stream.title }}
                    </h3>
                    <p class="text-sm font-medium text-on-surface-variant">
                      {{ stream.user?.name || 'Unknown streamer' }}
                    </p>
                    <span class="mt-1 text-[10px] font-bold uppercase text-zinc-500">
                      {{ formatStreamMeta(stream) }}
                    </span>
                  </div>
                </div>
              </RouterLink>
            </div>
          </section>

          <!-- Trending Videos -->
          <section class="mt-20 px-4 sm:px-6 lg:mt-24 lg:px-16">
            <div class="rounded-xl bg-surface-container p-6 sm:p-8 lg:p-12">
              <h2 class="mb-8 font-headline text-2xl font-black tracking-tight">
                TRENDING REPLAYS
              </h2>

              <div v-if="videosLoading" class="no-scrollbar flex gap-6 overflow-x-auto pb-4">
                <div
                  v-for="n in 4"
                  :key="`video-skeleton-${n}`"
                  class="w-[280px] flex-none animate-pulse sm:w-[300px]"
                >
                  <div class="mb-3 aspect-video rounded-lg bg-surface-container-high"></div>
                  <div class="mb-2 h-4 w-full rounded bg-surface-container-high"></div>
                  <div class="h-3 w-2/3 rounded bg-surface-container-high"></div>
                </div>
              </div>

              <div
                v-else-if="trendingVideos.length === 0"
                class="rounded-2xl border border-outline-variant/10 bg-surface-container-low p-6 text-sm text-on-surface-variant"
              >
                No videos found.
              </div>

              <div v-else class="no-scrollbar flex gap-6 overflow-x-auto pb-4">
                <RouterLink
                  v-for="video in trendingVideos"
                  :key="video.id"
                  :to="`/videos/${video.id}`"
                  class="group w-[280px] flex-none cursor-pointer sm:w-[300px]"
                >
                  <div class="relative mb-3 aspect-video overflow-hidden rounded-lg">
                    <img
                      :src="getVideoThumbnail(video)"
                      :alt="video.title"
                      class="h-full w-full object-cover grayscale transition-all duration-500 group-hover:grayscale-0"
                    />
                    <div class="absolute bottom-2 right-2 rounded-sm bg-black/80 px-2 py-1 text-[10px] font-bold text-white">
                      {{ formatDuration(video.duration) }}
                    </div>
                  </div>

                  <h4 class="line-clamp-2 text-sm font-bold text-on-surface">
                    {{ video.title }}
                  </h4>
                  <p class="mt-1 text-xs text-on-surface-variant">
                    {{ formatVideoStats(video) }}
                  </p>
                </RouterLink>
              </div>
            </div>
          </section>

          <!-- CTA -->
          <section class="mb-16 mt-24 px-4 sm:px-6 lg:mb-20 lg:mt-32 lg:px-16">
            <div class="relative overflow-hidden rounded-xl border border-primary/20 bg-surface-container-high shadow-2xl shadow-primary/10">
              <div class="pointer-events-none absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-primary/10 to-transparent"></div>

              <div class="relative z-10 max-w-3xl p-6 sm:p-8 lg:p-20">
                <h2 class="mb-6 font-headline text-3xl font-black tracking-tighter sm:text-4xl lg:text-5xl">
                  JOIN THE INNER CIRCLE
                </h2>

                <p class="mb-8 text-base leading-relaxed text-on-surface-variant sm:text-lg lg:mb-10">
                  Become an early adopter. Get exclusive access to beta features, limited edition drops, and VIP community events before anyone else.
                </p>

                <div class="flex flex-col gap-4 sm:flex-row">
                  <input
                    type="email"
                    placeholder="Enter your email address"
                    class="flex-1 rounded-full border-none bg-surface-container-low px-6 py-4 text-on-surface transition-all focus:ring-2 focus:ring-primary"
                  />

                  <button
                    type="button"
                    class="whitespace-nowrap rounded-full bg-neon-gradient px-8 py-4 text-xs font-bold uppercase tracking-widest text-on-primary transition-all hover:shadow-lg hover:shadow-primary/30 sm:px-10"
                  >
                    Request Invite
                  </button>
                </div>

                <p class="mt-6 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/60">
                  Secure Access • No Spam • Encryption Standard v2.0
                </p>
              </div>
            </div>
          </section>
        </main>
      </div>

      <AppFooter :collapsed="sidebarCollapsed" />
    </div>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import TopNavbar from '@/components/layout/TopNavbar.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppFooter from '@/components/layout/Footer.vue'

const sidebarCollapsed = ref(false)
const mobileSidebarOpen = ref(false)

const categories = ref([])
const liveStreams = ref([])
const trendingVideos = ref([])

const categoriesLoading = ref(false)
const streamsLoading = ref(false)
const videosLoading = ref(false)

const buildStorageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http')) return path
  return `http://localhost:8000/storage/${path}`
}

const getUserAvatar = (user) => {
  const avatar = user?.profile?.avatar || user?.avatar || null

  if (avatar) {
    return buildStorageUrl(avatar)
  }

  const name = user?.name || 'User'
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=111111&color=ffffff&size=256`
}

const normalizeCategories = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.categories)) return payload.data.categories
  if (Array.isArray(payload?.categories)) return payload.categories
  return []
}

const normalizePaginatedItems = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.data)) return payload.data.data
  return []
}

const loadCategories = async () => {
  categoriesLoading.value = true

  try {
    const response = await api.get('/categories')
    categories.value = normalizeCategories(response.data)
  } catch (error) {
    console.error('Failed to load categories', error)
    categories.value = []
  } finally {
    categoriesLoading.value = false
  }
}

const loadStreams = async () => {
  streamsLoading.value = true

  try {
    const response = await api.get('/stream/streams?page=1')
    const items = normalizePaginatedItems(response.data)

    liveStreams.value = items.slice(0, 3)
  } catch (error) {
    console.error('Failed to load streams', error)
    liveStreams.value = []
  } finally {
    streamsLoading.value = false
  }
}

const loadVideos = async () => {
  videosLoading.value = true

  try {
    const response = await api.get('/video/videos?page=1')
    const items = normalizePaginatedItems(response.data)

    trendingVideos.value = items.slice(0, 4)
  } catch (error) {
    console.error('Failed to load videos', error)
    trendingVideos.value = []
  } finally {
    videosLoading.value = false
  }
}

const getCategoryIcon = (categoryName = '') => {
  const value = categoryName.toLowerCase()

  if (value.includes('game')) return 'sports_esports'
  if (value.includes('music')) return 'music_note'
  if (value.includes('chat')) return 'chat_bubble'
  if (value.includes('anime')) return 'auto_awesome'
  if (value.includes('creative')) return 'code'
  if (value.includes('tech')) return 'memory'
  return 'sell'
}

const getCategoryIconClass = (categoryName = '') => {
  const value = categoryName.toLowerCase()

  if (value.includes('game')) return 'bg-primary/20 text-primary'
  if (value.includes('music')) return 'bg-secondary/20 text-secondary'
  if (value.includes('chat')) return 'bg-tertiary/20 text-tertiary'
  if (value.includes('anime')) return 'bg-fuchsia-500/20 text-fuchsia-500'
  if (value.includes('creative')) return 'bg-purple-500/20 text-purple-500'
  if (value.includes('tech')) return 'bg-cyan-500/20 text-cyan-400'
  return 'bg-primary/10 text-primary'
}

const getStreamThumbnail = (stream) => {
  return (
    buildStorageUrl(stream?.thumbnail) ||
    'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1200&q=80'
  )
}

const getVideoThumbnail = (video) => {
  return (
    buildStorageUrl(video?.thumbnail) ||
    buildStorageUrl(video?.cover) ||
    'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80'
  )
}

const formatStreamMeta = (stream) => {
  const categoryNames = (stream?.categories || []).map((category) => category.name).filter(Boolean)
  if (categoryNames.length) return categoryNames.join(' • ')
  return 'Live Stream'
}

const formatVideoStats = (video) => {
  const comments = video?.comments_count || 0
  const reactions = video?.stream?.reactions_count || 0
  return `${comments} comments • ${reactions} reactions`
}

const formatDuration = (seconds) => {
  if (!seconds || Number.isNaN(Number(seconds))) return '00:00'

  const total = Math.floor(Number(seconds))
  const hours = Math.floor(total / 3600)
  const minutes = Math.floor((total % 3600) / 60)
  const secs = total % 60

  if (hours > 0) {
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
  }

  return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
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

onMounted(async () => {
  handleResize()
  window.addEventListener('resize', handleResize)

  await Promise.all([
    loadCategories(),
    loadStreams(),
    loadVideos(),
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

.bg-neon-gradient {
  background: linear-gradient(135deg, #f680ff 0%, #c180ff 100%);
}

.text-glow-magenta {
  text-shadow: 0 0 20px rgba(246, 128, 255, 0.4);
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}

.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
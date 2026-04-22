<template>
  <div class="dark">
    <div class="min-h-screen bg-background text-on-surface">
      <TopNavbar @toggle-sidebar="handleSidebarToggle" />

      <div class="flex min-h-screen">
        <AppSidebar
          :collapsed="sidebarCollapsed"
          :mobile-open="mobileSidebarOpen"
          @close="mobileSidebarOpen = false"
        />

        <div
          :class="[
            'min-w-0 flex-1 pt-[72px] transition-all duration-300',
            sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'
          ]"
        >
          <main class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl space-y-10">
              <!-- Hero -->
              <section class="overflow-hidden rounded-[28px] border border-white/10 bg-surface-container p-6 shadow-xl sm:p-8 lg:p-10">
                <div class="grid items-center gap-8 lg:grid-cols-2">
                  <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-2 text-[10px] font-bold uppercase tracking-[0.2em] text-primary">
                      <span class="h-2 w-2 rounded-full bg-primary animate-pulse"></span>
                      Streaming Platform
                    </span>

                    <h1 class="mt-5 font-headline text-3xl font-black leading-tight text-white sm:text-4xl lg:text-5xl">
                      Stream live, interact with viewers, and save every stream as a replay
                    </h1>

                    <p class="mt-4 max-w-2xl text-sm leading-7 text-on-surface-variant sm:text-base">
                      Start live streams, receive comments and reactions in real time, and let users
                      watch the replay later from the videos page.
                    </p>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                      <RouterLink
                        to="/streams"
                        class="inline-flex items-center justify-center rounded-full bg-primary px-6 py-3 text-sm font-bold uppercase tracking-[0.15em] text-on-primary transition hover:opacity-90"
                      >
                        Explore Streams
                      </RouterLink>

                      <RouterLink
                        to="/videos"
                        class="inline-flex items-center justify-center rounded-full border border-white/10 bg-white/5 px-6 py-3 text-sm font-bold uppercase tracking-[0.15em] text-white transition hover:bg-white/10"
                      >
                        Watch Videos
                      </RouterLink>
                    </div>
                  </div>

                  <div class="grid gap-4">
                    <div class="overflow-hidden rounded-[24px] border border-white/10 bg-black/30">
                      <div class="relative aspect-video bg-zinc-950">
                        <img
                          v-if="featuredStream?.thumbnail_url || featuredStream?.thumbnail"
                          :src="getMediaUrl(featuredStream?.thumbnail_url || featuredStream?.thumbnail)"
                          :alt="featuredStream?.title || 'Featured stream'"
                          class="h-full w-full object-cover"
                        />
                        <div
                          v-else
                          class="flex h-full w-full items-center justify-center bg-[linear-gradient(135deg,#111,#22132c)]"
                        >
                          <span class="material-symbols-outlined text-6xl text-primary/70">live_tv</span>
                        </div>

                        <div class="absolute left-4 top-4 rounded-full bg-red-500 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-white">
                          Live
                        </div>

                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black via-black/60 to-transparent p-4">
                          <h3 class="line-clamp-2 text-lg font-bold text-white">
                            {{ featuredStream?.title || 'Live stream preview' }}
                          </h3>
                          <p class="mt-1 text-sm text-zinc-300">
                            {{ featuredStream?.user?.name || 'Streamer' }}
                          </p>
                        </div>
                      </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                      <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-center">
                        <p class="text-2xl font-black text-white">{{ stats.live }}</p>
                        <p class="mt-1 text-[10px] uppercase tracking-[0.15em] text-on-surface-variant">Live</p>
                      </div>

                      <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-center">
                        <p class="text-2xl font-black text-white">{{ stats.videos }}</p>
                        <p class="mt-1 text-[10px] uppercase tracking-[0.15em] text-on-surface-variant">Videos</p>
                      </div>

                      <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-center">
                        <p class="text-2xl font-black text-white">{{ stats.comments }}</p>
                        <p class="mt-1 text-[10px] uppercase tracking-[0.15em] text-on-surface-variant">Comments</p>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- Simple project section -->
              <section class="overflow-hidden rounded-[28px] border border-white/10 bg-surface-container p-6 shadow-xl sm:p-8">
                <div class="grid gap-6 lg:grid-cols-2">
                  <div>
                    <h2 class="font-headline text-2xl font-black text-white sm:text-3xl">
                      Everything around the live experience
                    </h2>

                    <p class="mt-3 text-sm leading-7 text-on-surface-variant sm:text-base">
                      The platform is built around one idea: start a stream, let people interact
                      live, then keep the content available as replay videos.
                    </p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                      <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <span class="material-symbols-outlined text-primary">sensors</span>
                        <h3 class="mt-2 text-sm font-bold text-white">Live streams</h3>
                        <p class="mt-1 text-xs leading-6 text-on-surface-variant">
                          Go live instantly.
                        </p>
                      </div>

                      <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <span class="material-symbols-outlined text-primary">forum</span>
                        <h3 class="mt-2 text-sm font-bold text-white">Comments</h3>
                        <p class="mt-1 text-xs leading-6 text-on-surface-variant">
                          Talk with viewers in real time.
                        </p>
                      </div>

                      <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <span class="material-symbols-outlined text-primary">video_library</span>
                        <h3 class="mt-2 text-sm font-bold text-white">Replays</h3>
                        <p class="mt-1 text-xs leading-6 text-on-surface-variant">
                          Save streams as videos.
                        </p>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center justify-center">
                    <div class="relative w-full max-w-md rounded-[28px] border border-white/10 bg-black/30 p-6">
                      <div class="absolute left-8 top-8 h-24 w-24 rounded-full bg-primary/15 blur-3xl"></div>
                      <div class="absolute bottom-8 right-8 h-24 w-24 rounded-full bg-purple-500/15 blur-3xl"></div>

                      <div class="relative space-y-4">
                        <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 animate-float">
                          <span class="material-symbols-outlined text-primary">live_tv</span>
                          <div>
                            <p class="text-sm font-bold text-white">Stream started</p>
                            <p class="text-xs text-on-surface-variant">Broadcast is live</p>
                          </div>
                        </div>

                        <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 animate-float-delay">
                          <span class="material-symbols-outlined text-primary">favorite</span>
                          <div>
                            <p class="text-sm font-bold text-white">Reactions received</p>
                            <p class="text-xs text-on-surface-variant">Audience is interacting</p>
                          </div>
                        </div>

                        <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 animate-float-slow">
                          <span class="material-symbols-outlined text-primary">movie</span>
                          <div>
                            <p class="text-sm font-bold text-white">Replay saved</p>
                            <p class="text-xs text-on-surface-variant">Video available after end</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- Live Streams -->
              <section class="space-y-5">
                <div class="flex items-end justify-between gap-4">
                  <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Live now</p>
                    <h2 class="mt-2 font-headline text-2xl font-black text-white">Active streams</h2>
                  </div>

                  <RouterLink
                    to="/streams"
                    class="text-sm font-bold text-primary transition hover:text-white"
                  >
                    View all
                  </RouterLink>
                </div>

                <div v-if="streamsLoading" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                  <div
                    v-for="n in 3"
                    :key="`stream-skeleton-${n}`"
                    class="overflow-hidden rounded-[24px] border border-white/10 bg-white/5 animate-pulse"
                  >
                    <div class="aspect-video bg-white/5"></div>
                    <div class="space-y-3 p-5">
                      <div class="h-4 w-2/3 rounded bg-white/10"></div>
                      <div class="h-3 w-1/2 rounded bg-white/10"></div>
                    </div>
                  </div>
                </div>

                <div v-else-if="liveStreams.length" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                  <RouterLink
                    v-for="stream in liveStreams"
                    :key="stream.id"
                    :to="`/streams/${stream.id}`"
                    class="group overflow-hidden rounded-[24px] border border-white/10 bg-surface-container shadow-lg transition hover:-translate-y-1"
                  >
                    <div class="relative aspect-video bg-zinc-950">
                      <img
                        v-if="stream.thumbnail_url || stream.thumbnail"
                        :src="getMediaUrl(stream.thumbnail_url || stream.thumbnail)"
                        :alt="stream.title"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                      />
                      <div
                        v-else
                        class="flex h-full w-full items-center justify-center bg-[linear-gradient(135deg,#111,#22132c)]"
                      >
                        <span class="material-symbols-outlined text-5xl text-primary/70">live_tv</span>
                      </div>

                      <div class="absolute left-4 top-4 rounded-full bg-red-500 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-white">
                        Live
                      </div>
                    </div>

                    <div class="p-5">
                      <h3 class="line-clamp-2 text-lg font-bold text-white">{{ stream.title }}</h3>
                      <p class="mt-2 text-sm text-on-surface-variant">
                        {{ stream.user?.name || 'Unknown user' }}
                      </p>
                    </div>
                  </RouterLink>
                </div>

                <div
                  v-else
                  class="rounded-[24px] border border-dashed border-white/10 bg-white/5 px-6 py-10 text-center text-on-surface-variant"
                >
                  No live streams right now.
                </div>
              </section>

              <!-- Videos -->
              <section class="space-y-5">
                <div class="flex items-end justify-between gap-4">
                  <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Latest videos</p>
                    <h2 class="mt-2 font-headline text-2xl font-black text-white">Replay videos</h2>
                  </div>

                  <RouterLink
                    to="/videos"
                    class="text-sm font-bold text-primary transition hover:text-white"
                  >
                    View all
                  </RouterLink>
                </div>

                <div v-if="videosLoading" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                  <div
                    v-for="n in 4"
                    :key="`video-skeleton-${n}`"
                    class="overflow-hidden rounded-[24px] border border-white/10 bg-white/5 animate-pulse"
                  >
                    <div class="aspect-video bg-white/5"></div>
                    <div class="space-y-3 p-5">
                      <div class="h-4 w-2/3 rounded bg-white/10"></div>
                      <div class="h-3 w-1/2 rounded bg-white/10"></div>
                    </div>
                  </div>
                </div>

                <div v-else-if="latestVideos.length" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                  <RouterLink
                    v-for="video in latestVideos"
                    :key="video.id"
                    :to="`/videos/${video.id}`"
                    class="group overflow-hidden rounded-[24px] border border-white/10 bg-surface-container shadow-lg transition hover:-translate-y-1"
                  >
                    <div class="relative aspect-video bg-zinc-950">
                      <img
                        v-if="video.stream?.thumbnail_url || video.stream?.thumbnail"
                        :src="getMediaUrl(video.stream?.thumbnail_url || video.stream?.thumbnail)"
                        :alt="video.title"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                      />
                      <div
                        v-else
                        class="flex h-full w-full items-center justify-center bg-[linear-gradient(135deg,#111,#22132c)]"
                      >
                        <span class="material-symbols-outlined text-5xl text-primary/70">video_library</span>
                      </div>

                      <div class="absolute bottom-3 right-3 rounded-full bg-black/70 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-white">
                        {{ formatDuration(video.duration) }}
                      </div>
                    </div>

                    <div class="p-5">
                      <h3 class="line-clamp-2 text-base font-bold text-white">{{ video.title }}</h3>
                      <p class="mt-2 text-sm text-on-surface-variant">
                        {{ video.user?.name || 'Unknown user' }}
                      </p>
                    </div>
                  </RouterLink>
                </div>

                <div
                  v-else
                  class="rounded-[24px] border border-dashed border-white/10 bg-white/5 px-6 py-10 text-center text-on-surface-variant"
                >
                  No videos available yet.
                </div>
              </section>
            </div>
          </main>

          <AppFooter />
        </div>
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
import AppFooter from '@/components/layout/Footer.vue'

const sidebarCollapsed = ref(false)
const mobileSidebarOpen = ref(false)

const streamsLoading = ref(false)
const videosLoading = ref(false)

const streams = ref([])
const videos = ref([])

const stats = computed(() => {
  const live = streams.value.filter((stream) => String(stream?.status).toLowerCase() === 'live').length
  const comments =
    streams.value.reduce((sum, stream) => sum + Number(stream?.comments_count || 0), 0) +
    videos.value.reduce((sum, video) => sum + Number(video?.comments_count || 0), 0)

  return {
    live,
    videos: videos.value.length,
    comments,
  }
})

const liveStreams = computed(() => {
  return streams.value.filter((stream) => String(stream?.status).toLowerCase() === 'live').slice(0, 3)
})

const latestVideos = computed(() => {
  return videos.value.slice(0, 4)
})

const featuredStream = computed(() => {
  return liveStreams.value[0] || streams.value[0] || null
})

const normalizeCollection = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.data)) return payload.data.data
  return []
}

const getMediaUrl = (path) => {
  if (!path) return null
  if (typeof path === 'string' && path.startsWith('http')) return path
  return `http://localhost:8000/storage/${String(path).replace(/^\/+/, '')}`
}

const getInitials = (name = 'User') => {
  return String(name)
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase()
}

const formatDuration = (seconds) => {
  const total = Number(seconds || 0)

  if (!total) return '0:00'

  const hrs = Math.floor(total / 3600)
  const mins = Math.floor((total % 3600) / 60)
  const secs = Math.floor(total % 60)

  if (hrs > 0) {
    return `${hrs}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
  }

  return `${mins}:${String(secs).padStart(2, '0')}`
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

const loadStreams = async () => {
  streamsLoading.value = true

  try {
    const response = await api.get('/stream/streams')
    streams.value = normalizeCollection(response.data?.data || response.data)
  } catch (error) {
    console.error('Failed to load streams', error)
    streams.value = []
  } finally {
    streamsLoading.value = false
  }
}

const loadVideos = async () => {
  videosLoading.value = true

  try {
    const response = await api.get('/video/videos')
    videos.value = normalizeCollection(response.data?.data || response.data)
  } catch (error) {
    console.error('Failed to load videos', error)
    videos.value = []
  } finally {
    videosLoading.value = false
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
  await Promise.all([loadStreams(), loadVideos()])
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
  document.body.style.overflow = ''
})
</script>

<style scoped>
.animate-float {
  animation: floatY 3s ease-in-out infinite;
}

.animate-float-delay {
  animation: floatY 3.6s ease-in-out infinite;
  animation-delay: 0.4s;
}

.animate-float-slow {
  animation: floatY 4.2s ease-in-out infinite;
  animation-delay: 0.8s;
}

@keyframes floatY {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-8px);
  }
}
</style>
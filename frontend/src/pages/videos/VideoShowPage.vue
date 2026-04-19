<template>
  <div class="dark">
    <div class="min-h-screen bg-background font-body text-on-surface selection:bg-primary/30">
      <TopNavbar @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed" />

      <div class="flex min-h-screen">
        <AppSidebar :collapsed="sidebarCollapsed" />

        <main
          :class="[
            'flex min-h-screen flex-1 flex-col pt-0 transition-all duration-300',
            sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'
          ]"
        >
          <div class="flex min-h-screen flex-col lg:flex-row">
            <!-- Left content -->
            <section class="flex flex-1 flex-col gap-6 p-4 lg:p-6">
              <!-- Player -->
              <div
                v-if="loading"
                class="relative aspect-video w-full animate-pulse overflow-hidden rounded-lg bg-surface-container-lowest"
              ></div>

              <div
                v-else-if="video"
                class="group relative w-full overflow-hidden rounded-lg bg-surface-container-lowest"
              >
                <div class="aspect-video w-full bg-black">
                  <video
                    v-if="video.url"
                    :src="video.url"
                    controls
                    class="h-full w-full object-cover"
                    preload="metadata"
                  ></video>

                  <div
                    v-else
                    class="flex h-full w-full items-center justify-center bg-gradient-to-br from-black via-zinc-950 to-black"
                  >
                    <span class="font-headline text-lg font-bold tracking-widest text-zinc-300">
                      VIDEO NOT AVAILABLE
                    </span>
                  </div>

                  <div class="absolute left-6 top-6 flex gap-3">
                    <div
                      class="flex items-center gap-2 rounded-full bg-surface-container-high px-3 py-1 text-xs font-bold text-on-surface-variant"
                    >
                      <span class="h-2 w-2 rounded-full bg-zinc-500"></span>
                      REPLAY
                    </div>

                    <div
                      v-if="video.stream?.status"
                      class="flex items-center gap-2 rounded-full bg-black/60 px-3 py-1 text-xs font-bold text-white backdrop-blur-md"
                    >
                      <span class="material-symbols-outlined text-sm">live_tv</span>
                      {{ video.stream.status.toUpperCase() }}
                    </div>
                  </div>

                  <div
                    v-if="video.duration"
                    class="absolute bottom-6 right-6 rounded-full bg-black/60 px-4 py-2 text-xs font-bold text-white backdrop-blur-md"
                  >
                    {{ formatDuration(video.duration) }}
                  </div>
                </div>
              </div>

              <!-- Title / desc -->
              <div v-if="video" class="space-y-4">
                <h1 class="font-headline text-2xl font-black tracking-tight text-on-surface md:text-3xl">
                  {{ video.title }}
                </h1>

                <div class="flex items-center gap-3">
                  <button
                    type="button"
                    class="text-sm font-bold text-primary transition hover:text-primary-dim"
                    @click="showDescription = !showDescription"
                  >
                    {{ showDescription ? 'See less' : 'See more' }}
                  </button>
                </div>

                <p
                  v-if="showDescription"
                  class="max-w-4xl text-sm leading-relaxed text-on-surface-variant md:text-base"
                >
                  {{ video.description || 'No description available.' }}
                </p>

                <div class="flex flex-wrap gap-2 pt-1">
                  <span
                    v-for="category in video.categories || []"
                    :key="category.id"
                    class="rounded-full bg-surface-container-highest px-3 py-1 text-xs font-bold uppercase tracking-wider text-on-surface-variant"
                  >
                    {{ category.name }}
                  </span>
                </div>
              </div>

              <!-- Creator / follow / reactions -->
              <div
                v-if="video"
                class="relative flex flex-col justify-between gap-6 rounded-3xl border border-white/5 bg-surface-container p-6 lg:flex-row lg:items-center"
              >
                <div class="flex items-center gap-4">
                  <RouterLink
                    :to="`/profile/${video.user?.id}`"
                    class="block"
                  >
                    <img
                      :src="getAvatar(video.user?.avatar, video.user?.name)"
                      :alt="video.user?.name || 'Creator avatar'"
                      class="h-16 w-16 rounded-2xl object-cover transition hover:opacity-90"
                    />
                  </RouterLink>

                  <div>
                    <div class="flex flex-wrap items-center gap-3">
                      <RouterLink
                        :to="`/profile/${video.user?.id}`"
                        class="font-headline text-lg font-bold transition hover:text-primary"
                      >
                        {{ video.user?.name || 'Unknown creator' }}
                      </RouterLink>

                      <button
                        v-if="!isOwnVideo"
                        type="button"
                        class="rounded-full px-5 py-2 text-sm font-bold transition-all active:scale-95 disabled:opacity-60"
                        :class="isFollowing
                          ? 'border border-white/10 bg-surface-container-high text-white hover:bg-surface-bright'
                          : 'bg-primary text-on-primary-fixed hover:bg-primary-dim'"
                        :disabled="followLoading"
                        @click="toggleFollow"
                      >
                        {{ followLoading ? 'Loading...' : (isFollowing ? 'Following' : 'Follow') }}
                      </button>

                      <RouterLink
                        v-if="!isOwnVideo"
                        :to="`/messages?user=${video.user?.id}`"
                        class="rounded-full border border-white/10 bg-surface-container-high px-5 py-2 text-sm font-bold text-white transition hover:bg-surface-bright"
                      >
                        Message
                      </RouterLink>
                    </div>

                    <p class="text-sm text-on-surface-variant">
                      {{ video.user?.email || 'No email available' }}
                    </p>
                  </div>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                  <div class="flex items-center gap-2 rounded-full bg-gradient-to-r from-primary to-secondary px-6 py-3 font-bold text-on-primary-fixed">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">
                      favorite
                    </span>
                    <span>Reactions</span>
                  </div>

                  <div class="flex items-center gap-2 rounded-full bg-surface-container-high px-5 py-3 text-sm font-bold text-on-surface-variant">
                    <span class="material-symbols-outlined text-primary">favorite</span>
                    <span>{{ formatCompact(totalReactions) }}</span>
                  </div>
                </div>
              </div>

              <!-- Reactions summary -->
              <div
                v-if="video"
                class="rounded-3xl border border-white/5 bg-surface-container p-6"
              >
                <div class="mb-4 flex items-center justify-between">
                  <h2 class="font-headline text-xl font-black tracking-tight">
                    Reactions Summary
                  </h2>
                  <span class="text-sm font-bold text-on-surface-variant">
                    {{ formatCompact(totalReactions) }} total
                  </span>
                </div>

                <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                  <div
                    v-for="item in reactionOptions"
                    :key="item.type"
                    class="rounded-2xl bg-surface-container-high p-4 text-center"
                  >
                    <div class="text-3xl">{{ item.emoji }}</div>
                    <div class="mt-2 text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                      {{ item.type }}
                    </div>
                    <div class="mt-1 text-sm font-black text-white">
                      {{ formatCompact(reactionsSummary[item.type]) }}
                    </div>
                  </div>
                </div>
              </div>

              <div
                v-if="!loading && !video"
                class="rounded-3xl bg-surface-container-low p-12 text-center text-on-surface-variant"
              >
                Video not found.
              </div>
            </section>

            <!-- Right comments -->
            <aside
              class="sticky top-20 flex h-[calc(100vh-5rem)] w-full flex-col border-l border-white/5 bg-surface-container-low/50 backdrop-blur-xl lg:w-[420px]"
            >
              <div class="flex items-center justify-between border-b border-white/5 p-6">
                <h2 class="font-headline text-xl font-black tracking-tight">
                  COMMENTS
                </h2>

                <div class="rounded-full bg-surface-container-high px-4 py-2 text-xs font-bold text-on-surface-variant">
                  {{ video?.comments_count || 0 }}
                </div>
              </div>

              <div class="hide-scrollbar flex-1 space-y-6 overflow-y-auto p-6">
                <div
                  v-if="loading"
                  class="space-y-4"
                >
                  <div
                    v-for="n in 5"
                    :key="n"
                    class="flex gap-4 animate-pulse"
                  >
                    <div class="h-10 w-10 rounded-lg bg-surface-container-high"></div>
                    <div class="flex-1 space-y-2">
                      <div class="h-3 w-1/3 rounded bg-surface-container-high"></div>
                      <div class="h-3 w-full rounded bg-surface-container-high"></div>
                      <div class="h-3 w-2/3 rounded bg-surface-container-high"></div>
                    </div>
                  </div>
                </div>

                <div
                  v-else-if="displayedComments.length === 0"
                  class="rounded-lg bg-surface-container p-6 text-center text-on-surface-variant"
                >
                  No comments yet.
                </div>

                <div
                  v-else
                  v-for="comment in displayedComments"
                  :key="comment.id"
                  class="group flex gap-4"
                >
                  <RouterLink
                    :to="`/profile/${comment.user?.id}`"
                    class="block"
                  >
                    <img
                      :src="getAvatar(comment.user?.avatar, comment.user?.name)"
                      :alt="comment.user?.name || 'Comment user'"
                      class="h-10 w-10 rounded-lg object-cover"
                    />
                  </RouterLink>

                  <div class="flex-1 space-y-1">
                    <div class="flex items-center justify-between">
                      <RouterLink
                        :to="`/profile/${comment.user?.id}`"
                        class="text-sm font-bold text-secondary transition hover:text-primary"
                      >
                        {{ comment.user?.name || 'Unknown user' }}
                      </RouterLink>
                      <span class="text-[10px] font-bold uppercase text-on-surface-variant">
                        {{ formatCommentTime(comment.created_at) }}
                      </span>
                    </div>

                    <p class="text-sm leading-relaxed text-on-surface-variant">
                      {{ comment.content }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="border-t border-white/5 bg-surface-container p-6">
                <div class="rounded-2xl bg-surface-container-low p-4 text-sm text-on-surface-variant">
                  Comments are displayed from the current video data.
                </div>
              </div>
            </aside>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import api from '@/services/api'
import TopNavbar from '@/components/layout/TopNavbar.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'

const route = useRoute()

const sidebarCollapsed = ref(false)
const loading = ref(false)
const followLoading = ref(false)

const video = ref(null)
const showDescription = ref(false)
const isFollowing = ref(false)
const authUser = ref(null)

const reactionOptions = [
  { type: 'like', emoji: '👍' },
  { type: 'love', emoji: '❤️' },
  { type: 'haha', emoji: '😂' },
  { type: 'wow', emoji: '😮' },
  { type: 'sad', emoji: '😢' },
  { type: 'angry', emoji: '😡' },
  { type: 'fire', emoji: '🔥' },
  { type: 'clap', emoji: '👏' },
]

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

const getStoredUser = () => {
  try {
    return JSON.parse(localStorage.getItem('user') || 'null')
  } catch {
    return null
  }
}

const getAvatar = (avatar, name = 'User') => {
  if (avatar) return buildStorageUrl(avatar)
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=111111&color=ffffff&size=256`
}

const isOwnVideo = computed(() => {
  return Number(authUser.value?.id) === Number(video.value?.user?.id)
})

const reactionsSummary = computed(() => {
  const summary = video.value?.stream?.reactions_summary || {}
  return {
    like: Number(summary.like || 0),
    love: Number(summary.love || 0),
    haha: Number(summary.haha || 0),
    wow: Number(summary.wow || 0),
    sad: Number(summary.sad || 0),
    angry: Number(summary.angry || 0),
    clap: Number(summary.clap || 0),
    fire: Number(summary.fire || 0),
  }
})

const totalReactions = computed(() => {
  return Number(video.value?.stream?.reactions_count || 0)
})

const displayedComments = computed(() => {
  const list = Array.isArray(video.value?.comments) ? [...video.value.comments] : []
  return list.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0))
})

const formatCompact = (value) => {
  const number = Number(value || 0)
  if (number >= 1000000) return `${(number / 1000000).toFixed(1)}M`
  if (number >= 1000) return `${(number / 1000).toFixed(1)}k`
  return `${number}`
}

const formatCommentTime = (date) => {
  if (!date) return '--:--'
  const d = new Date(date)
  return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
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

const loadVideo = async () => {
  loading.value = true
  try {
    const response = await api.get(`/video/videos/${route.params.id}`)
    video.value = response.data?.data || null
  } catch (error) {
    console.error('Failed to load video', error)
    video.value = null
  } finally {
    loading.value = false
  }
}

const loadFollowingState = async () => {
  if (!authUser.value?.id || !video.value?.user?.id || isOwnVideo.value) {
    isFollowing.value = false
    return
  }

  try {
    const response = await api.get(`/subscrip/users/${authUser.value.id}/following`)
    const list = normalizeCollection(response.data)
    isFollowing.value = list.some((item) => Number(item.id) === Number(video.value.user.id))
  } catch (error) {
    isFollowing.value = false
    console.error('Failed to load following state', error)
  }
}

const toggleFollow = async () => {
  if (!video.value?.user?.id || followLoading.value || isOwnVideo.value) return

  followLoading.value = true
  try {
    if (isFollowing.value) {
      await api.delete(`/subscrip/subscriptions/${video.value.user.id}/unfollow`)
      isFollowing.value = false
    } else {
      await api.post('/subscrip/subscriptions/follow', {
        streamer_id: video.value.user.id,
      })
      isFollowing.value = true
    }
  } catch (error) {
    console.error('Failed to toggle follow', error)
  } finally {
    followLoading.value = false
  }
}

onMounted(async () => {
  authUser.value = getStoredUser()
  await loadVideo()
  await loadFollowingState()
})
</script>

<style scoped>
.material-symbols-outlined {
  font-variation-settings:
    'FILL' 0,
    'wght' 400,
    'GRAD' 0,
    'opsz' 24;
  display: inline-block;
  line-height: 1;
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
}

.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
<template>
  <div class="dark">
    <div class="min-h-screen bg-background font-body text-on-surface selection:bg-primary/30">
      <TopNavbar @toggle-sidebar="handleSidebarToggle" />

      <div class="flex min-h-screen">
        <AppSidebar
          :collapsed="sidebarCollapsed"
          :mobile-open="mobileSidebarOpen"
          @close="mobileSidebarOpen = false"
        />

        <main
          :class="[
            'min-w-0 flex min-h-screen flex-1 flex-col pt-0 transition-all duration-300',
            sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'
          ]"
        >
          <div class="flex min-h-screen flex-col xl:flex-row">
            <section class="flex min-w-0 flex-1 flex-col gap-6 p-4 sm:p-5 lg:p-6">
              <div
                v-if="loading"
                class="relative aspect-video w-full animate-pulse overflow-hidden rounded-lg bg-surface-container-lowest"
              ></div>

              <div
                v-else-if="stream"
                class="group relative w-full overflow-hidden rounded-lg bg-surface-container-lowest"
              >
                <div class="aspect-video w-full">
                  <div
                    class="relative flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-black via-zinc-950 to-black"
                  >
                    <div
                      class="absolute inset-0 bg-cover bg-center opacity-25 blur-sm"
                      :style="playerBackgroundStyle"
                    ></div>

                    <div class="relative z-10 flex flex-col items-center gap-4 px-4 text-center">
                      <div
                        v-if="stream.status === 'live'"
                        class="h-16 w-16 animate-spin rounded-full border-4 border-primary/20 border-t-primary"
                      ></div>

                      <span
                        class="font-headline text-base font-bold tracking-widest sm:text-lg"
                        :class="stream.status === 'live' ? 'text-primary' : 'text-zinc-300'"
                      >
                        {{ stream.status === 'live' ? 'CONNECTING...' : 'STREAM ENDED' }}
                      </span>
                    </div>
                  </div>

                  <div class="absolute left-3 top-3 flex flex-wrap gap-2 sm:left-6 sm:top-6 sm:gap-3">
                    <div
                      class="flex items-center gap-2 rounded-full px-3 py-1 text-[10px] font-bold sm:text-xs"
                      :class="stream.status === 'live'
                        ? 'animate-pulse bg-tertiary-container text-on-tertiary-container'
                        : 'bg-surface-container-high text-on-surface-variant'"
                    >
                      <span
                        class="h-2 w-2 rounded-full"
                        :class="stream.status === 'live' ? 'bg-on-tertiary-container' : 'bg-zinc-500'"
                      ></span>
                      {{ stream.status.toUpperCase() }}
                    </div>

                    <div class="flex items-center gap-2 rounded-full bg-black/60 px-3 py-1 text-[10px] font-bold text-white backdrop-blur-md sm:text-xs">
                      <span class="material-symbols-outlined text-sm">visibility</span>
                      {{ formatViewers(stream.current_viewers) }}
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="stream" class="space-y-4">
                <h1 class="font-headline text-2xl font-black tracking-tight text-on-surface sm:text-3xl">
                  {{ stream.title }}
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
                  {{ stream.description || 'No description available.' }}
                </p>

                <div class="flex flex-wrap gap-2 pt-1">
                  <span
                    v-for="category in stream.categories || []"
                    :key="category.id"
                    class="rounded-full bg-surface-container-highest px-3 py-1 text-xs font-bold uppercase tracking-wider text-on-surface-variant"
                  >
                    {{ category.name }}
                  </span>
                </div>
              </div>

              <div
                v-if="stream"
                class="relative flex flex-col justify-between gap-6 rounded-3xl border border-white/5 bg-surface-container p-4 sm:p-6 lg:flex-row lg:items-center"
              >
                <div class="flex min-w-0 items-center gap-4">
                  <RouterLink
                    :to="`/profile/${stream.user?.id}`"
                    class="block shrink-0"
                  >
                    <img
                      :src="getAvatar(stream.user?.avatar, stream.user?.name)"
                      :alt="stream.user?.name || 'Streamer avatar'"
                      class="h-14 w-14 rounded-2xl object-cover transition hover:opacity-90 sm:h-16 sm:w-16"
                    />
                  </RouterLink>

                  <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                      <RouterLink
                        :to="`/profile/${stream.user?.id}`"
                        class="truncate font-headline text-base font-bold transition hover:text-primary sm:text-lg"
                      >
                        {{ stream.user?.name || 'Unknown streamer' }}
                      </RouterLink>

                      <button
                        v-if="!isOwnStream"
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
                    </div>

                    <p class="truncate text-sm text-on-surface-variant">
                      {{ stream.user?.email || 'No email available' }}
                    </p>
                  </div>
                </div>

                <div class="relative flex flex-wrap items-center gap-3 sm:gap-4">
                  <button
                    type="button"
                    class="flex items-center gap-2 rounded-full bg-gradient-to-r from-primary to-secondary px-5 py-3 font-bold text-on-primary-fixed transition-all hover:shadow-[0_0_20px_rgba(246,128,255,0.4)] active:scale-95 sm:px-6"
                    @click="toggleReactionsMenu"
                  >
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">
                      favorite
                    </span>
                    <span>React</span>
                  </button>

                  <div class="flex items-center gap-2 rounded-full bg-surface-container-high px-4 py-3 text-sm font-bold text-on-surface-variant sm:px-5">
                    <span class="material-symbols-outlined text-primary">favorite</span>
                    <span>{{ formatCompact(totalReactions) }}</span>
                  </div>

                  <div
                    v-if="showReactionsMenu"
                    class="absolute bottom-full right-0 z-30 mb-3 max-w-[calc(100vw-2rem)] rounded-3xl border border-white/10 bg-surface-container p-3 shadow-[0_20px_50px_rgba(0,0,0,0.45)]"
                  >
                    <div class="flex flex-wrap items-center gap-2">
                      <button
                        v-for="item in reactionOptions"
                        :key="item.type"
                        type="button"
                        class="group flex min-w-[68px] flex-col items-center rounded-2xl px-3 py-3 transition hover:bg-surface-container-high active:scale-95 disabled:opacity-60"
                        :disabled="reactionLoading"
                        @click="submitReaction(item.type)"
                      >
                        <span class="text-3xl transition-transform group-hover:scale-125">
                          {{ item.emoji }}
                        </span>
                        <span class="mt-2 text-xs font-bold text-on-surface-variant">
                          {{ formatCompact(reactionsSummary[item.type]) }}
                        </span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div
                v-if="!loading && !stream"
                class="rounded-3xl bg-surface-container-low p-8 text-center text-on-surface-variant sm:p-12"
              >
                Stream not found.
              </div>
            </section>

            <aside
              v-if="showChat"
              :class="[
                'border-white/5 bg-surface-container-low/50 backdrop-blur-xl',
                'xl:sticky xl:top-20 xl:h-[calc(100vh-5rem)] xl:w-[420px] xl:border-l',
                'fixed inset-x-0 bottom-0 top-[72px] z-40 flex flex-col border-t'
              ]"
            >
              <div class="flex items-center justify-between border-b border-white/5 p-4 sm:p-6">
                <h2 class="font-headline text-lg font-black tracking-tight sm:text-xl">
                  LIVE CHAT
                </h2>

                <div class="flex gap-2">
                  <button
                    class="rounded-lg p-2 transition-colors hover:bg-surface-bright"
                    @click="showChat = false"
                  >
                    <span class="material-symbols-outlined text-on-surface-variant">close</span>
                  </button>
                </div>
              </div>

              <div class="hide-scrollbar flex-1 space-y-6 overflow-y-auto p-4 sm:p-6">
                <div
                  v-if="displayedComments.length === 0"
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
                    class="block shrink-0"
                  >
                    <img
                      :src="getAvatar(comment.user?.avatar, comment.user?.name)"
                      :alt="comment.user?.name || 'Comment user'"
                      class="h-10 w-10 rounded-lg object-cover"
                    />
                  </RouterLink>

                  <div class="min-w-0 flex-1 space-y-1">
                    <div class="flex items-center justify-between gap-3">
                      <RouterLink
                        :to="`/profile/${comment.user?.id}`"
                        class="truncate text-sm font-bold text-secondary transition hover:text-primary"
                      >
                        {{ comment.user?.name || 'Unknown user' }}
                      </RouterLink>
                      <span class="shrink-0 text-[10px] font-bold uppercase text-on-surface-variant">
                        {{ formatCommentTime(comment.created_at) }}
                      </span>
                    </div>

                    <p class="break-words text-sm leading-relaxed text-on-surface-variant">
                      {{ comment.content }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="space-y-4 border-t border-white/5 bg-surface-container p-4 sm:p-6">
                <p v-if="commentError" class="text-sm text-error">
                  {{ commentError }}
                </p>

                <div class="relative flex items-center">
                  <input
                    v-model="commentForm.content"
                    type="text"
                    placeholder="Send a message..."
                    class="w-full rounded-xl border-none bg-surface-container-low py-4 pl-4 pr-14 text-sm transition-all ring-primary/30 focus:ring-2"
                    @keydown.enter.prevent="submitComment"
                  />

                  <button
                    class="absolute right-2 rounded-lg bg-primary p-2 text-on-primary shadow-lg shadow-primary/20 transition-transform hover:scale-105 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="commentLoading || !commentForm.content.trim()"
                    @click="submitComment"
                  >
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">
                      send
                    </span>
                  </button>
                </div>
              </div>
            </aside>
          </div>
        </main>

        <button
          v-if="!showChat"
          type="button"
          class="fixed bottom-6 right-4 z-40 rounded-full bg-gradient-to-r from-primary to-secondary px-5 py-3 text-sm font-bold text-on-primary-fixed shadow-[0_10px_30px_rgba(246,128,255,0.35)] transition hover:scale-105 active:scale-95 sm:bottom-8 sm:right-8 sm:px-6 sm:py-4 sm:text-base"
          @click="showChat = true"
        >
          Open Chat
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import api from '@/services/api'
import TopNavbar from '@/components/layout/TopNavbar.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'

const route = useRoute()

const sidebarCollapsed = ref(false)
const mobileSidebarOpen = ref(false)
const isMobileView = ref(false)

const loading = ref(false)
const reactionLoading = ref(false)
const commentLoading = ref(false)
const followLoading = ref(false)

const stream = ref(null)
const showReactionsMenu = ref(false)
const showDescription = ref(false)
const showChat = ref(false)
const commentError = ref('')
const isFollowing = ref(false)
const authUser = ref(null)

const commentForm = ref({
  content: '',
})

const reactionBoosts = reactive({
  like: 0,
  love: 0,
  haha: 0,
  wow: 0,
  sad: 0,
  angry: 0,
  clap: 0,
  fire: 0,
})

const totalReactionBoost = ref(0)

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

const buildStorageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http')) return path
  return `http://localhost:8000/storage/${path}`
}

const normalizeCollection = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.data)) return payload.data.data
  return []
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

const isOwnStream = computed(() => {
  return Number(authUser.value?.id) === Number(stream.value?.user?.id)
})

const playerBackgroundStyle = computed(() => {
  return {
    backgroundImage:
      "url('https://images.unsplash.com/photo-1511512578047-dfb367046420?q=80&w=1600&auto=format&fit=crop')",
  }
})

const baseReactionsSummary = computed(() => {
  const summary = stream.value?.reactions_summary || {}
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

const reactionsSummary = computed(() => {
  return {
    like: baseReactionsSummary.value.like + reactionBoosts.like,
    love: baseReactionsSummary.value.love + reactionBoosts.love,
    haha: baseReactionsSummary.value.haha + reactionBoosts.haha,
    wow: baseReactionsSummary.value.wow + reactionBoosts.wow,
    sad: baseReactionsSummary.value.sad + reactionBoosts.sad,
    angry: baseReactionsSummary.value.angry + reactionBoosts.angry,
    clap: baseReactionsSummary.value.clap + reactionBoosts.clap,
    fire: baseReactionsSummary.value.fire + reactionBoosts.fire,
  }
})

const totalReactions = computed(() => {
  const base = Number(stream.value?.reactions_count || 0)
  return base + totalReactionBoost.value
})

const displayedComments = computed(() => {
  const list = Array.isArray(stream.value?.comments) ? [...stream.value.comments] : []
  return list.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0))
})

const formatCompact = (value) => {
  const number = Number(value || 0)
  if (number >= 1000000) return `${(number / 1000000).toFixed(1)}M`
  if (number >= 1000) return `${(number / 1000).toFixed(1)}k`
  return `${number}`
}

const formatViewers = (value) => formatCompact(value || 0)

const formatCommentTime = (date) => {
  if (!date) return '--:--'
  const d = new Date(date)
  return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const handleSidebarToggle = () => {
  if (window.innerWidth < 768) {
    mobileSidebarOpen.value = !mobileSidebarOpen.value
    return
  }

  sidebarCollapsed.value = !sidebarCollapsed.value
}

const handleResize = () => {
  isMobileView.value = window.innerWidth < 1280

  if (window.innerWidth >= 768) {
    mobileSidebarOpen.value = false
  }

  if (window.innerWidth >= 1280) {
    showChat.value = true
  }
}

watch([mobileSidebarOpen, showChat, isMobileView], ([sidebarOpen, chatOpen, mobile]) => {
  const lockScroll = sidebarOpen || (mobile && chatOpen)
  document.body.style.overflow = lockScroll ? 'hidden' : ''
})

const toggleReactionsMenu = () => {
  showReactionsMenu.value = !showReactionsMenu.value
}

const loadStream = async () => {
  loading.value = true
  try {
    const response = await api.get(`/stream/streams/${route.params.id}`)
    stream.value = response.data?.data || null

    reactionBoosts.like = 0
    reactionBoosts.love = 0
    reactionBoosts.haha = 0
    reactionBoosts.wow = 0
    reactionBoosts.sad = 0
    reactionBoosts.angry = 0
    reactionBoosts.clap = 0
    reactionBoosts.fire = 0
    totalReactionBoost.value = 0
  } catch (error) {
    console.error('Failed to load stream', error)
    stream.value = null
  } finally {
    loading.value = false
  }
}

const loadFollowingState = async () => {
  if (!authUser.value?.id || !stream.value?.user?.id || isOwnStream.value) {
    isFollowing.value = false
    return
  }

  try {
    const response = await api.get(`/subscrip/users/${authUser.value.id}/following`)
    const list = normalizeCollection(response.data)
    isFollowing.value = list.some((item) => Number(item.id) === Number(stream.value.user.id))
  } catch (error) {
    isFollowing.value = false
    console.error('Failed to load following state', error)
  }
}

const toggleFollow = async () => {
  if (!stream.value?.user?.id || followLoading.value || isOwnStream.value) return

  followLoading.value = true
  try {
    if (isFollowing.value) {
      await api.delete(`/subscrip/subscriptions/${stream.value.user.id}/unfollow`)
      isFollowing.value = false
    } else {
      await api.post('/subscrip/subscriptions/follow', {
        streamer_id: stream.value.user.id,
      })
      isFollowing.value = true
    }
  } catch (error) {
    console.error('Failed to toggle follow', error)
  } finally {
    followLoading.value = false
  }
}

const submitReaction = async (type) => {
  if (!stream.value || stream.value.status !== 'live') return

  reactionBoosts[type] += 1
  totalReactionBoost.value += 1

  try {
    await api.post('/reaction/reactions', {
      stream_id: stream.value.id,
      type,
    })
  } catch (error) {
    reactionBoosts[type] -= 1
    totalReactionBoost.value -= 1
    console.error('Failed to submit reaction', error)
  }
}

const submitComment = async () => {
  const content = commentForm.value.content.trim()
  if (!stream.value || !content || commentLoading.value) return

  commentLoading.value = true
  commentError.value = ''

  try {
    const response = await api.post('/comments', {
      stream_id: stream.value.id,
      content,
    })

    const newComment = response.data?.data

    if (newComment) {
      stream.value = {
        ...stream.value,
        comments: [newComment, ...(Array.isArray(stream.value.comments) ? stream.value.comments : [])],
        comments_count: Number(stream.value.comments_count || 0) + 1,
      }
    }

    commentForm.value.content = ''
  } catch (error) {
    commentError.value = error.response?.data?.message || 'Failed to send comment.'
  } finally {
    commentLoading.value = false
  }
}

onMounted(async () => {
  authUser.value = getStoredUser()
  handleResize()
  window.addEventListener('resize', handleResize)

  if (window.innerWidth < 1280) {
    showChat.value = false
  }

  await loadStream()
  await loadFollowingState()
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
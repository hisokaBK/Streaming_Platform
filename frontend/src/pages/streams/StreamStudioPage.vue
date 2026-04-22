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
            'min-w-0 flex min-h-screen flex-1 flex-col pt-0 transition-all duration-300 pt-[72px]',
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
                class="group relative overflow-hidden rounded-lg bg-surface-container-lowest"
              >
                <div class="relative aspect-video w-full bg-black">
                  <video
                    ref="localVideoEl"
                    autoplay
                    playsinline
                    muted
                    class="absolute inset-0 h-full w-full object-cover"
                  ></video>

                  <div
                    v-if="!connected"
                    class="absolute inset-0"
                  >
                    <div class="relative flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-black via-zinc-950 to-black">
                      <div
                        class="absolute inset-0 bg-cover bg-center opacity-25 blur-sm"
                        :style="playerBackgroundStyle"
                      ></div>

                      <div class="relative z-10 flex flex-col items-center gap-4 px-4 text-center">
                        <div
                          v-if="connecting"
                          class="h-16 w-16 animate-spin rounded-full border-4 border-primary/20 border-t-primary"
                        ></div>

                        <span class="font-headline text-base font-bold tracking-widest text-primary sm:text-lg">
                          {{ connecting ? 'CONNECTING...' : 'STUDIO OFFLINE' }}
                        </span>

                        <p
                          v-if="errorMessage"
                          class="max-w-md text-sm text-red-300"
                        >
                          {{ errorMessage }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <div class="absolute left-3 top-3 flex flex-wrap gap-2 sm:left-6 sm:top-6 sm:gap-3">
                    <div
                      class="flex items-center gap-2 rounded-full px-3 py-1 text-[10px] font-bold sm:text-xs"
                      :class="connected
                        ? 'animate-pulse bg-tertiary-container text-on-tertiary-container'
                        : 'bg-surface-container-high text-on-surface-variant'"
                    >
                      <span
                        class="h-2 w-2 rounded-full"
                        :class="connected ? 'bg-on-tertiary-container' : 'bg-zinc-500'"
                      ></span>
                      {{ connected ? 'LIVE' : 'OFFLINE' }}
                    </div>

                    <div class="flex items-center gap-2 rounded-full bg-black/60 px-3 py-1 text-[10px] font-bold text-white backdrop-blur-md sm:text-xs">
                      <span class="material-symbols-outlined text-sm">visibility</span>
                      {{ formatCompact(viewersCount) }} VIEWERS
                    </div>

                    <div
                      v-if="screenShareEnabled"
                      class="rounded-full bg-primary/80 px-3 py-1 text-[10px] font-bold text-white backdrop-blur-md sm:text-xs"
                    >
                      SCREEN SHARE
                    </div>
                  </div>
                </div>
              </div>

              <div
                v-if="stream"
                class="rounded-3xl border border-white/5 bg-surface-container p-4 sm:p-6"
              >
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                  <div class="space-y-3">
                    <h1 class="font-headline text-2xl font-black tracking-tight text-on-surface sm:text-3xl">
                      {{ stream.title }}
                    </h1>

                    <div class="flex flex-wrap gap-2">
                      <span
                        v-for="category in stream.categories || []"
                        :key="category.id"
                        class="rounded-full bg-surface-container-highest px-3 py-1 text-xs font-bold uppercase tracking-wider text-on-surface-variant"
                      >
                        {{ category.name }}
                      </span>
                    </div>

                    <button
                      type="button"
                      class="text-sm font-bold text-primary transition hover:text-primary-dim"
                      @click="showDescription = !showDescription"
                    >
                      {{ showDescription ? 'See less' : 'See more' }}
                    </button>

                    <p
                      v-if="showDescription"
                      class="max-w-4xl text-sm leading-relaxed text-on-surface-variant md:text-base"
                    >
                      {{ stream.description || 'No description available.' }}
                    </p>
                  </div>

                  <div class="flex flex-wrap gap-3">
                    <button
                      type="button"
                      class="group relative flex h-14 w-14 items-center justify-center rounded-full text-sm font-bold transition-all"
                      :class="cameraEnabled
                        ? 'bg-primary text-on-primary-fixed shadow-[0_0_20px_rgba(246,128,255,0.25)]'
                        : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-bright'"
                      :title="cameraEnabled ? 'Camera On' : 'Camera Off'"
                      :aria-label="cameraEnabled ? 'Camera On' : 'Camera Off'"
                      @click="toggleCamera"
                    >
                      <span class="material-symbols-outlined text-[26px]">
                        {{ cameraEnabled ? 'videocam' : 'videocam_off' }}
                      </span>

                      <span class="pointer-events-none absolute -top-11 left-1/2 z-20 -translate-x-1/2 whitespace-nowrap rounded-full bg-black/85 px-3 py-1 text-xs font-bold text-white opacity-0 transition-all duration-200 group-hover:-top-12 group-hover:opacity-100">
                        {{ cameraEnabled ? 'Camera On' : 'Camera Off' }}
                      </span>
                    </button>

                    <button
                      type="button"
                      class="group relative flex h-14 w-14 items-center justify-center rounded-full text-sm font-bold transition-all"
                      :class="microphoneEnabled
                        ? 'bg-secondary text-on-primary-fixed shadow-[0_0_20px_rgba(167,139,250,0.25)]'
                        : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-bright'"
                      :title="microphoneEnabled ? 'Mic On' : 'Mic Off'"
                      :aria-label="microphoneEnabled ? 'Mic On' : 'Mic Off'"
                      @click="toggleMicrophone"
                    >
                      <span class="material-symbols-outlined text-[26px]">
                        {{ microphoneEnabled ? 'mic' : 'mic_off' }}
                      </span>

                      <span class="pointer-events-none absolute -top-11 left-1/2 z-20 -translate-x-1/2 whitespace-nowrap rounded-full bg-black/85 px-3 py-1 text-xs font-bold text-white opacity-0 transition-all duration-200 group-hover:-top-12 group-hover:opacity-100">
                        {{ microphoneEnabled ? 'Mic On' : 'Mic Off' }}
                      </span>
                    </button>

                    <button
                      type="button"
                      class="group relative flex h-14 w-14 items-center justify-center rounded-full text-sm font-bold transition-all"
                      :class="screenShareEnabled
                        ? 'bg-tertiary text-on-primary-fixed shadow-[0_0_20px_rgba(251,146,60,0.25)]'
                        : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-bright'"
                      :title="screenShareEnabled ? 'Stop Share' : 'Share Screen'"
                      :aria-label="screenShareEnabled ? 'Stop Share' : 'Share Screen'"
                      @click="toggleScreenShare"
                    >
                      <span class="material-symbols-outlined text-[26px]">
                        {{ screenShareEnabled ? 'cancel_presentation' : 'screen_share' }}
                      </span>

                      <span class="pointer-events-none absolute -top-11 left-1/2 z-20 -translate-x-1/2 whitespace-nowrap rounded-full bg-black/85 px-3 py-1 text-xs font-bold text-white opacity-0 transition-all duration-200 group-hover:-top-12 group-hover:opacity-100">
                        {{ screenShareEnabled ? 'Stop Share' : 'Share Screen' }}
                      </span>
                    </button>

                    <button
                      type="button"
                      class="group relative flex h-14 w-14 items-center justify-center rounded-full bg-surface-container-high text-on-surface transition-all hover:bg-surface-bright"
                      :title="showEditPanel ? 'Close Update' : 'Update Stream'"
                      :aria-label="showEditPanel ? 'Close Update' : 'Update Stream'"
                      @click="showEditPanel = !showEditPanel"
                    >
                      <span class="material-symbols-outlined text-[26px]">
                        {{ showEditPanel ? 'close' : 'edit_square' }}
                      </span>

                      <span class="pointer-events-none absolute -top-11 left-1/2 z-20 -translate-x-1/2 whitespace-nowrap rounded-full bg-black/85 px-3 py-1 text-xs font-bold text-white opacity-0 transition-all duration-200 group-hover:-top-12 group-hover:opacity-100">
                        {{ showEditPanel ? 'Close Update' : 'Update Stream' }}
                      </span>
                    </button>

                    <button
                      type="button"
                      class="group relative flex h-14 w-14 items-center justify-center rounded-full bg-error text-white transition-all hover:opacity-90 disabled:opacity-60"
                      title="End Stream"
                      aria-label="End Stream"
                      :disabled="endLoading"
                      @click="openEndConfirmModal"
                    >
                      <span class="material-symbols-outlined text-[26px]">stop_circle</span>

                      <span class="pointer-events-none absolute -top-11 left-1/2 z-20 -translate-x-1/2 whitespace-nowrap rounded-full bg-black/85 px-3 py-1 text-xs font-bold text-white opacity-0 transition-all duration-200 group-hover:-top-12 group-hover:opacity-100">
                        End Stream
                      </span>
                    </button>
                  </div>
                </div>

                <p
                  v-if="debugMessage"
                  class="mt-4 text-xs text-zinc-400"
                >
                  {{ debugMessage }}
                </p>
              </div>

              <div
                v-if="stream"
                class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3"
              >
                <div class="rounded-3xl border border-white/5 bg-surface-container p-5">
                  <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                    Live Viewers
                  </p>
                  <p class="font-headline text-3xl font-black text-white">
                    {{ formatCompact(viewersCount) }}
                  </p>
                </div>

                <div class="rounded-3xl border border-white/5 bg-surface-container p-5">
                  <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                    Comments
                  </p>
                  <p class="font-headline text-3xl font-black text-white">
                    {{ formatCompact(stream.comments_count || 0) }}
                  </p>
                </div>

                <div class="rounded-3xl border border-white/5 bg-surface-container p-5">
                  <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                    Reactions
                  </p>
                  <p class="font-headline text-3xl font-black text-white">
                    {{ formatCompact(totalReactions) }}
                  </p>
                </div>
              </div>

              <div
                v-if="stream"
                class="rounded-3xl border border-white/5 bg-surface-container p-5 sm:p-6"
              >
                <div class="mb-4 flex items-center justify-between">
                  <h2 class="font-headline text-xl font-black tracking-tight text-white">
                    Live Reactions
                  </h2>
                </div>

                <div class="flex flex-wrap gap-3">
                  <div
                    v-for="item in reactionOptions"
                    :key="item.type"
                    class="flex min-w-[92px] flex-col items-center rounded-2xl bg-surface-container-high px-4 py-4"
                  >
                    <span class="text-3xl">{{ item.emoji }}</span>
                    <span class="mt-2 text-xs font-bold text-on-surface-variant">
                      {{ formatCompact(reactionsSummary[item.type]) }}
                    </span>
                  </div>
                </div>
              </div>

              <div
                v-if="showEditPanel && stream"
                class="rounded-3xl border border-white/5 bg-surface-container p-5 shadow-xl sm:p-6 lg:p-8"
              >
                <div class="mb-6 flex items-center justify-between">
                  <h2 class="font-headline text-xl font-black tracking-tight text-white sm:text-2xl">
                    Update Stream
                  </h2>
                </div>

                <form class="space-y-6" @submit.prevent="submitUpdate">
                  <div class="space-y-2">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                      Stream Title
                    </label>

                    <input
                      v-model="editForm.title"
                      type="text"
                      class="w-full rounded-xl border-none bg-surface-container-low px-4 py-4 text-base font-semibold text-on-surface placeholder:text-zinc-700 transition-all focus:ring-2 focus:ring-primary/50 sm:px-6 sm:text-lg"
                    />
                  </div>

                  <div class="space-y-2">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                      Description
                    </label>

                    <textarea
                      v-model="editForm.description"
                      rows="5"
                      class="w-full resize-none rounded-xl border-none bg-surface-container-low px-4 py-4 text-on-surface placeholder:text-zinc-700 transition-all focus:ring-2 focus:ring-primary/50 sm:px-6"
                    ></textarea>
                  </div>

                  <div class="space-y-4">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                      Categories
                    </label>

                    <div class="flex flex-wrap gap-2">
                      <button
                        v-for="category in stream.categories || []"
                        :key="category.id"
                        type="button"
                        class="rounded-full px-5 py-2 text-xs font-bold transition-all"
                        :class="editForm.category_ids.includes(Number(category.id))
                          ? 'border border-primary/30 bg-primary/20 text-primary'
                          : 'bg-surface-container-highest text-on-surface hover:bg-surface-bright'"
                        @click="toggleCategory(category.id)"
                      >
                        {{ category.name }}
                      </button>
                    </div>

                    <div
                      v-if="selectedCategoriesPreview.length > 0"
                      class="rounded-2xl bg-surface-container-low p-4"
                    >
                      <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                        Selected Categories
                      </p>

                      <div class="flex flex-wrap gap-2">
                        <span
                          v-for="category in selectedCategoriesPreview"
                          :key="category.id"
                          class="rounded-full border border-primary/30 bg-primary/15 px-4 py-2 text-xs font-bold text-primary"
                        >
                          {{ category.name }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <div class="flex flex-wrap gap-3">
                    <button
                      type="submit"
                      :disabled="updateLoading"
                      class="rounded-full bg-gradient-to-r from-primary to-secondary px-6 py-3 text-sm font-bold text-on-primary-fixed transition-all hover:shadow-[0_0_20px_rgba(246,128,255,0.4)] disabled:opacity-60"
                    >
                      {{ updateLoading ? 'Updating...' : 'Save Changes' }}
                    </button>

                    <button
                      type="button"
                      class="rounded-full bg-surface-container-high px-6 py-3 text-sm font-bold text-on-surface transition-all hover:bg-surface-bright"
                      @click="showEditPanel = false"
                    >
                      Cancel
                    </button>
                  </div>
                </form>
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

      <div
        v-if="showEndConfirmModal"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 px-4 backdrop-blur-sm"
      >
        <div class="w-full max-w-md rounded-3xl border border-white/10 bg-surface-container p-6 shadow-[0_20px_80px_rgba(0,0,0,0.45)] sm:p-7">
          <div class="mb-5 flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-error/15 text-error">
              <span class="material-symbols-outlined text-[28px]">warning</span>
            </div>

            <div>
              <h3 class="font-headline text-xl font-black text-white">
                End this live stream?
              </h3>
              <p class="mt-1 text-sm text-on-surface-variant">
                This action will stop the live and create the replay flow in your system.
              </p>
            </div>
          </div>

          <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
            <button
              type="button"
              class="rounded-full bg-surface-container-high px-5 py-3 text-sm font-bold text-on-surface transition-all hover:bg-surface-bright"
              :disabled="endLoading"
              @click="closeEndConfirmModal"
            >
              Cancel
            </button>

            <button
              type="button"
              class="rounded-full bg-error px-5 py-3 text-sm font-bold text-white transition-all hover:opacity-90 disabled:opacity-60"
              :disabled="endLoading"
              @click="endStream"
            >
              {{ endLoading ? 'Ending...' : 'Yes, End Live' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { Room, RoomEvent, Track } from 'livekit-client'
import api from '@/services/api'
import { useStreamRealtime } from '@/composables/useStreamRealtime'
import TopNavbar from '@/components/layout/TopNavbar.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'

const route = useRoute()
const router = useRouter()

const sidebarCollapsed = ref(false)
const mobileSidebarOpen = ref(false)
const isMobileView = ref(false)

const room = ref(null)
const localVideoEl = ref(null)

const loading = ref(true)
const connecting = ref(false)
const connected = ref(false)
const errorMessage = ref('')
const debugMessage = ref('')

const stream = ref(null)
const viewersCount = ref(0)

const showChat = ref(false)
const showDescription = ref(false)
const showEditPanel = ref(false)
const showEndConfirmModal = ref(false)

const updateLoading = ref(false)
const endLoading = ref(false)
const commentLoading = ref(false)

const cameraEnabled = ref(true)
const microphoneEnabled = ref(true)
const screenShareEnabled = ref(false)

const commentError = ref('')
const pollIntervalId = ref(null)
const previewTrack = ref(null)

const { subscribeToStreamChannel, unsubscribeFromStreamChannel } = useStreamRealtime(stream)

const commentForm = reactive({
  content: '',
})

const editForm = reactive({
  title: '',
  description: '',
  category_ids: [],
})

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

const getAvatar = (avatar, name = 'User') => {
  if (avatar) return buildStorageUrl(avatar)
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=111111&color=ffffff&size=256`
}

const playerBackgroundStyle = computed(() => {
  const thumbnail = buildStorageUrl(stream.value?.thumbnail)

  return {
    backgroundImage: thumbnail
      ? `url('${thumbnail}')`
      : "url('https://images.unsplash.com/photo-1511512578047-dfb367046420?q=80&w=1600&auto=format&fit=crop')",
  }
})

const reactionsSummary = computed(() => {
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

const totalReactions = computed(() => {
  return Object.values(reactionsSummary.value).reduce((sum, value) => sum + Number(value || 0), 0)
})

const displayedComments = computed(() => {
  const list = Array.isArray(stream.value?.comments) ? [...stream.value.comments] : []
  return list.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0))
})

const selectedCategoriesPreview = computed(() => {
  const allCategories = Array.isArray(stream.value?.categories) ? stream.value.categories : []

  return allCategories.filter((category) =>
    editForm.category_ids.includes(Number(category.id))
  )
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

const applyStreamToForm = () => {
  if (!stream.value) return

  editForm.title = stream.value.title || ''
  editForm.description = stream.value.description || ''
  editForm.category_ids = (stream.value.categories || []).map((category) => Number(category.id))
}

const loadStream = async ({ silent = false } = {}) => {
  if (!silent) loading.value = true

  try {
    const response = await api.get(`/stream/streams/${route.params.id}`)
    stream.value = response.data?.data || null

    if (!showEditPanel.value) {
      applyStreamToForm()
    }
  } catch (error) {
    console.error('Failed to load stream', error)
    if (!silent) {
      stream.value = null
      errorMessage.value = error.response?.data?.message || 'Failed to load stream.'
    }
  } finally {
    if (!silent) loading.value = false
  }
}

const startPolling = () => {
  stopPolling()

  pollIntervalId.value = window.setInterval(() => {
    loadStream({ silent: true })
  }, 2000)
}

const stopPolling = () => {
  if (pollIntervalId.value) {
    clearInterval(pollIntervalId.value)
    pollIntervalId.value = null
  }
}

const syncViewersCount = () => {
  viewersCount.value = room.value ? room.value.remoteParticipants.size : 0
}

const attachPreviewTrack = async () => {
  await nextTick()

  if (!room.value || !localVideoEl.value) return

  const publications = Array.from(room.value.localParticipant.videoTrackPublications.values())

  const screenPublication = publications.find((pub) => pub.source === Track.Source.ScreenShare)
  const cameraPublication = publications.find((pub) => pub.source === Track.Source.Camera)

  const trackToUse = screenPublication?.videoTrack || cameraPublication?.videoTrack

  if (previewTrack.value && previewTrack.value !== trackToUse && localVideoEl.value) {
    previewTrack.value.detach(localVideoEl.value)
  }

  previewTrack.value = trackToUse || null

  if (trackToUse && localVideoEl.value) {
    trackToUse.attach(localVideoEl.value)
    localVideoEl.value.autoplay = true
    localVideoEl.value.playsInline = true
    localVideoEl.value.muted = true
  }
}

const disconnectStudio = () => {
  stopPolling()

  if (previewTrack.value && localVideoEl.value) {
    previewTrack.value.detach(localVideoEl.value)
  }

  if (room.value) {
    room.value.disconnect()
    room.value = null
  }

  previewTrack.value = null
  viewersCount.value = 0
  connected.value = false
  connecting.value = false
}

const connectStudio = async () => {
  try {
    connecting.value = true
    debugMessage.value = 'Requesting studio token...'
    errorMessage.value = ''

    await loadStream()
    subscribeToStreamChannel(route.params.id)

    const response = await api.post(`/stream/streams/${route.params.id}/studio-token`)
    const { token, url, room_name } = response.data?.data || {}

    if (!token || !url || !room_name) {
      throw new Error('Token response is incomplete.')
    }

    debugMessage.value = `Connecting to ${room_name}...`

    const liveRoom = new Room()
    room.value = liveRoom

    liveRoom.on(RoomEvent.ParticipantConnected, () => {
      syncViewersCount()
    })

    liveRoom.on(RoomEvent.ParticipantDisconnected, () => {
      syncViewersCount()
    })

    liveRoom.on(RoomEvent.LocalTrackPublished, async () => {
      await attachPreviewTrack()
    })

    liveRoom.on(RoomEvent.LocalTrackUnpublished, async () => {
      await attachPreviewTrack()
    })

    await liveRoom.connect(url, token)

    debugMessage.value = 'Connected. Enabling camera...'
    await liveRoom.localParticipant.setCameraEnabled(true)

    debugMessage.value = 'Enabling microphone...'
    await liveRoom.localParticipant.setMicrophoneEnabled(true)

    cameraEnabled.value = true
    microphoneEnabled.value = true
    screenShareEnabled.value = false

    connected.value = true
    syncViewersCount()
    await attachPreviewTrack()

    debugMessage.value = 'Live started successfully.'
    startPolling()
  } catch (error) {
    console.error('Live studio error:', error)
    console.error('Live studio response:', error?.response?.data)

    errorMessage.value =
      error?.response?.data?.message ||
      error?.message ||
      'Failed to start live studio.'
  } finally {
    connecting.value = false
    loading.value = false
  }
}

const toggleCamera = async () => {
  if (!room.value) return

  try {
    const nextState = !cameraEnabled.value
    await room.value.localParticipant.setCameraEnabled(nextState)
    cameraEnabled.value = nextState
    await attachPreviewTrack()
  } catch (error) {
    console.error('Failed to toggle camera', error)
  }
}

const toggleMicrophone = async () => {
  if (!room.value) return

  try {
    const nextState = !microphoneEnabled.value
    await room.value.localParticipant.setMicrophoneEnabled(nextState)
    microphoneEnabled.value = nextState
  } catch (error) {
    console.error('Failed to toggle microphone', error)
  }
}

const toggleScreenShare = async () => {
  if (!room.value) return

  try {
    const nextState = !screenShareEnabled.value
    await room.value.localParticipant.setScreenShareEnabled(nextState)
    screenShareEnabled.value = nextState
    await attachPreviewTrack()
  } catch (error) {
    console.error('Failed to toggle screen share', error)
    errorMessage.value = error?.message || 'Failed to toggle screen sharing.'
  }
}

const toggleCategory = (categoryId) => {
  const id = Number(categoryId)

  if (editForm.category_ids.includes(id)) {
    editForm.category_ids = editForm.category_ids.filter((item) => item !== id)
  } else {
    editForm.category_ids = [...editForm.category_ids, id]
  }
}

const submitUpdate = async () => {
  updateLoading.value = true

  try {
    const payload = {
      title: editForm.title.trim(),
      description: editForm.description.trim(),
      category_ids: editForm.category_ids.map((id) => Number(id)),
    }

    const response = await api.put(`/stream/streams/${route.params.id}`, payload)
    stream.value = response.data?.data || stream.value
    applyStreamToForm()
    showEditPanel.value = false
  } catch (error) {
    console.error('Failed to update stream', error)
    errorMessage.value = error.response?.data?.message || 'Failed to update stream.'
  } finally {
    updateLoading.value = false
  }
}

const submitComment = async () => {
  const content = commentForm.content.trim()

  if (!content || commentLoading.value || !stream.value) return

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

    commentForm.content = ''
  } catch (error) {
    console.error('Failed to send comment', error)
    commentError.value = error.response?.data?.message || 'Failed to send comment.'
  } finally {
    commentLoading.value = false
  }
}

const openEndConfirmModal = () => {
  showEndConfirmModal.value = true
}

const closeEndConfirmModal = () => {
  if (endLoading.value) return
  showEndConfirmModal.value = false
}

const endStream = async () => {
  if (!stream.value) return

  endLoading.value = true

  try {
    await api.patch(`/stream/streams/${route.params.id}/end`)
    closeEndConfirmModal()
    disconnectStudio()
    await loadStream()
    router.push(`/streams/${route.params.id}`)
  } catch (error) {
    console.error('Failed to end stream', error)
    errorMessage.value = error.response?.data?.message || 'Failed to end stream.'
  } finally {
    endLoading.value = false
  }
}

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)

  if (window.innerWidth < 1280) {
    showChat.value = false
  }

  connectStudio()
})

onBeforeUnmount(() => {
  unsubscribeFromStreamChannel()
  disconnectStudio()
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
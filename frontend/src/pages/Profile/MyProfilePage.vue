<template>
  <div class="dark">
    <div class="min-h-screen bg-background text-on-surface antialiased">
      <TopNavbar
        :profile="profile"
        @toggle-sidebar="handleSidebarToggle"
      />

      <div class="flex min-h-screen">
        <AppSidebar
          :collapsed="sidebarCollapsed"
          :mobile-open="mobileSidebarOpen"
          @close="mobileSidebarOpen = false"
        />

        <main
          :class="[
            'min-w-0 flex-1 pb-24 transition-all duration-300',
            sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'
          ]"
        >
          <section class="relative h-[420px] w-full overflow-hidden bg-surface-container-lowest md:h-[460px]">
            <img
              :src="coverImage"
              alt="Banner Image"
              class="h-full w-full object-cover opacity-60"
            />

            <div class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-transparent"></div>

            <div class="absolute inset-0 flex flex-col items-center justify-center translate-y-10 px-4 md:translate-y-12">
              <div class="group relative">
                <div class="absolute -inset-1 rounded-full bg-gradient-to-r from-primary to-tertiary blur opacity-40 transition duration-1000 group-hover:opacity-70 group-hover:duration-200"></div>
                <img
                  :src="avatarImage"
                  alt="Avatar"
                  class="relative h-28 w-28 rounded-full border-4 border-background object-cover sm:h-32 sm:w-32 md:h-40 md:w-40"
                />
              </div>

              <div class="mt-6 text-center">
                <h1 class="neon-glow font-headline text-3xl font-black tracking-tighter sm:text-4xl md:text-5xl">
                  {{ profile?.user?.name || 'My Profile' }}
                </h1>
                <p class="mt-1 break-all px-4 font-medium text-on-surface-variant">
                  {{ profile?.user?.email || 'No email' }}
                </p>
                <span
                  class="mt-3 inline-block rounded-full bg-tertiary-container px-4 py-1 text-xs font-black uppercase tracking-[0.2em] text-on-tertiary-container"
                >
                  {{ profile?.user?.role || 'User' }}
                </span>
              </div>
            </div>

            <div class="absolute right-4 top-4 md:right-8 md:top-8">
              <button
                type="button"
                class="rounded-full border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/20 active:scale-95 md:px-6 md:py-2.5"
                @click="openEditModal"
              >
                Edit Profile
              </button>
            </div>
          </section>

          <div class="mx-auto mt-16 max-w-6xl px-4 sm:px-6 md:mt-20">
            <div class="mb-16 flex flex-col gap-12 md:flex-row">
              <div class="flex-1 space-y-6">
                <div class="space-y-4">
                  <h2 class="font-label text-sm font-black uppercase tracking-[0.3em] text-primary">
                    Bio
                  </h2>
                  <p class="text-lg font-light italic leading-relaxed text-on-surface-variant sm:text-xl">
                    {{ profile?.bio || 'No bio added yet.' }}
                  </p>
                </div>

                <div class="flex gap-8 sm:gap-10">
                  <button
                    type="button"
                    class="text-left transition hover:opacity-80"
                    @click="openFollowersModal"
                  >
                    <span class="block font-headline text-2xl font-black text-white">
                      {{ profile?.user?.followers_count ?? 0 }}
                    </span>
                    <span class="font-label text-xs uppercase tracking-widest text-zinc-500">
                      Followers
                    </span>
                  </button>

                  <button
                    type="button"
                    class="text-left transition hover:opacity-80"
                    @click="openFollowingModal"
                  >
                    <span class="block font-headline text-2xl font-black text-white">
                      {{ profile?.user?.following_count ?? 0 }}
                    </span>
                    <span class="font-label text-xs uppercase tracking-widest text-zinc-500">
                      Following
                    </span>
                  </button>
                </div>
              </div>

              <div class="md:w-1/3">
                <div
                  v-if="profile?.live_stream"
                  class="group relative overflow-hidden rounded-lg bg-surface-container-high p-1 shadow-2xl transition-transform hover:-translate-y-1"
                >
                  <div class="absolute inset-0 bg-gradient-to-br from-tertiary/40 to-primary/40 opacity-20 group-hover:opacity-40"></div>

                  <div class="relative rounded-[1.8rem] bg-background p-6">
                    <div class="mb-6 flex items-start justify-between gap-3">
                      <span class="flex items-center gap-1 rounded-full bg-tertiary px-3 py-1 text-[10px] font-black text-on-tertiary">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span>
                        LIVE NOW
                      </span>
                      <span class="text-right text-xs text-zinc-500">
                        {{ profile.live_stream.current_viewers }} watching
                      </span>
                    </div>

                    <h3 class="mb-2 font-headline text-xl font-bold">
                      {{ profile.live_stream.title }}
                    </h3>

                    <p class="mb-6 text-sm text-zinc-400">
                      {{ profile.live_stream.description || 'Live stream in progress.' }}
                    </p>

                    <div class="mb-4 grid grid-cols-2 gap-3 text-xs text-zinc-500">
                      <div>Comments: {{ profile.live_stream.comments_count }}</div>
                      <div>Reactions: {{ profile.live_stream.reactions_count }}</div>
                    </div>

                    <RouterLink
                      :to="`/streams/${profile.live_stream.id}/studio`"
                      class="block w-full rounded-full bg-white py-3 text-center text-sm font-black uppercase tracking-widest text-black transition-colors hover:bg-zinc-200"
                    >
                      Tune In
                    </RouterLink>
                  </div>
                </div>

                <div
                  v-else
                  class="rounded-[1.8rem] border border-white/10 bg-surface-container-low p-6 text-center text-on-surface-variant"
                >
                  No live stream right now.
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-8">
              <section class="rounded-3xl bg-surface-container-low p-4 sm:p-6">
                <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                  <div>
                    <h2 class="font-label text-sm font-black uppercase tracking-[0.2em] text-white">
                      Videos
                    </h2>
                    <p class="mt-1 text-sm text-zinc-500">
                      Current profile videos list
                    </p>
                  </div>

                  <div class="flex flex-col gap-3 md:flex-row">
                    <select
                      v-model="selectedCategory"
                      class="rounded-full border border-white/10 bg-surface-container px-4 py-3 text-sm text-on-surface outline-none"
                    >
                      <option value="all">All categories</option>
                      <option
                        v-for="category in videoCategories"
                        :key="category.id"
                        :value="String(category.id)"
                      >
                        {{ category.name }}
                      </option>
                    </select>
                  </div>
                </div>

                <div v-if="paginatedVideos.length === 0" class="rounded-2xl bg-surface-container-high p-10 text-center text-zinc-500">
                  No videos found.
                </div>

                <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2">
                  <RouterLink
                    v-for="video in paginatedVideos"
                    :key="video.id"
                    :to="`/videos/${video.id}`"
                    class="group block cursor-pointer"
                  >
                    <div class="relative mb-4 aspect-video overflow-hidden rounded-lg shadow-xl">
                      <template v-if="getVideoSrc(video)">
                        <video
                          :src="getVideoSrc(video)"
                          :poster="getVideoPoster(video)"
                          class="h-full w-full object-cover"
                          muted
                          preload="metadata"
                        ></video>
                      </template>

                      <img
                        v-else
                        :src="getVideoPoster(video)"
                        :alt="video.title || 'Video preview'"
                        class="h-full w-full object-cover"
                      />

                      <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-tertiary/20"></div>
                      <div class="absolute inset-0 bg-black/40 transition-colors group-hover:bg-black/20"></div>

                      <div class="absolute bottom-3 right-3 rounded bg-black/80 px-2 py-1 text-[10px] font-bold text-white">
                        {{ formatDuration(video.duration) }}
                      </div>

                      <div class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity group-hover:opacity-100">
                        <span class="material-symbols-outlined text-6xl text-white opacity-80">
                          play_circle
                        </span>
                      </div>
                    </div>

                    <div
                      v-if="video.categories?.length"
                      class="mb-2 flex flex-wrap gap-2"
                    >
                      <span
                        v-for="category in video.categories"
                        :key="`${video.id}-${category.id}`"
                        class="rounded-full border border-primary/20 bg-primary/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-primary"
                      >
                        {{ category.name }}
                      </span>
                    </div>

                    <h3 class="mb-1 font-headline text-lg font-bold transition-colors group-hover:text-primary">
                      {{ video.title }}
                    </h3>

                    <p class="mb-3 line-clamp-2 text-sm font-light text-zinc-500">
                      {{ video.description || 'No description.' }}
                    </p>

                    <div class="flex flex-wrap items-center gap-4 font-label text-[10px] uppercase tracking-widest text-zinc-600">
                      <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">comment</span>
                        {{ video.comments_count || 0 }}
                      </span>
                      <span>{{ formatDate(video.created_at) }}</span>
                    </div>

                    <div class="mt-4">
                      <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-black uppercase tracking-widest text-black transition-colors group-hover:bg-zinc-200">
                        Open Video
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                      </span>
                    </div>
                  </RouterLink>
                </div>

                <div v-if="totalPages > 1" class="mt-8 flex flex-wrap items-center justify-center gap-2">
                  <button
                    type="button"
                    class="rounded-full border border-white/10 px-4 py-2 text-sm text-zinc-400 transition hover:bg-white/5 disabled:opacity-40"
                    :disabled="currentPage === 1"
                    @click="currentPage--"
                  >
                    Prev
                  </button>

                  <button
                    v-for="page in totalPages"
                    :key="page"
                    type="button"
                    class="h-10 w-10 rounded-full text-sm font-bold transition"
                    :class="page === currentPage ? 'bg-primary text-on-primary' : 'bg-surface-container text-zinc-400 hover:bg-surface-container-high'"
                    @click="currentPage = page"
                  >
                    {{ page }}
                  </button>

                  <button
                    type="button"
                    class="rounded-full border border-white/10 px-4 py-2 text-sm text-zinc-400 transition hover:bg-white/5 disabled:opacity-40"
                    :disabled="currentPage === totalPages"
                    @click="currentPage++"
                  >
                    Next
                  </button>
                </div>
              </section>
            </div>
          </div>
        </main>
      </div>

      <UsersListModal
        :open="showFollowersModal"
        title="Followers"
        :users="followersUsers"
        :loading="followersLoading"
        @close="showFollowersModal = false"
      />

      <UsersListModal
        :open="showFollowingModal"
        title="Following"
        :users="followingUsers"
        :loading="followingLoading"
        @close="showFollowingModal = false"
      />

      <div
        v-if="showEditModal"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/70 px-4 backdrop-blur-sm"
        @click.self="closeEditModal"
      >
        <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto overflow-x-hidden rounded-lg border border-outline-variant/10 bg-surface-container shadow-[0_20px_40px_rgba(0,0,0,0.6)]">
          <div class="pointer-events-none absolute left-1/4 top-0 h-[500px] w-[500px] rounded-full bg-primary/10 blur-[120px]"></div>
          <div class="pointer-events-none absolute bottom-0 right-1/4 h-[400px] w-[400px] rounded-full bg-secondary/10 blur-[100px]"></div>

          <div class="relative space-y-8 px-4 pb-6 pt-6 sm:px-6 sm:pb-8 sm:pt-8 md:px-8">
            <div class="flex items-center justify-between gap-4">
              <div>
                <h1 class="font-headline text-2xl font-extrabold -tracking-tight text-white sm:text-3xl">
                  Edit Profile
                </h1>
                <p class="mt-1 text-sm text-on-surface-variant">
                  Customize your digital identity on Hisoka Noir
                </p>
              </div>

              <button
                type="button"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-surface-bright/20 transition-colors hover:bg-surface-bright/40"
                @click="closeEditModal"
              >
                <span class="material-symbols-outlined text-white">close</span>
              </button>
            </div>

            <form class="space-y-8" @submit.prevent="handleUpdateProfile">
              <div class="group relative">
                <label class="mb-3 block text-xs font-label uppercase tracking-widest text-on-surface-variant">
                  Update Cover Photo
                </label>

                <label
                  class="dashed-outline relative flex h-40 w-full cursor-pointer flex-col items-center justify-center overflow-hidden rounded-lg bg-surface-container-low transition-all duration-300 group-hover:bg-surface-container-high sm:h-48"
                >
                  <img
                    :src="editBackgroundPreview || coverImage"
                    alt="Cover preview"
                    class="absolute inset-0 h-full w-full object-cover opacity-30 transition-opacity group-hover:opacity-40"
                  />

                  <div class="relative z-10 flex flex-col items-center px-4 text-center">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-surface-bright/50 backdrop-blur-md transition-transform group-hover:scale-110">
                      <span class="material-symbols-outlined text-white">photo_camera</span>
                    </div>

                    <span class="text-sm font-medium text-white">
                      Drag or click to upload cover
                    </span>
                    <span class="mt-1 text-xs text-on-surface-variant">
                      16:9 ratio recommended
                    </span>
                  </div>

                  <input
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="onBackgroundChange"
                  />
                </label>
              </div>

              <div class="flex flex-col items-start gap-8 md:flex-row">
                <div class="group shrink-0">
                  <label class="mb-3 block text-xs font-label uppercase tracking-widest text-on-surface-variant">
                    Update Avatar
                  </label>

                  <label
                    class="dashed-outline-circle relative flex h-28 w-28 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-full bg-surface-container-low transition-all duration-300 group-hover:bg-surface-container-high sm:h-32 sm:w-32"
                  >
                    <img
                      :src="editAvatarPreview || avatarImage"
                      alt="Avatar preview"
                      class="absolute inset-0 h-full w-full object-cover opacity-60 transition-opacity group-hover:opacity-80"
                    />

                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                      <span class="material-symbols-outlined scale-110 text-white">photo_camera</span>
                    </div>

                    <input
                      type="file"
                      accept="image/*"
                      class="hidden"
                      @change="onAvatarChange"
                    />
                  </label>
                </div>

                <div class="w-full flex-grow space-y-2">
                  <div class="flex items-center justify-between gap-3">
                    <label class="block text-xs font-label uppercase tracking-widest text-on-surface-variant">
                      Manifesto (Bio)
                    </label>
                    <span class="text-[10px] font-label text-on-surface-variant/60">
                      {{ bioLength }} / 240
                    </span>
                  </div>

                  <textarea
                    v-model="editForm.bio"
                    rows="6"
                    maxlength="240"
                    class="w-full resize-none rounded-lg border-none bg-surface-container-low p-4 leading-relaxed text-white transition-all focus:ring-2 focus:ring-primary/40 sm:p-6"
                    placeholder="Write something about yourself..."
                  ></textarea>
                </div>
              </div>

              <p v-if="updateSuccess" class="text-sm text-green-400">
                {{ updateSuccess }}
              </p>

              <p v-if="updateError" class="text-sm text-error">
                {{ updateError }}
              </p>

              <div class="flex flex-col items-center justify-end gap-4 pt-4 sm:flex-row">
                <button
                  type="button"
                  class="w-full rounded-full border border-outline-variant/30 px-8 py-3 font-semibold text-white transition-all duration-300 hover:bg-surface-bright/10 sm:w-auto"
                  @click="closeEditModal"
                >
                  Cancel Changes
                </button>

                <button
                  type="submit"
                  :disabled="updateLoading"
                  class="bg-gradient-primary w-full rounded-full px-10 py-3 font-bold text-on-primary shadow-[0_10px_30px_rgba(246,128,255,0.3)] transition-all duration-300 hover:scale-105 active:scale-95 disabled:cursor-not-allowed disabled:opacity-70 sm:w-auto"
                >
                  {{ updateLoading ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, reactive, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import TopNavbar from '@/components/layout/TopNavbar.vue'
import UsersListModal from '@/components/profile/UsersListModal.vue'

const sidebarCollapsed = ref(false)
const mobileSidebarOpen = ref(false)

const profile = ref(null)
const loading = ref(false)

const showFollowersModal = ref(false)
const showFollowingModal = ref(false)
const followersLoading = ref(false)
const followingLoading = ref(false)
const followersUsers = ref([])
const followingUsers = ref([])

const showEditModal = ref(false)
const updateLoading = ref(false)
const updateSuccess = ref('')
const updateError = ref('')

const editForm = reactive({
  bio: '',
  avatar: null,
  background_image: null,
})

const editAvatarPreview = ref('')
const editBackgroundPreview = ref('')

const currentPage = ref(1)
const perPage = 4
const selectedCategory = ref('all')

const APP_URL = (import.meta.env.VITE_APP_URL || 'http://localhost:8000').replace(/\/$/, '')
const STORAGE_BASE = `${APP_URL}/storage`

const normalizeCollection = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.data)) return payload.data.data
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

const getVideoSrc = (video) => buildStorageUrl(video?.url)

const getVideoPoster = (video) => {
  return buildStorageUrl(
    video?.thumbnail ||
    video?.thumbnail_url ||
    video?.stream?.thumbnail ||
    video?.stream?.thumbnail_url
  ) || `https://ui-avatars.com/api/?name=${encodeURIComponent(video?.title || 'Video')}&background=111111&color=ffffff&size=512`
}

const normalizeUsersWithAvatar = (users = []) => {
  return users.map((user) => ({
    ...user,
    avatar: user?.avatar ? buildStorageUrl(user.avatar) : null,
  }))
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

const coverImage = computed(() => {
  return profile.value?.background_image
    ? buildStorageUrl(profile.value.background_image)
    : 'src/assets/background.png'
})

const avatarImage = computed(() => {
  return profile.value?.avatar
    ? buildStorageUrl(profile.value.avatar)
    : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(profile.value?.user?.name || 'User') + '&background=111111&color=ffffff&size=256'
})

const bioLength = computed(() => (editForm.bio || '').length)

const videoCategories = computed(() => {
  const videos = profile.value?.videos_preview || []
  const categoriesMap = new Map()

  videos.forEach((video) => {
    const categories = video?.categories || []

    categories.forEach((category) => {
      if (!categoriesMap.has(Number(category.id))) {
        categoriesMap.set(Number(category.id), {
          id: Number(category.id),
          name: category.name,
        })
      }
    })
  })

  return Array.from(categoriesMap.values())
})

const filteredVideos = computed(() => {
  const videos = profile.value?.videos_preview || []

  if (selectedCategory.value === 'all') {
    return videos
  }

  return videos.filter((video) => {
    const categories = video?.categories || []

    return categories.some(
      (category) => String(category.id) === String(selectedCategory.value)
    )
  })
})

const totalPages = computed(() => {
  return Math.max(1, Math.ceil(filteredVideos.value.length / perPage))
})

const paginatedVideos = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredVideos.value.slice(start, start + perPage)
})

watch(selectedCategory, () => {
  currentPage.value = 1
})

watch(totalPages, (value) => {
  if (currentPage.value > value) {
    currentPage.value = value
  }
})

const formatDate = (date) => {
  if (!date) return 'Unknown date'
  return new Date(date).toLocaleDateString()
}

const formatDuration = (value) => {
  if (value === null || value === undefined || value === '') {
    return '00:00'
  }

  const asNumber = Number(value)

  if (!Number.isFinite(asNumber)) {
    return String(value)
  }

  const totalSeconds = Math.max(0, Math.floor(asNumber))
  const hours = Math.floor(totalSeconds / 3600)
  const minutes = Math.floor((totalSeconds % 3600) / 60)
  const seconds = totalSeconds % 60

  if (hours > 0) {
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
  }

  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
}

const loadProfile = async () => {
  loading.value = true

  try {
    const response = await api.get('/profile/profile')
    profile.value = response.data?.data?.profile || null
    editForm.bio = profile.value?.bio || ''
  } catch (error) {
    console.error('Failed to load profile', error)
  } finally {
    loading.value = false
  }
}

const openFollowersModal = async () => {
  showFollowersModal.value = true
  followersLoading.value = true

  try {
    const userId = profile.value?.user?.id
    const response = await api.get(`/subscrip/users/${userId}/followers`)
    followersUsers.value = normalizeUsersWithAvatar(normalizeCollection(response.data))
  } catch (error) {
    followersUsers.value = []
    console.error('Failed to load followers', error)
  } finally {
    followersLoading.value = false
  }
}

const openFollowingModal = async () => {
  showFollowingModal.value = true
  followingLoading.value = true

  try {
    const userId = profile.value?.user?.id
    const response = await api.get(`/subscrip/users/${userId}/following`)
    followingUsers.value = normalizeUsersWithAvatar(normalizeCollection(response.data))
  } catch (error) {
    followingUsers.value = []
    console.error('Failed to load following', error)
  } finally {
    followingLoading.value = false
  }
}

const resetEditState = () => {
  updateSuccess.value = ''
  updateError.value = ''
  editForm.bio = profile.value?.bio || ''
  editForm.avatar = null
  editForm.background_image = null

  if (editAvatarPreview.value) {
    URL.revokeObjectURL(editAvatarPreview.value)
    editAvatarPreview.value = ''
  }

  if (editBackgroundPreview.value) {
    URL.revokeObjectURL(editBackgroundPreview.value)
    editBackgroundPreview.value = ''
  }
}

const openEditModal = () => {
  resetEditState()
  showEditModal.value = true
}

const closeEditModal = () => {
  resetEditState()
  showEditModal.value = false
}

const onAvatarChange = (event) => {
  const file = event.target.files?.[0] || null
  editForm.avatar = file

  if (editAvatarPreview.value) {
    URL.revokeObjectURL(editAvatarPreview.value)
  }

  editAvatarPreview.value = file ? URL.createObjectURL(file) : ''
}

const onBackgroundChange = (event) => {
  const file = event.target.files?.[0] || null
  editForm.background_image = file

  if (editBackgroundPreview.value) {
    URL.revokeObjectURL(editBackgroundPreview.value)
  }

  editBackgroundPreview.value = file ? URL.createObjectURL(file) : ''
}

const handleUpdateProfile = async () => {
  updateLoading.value = true
  updateSuccess.value = ''
  updateError.value = ''

  try {
    const formData = new FormData()

    formData.append('bio', editForm.bio || '')

    if (editForm.avatar) {
      formData.append('avatar', editForm.avatar)
    }

    if (editForm.background_image) {
      formData.append('background_image', editForm.background_image)
    }

    formData.append('_method', 'PUT')

    const response = await api.post('/profile/profile', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    profile.value = response.data?.data?.profile || profile.value
    updateSuccess.value = response.data?.message || 'Profile updated successfully'

    setTimeout(() => {
      closeEditModal()
    }, 900)
  } catch (error) {
    if (error.response?.status === 422) {
      const validationErrors = error.response.data.errors || {}
      updateError.value = Object.values(validationErrors).flat().join(' ') || 'Validation failed.'
    } else {
      updateError.value = error.response?.data?.message || 'Failed to update profile'
    }
  } finally {
    updateLoading.value = false
  }
}

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
  loadProfile()
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
  document.body.style.overflow = ''

  if (editAvatarPreview.value) URL.revokeObjectURL(editAvatarPreview.value)
  if (editBackgroundPreview.value) URL.revokeObjectURL(editBackgroundPreview.value)
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

.glass-panel {
  background: rgba(25, 25, 25, 0.7);
  backdrop-filter: blur(12px);
}

.neon-glow {
  text-shadow: 0 0 15px rgba(246, 128, 255, 0.4);
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #f680ff 0%, #c180ff 100%);
}

.dashed-outline {
  background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' rx='32' ry='32' stroke='%23484848' stroke-width='2' stroke-dasharray='8%2c 12' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e");
}

.dashed-outline-circle {
  background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='50%25' cy='50%25' r='48%25' fill='none' stroke='%23484848' stroke-width='2' stroke-dasharray='8%2c 8' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e");
}
</style>
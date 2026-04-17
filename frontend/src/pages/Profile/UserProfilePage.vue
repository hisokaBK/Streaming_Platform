<template>
  <div class="dark">
    <div class="min-h-screen bg-background text-on-surface antialiased">
      <TopNavbar
        :profile="profile"
        @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed"
      />

      <div class="flex min-h-screen">
        <AppSidebar :collapsed="sidebarCollapsed" />

        <main
          :class="[
            'flex-1 pb-24 transition-all duration-300',
            sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'
          ]"
        >
          <!-- Hero Section -->
          <section class="relative h-96 w-full overflow-hidden">
            <img
              :src="coverImage"
              alt="Profile Banner"
              class="h-full w-full object-cover"
            />

            <div class="absolute inset-0 bg-gradient-to-t from-background via-background/40 to-transparent"></div>

            <div class="absolute bottom-0 left-0 flex w-full flex-col gap-6 px-8 pb-8 md:flex-row md:items-end">
              <div class="group relative">
                <div class="h-32 w-32 overflow-hidden rounded-full border-4 border-background bg-zinc-800 shadow-2xl md:h-44 md:w-44">
                  <img
                    :src="avatarImage"
                    :alt="profile?.user?.name || 'User avatar'"
                    class="h-full w-full object-cover"
                  />
                </div>

                <div class="absolute bottom-2 right-2 h-8 w-8 rounded-full border-4 border-background bg-green-500"></div>
              </div>

              <div class="flex-1">
                <div class="mb-4 flex flex-col gap-4 md:flex-row md:items-center">
                  <h1 class="font-headline text-4xl font-black tracking-tighter text-white md:text-6xl">
                    {{ profile?.user?.name || 'User Profile' }}
                  </h1>

                  <div class="flex gap-3">
                    <button
                      type="button"
                      class="rounded-full bg-primary px-8 py-2 text-sm font-bold text-on-primary-fixed shadow-lg shadow-primary/20 transition-all hover:scale-105 active:scale-95"
                    >
                      Follow
                    </button>

                    <button
                      type="button"
                      class="rounded-full border border-outline-variant/20 bg-surface-container-high px-6 py-2 text-sm font-semibold text-white transition-all hover:bg-surface-bright"
                    >
                      Message
                    </button>
                  </div>
                </div>

                <p class="max-w-2xl font-body text-lg font-medium leading-relaxed text-on-surface-variant">
                  {{ profile?.bio || 'No bio added yet.' }}
                </p>
              </div>
            </div>
          </section>

          <!-- Lower Part Like MyProfile -->
          <div class="mx-auto mt-20 max-w-6xl px-6">
            <div class="mb-16 flex flex-col gap-12 md:flex-row">
              <div class="flex-1 space-y-6">
                <div class="space-y-4">
                  <h2 class="font-label text-sm font-black uppercase tracking-[0.3em] text-primary">
                    Bio
                  </h2>
                  <p class="text-xl font-light italic leading-relaxed text-on-surface-variant">
                    {{ profile?.bio || 'No bio added yet.' }}
                  </p>
                </div>

                <div class="flex gap-10">
                  <div>
                    <span class="block font-headline text-2xl font-black text-white">
                      {{ profile?.user?.followers_count ?? 0 }}
                    </span>
                    <span class="font-label text-xs uppercase tracking-widest text-zinc-500">
                      Followers
                    </span>
                  </div>

                  <div>
                    <span class="block font-headline text-2xl font-black text-white">
                      {{ profile?.user?.following_count ?? 0 }}
                    </span>
                    <span class="font-label text-xs uppercase tracking-widest text-zinc-500">
                      Following
                    </span>
                  </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <button
                    type="button"
                    class="rounded-2xl border border-white/10 bg-surface-container-high p-5 text-left transition hover:bg-surface-container"
                    @click="openFollowersModal"
                  >
                    <div class="mb-2 flex items-center justify-between">
                      <h3 class="font-headline text-lg font-bold">Followers</h3>
                      <span class="material-symbols-outlined text-primary">group</span>
                    </div>
                    <p class="text-sm text-zinc-500">
                      See all followers
                    </p>
                  </button>

                  <button
                    type="button"
                    class="rounded-2xl border border-white/10 bg-surface-container-high p-5 text-left transition hover:bg-surface-container"
                    @click="openFollowingModal"
                  >
                    <div class="mb-2 flex items-center justify-between">
                      <h3 class="font-headline text-lg font-bold">Following</h3>
                      <span class="material-symbols-outlined text-primary">person_add</span>
                    </div>
                    <p class="text-sm text-zinc-500">
                      See all following
                    </p>
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
                    <div class="mb-6 flex items-start justify-between">
                      <span class="flex items-center gap-1 rounded-full bg-tertiary px-3 py-1 text-[10px] font-black text-on-tertiary">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span>
                        LIVE NOW
                      </span>
                      <span class="text-xs text-zinc-500">
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
                      :to="`/streams/${profile.live_stream.id}`"
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
              <section class="rounded-3xl bg-surface-container-low p-6">
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
                    </select>
                  </div>
                </div>

                <div v-if="paginatedVideos.length === 0" class="rounded-2xl bg-surface-container-high p-10 text-center text-zinc-500">
                  No videos found.
                </div>

                <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2">
                  <div
                    v-for="video in paginatedVideos"
                    :key="video.id"
                    class="group cursor-pointer"
                  >
                    <div class="relative mb-4 aspect-video overflow-hidden rounded-lg shadow-xl">
                      <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-tertiary/20"></div>
                      <div class="absolute inset-0 bg-black/40 transition-colors group-hover:bg-black/20"></div>
                      <div class="absolute bottom-3 right-3 rounded bg-black/80 px-2 py-1 text-[10px] font-bold text-white">
                        {{ video.duration || '00:00' }}
                      </div>
                    </div>

                    <h3 class="mb-1 font-headline text-lg font-bold transition-colors group-hover:text-primary">
                      {{ video.title }}
                    </h3>

                    <p class="mb-3 line-clamp-2 text-sm font-light text-zinc-500">
                      {{ video.description || 'No description.' }}
                    </p>

                    <div class="flex items-center gap-4 font-label text-[10px] uppercase tracking-widest text-zinc-600">
                      <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">comment</span>
                        {{ video.comments_count || 0 }}
                      </span>
                      <span>{{ formatDate(video.created_at) }}</span>
                    </div>
                  </div>
                </div>

                <div v-if="totalPages > 1" class="mt-8 flex items-center justify-center gap-2">
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
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import api from '@/services/api'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import TopNavbar from '@/components/layout/TopNavbar.vue'
import UsersListModal from '@/components/profile/UsersListModal.vue'

const route = useRoute()

const sidebarCollapsed = ref(false)

const profile = ref(null)
const loading = ref(false)

const showFollowersModal = ref(false)
const showFollowingModal = ref(false)
const followersLoading = ref(false)
const followingLoading = ref(false)
const followersUsers = ref([])
const followingUsers = ref([])

const currentPage = ref(1)
const perPage = 4
const selectedCategory = ref('all')

const userId = computed(() => route.params.id)

const buildStorageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http')) return path
  return `http://localhost:8000/storage/${path}`
}

const coverImage = computed(() => {
  return profile.value?.background_image
    ? buildStorageUrl(profile.value.background_image)
    : '../src/assets/background.png'
})

const avatarImage = computed(() => {
  return profile.value?.avatar
    ? buildStorageUrl(profile.value.avatar)
    : `https://ui-avatars.com/api/?name=${encodeURIComponent(profile.value?.user?.name || 'User')}&background=111111&color=ffffff&size=256`
})

const filteredVideos = computed(() => {
  const list = profile.value?.videos_preview || []

  if (selectedCategory.value === 'all') {
    return list
  }

  return list
})

const totalPages = computed(() => {
  return Math.max(1, Math.ceil(filteredVideos.value.length / perPage))
})

const paginatedVideos = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredVideos.value.slice(start, start + perPage)
})

const formatDate = (date) => {
  if (!date) return 'Unknown date'
  return new Date(date).toLocaleDateString()
}

const loadProfile = async () => {
  loading.value = true

  try {
    const response = await api.get(`/profile/profile/${userId.value}`)
    profile.value = response.data?.data?.profile || null
  } catch (error) {
    console.error('Failed to load user profile', error)
  } finally {
    loading.value = false
  }
}

const openFollowersModal = async () => {
  showFollowersModal.value = true
  followersLoading.value = true

  try {
    const response = await api.get(`/subscrip/users/${userId.value}/followers`)
    followersUsers.value = response.data?.data?.followers || response.data?.data || []
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
    const response = await api.get(`/subscrip/users/${userId.value}/following`)
    followingUsers.value = response.data?.data?.following || response.data?.data || []
  } catch (error) {
    followingUsers.value = []
    console.error('Failed to load following', error)
  } finally {
    followingLoading.value = false
  }
}

onMounted(() => {
  loadProfile()
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
</style>
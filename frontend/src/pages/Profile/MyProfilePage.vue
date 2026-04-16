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
          <section class="relative h-[460px] w-full overflow-hidden bg-surface-container-lowest">
            <img
              :src="coverImage"
              alt="Banner Image"
              class="h-full w-full object-cover opacity-60"
            />

            <div class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-transparent"></div>

            <div class="absolute inset-0 flex flex-col items-center justify-center translate-y-12">
              <div class="group relative">
                <div class="absolute -inset-1 rounded-full bg-gradient-to-r from-primary to-tertiary blur opacity-40 transition duration-1000 group-hover:opacity-70 group-hover:duration-200"></div>
                <img
                  :src="avatarImage"
                  alt="Avatar"
                  class="relative h-32 w-32 rounded-full border-4 border-background object-cover md:h-40 md:w-40"
                />
              </div>

              <div class="mt-6 text-center">
                <h1 class="neon-glow font-headline text-4xl font-black tracking-tighter md:text-5xl">
                  {{ profile?.user?.name || 'My Profile' }}
                </h1>
                <p class="mt-1 font-medium text-on-surface-variant">
                  {{ profile?.user?.email || 'No email' }}
                </p>
                <span
                  class="mt-3 inline-block rounded-full bg-tertiary-container px-4 py-1 text-xs font-black uppercase tracking-[0.2em] text-on-tertiary-container"
                >
                  {{ profile?.user?.role || 'User' }}
                </span>
              </div>
            </div>

            <div class="absolute right-8 top-8">
              <button
                type="button"
                class="rounded-full border border-white/10 bg-white/10 px-6 py-2.5 font-semibold text-white backdrop-blur-md transition-all hover:bg-white/20 active:scale-95"
                @click="openEditModal"
              >
                Edit Profile
              </button>
            </div>
          </section>

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

      <div
        v-if="showEditModal"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/70 px-4 backdrop-blur-sm"
        @click.self="closeEditModal"
      >
        <div class="relative w-full max-w-3xl overflow-hidden rounded-lg border border-outline-variant/10 bg-surface-container shadow-[0_20px_40px_rgba(0,0,0,0.6)]">
          <div class="absolute left-1/4 top-0 h-[500px] w-[500px] rounded-full bg-primary/10 blur-[120px] pointer-events-none"></div>
          <div class="absolute bottom-0 right-1/4 h-[400px] w-[400px] rounded-full bg-secondary/10 blur-[100px] pointer-events-none"></div>

          <div class="relative px-8 pb-8 pt-8 space-y-8">
            <div class="flex items-center justify-between">
              <div>
                <h1 class="font-headline text-3xl font-extrabold -tracking-tight text-white">
                  Edit Profile
                </h1>
                <p class="mt-1 text-sm text-on-surface-variant">
                  Customize your digital identity on Hisoka Noir
                </p>
              </div>

              <button
                type="button"
                class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-bright/20 transition-colors hover:bg-surface-bright/40"
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
                  class="dashed-outline relative flex h-48 w-full cursor-pointer flex-col items-center justify-center overflow-hidden rounded-lg bg-surface-container-low transition-all duration-300 group-hover:bg-surface-container-high"
                >
                  <img
                    :src="editBackgroundPreview || coverImage"
                    alt="Cover preview"
                    class="absolute inset-0 h-full w-full object-cover opacity-30 transition-opacity group-hover:opacity-40"
                  />

                  <div class="relative z-10 flex flex-col items-center">
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
                <div class="group flex-shrink-0">
                  <label class="mb-3 block text-xs font-label uppercase tracking-widest text-on-surface-variant">
                    Update Avatar
                  </label>

                  <label
                    class="dashed-outline-circle relative flex h-32 w-32 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-full bg-surface-container-low transition-all duration-300 group-hover:bg-surface-container-high"
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
                  <div class="flex items-center justify-between">
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
                    class="w-full resize-none rounded-lg border-none bg-surface-container-low p-6 leading-relaxed text-white transition-all focus:ring-2 focus:ring-primary/40"
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
import { computed, onMounted, onBeforeUnmount, reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import TopNavbar from '@/components/layout/TopNavbar.vue'
import UsersListModal from '@/components/profile/UsersListModal.vue'

const sidebarCollapsed = ref(false)

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

const filteredVideos = computed(() => {
  const videos = profile.value?.videos_preview || []

  if (selectedCategory.value === 'all') {
    return videos
  }

  return videos
})

const totalPages = computed(() => {
  return Math.max(1, Math.ceil(filteredVideos.value.length / perPage))
})

const paginatedVideos = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredVideos.value.slice(start, start + perPage)
})

const buildStorageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http')) return path
  return `http://localhost:8000/storage/${path}`
}

const formatDate = (date) => {
  if (!date) return 'Unknown date'
  return new Date(date).toLocaleDateString()
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
    const userId = profile.value?.user?.id
    const response = await api.get(`/subscrip/users/${userId}/following`)
    followingUsers.value = response.data?.data?.following || response.data?.data || []
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
  loadProfile()
})

onBeforeUnmount(() => {
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
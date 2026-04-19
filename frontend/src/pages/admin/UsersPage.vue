<template>
  <div class="dark">
    <div class="min-h-screen bg-background text-on-background antialiased selection:bg-primary selection:text-on-primary">
      <TopNavbar @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed" />

      <div class="flex min-h-screen">
        <AdminSidebar :collapsed="sidebarCollapsed" />

        <main
          :class="[
            'min-h-screen flex-1 p-8 transition-all duration-300',
            sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'
          ]"
        >
          <div class="relative mb-10">
            <div class="pointer-events-none absolute -left-24 -top-24 h-96 w-96 rounded-full bg-primary/10 blur-[120px]"></div>

            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
              <div>
                <h1 class="mb-2 font-headline text-4xl font-extrabold tracking-tighter text-on-surface md:text-5xl">
                  Users Management
                </h1>
                <p class="max-w-2xl text-lg text-on-surface-variant">
                  Manage platform users, review profiles, and control banned accounts.
                </p>
              </div>

              <div class="flex w-full max-w-md items-center rounded-full border border-white/10 bg-surface-container px-4 py-3">
                <span class="material-symbols-outlined mr-3 text-on-surface-variant">search</span>
                <input
                  v-model="search"
                  type="text"
                  placeholder="Search by name or email..."
                  class="w-full border-none bg-transparent text-sm text-white placeholder:text-zinc-500 focus:ring-0"
                />
              </div>
            </div>
          </div>

          <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-3xl border border-white/5 bg-surface-container p-5">
              <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-zinc-500">
                Total Users
              </p>
              <h2 class="font-headline text-3xl font-black text-white">
                {{ meta.total }}
              </h2>
            </div>

            <div class="rounded-3xl border border-white/5 bg-surface-container p-5">
              <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-zinc-500">
                Active In Page
              </p>
              <h2 class="font-headline text-3xl font-black text-white">
                {{ activeUsersCount }}
              </h2>
            </div>

            <div class="rounded-3xl border border-white/5 bg-surface-container p-5">
              <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-zinc-500">
                Banned In Page
              </p>
              <h2 class="font-headline text-3xl font-black text-white">
                {{ bannedUsersCount }}
              </h2>
            </div>
          </div>

          <div class="overflow-hidden rounded-3xl border border-white/5 bg-surface-container shadow-xl">
            <div class="border-b border-white/5 px-6 py-5">
              <div class="flex items-center justify-between gap-4">
                <div>
                  <h2 class="font-headline text-2xl font-bold text-white">
                    Platform Users
                  </h2>
                  <p class="mt-1 text-sm text-on-surface-variant">
                    Review users and manage account restrictions.
                  </p>
                </div>

                <button
                  type="button"
                  class="rounded-full border border-white/10 px-4 py-2 text-xs font-bold uppercase tracking-widest text-zinc-300 transition hover:bg-white/5"
                  @click="loadUsers(meta.current_page)"
                >
                  Refresh
                </button>
              </div>
            </div>

            <div v-if="loading" class="divide-y divide-white/5">
              <div
                v-for="n in 8"
                :key="n"
                class="flex animate-pulse items-center gap-4 px-6 py-5"
              >
                <div class="h-12 w-12 rounded-full bg-surface-container-high"></div>
                <div class="flex-1">
                  <div class="mb-2 h-4 w-40 rounded bg-surface-container-high"></div>
                  <div class="h-3 w-56 rounded bg-surface-container-high"></div>
                </div>
                <div class="h-8 w-24 rounded-full bg-surface-container-high"></div>
                <div class="h-10 w-28 rounded-full bg-surface-container-high"></div>
              </div>
            </div>

            <div
              v-else-if="filteredUsers.length === 0"
              class="px-6 py-16 text-center"
            >
              <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-surface-container-high text-zinc-500">
                <span class="material-symbols-outlined text-3xl">group_off</span>
              </div>

              <h3 class="mb-2 font-headline text-2xl font-bold text-white">
                No users found
              </h3>

              <p class="text-on-surface-variant">
                Try another search or reload the page.
              </p>
            </div>

            <div v-else class="divide-y divide-white/5">
              <div
                v-for="user in filteredUsers"
                :key="user.id"
                class="flex flex-col gap-4 px-6 py-5 lg:flex-row lg:items-center lg:justify-between"
              >
                <div class="flex min-w-0 items-center gap-4">
                  <RouterLink :to="`/profile/${user.id}`" class="block">
                    <template v-if="avatarUrl(user)">
                      <img
                        :src="avatarUrl(user)"
                        :alt="user.name || 'User avatar'"
                        class="h-12 w-12 rounded-full border border-white/10 object-cover"
                      />
                    </template>

                    <template v-else>
                      <div class="flex h-12 w-12 items-center justify-center rounded-full border border-white/10 bg-zinc-900 text-sm font-bold uppercase text-white">
                        {{ getInitials(user.name) }}
                      </div>
                    </template>
                  </RouterLink>

                  <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                      <RouterLink
                        :to="`/profile/${user.id}`"
                        class="truncate font-headline text-lg font-bold text-white transition hover:text-primary"
                      >
                        {{ user.name }}
                      </RouterLink>

                      <span
                        class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest"
                        :class="user.is_banned
                          ? 'bg-error/10 text-error'
                          : 'bg-green-500/10 text-green-400'"
                      >
                        {{ user.is_banned ? 'Banned' : 'Active' }}
                      </span>

                      <span
                        class="rounded-full bg-primary/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-primary"
                      >
                        {{ user.role || 'user' }}
                      </span>
                    </div>

                    <p class="mt-1 truncate text-sm text-on-surface-variant">
                      {{ user.email }}
                    </p>

                    <p class="mt-1 text-xs text-zinc-500">
                      Joined {{ formatDate(user.created_at) }}
                    </p>
                  </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 lg:justify-end">
                  <RouterLink
                    :to="`/profile/${user.id}`"
                    class="rounded-full border border-white/10 px-4 py-2 text-xs font-bold uppercase tracking-widest text-zinc-300 transition hover:bg-white/5"
                  >
                    View Profile
                  </RouterLink>

                  <button
                    type="button"
                    :disabled="actionLoadingId === user.id"
                    class="rounded-full px-5 py-2 text-xs font-bold uppercase tracking-widest transition-all active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                    :class="user.is_banned
                      ? 'bg-green-500/10 text-green-400 hover:bg-green-500/20'
                      : 'bg-error/10 text-error hover:bg-error/20'"
                    @click="toggleBan(user)"
                  >
                    {{
                      actionLoadingId === user.id
                        ? 'Processing...'
                        : user.is_banned
                          ? 'Unban User'
                          : 'Ban User'
                    }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div
            v-if="errorMessage"
            class="mt-4 rounded-2xl border border-error/20 bg-error/10 px-4 py-3 text-sm text-error"
          >
            {{ errorMessage }}
          </div>

          <div
            v-if="successMessage"
            class="mt-4 rounded-2xl border border-green-500/20 bg-green-500/10 px-4 py-3 text-sm text-green-400"
          >
            {{ successMessage }}
          </div>

          <div v-if="meta.last_page > 1" class="mt-10 flex justify-center">
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
import AdminSidebar from '@/components/admin/AdminSidebar.vue'

const sidebarCollapsed = ref(false)

const loading = ref(false)
const users = ref([])
const search = ref('')
const actionLoadingId = ref(null)
const errorMessage = ref('')
const successMessage = ref('')

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

const avatarUrl = (user) => {
  const avatar = user?.profile?.avatar
  if (!avatar) return null
  return buildStorageUrl(avatar)
}

const getInitials = (name = 'User') => {
  return name
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase()
}

const formatDate = (date) => {
  if (!date) return 'Unknown date'
  return new Date(date).toLocaleDateString()
}

const filteredUsers = computed(() => {
  const keyword = search.value.trim().toLowerCase()

  if (!keyword) return users.value

  return users.value.filter((user) => {
    const name = user.name?.toLowerCase() || ''
    const email = user.email?.toLowerCase() || ''
    return name.includes(keyword) || email.includes(keyword)
  })
})

const activeUsersCount = computed(() => {
  return users.value.filter((user) => !user.is_banned).length
})

const bannedUsersCount = computed(() => {
  return users.value.filter((user) => user.is_banned).length
})

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

const loadUsers = async (page = 1) => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get(`/users?page=${page}`)

    users.value = normalizeCollection(response.data)
    meta.value = response.data?.meta || {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
    }
  } catch (error) {
    console.error('Failed to load users', error)
    console.error('Users error response:', error.response?.data)
    console.error('Users error status:', error.response?.status)

    users.value = []
    errorMessage.value =
      error.response?.data?.message || 'Failed to load users.'
  } finally {
    loading.value = false
  }
}

const toggleBan = async (user) => {
  actionLoadingId.value = user.id
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const endpoint = user.is_banned
      ? `/admin/users/${user.id}/unban`
      : `/admin/users/${user.id}/ban`

    const response = await api.patch(endpoint)
    const updatedUser = response.data?.data || null

    if (updatedUser) {
      users.value = users.value.map((item) =>
        Number(item.id) === Number(updatedUser.id) ? updatedUser : item
      )
    }

    successMessage.value = response.data?.message || 'User status updated successfully.'
  } catch (error) {
    console.error('Failed to update user status', error)
    errorMessage.value = error.response?.data?.message || 'Failed to update user status.'
  } finally {
    actionLoadingId.value = null
  }
}

const goToPage = async (page) => {
  if (page < 1 || page > meta.value.last_page) return
  await loadUsers(page)
}

onMounted(() => {
  loadUsers()
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
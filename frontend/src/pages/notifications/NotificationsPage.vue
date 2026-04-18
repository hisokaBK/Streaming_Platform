<template>
  <div class="dark">
    <div class="min-h-screen bg-background text-on-surface font-body selection:bg-primary/30">
      <TopNavbar @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed" />

      <div class="flex min-h-screen">
        <AppSidebar :collapsed="sidebarCollapsed" />

        <main
          :class="[
            'mx-auto w-full max-w-7xl px-6 pb-20 pt-32 transition-all duration-300 md:px-12',
            sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'
          ]"
        >
          <!-- Header -->
          <header class="mb-12 flex flex-col justify-between gap-6 md:flex-row md:items-end">
            <div>
              <h1 class="mb-2 font-headline text-5xl font-extrabold tracking-tighter text-on-surface">
                Notifications
              </h1>
              <p class="text-lg text-on-surface-variant">
                Stay updated with the latest live activity from streamers you follow.
              </p>
            </div>

            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2 rounded-full bg-surface-container-high px-4 py-2">
                <span
                  class="h-2 w-2 rounded-full bg-primary"
                  :class="unreadCount > 0 ? 'animate-pulse' : ''"
                ></span>
                <span class="text-sm font-bold tracking-tight text-on-surface">
                  {{ unreadCount }} UNREAD
                </span>
              </div>

              <button
                type="button"
                :disabled="markAllLoading || unreadCount === 0"
                class="rounded-full border border-primary/20 px-6 py-2.5 text-sm font-semibold text-primary transition-all hover:bg-primary/5 disabled:cursor-not-allowed disabled:opacity-50"
                @click="markAllAsRead"
              >
                {{ markAllLoading ? 'Marking...' : 'Mark all as read' }}
              </button>
            </div>
          </header>

          <p v-if="generalError" class="mb-6 text-sm text-error">
            {{ generalError }}
          </p>

          <!-- Notifications List -->
          <div class="space-y-4">
            <!-- Loading -->
            <div
              v-if="loading"
              v-for="n in 5"
              :key="`loading-${n}`"
              class="animate-pulse rounded-lg bg-surface-container-lowest/50 p-6"
            >
              <div class="mb-3 flex items-center gap-2">
                <div class="h-3 w-20 rounded-full bg-surface-container-high"></div>
                <div class="h-1 w-1 rounded-full bg-surface-container-high"></div>
                <div class="h-3 w-24 rounded-full bg-surface-container-high"></div>
              </div>
              <div class="mb-2 h-5 w-3/4 rounded-full bg-surface-container-high"></div>
              <div class="h-5 w-1/2 rounded-full bg-surface-container-high"></div>
            </div>

            <!-- Empty -->
            <div
              v-else-if="notifications.length === 0"
              class="rounded-3xl bg-surface-container-low p-12 text-center"
            >
              <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary">
                <span class="material-symbols-outlined text-3xl">notifications_off</span>
              </div>
              <h2 class="mb-2 font-headline text-2xl font-bold text-on-surface">
                No notifications yet
              </h2>
              <p class="text-on-surface-variant">
                When a streamer you follow goes live, you'll see it here.
              </p>
            </div>

            <!-- Items -->
            <div
              v-else
              v-for="notification in notifications"
              :key="notification.id"
              class="group relative transition-all duration-300"
              :class="notification.is_read
                ? 'rounded-lg bg-surface-container-lowest hover:bg-surface-container-low'
                : 'rounded-r-lg rounded-l-md border-l-4 border-primary bg-surface-container-low shadow-[0_10px_30px_rgba(246,128,255,0.05)] hover:bg-surface-container'"
            >
              <div class="flex items-start justify-between gap-4 p-6">
                <div class="flex flex-1 items-start gap-4">
                  <img
                    :src="getActorAvatar(notification)"
                    :alt="notification.actor?.name || 'Live actor'"
                    class="h-12 w-12 rounded-full border border-primary/20 object-cover"
                  />

                  <div class="flex-1">
                    <div class="mb-1 flex flex-wrap items-center gap-2">
                      <span
                        class="text-xs font-bold uppercase tracking-widest"
                        :class="notification.is_read ? 'text-on-surface-variant' : 'text-primary'"
                      >
                        {{ notification.is_read ? 'Live Update' : 'New Live' }}
                      </span>

                      <span class="h-1 w-1 rounded-full bg-outline-variant"></span>

                      <span class="text-xs text-on-surface-variant">
                        {{ formatNotificationDate(notification.created_at) }}
                      </span>
                    </div>

                    <RouterLink
                      v-if="notification.stream?.id"
                      :to="`/streams/${notification.stream.id}`"
                      class="block"
                    >
                      <p
                        class="text-lg leading-relaxed transition-colors"
                        :class="notification.is_read
                          ? 'font-normal text-on-surface-variant hover:text-on-surface'
                          : 'font-medium text-on-surface hover:text-primary'"
                      >
                        <span class="font-bold">
                          {{ notification.actor?.name || 'Someone' }}
                        </span>
                        is live now —
                        <span class="underline decoration-primary/40 underline-offset-4">
                          {{ notification.stream?.title || 'Open stream' }}
                        </span>
                      </p>
                    </RouterLink>

                    <p
                      v-else
                      class="text-lg leading-relaxed"
                      :class="notification.is_read
                        ? 'font-normal text-on-surface-variant'
                        : 'font-medium text-on-surface'"
                    >
                      {{ notification.title || notification.content }}
                    </p>

                    <p class="mt-2 text-sm text-on-surface-variant">
                      {{ notification.content }}
                    </p>
                  </div>
                </div>

                <button
                  v-if="!notification.is_read"
                  type="button"
                  :disabled="markOneLoadingId === notification.id"
                  class="shrink-0 translate-x-2 rounded-full bg-primary/10 px-4 py-2 text-xs font-bold text-on-surface transition-all duration-300 group-hover:translate-x-0 group-hover:bg-primary/20 disabled:cursor-not-allowed disabled:opacity-60"
                  @click="markOneAsRead(notification.id)"
                >
                  <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">done_all</span>
                    {{ markOneLoadingId === notification.id ? 'Loading...' : 'Mark as read' }}
                  </span>
                </button>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <footer
            v-if="!loading && meta.last_page > 1"
            class="mt-16 flex items-center justify-between"
          >
            <p class="text-sm text-on-surface-variant">
              Showing {{ notifications.length }} of {{ meta.total }} notifications
            </p>

            <div class="flex items-center gap-2">
              <button
                type="button"
                class="rounded-full p-2 text-on-surface-variant transition-all hover:bg-surface-container-high hover:text-on-surface disabled:opacity-40"
                :disabled="meta.current_page === 1"
                @click="goToPage(meta.current_page - 1)"
              >
                <span class="material-symbols-outlined">chevron_left</span>
              </button>

              <div class="flex items-center gap-1">
                <button
                  v-for="page in visiblePages"
                  :key="page"
                  type="button"
                  class="flex h-10 w-10 items-center justify-center rounded-full font-bold transition-all"
                  :class="page === meta.current_page
                    ? 'bg-primary text-on-primary'
                    : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                  @click="goToPage(page)"
                >
                  {{ page }}
                </button>
              </div>

              <button
                type="button"
                class="rounded-full p-2 text-on-surface-variant transition-all hover:bg-surface-container-high hover:text-on-surface disabled:opacity-40"
                :disabled="meta.current_page === meta.last_page"
                @click="goToPage(meta.current_page + 1)"
              >
                <span class="material-symbols-outlined">chevron_right</span>
              </button>
            </div>
          </footer>
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
import AppSidebar from '@/components/layout/AppSidebar.vue'

const sidebarCollapsed = ref(false)

const notifications = ref([])
const loading = ref(false)
const generalError = ref('')
const markAllLoading = ref(false)
const markOneLoadingId = ref(null)

const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
})

const buildStorageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http')) return path
  return `http://localhost:8000/storage/${path}`
}

const getActorAvatar = (notification) => {
  const avatar = notification.actor?.avatar
  const name = notification.actor?.name || 'User'

  if (avatar) {
    return buildStorageUrl(avatar)
  }

  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=111111&color=ffffff&size=256`
}

const unreadCount = computed(() => {
  return notifications.value.filter((item) => !item.is_read).length
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

const formatNotificationDate = (value) => {
  if (!value) return 'Unknown time'

  const date = new Date(value)
  return date.toLocaleString()
}

const loadNotifications = async (page = 1) => {
  loading.value = true
  generalError.value = ''

  try {
    const response = await api.get(`/notification/notifications?page=${page}`)

    notifications.value = response.data?.data?.data || response.data?.data || []
    meta.value = response.data?.meta || {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: notifications.value.length,
    }
  } catch (error) {
    console.error('Failed to load notifications', error)
    notifications.value = []
    generalError.value =
      error.response?.data?.message || 'Failed to load notifications.'
  } finally {
    loading.value = false
  }
}

const markOneAsRead = async (notificationId) => {
  markOneLoadingId.value = notificationId

  try {
    const response = await api.patch(`/notification/notifications/${notificationId}/read`)
    const updated = response.data?.data

    notifications.value = notifications.value.map((item) => {
      if (item.id === notificationId) {
        return {
          ...item,
          ...(updated || {}),
          is_read: true,
        }
      }

      return item
    })
  } catch (error) {
    console.error('Failed to mark notification as read', error)
  } finally {
    markOneLoadingId.value = null
  }
}

const markAllAsRead = async () => {
  if (unreadCount.value === 0) return

  markAllLoading.value = true

  try {
    await api.patch('/notification/notifications/read-all')

    notifications.value = notifications.value.map((item) => ({
      ...item,
      is_read: true,
    }))
  } catch (error) {
    console.error('Failed to mark all notifications as read', error)
  } finally {
    markAllLoading.value = false
  }
}

const goToPage = async (page) => {
  if (page < 1 || page > meta.value.last_page) return
  await loadNotifications(page)
}

onMounted(() => {
  loadNotifications()
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
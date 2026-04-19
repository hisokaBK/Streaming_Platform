<template>
  <div class="dark">
    <div class="min-h-screen overflow-x-hidden bg-background text-on-surface font-body selection:bg-primary/30">
      <TopNavbar @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed" />

      <div class="flex min-h-screen w-full">
        <AppSidebar :collapsed="sidebarCollapsed" />

        <main
          :class="[
            'min-w-0 flex-1 px-4 pb-20 pt-28 transition-all duration-300 sm:px-6 md:px-8 lg:px-12',
            sidebarCollapsed
              ? 'ml-20'
              : 'ml-64'
          ]"
        >
          <div class="mx-auto w-full max-w-7xl">
            <!-- Header -->
            <header class="mb-8 flex flex-col gap-5 md:mb-12 md:flex-row md:items-end md:justify-between">
              <div class="min-w-0">
                <h1 class="mb-2 font-headline text-3xl font-extrabold tracking-tighter text-on-surface sm:text-4xl lg:text-5xl">
                  Notifications
                </h1>
                <p class="text-sm text-on-surface-variant sm:text-base lg:text-lg">
                  Stay updated with the latest live activity from streamers you follow.
                </p>
              </div>

              <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-start md:justify-end">
                <div class="flex items-center justify-center gap-2 rounded-full bg-surface-container-high px-4 py-2 sm:justify-start">
                  <span
                    class="h-2 w-2 rounded-full bg-primary"
                    :class="unreadCount > 0 ? 'animate-pulse' : ''"
                  ></span>
                  <span class="text-xs font-bold tracking-tight text-on-surface sm:text-sm">
                    {{ unreadCount }} UNREAD
                  </span>
                </div>

                <button
                  type="button"
                  :disabled="markAllLoading || unreadCount === 0"
                  class="w-full rounded-full border border-primary/20 px-4 py-2.5 text-sm font-semibold text-primary transition-all hover:bg-primary/5 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto sm:px-6"
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
                class="animate-pulse rounded-lg bg-surface-container-lowest/50 p-4 sm:p-5 md:p-6"
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
                class="rounded-3xl bg-surface-container-low p-8 text-center sm:p-10 md:p-12"
              >
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-primary sm:h-16 sm:w-16">
                  <span class="material-symbols-outlined text-3xl">notifications_off</span>
                </div>
                <h2 class="mb-2 font-headline text-xl font-bold text-on-surface sm:text-2xl">
                  No notifications yet
                </h2>
                <p class="text-sm text-on-surface-variant sm:text-base">
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
                <div class="flex flex-col gap-4 p-4 sm:p-5 md:flex-row md:items-start md:justify-between md:p-6">
                  <div class="flex min-w-0 flex-1 items-start gap-3 sm:gap-4">
                    <img
                      :src="getActorAvatar(notification)"
                      :alt="notification.actor?.name || 'Live actor'"
                      class="h-10 w-10 shrink-0 rounded-full border border-primary/20 object-cover sm:h-12 sm:w-12"
                    />

                    <div class="min-w-0 flex-1">
                      <div class="mb-1 flex flex-wrap items-center gap-2">
                        <span
                          class="text-[10px] font-bold uppercase tracking-widest sm:text-xs"
                          :class="notification.is_read ? 'text-on-surface-variant' : 'text-primary'"
                        >
                          {{ notification.is_read ? 'Live Update' : 'New Live' }}
                        </span>

                        <span class="h-1 w-1 rounded-full bg-outline-variant"></span>

                        <span class="text-[11px] text-on-surface-variant sm:text-xs">
                          {{ formatNotificationDate(notification.created_at) }}
                        </span>
                      </div>

                      <RouterLink
                        v-if="notification.stream?.id"
                        :to="`/streams/${notification.stream.id}`"
                        class="block min-w-0"
                      >
                        <p
                          class="break-words text-base leading-relaxed transition-colors sm:text-lg"
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
                        class="break-words text-base leading-relaxed sm:text-lg"
                        :class="notification.is_read
                          ? 'font-normal text-on-surface-variant'
                          : 'font-medium text-on-surface'"
                      >
                        {{ notification.title || notification.content }}
                      </p>

                      <p class="mt-2 break-words text-sm text-on-surface-variant">
                        {{ notification.content }}
                      </p>
                    </div>
                  </div>

                  <button
                    v-if="!notification.is_read"
                    type="button"
                    :disabled="markOneLoadingId === notification.id"
                    class="w-full shrink-0 rounded-full bg-primary/10 px-4 py-2 text-xs font-bold text-on-surface transition-all duration-300 hover:bg-primary/20 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto md:translate-x-2 md:group-hover:translate-x-0"
                    @click="markOneAsRead(notification.id)"
                  >
                    <span class="flex items-center justify-center gap-2">
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
              class="mt-10 flex flex-col gap-4 sm:mt-12 md:mt-16 md:flex-row md:items-center md:justify-between"
            >
              <p class="text-center text-sm text-on-surface-variant md:text-left">
                Showing {{ notifications.length }} of {{ meta.total }} notifications
              </p>

              <div class="flex flex-wrap items-center justify-center gap-2 md:justify-end">
                <button
                  type="button"
                  class="rounded-full p-2 text-on-surface-variant transition-all hover:bg-surface-container-high hover:text-on-surface disabled:opacity-40"
                  :disabled="meta.current_page === 1"
                  @click="goToPage(meta.current_page - 1)"
                >
                  <span class="material-symbols-outlined">chevron_left</span>
                </button>

                <div class="flex flex-wrap items-center justify-center gap-1">
                  <button
                    v-for="page in visiblePages"
                    :key="page"
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold transition-all sm:h-10 sm:w-10"
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
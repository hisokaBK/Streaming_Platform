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
          <div class="relative mb-12">
            <div class="pointer-events-none absolute -left-24 -top-24 h-96 w-96 rounded-full bg-primary/10 blur-[120px]"></div>

            <h1 class="mb-2 font-headline text-4xl font-extrabold tracking-tighter text-on-surface md:text-5xl">
              Admin Dashboard
            </h1>
            <p class="max-w-2xl text-lg text-on-surface-variant">
              Real-time platform overview and engagement metrics.
            </p>
          </div>

          <div v-if="loading" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div
              v-for="n in 8"
              :key="n"
              class="rounded-lg border border-white/5 bg-surface-container p-6 animate-pulse"
            >
              <div class="mb-4 h-12 w-12 rounded-full bg-surface-container-high"></div>
              <div class="mb-2 h-3 w-24 rounded bg-surface-container-high"></div>
              <div class="h-8 w-20 rounded bg-surface-container-high"></div>
            </div>
          </div>

          <template v-else>
            <div class="mb-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
              <div
                v-for="card in statCards"
                :key="card.key"
                class="group relative overflow-hidden rounded-lg border border-white/5 bg-surface-container p-6 transition-all duration-500 hover:bg-surface-container-high"
              >
                <div
                  class="absolute right-0 top-0 h-32 w-32 translate-x-10 -translate-y-10 rounded-full blur-3xl transition-colors group-hover:opacity-100"
                  :class="card.blurClass"
                ></div>

                <div class="relative z-10">
                  <div class="mb-4 flex items-start justify-between">
                    <div :class="['rounded-full p-3', card.iconWrapperClass]">
                      <span
                        class="material-symbols-outlined"
                        style="font-variation-settings: 'FILL' 1;"
                      >
                        {{ card.icon }}
                      </span>
                    </div>

                    <span
                      v-if="card.badge"
                      :class="card.badgeClass"
                      class="rounded-full px-3 py-1 text-xs font-bold"
                    >
                      {{ card.badge }}
                    </span>
                  </div>

                  <p class="mb-1 text-sm font-medium uppercase tracking-widest text-on-surface-variant">
                    {{ card.label }}
                  </p>

                  <h3 class="font-headline text-3xl font-black text-white">
                    {{ formatCompact(card.value) }}
                  </h3>
                </div>
              </div>
            </div>

            <div class="space-y-6">
              <div class="mb-2 flex items-center justify-between">
                <h2 class="font-headline text-2xl font-bold text-white">
                  Platform Highlights
                </h2>

                <RouterLink
                  to="/admin/users"
                  class="text-sm font-bold text-primary hover:underline"
                >
                  View users
                </RouterLink>
              </div>

              <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <div
                  v-for="item in topStatCards"
                  :key="item.key"
                  class="rounded-lg border border-white/5 bg-surface-container-low p-8"
                >
                  <template v-if="item.data">
                    <div class="mb-2 flex items-center gap-2">
                      <span
                        :class="item.tagClass"
                        class="rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-tighter"
                      >
                        {{ item.label }}
                      </span>
                    </div>

                    <RouterLink
                      :to="item.targetLink(item.data)"
                      class="mb-2 block text-xl font-bold text-white transition hover:text-primary"
                    >
                      {{ item.data.title }}
                    </RouterLink>

                    <RouterLink
                      v-if="item.data.user?.id"
                      :to="`/profile/${item.data.user.id}`"
                      class="mb-3 block text-sm font-medium text-on-surface-variant transition hover:text-primary"
                    >
                      {{ item.data.user?.name || 'Unknown creator' }}
                    </RouterLink>

                    <div class="flex items-center gap-4 text-sm text-zinc-500">
                      <span class="flex items-center gap-1">
                        <span
                          class="material-symbols-outlined text-xs"
                          :class="item.iconClass"
                          style="font-variation-settings: 'FILL' 1;"
                        >
                          {{ item.metricIcon }}
                        </span>
                        {{ formatCompact(item.metricValue(item.data)) }} {{ item.metricLabel }}
                      </span>

                      <RouterLink
                        :to="item.targetLink(item.data)"
                        class="ml-auto text-primary transition hover:underline"
                      >
                        Open
                      </RouterLink>
                    </div>
                  </template>

                  <template v-else>
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                      <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full border border-zinc-800 bg-zinc-900 text-zinc-700">
                        <span class="material-symbols-outlined text-3xl">inbox_customize</span>
                      </div>

                      <p class="mb-1 text-[10px] uppercase tracking-[0.2em] text-zinc-600">
                        {{ item.label }}
                      </p>

                      <h4 class="font-medium italic text-zinc-500">
                        No data available
                      </h4>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </template>
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

const statistics = ref({
  users: {
    total_users: 0,
    banned_users: 0,
    active_users: 0,
  },
  streams: {
    total_streams: 0,
    live_streams: 0,
  },
  videos: {
    total_videos: 0,
  },
  engagement: {
    total_comments: 0,
    total_reactions: 0,
  },
  top_stats: {
    most_reacted_stream: null,
    most_commented_stream: null,
    most_reacted_video: null,
    most_commented_video: null,
  },
})

const loadStatistics = async () => {
  loading.value = true

  try {
    const response = await api.get('/admin/statistics')
    statistics.value = response.data?.data || statistics.value
  } catch (error) {
    console.error('Failed to load admin statistics', error)
  } finally {
    loading.value = false
  }
}

const formatCompact = (value) => {
  const number = Number(value || 0)

  if (number >= 1000000) {
    return `${(number / 1000000).toFixed(1)}M`
  }

  if (number >= 1000) {
    return `${(number / 1000).toFixed(1)}k`
  }

  return `${number}`
}

const statCards = computed(() => [
  {
    key: 'total_users',
    label: 'Total Users',
    value: statistics.value.users?.total_users,
    icon: 'group',
    iconWrapperClass: 'bg-primary/10 text-primary',
    blurClass: 'bg-primary/5 group-hover:bg-primary/10',
  },
  {
    key: 'active_users',
    label: 'Active Users',
    value: statistics.value.users?.active_users,
    icon: 'bolt',
    iconWrapperClass: 'bg-secondary/10 text-secondary',
    blurClass: 'bg-secondary/5 group-hover:bg-secondary/10',
    badge: 'ACTIVE',
    badgeClass: 'bg-green-400/10 text-green-400',
  },
  {
    key: 'banned_users',
    label: 'Banned Users',
    value: statistics.value.users?.banned_users,
    icon: 'block',
    iconWrapperClass: 'bg-error-container/20 text-error',
    blurClass: 'bg-error/5 group-hover:bg-error/10',
  },
  {
    key: 'total_streams',
    label: 'Total Streams',
    value: statistics.value.streams?.total_streams,
    icon: 'video_library',
    iconWrapperClass: 'bg-tertiary-container/20 text-tertiary',
    blurClass: 'bg-tertiary/5 group-hover:bg-tertiary/10',
  },
  {
    key: 'live_streams',
    label: 'Live Streams',
    value: statistics.value.streams?.live_streams,
    icon: 'sensors',
    iconWrapperClass: 'bg-primary/10 text-primary',
    blurClass: 'bg-primary/5 group-hover:bg-primary/10',
    badge: 'LIVE',
    badgeClass: 'border border-primary/20 bg-primary/10 text-primary animate-pulse',
  },
  {
    key: 'total_videos',
    label: 'Total Videos',
    value: statistics.value.videos?.total_videos,
    icon: 'movie',
    iconWrapperClass: 'bg-secondary/10 text-secondary',
    blurClass: 'bg-secondary/5 group-hover:bg-secondary/10',
  },
  {
    key: 'total_comments',
    label: 'Total Comments',
    value: statistics.value.engagement?.total_comments,
    icon: 'forum',
    iconWrapperClass: 'bg-tertiary/10 text-tertiary',
    blurClass: 'bg-tertiary/5 group-hover:bg-tertiary/10',
  },
  {
    key: 'total_reactions',
    label: 'Total Reactions',
    value: statistics.value.engagement?.total_reactions,
    icon: 'favorite',
    iconWrapperClass: 'bg-primary-fixed-dim/20 text-primary-fixed-dim',
    blurClass: 'bg-primary-fixed-dim/5 group-hover:bg-primary-fixed-dim/10',
  },
])

const topStatCards = computed(() => [
  {
    key: 'most_reacted_stream',
    label: 'Most Reacted Stream',
    data: statistics.value.top_stats?.most_reacted_stream,
    metricLabel: 'Reactions',
    metricIcon: 'favorite',
    iconClass: 'text-primary',
    tagClass: 'bg-primary/10 text-primary',
    metricValue: (data) => data?.reactions_count || 0,
    targetLink: (data) => `/streams/${data.id}`,
  },
  {
    key: 'most_commented_stream',
    label: 'Most Commented Stream',
    data: statistics.value.top_stats?.most_commented_stream,
    metricLabel: 'Comments',
    metricIcon: 'forum',
    iconClass: 'text-secondary',
    tagClass: 'bg-secondary/10 text-secondary',
    metricValue: (data) => data?.comments_count || 0,
    targetLink: (data) => `/streams/${data.id}`,
  },
  {
    key: 'most_reacted_video',
    label: 'Most Reacted Video',
    data: statistics.value.top_stats?.most_reacted_video,
    metricLabel: 'Reactions',
    metricIcon: 'favorite',
    iconClass: 'text-primary',
    tagClass: 'bg-primary/10 text-primary',
    metricValue: (data) => data?.reactions_count || 0,
    targetLink: (data) => `/videos/${data.id}`,
  },
  {
    key: 'most_commented_video',
    label: 'Most Commented Video',
    data: statistics.value.top_stats?.most_commented_video,
    metricLabel: 'Comments',
    metricIcon: 'forum',
    iconClass: 'text-tertiary',
    tagClass: 'bg-tertiary/10 text-tertiary',
    metricValue: (data) => data?.comments_count || 0,
    targetLink: (data) => `/videos/${data.id}`,
  },
])

onMounted(() => {
  loadStatistics()
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
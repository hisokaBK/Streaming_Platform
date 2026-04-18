<template>
  <div class="dark">
    <div class="min-h-screen bg-background font-body text-on-surface selection:bg-primary/30">
      <TopNavbar @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed" />

      <div class="flex min-h-screen">
        <AppSidebar :collapsed="sidebarCollapsed" />

        <main
          :class="[
            'min-h-screen flex-1 pb-24 pt-20 transition-all duration-300',
            sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'
          ]"
        >
          <div class="mx-auto max-w-6xl p-8 lg:p-12">
            <div v-if="pageLoading" class="space-y-6">
              <div class="h-12 w-72 animate-pulse rounded bg-surface-container-high"></div>
              <div class="h-6 w-full animate-pulse rounded bg-surface-container-high"></div>
              <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <div class="space-y-6 lg:col-span-8">
                  <div class="h-96 animate-pulse rounded-lg bg-surface-container-high"></div>
                </div>
                <div class="space-y-6 lg:col-span-4">
                  <div class="h-80 animate-pulse rounded-lg bg-surface-container-high"></div>
                </div>
              </div>
            </div>

            <template v-else>
              <div class="mb-12">
                <h1 class="font-headline text-4xl font-black tracking-tight text-on-surface lg:text-5xl">
                  Stream Settings
                </h1>
                <p class="max-w-2xl text-on-surface-variant">
                  Update your stream details and categories. Changes take effect immediately on your live feed.
                </p>
              </div>

              <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <div class="space-y-8 lg:col-span-8">
                  <section class="rounded-lg border border-outline-variant/15 bg-surface-container p-8 lg:p-10">
                    <form class="space-y-8" @submit.prevent="handleUpdateStream">
                      <div>
                        <label class="mb-4 block font-headline text-[10px] font-bold uppercase tracking-[0.2em] text-fuchsia-500">
                          Stream Title
                        </label>
                        <input
                          v-model="form.title"
                          type="text"
                          class="w-full rounded-lg border-none bg-surface-container-low p-4 font-medium text-on-surface outline-none ring-1 ring-white/5 transition-all focus:bg-surface-container-high focus:ring-primary/50"
                          placeholder="Enter stream title"
                        />
                        <p v-if="errors.title" class="mt-2 text-sm text-error">
                          {{ errors.title[0] }}
                        </p>
                      </div>

                      <div>
                        <label class="mb-4 block font-headline text-[10px] font-bold uppercase tracking-[0.2em] text-fuchsia-500">
                          Description
                        </label>
                        <textarea
                          v-model="form.description"
                          rows="6"
                          class="w-full rounded-lg border-none bg-surface-container-low p-4 font-medium leading-relaxed text-on-surface outline-none ring-1 ring-white/5 transition-all focus:bg-surface-container-high focus:ring-primary/50"
                          placeholder="Enter stream description"
                        ></textarea>
                        <p v-if="errors.description" class="mt-2 text-sm text-error">
                          {{ errors.description[0] }}
                        </p>
                      </div>

                      <div>
                        <label class="mb-4 block font-headline text-[10px] font-bold uppercase tracking-[0.2em] text-fuchsia-500">
                          Categories
                        </label>

                        <div v-if="categoriesLoading" class="flex flex-wrap gap-3">
                          <div
                            v-for="n in 5"
                            :key="n"
                            class="h-10 w-28 animate-pulse rounded-full bg-surface-container-high"
                          ></div>
                        </div>

                        <div v-else-if="categories.length > 0" class="flex flex-wrap gap-3">
                          <button
                            v-for="category in categories"
                            :key="category.id"
                            type="button"
                            class="rounded-full px-4 py-2 text-xs font-bold transition-all"
                            :class="isSelectedCategory(category.id)
                              ? 'border border-primary/30 bg-primary/20 text-primary'
                              : 'bg-surface-container-highest text-on-surface hover:bg-surface-bright'"
                            @click="toggleCategory(category.id)"
                          >
                            {{ category.name }}
                          </button>
                        </div>

                        <div
                          v-else
                          class="rounded-2xl bg-surface-container-low p-4 text-sm text-on-surface-variant"
                        >
                          No categories available.
                        </div>

                        <p v-if="errors.category_ids" class="mt-2 text-sm text-error">
                          {{ errors.category_ids[0] }}
                        </p>
                      </div>

                      <div v-if="selectedCategoriesPreview.length > 0">
                        <label class="mb-4 block font-headline text-[10px] font-bold uppercase tracking-[0.2em] text-fuchsia-500">
                          Current Categories
                        </label>

                        <div class="flex flex-wrap gap-3">
                          <div
                            v-for="category in selectedCategoriesPreview"
                            :key="category.id"
                            class="flex items-center gap-2 rounded-full bg-surface-container-highest px-4 py-2 text-xs font-bold"
                          >
                            {{ category.name }}
                          </div>
                        </div>
                      </div>

                      <p v-if="generalError" class="text-sm text-error">
                        {{ generalError }}
                      </p>

                      <p v-if="successMessage" class="text-sm text-green-400">
                        {{ successMessage }}
                      </p>
                    </form>
                  </section>
                </div>

                <div class="space-y-8 lg:col-span-4">
                  <div class="overflow-hidden rounded-lg border border-outline-variant/15 bg-surface-container">
                    <div class="flex items-center justify-between border-b border-outline-variant/15 bg-surface-container-high/50 p-6">
                      <h3 class="font-headline text-[10px] font-bold uppercase tracking-[0.2em] text-on-surface-variant">
                        Stream Status
                      </h3>

                      <div
                        class="flex items-center gap-2 rounded-full px-3 py-1"
                        :class="stream?.status === 'live'
                          ? 'animate-pulse bg-tertiary-container/20'
                          : 'bg-surface-container-high'"
                      >
                        <span
                          class="h-2 w-2 rounded-full"
                          :class="stream?.status === 'live' ? 'bg-tertiary' : 'bg-zinc-500'"
                        ></span>
                        <span
                          class="text-[10px] font-bold uppercase"
                          :class="stream?.status === 'live' ? 'text-tertiary' : 'text-zinc-400'"
                        >
                          {{ stream?.status || 'unknown' }}
                        </span>
                      </div>
                    </div>

                    <div class="space-y-6 p-6">
                      <div class="group relative aspect-video w-full overflow-hidden rounded-lg bg-black">
                        <img
                          alt="Stream Preview"
                          class="h-full w-full object-cover opacity-60 transition-transform duration-700 group-hover:scale-110"
                          src="https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=1200&auto=format&fit=crop"
                        />
                        <div class="absolute inset-0 flex items-center justify-center">
                          <span class="material-symbols-outlined text-4xl text-white/50 transition-colors group-hover:text-primary">
                            play_circle
                          </span>
                        </div>
                      </div>

                      <div class="space-y-4">
                        <div class="flex justify-between text-xs">
                          <span class="text-on-surface-variant">Started At</span>
                          <span class="font-mono text-on-surface">{{ formatDateTime(stream?.started_at) }}</span>
                        </div>

                        <div class="flex justify-between text-xs">
                          <span class="text-on-surface-variant">Viewers</span>
                          <span class="font-mono text-on-surface">{{ stream?.current_viewers ?? 0 }}</span>
                        </div>

                        <div class="flex justify-between text-xs">
                          <span class="text-on-surface-variant">Comments</span>
                          <span class="font-mono text-on-surface">{{ stream?.comments_count ?? 0 }}</span>
                        </div>

                        <div class="flex justify-between text-xs">
                          <span class="text-on-surface-variant">Reactions</span>
                          <span class="font-mono text-on-surface">{{ stream?.reactions_count ?? 0 }}</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="space-y-4">
                    <button
                      type="button"
                      :disabled="updateLoading"
                      class="flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-primary to-secondary py-4 font-bold tracking-tight text-on-primary-fixed transition-all hover:shadow-[0_0_30px_rgba(246,128,255,0.4)] disabled:cursor-not-allowed disabled:opacity-60"
                      @click="handleUpdateStream"
                    >
                      <span class="material-symbols-outlined">save</span>
                      {{ updateLoading ? 'Saving...' : 'Save Changes' }}
                    </button>

                    <button
                      type="button"
                      :disabled="endLoading || stream?.status === 'ended'"
                      class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-error/30 py-4 font-bold tracking-tight text-error transition-all hover:bg-error/10 hover:border-error disabled:cursor-not-allowed disabled:opacity-50"
                      @click="handleEndStream"
                    >
                      <span class="material-symbols-outlined">power_settings_new</span>
                      {{ endLoading ? 'Ending...' : 'End Stream' }}
                    </button>
                  </div>
                </div>
              </div>

              <div
                v-if="stream?.status === 'ended'"
                class="mt-12 flex items-center gap-6 rounded-lg border-l-4 border-zinc-500 bg-surface-container-highest p-8"
              >
                <span class="material-symbols-outlined text-4xl text-zinc-500">lock</span>
                <div>
                  <h3 class="text-xl font-bold">Stream Ended</h3>
                  <p class="text-sm text-on-surface-variant">
                    This broadcast has concluded. Settings are now read-only.
                  </p>
                </div>
              </div>
            </template>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import TopNavbar from '@/components/layout/TopNavbar.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'

const route = useRoute()
const router = useRouter()

const sidebarCollapsed = ref(false)
const categories = ref([])
const stream = ref(null)

const categoriesLoading = ref(false)
const pageLoading = ref(false)
const updateLoading = ref(false)
const endLoading = ref(false)

const generalError = ref('')
const successMessage = ref('')
const errors = ref({})

const form = reactive({
  title: '',
  description: '',
  category_ids: [],
})

const normalizeCategories = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.categories)) return payload.categories
  if (Array.isArray(payload?.data?.categories)) return payload.data.categories
  return []
}

const selectedCategoriesPreview = computed(() => {
  return categories.value.filter((category) =>
    form.category_ids.includes(Number(category.id))
  )
})

const loadCategories = async () => {
  categoriesLoading.value = true

  try {
    const response = await api.get('/categories')
    const normalized = normalizeCategories(response.data)

    categories.value = normalized.map((category) => ({
      id: Number(category.id),
      name: category.name,
    }))
  } catch (error) {
    console.error('Failed to load categories', error)
    categories.value = []
  } finally {
    categoriesLoading.value = false
  }
}

const loadStream = async () => {
  pageLoading.value = true
  generalError.value = ''

  try {
    const response = await api.get(`/stream/streams/${route.params.id}/edit`)
    stream.value = response.data?.data || null

    form.title = stream.value?.title || ''
    form.description = stream.value?.description || ''
    form.category_ids = (stream.value?.categories || []).map((category) => Number(category.id))
  } catch (error) {
    console.error('Failed to load edit stream data', error)

    if ([401, 403, 404].includes(error.response?.status)) {
      router.replace('/streams')
      return
    }

    generalError.value = error.response?.data?.message || 'Failed to load stream.'
  } finally {
    pageLoading.value = false
  }
}

const isSelectedCategory = (categoryId) => {
  return form.category_ids.includes(Number(categoryId))
}

const toggleCategory = (categoryId) => {
  if (stream.value?.status === 'ended') return

  const id = Number(categoryId)

  if (form.category_ids.includes(id)) {
    form.category_ids = form.category_ids.filter((item) => item !== id)
  } else {
    form.category_ids = [...form.category_ids, id]
  }
}

const formatDateTime = (value) => {
  if (!value) return '--'
  return new Date(value).toLocaleString()
}

const handleUpdateStream = async () => {
  if (stream.value?.status === 'ended') return

  updateLoading.value = true
  generalError.value = ''
  successMessage.value = ''
  errors.value = {}

  try {
    const payload = {
      title: form.title.trim(),
      description: form.description.trim(),
      category_ids: form.category_ids.map((id) => Number(id)),
    }

    const response = await api.put(`/stream/streams/${route.params.id}`, payload)

    stream.value = response.data?.data || stream.value
    successMessage.value = response.data?.message || 'Stream updated successfully.'

    form.title = stream.value?.title || ''
    form.description = stream.value?.description || ''
    form.category_ids = (stream.value?.categories || []).map((category) => Number(category.id))
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else if ([401, 403, 404].includes(error.response?.status)) {
      router.replace('/streams')
    } else {
      generalError.value = error.response?.data?.message || 'Failed to update stream.'
    }
  } finally {
    updateLoading.value = false
  }
}

const handleEndStream = async () => {
  if (stream.value?.status === 'ended') return

  endLoading.value = true
  generalError.value = ''
  successMessage.value = ''

  try {
    const response = await api.patch(`/stream/streams/${route.params.id}/end`)

    stream.value = response.data?.data || stream.value
    successMessage.value = response.data?.message || 'Stream ended successfully.'
  } catch (error) {
    if ([401, 403, 404].includes(error.response?.status)) {
      router.replace('/streams')
    } else {
      generalError.value = error.response?.data?.message || 'Failed to end stream.'
    }
  } finally {
    endLoading.value = false
  }
}

onMounted(async () => {
  await Promise.all([loadCategories(), loadStream()])
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
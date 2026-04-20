<template>
  <div class="dark">
    <div class="min-h-screen bg-background text-on-background selection:bg-primary/30">
      <TopNavbar @toggle-sidebar="handleSidebarToggle" />

      <div class="flex min-h-screen">
        <AppSidebar
          :collapsed="sidebarCollapsed"
          :mobile-open="mobileSidebarOpen"
          @close="mobileSidebarOpen = false"
        />

        <main
          :class="[
            'min-w-0 min-h-screen flex-1 pb-24 pt-2 transition-all duration-300 pt-[72px]',
            sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'
          ]"
        >
          <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
            <div class="relative mb-10 lg:mb-12">
              <div class="absolute -left-12 -top-12 h-64 w-64 rounded-full bg-primary/10 blur-[100px]"></div>

              <h1 class="relative z-10 mb-2 font-headline text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl">
                Start Your
                <span class="bg-gradient-to-r from-primary via-secondary to-tertiary bg-clip-text text-transparent">
                  Broadcast
                </span>
              </h1>

              <p class="max-w-xl text-base font-medium text-on-surface-variant lg:text-lg">
                Configure your stream details before going live to your audience.
              </p>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
              <div class="space-y-8 lg:col-span-2">
                <section class="rounded-lg bg-surface-container p-5 shadow-xl sm:p-6 lg:p-8">
                  <form class="space-y-6" @submit.prevent="handleCreateStream">
                    <div class="space-y-2">
                      <label class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                        Stream Title
                      </label>

                      <input
                        v-model="form.title"
                        type="text"
                        placeholder="e.g., Late Night Cyberpunk Sessions"
                        class="w-full rounded-xl border-none bg-surface-container-low px-4 py-4 text-base font-semibold text-on-surface placeholder:text-zinc-700 transition-all focus:ring-2 focus:ring-primary/50 sm:px-6 sm:text-lg"
                      />

                      <p v-if="errors.title" class="text-sm text-error">
                        {{ errors.title[0] }}
                      </p>
                    </div>

                    <div class="space-y-2">
                      <label class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                        Description
                      </label>

                      <textarea
                        v-model="form.description"
                        rows="5"
                        placeholder="Tell your viewers what's happening tonight..."
                        class="w-full resize-none rounded-xl border-none bg-surface-container-low px-4 py-4 text-on-surface placeholder:text-zinc-700 transition-all focus:ring-2 focus:ring-primary/50 sm:px-6"
                      ></textarea>

                      <p v-if="errors.description" class="text-sm text-error">
                        {{ errors.description[0] }}
                      </p>
                    </div>

                    <div class="space-y-4">
                      <div class="flex items-center justify-between gap-3">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                          Categories
                        </label>

                        <button
                          type="button"
                          class="shrink-0 text-xs font-bold text-primary transition hover:text-primary-dim"
                          @click="loadCategories"
                        >
                          Refresh
                        </button>
                      </div>

                      <div v-if="categoriesLoading" class="flex flex-wrap gap-2">
                        <div
                          v-for="n in 6"
                          :key="n"
                          class="h-10 w-28 animate-pulse rounded-full bg-surface-container-high"
                        ></div>
                      </div>

                      <div
                        v-else-if="categories.length > 0"
                        class="flex flex-wrap gap-2"
                      >
                        <button
                          v-for="category in categories"
                          :key="category.id"
                          type="button"
                          class="rounded-full px-5 py-2 text-xs font-bold transition-all"
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
                        class="rounded-2xl bg-surface-container-low p-5 text-sm text-on-surface-variant"
                      >
                        No categories found.
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

                      <p v-if="errors.category_ids" class="text-sm text-error">
                        {{ errors.category_ids[0] }}
                      </p>
                    </div>

                    <div
                      v-if="generalError"
                      class="rounded-2xl border border-error/20 bg-error/10 px-4 py-3 text-sm text-error"
                    >
                      {{ generalError }}
                    </div>

                    <p v-if="successMessage" class="text-sm text-green-400">
                      {{ successMessage }}
                    </p>

                    <div class="space-y-4 pt-4">
                      <button
                        type="submit"
                        :disabled="createLoading"
                        class="flex w-full items-center justify-center gap-3 rounded-full bg-gradient-to-br from-primary to-secondary py-5 text-base font-black uppercase tracking-tighter text-on-primary-fixed shadow-[0_0_40px_rgba(246,128,255,0.4)] transition-all hover:scale-[1.02] hover:shadow-[0_0_60px_rgba(246,128,255,0.6)] active:scale-95 disabled:cursor-not-allowed disabled:opacity-60 sm:py-6 sm:text-lg"
                      >
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">
                          sensors
                        </span>
                        {{ createLoading ? 'Creating...' : 'Go Live' }}
                      </button>

                      <RouterLink
                        to="/streams"
                        class="block w-full rounded-full border border-outline-variant/30 bg-transparent py-4 text-center text-sm font-bold uppercase tracking-widest text-on-surface-variant transition-all hover:bg-surface-container-high hover:text-on-surface"
                      >
                        Cancel Broadcast
                      </RouterLink>
                    </div>
                  </form>
                </section>
              </div>

              <div class="space-y-8">
                <section class="relative overflow-hidden rounded-lg border border-outline-variant/10 bg-surface-container-high">
                  <img
                    src="https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=1200&auto=format&fit=crop"
                    alt="Streaming setup preview"
                    class="h-full w-full object-cover grayscale brightness-50 transition-all duration-700"
                  />

                  <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/60 px-6 text-center backdrop-blur-[2px]">
                    <span class="material-symbols-outlined mb-2 text-4xl text-primary">live_tv</span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">
                      Stream Setup Preview
                    </span>

                    <p class="mt-3 max-w-xs text-sm text-on-surface-variant">
                      Your stream will start with the title, description, and categories you choose here.
                    </p>
                  </div>
                </section>

                <section class="rounded-lg bg-surface-container p-5 sm:p-6">
                  <h3 class="mb-3 font-headline text-lg font-bold text-on-surface sm:text-xl">
                    Before You Go Live
                  </h3>

                  <div class="space-y-3 text-sm text-on-surface-variant">
                    <p>• Add a clear stream title so viewers understand your content quickly.</p>
                    <p>• Write a short description to make your stream more attractive.</p>
                    <p>• Choose the right categories to help viewers find your broadcast.</p>
                  </div>
                </section>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import api from '@/services/api'
import TopNavbar from '@/components/layout/TopNavbar.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'

const router = useRouter()

const sidebarCollapsed = ref(false)
const mobileSidebarOpen = ref(false)

const categories = ref([])
const categoriesLoading = ref(false)
const createLoading = ref(false)

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

const loadCategories = async () => {
  categoriesLoading.value = true
  generalError.value = ''

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
    generalError.value =
      error.response?.data?.message || 'Failed to load categories.'
  } finally {
    categoriesLoading.value = false
  }
}

const isSelectedCategory = (categoryId) => {
  return form.category_ids.includes(Number(categoryId))
}

const toggleCategory = (categoryId) => {
  const id = Number(categoryId)

  if (form.category_ids.includes(id)) {
    form.category_ids = form.category_ids.filter((item) => item !== id)
  } else {
    form.category_ids = [...form.category_ids, id]
  }
}

const selectedCategoriesPreview = computed(() => {
  return categories.value.filter((category) =>
    form.category_ids.includes(Number(category.id))
  )
})

const handleCreateStream = async () => {
  createLoading.value = true
  generalError.value = ''
  successMessage.value = ''
  errors.value = {}

  try {
    const payload = {
      title: form.title.trim(),
      description: form.description.trim(),
      category_ids: form.category_ids.map((id) => Number(id)),
    }

    const response = await api.post('/stream/streams', payload)

    successMessage.value = response.data?.message || 'Stream created successfully.'

    const streamId = response.data?.data?.id

    if (streamId) {
      setTimeout(() => {
        router.push(`/streams/${streamId}/studio`)
      }, 800)
    } else {
      setTimeout(() => {
        router.push('/streams')
      }, 800)
    }
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response?.data?.errors || {}
      generalError.value =
        error.response?.data?.message ||
        'Unable to create stream. Please check your input.'
    } else {
      generalError.value =
        error.response?.data?.message || 'Failed to create stream.'
    }
  } finally {
    createLoading.value = false
  }
}

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
  loadCategories()
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
}
</style>
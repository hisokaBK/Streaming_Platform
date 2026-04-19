<template>
  <div class="dark">
    <div class="min-h-screen bg-background text-on-background antialiased selection:bg-primary selection:text-on-primary">
      <TopNavbar @toggle-sidebar="handleSidebarToggle" />

      <div class="flex min-h-screen pt-[72px]">
        <AdminSidebar
          :collapsed="sidebarCollapsed"
          :mobile-open="mobileSidebarOpen"
          @close="mobileSidebarOpen = false"
        />

        <main
          :class="[
            'min-w-0 min-h-screen flex-1 px-4 py-6 transition-all duration-300 sm:px-6 lg:px-8',
            sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'
          ]"
        >
          <div class="relative mb-10">
            <div class="pointer-events-none absolute -left-24 -top-24 h-96 w-96 rounded-full bg-primary/10 blur-[120px]"></div>

            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
              <div class="min-w-0">
                <h1 class="mb-2 font-headline text-3xl font-extrabold tracking-tighter text-on-surface sm:text-4xl md:text-5xl">
                  Categories Management
                </h1>
                <p class="max-w-2xl text-base text-on-surface-variant sm:text-lg">
                  Create, update, and organize platform categories used across streams and videos.
                </p>
              </div>

              <div class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto">
                <div class="flex w-full min-w-0 items-center rounded-full border border-white/10 bg-surface-container px-4 py-3 sm:min-w-[280px]">
                  <span class="material-symbols-outlined mr-3 text-on-surface-variant">search</span>
                  <input
                    v-model="search"
                    type="text"
                    placeholder="Search categories..."
                    class="w-full border-none bg-transparent text-sm text-white placeholder:text-zinc-500 focus:ring-0"
                  />
                </div>

                <button
                  type="button"
                  class="rounded-full bg-gradient-to-r from-primary to-secondary px-6 py-3 font-bold text-on-primary-fixed shadow-[0_10px_30px_rgba(246,128,255,0.25)] transition-all hover:scale-105 active:scale-95"
                  @click="openCreateModal"
                >
                  New Category
                </button>
              </div>
            </div>
          </div>

          <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-3xl border border-white/5 bg-surface-container p-5">
              <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-zinc-500">
                Total Categories
              </p>
              <h2 class="font-headline text-3xl font-black text-white">
                {{ categories.length }}
              </h2>
            </div>

            <div class="rounded-3xl border border-white/5 bg-surface-container p-5">
              <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-zinc-500">
                Filtered Results
              </p>
              <h2 class="font-headline text-3xl font-black text-white">
                {{ filteredCategories.length }}
              </h2>
            </div>
          </div>

          <div class="overflow-hidden rounded-3xl border border-white/5 bg-surface-container shadow-xl">
            <div class="border-b border-white/5 px-4 py-5 sm:px-6">
              <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h2 class="font-headline text-xl font-bold text-white sm:text-2xl">
                    Platform Categories
                  </h2>
                  <p class="mt-1 text-sm text-on-surface-variant">
                    Manage available categories for content organization.
                  </p>
                </div>

                <button
                  type="button"
                  class="rounded-full border border-white/10 px-4 py-2 text-xs font-bold uppercase tracking-widest text-zinc-300 transition hover:bg-white/5"
                  @click="loadCategories"
                >
                  Refresh
                </button>
              </div>
            </div>

            <div v-if="loading" class="divide-y divide-white/5">
              <div
                v-for="n in 8"
                :key="n"
                class="flex animate-pulse items-center gap-4 px-4 py-5 sm:px-6"
              >
                <div class="h-12 w-12 rounded-2xl bg-surface-container-high"></div>
                <div class="flex-1">
                  <div class="mb-2 h-4 w-40 rounded bg-surface-container-high"></div>
                  <div class="h-3 w-24 rounded bg-surface-container-high"></div>
                </div>
                <div class="hidden h-10 w-24 rounded-full bg-surface-container-high sm:block"></div>
                <div class="hidden h-10 w-24 rounded-full bg-surface-container-high sm:block"></div>
              </div>
            </div>

            <div
              v-else-if="filteredCategories.length === 0"
              class="px-6 py-16 text-center"
            >
              <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-surface-container-high text-zinc-500">
                <span class="material-symbols-outlined text-3xl">category</span>
              </div>

              <h3 class="mb-2 font-headline text-2xl font-bold text-white">
                No categories found
              </h3>

              <p class="text-on-surface-variant">
                Try another search or create a new category.
              </p>
            </div>

            <div v-else class="divide-y divide-white/5">
              <div
                v-for="category in filteredCategories"
                :key="category.id"
                class="flex flex-col gap-4 px-4 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between"
              >
                <div class="flex min-w-0 items-center gap-4">
                  <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                    <span class="material-symbols-outlined">sell</span>
                  </div>

                  <div class="min-w-0">
                    <h3 class="truncate font-headline text-lg font-bold text-white">
                      {{ category.name }}
                    </h3>

                    <p class="mt-1 text-xs text-zinc-500">
                      Category ID: {{ category.id }}
                    </p>
                  </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 lg:justify-end">
                  <button
                    type="button"
                    class="rounded-full border border-white/10 px-4 py-2 text-xs font-bold uppercase tracking-widest text-zinc-300 transition hover:bg-white/5"
                    @click="openEditModal(category)"
                  >
                    Edit
                  </button>

                  <button
                    type="button"
                    class="rounded-full bg-error/10 px-5 py-2 text-xs font-bold uppercase tracking-widest text-error transition-all hover:bg-error/20 active:scale-95"
                    @click="openDeleteModal(category)"
                  >
                    Delete
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
        </main>
      </div>

      <!-- Create Modal -->
      <div
        v-if="showCreateModal"
        class="fixed inset-0 z-[80] flex items-center justify-center bg-black/70 px-4 backdrop-blur-sm"
        @click.self="closeCreateModal"
      >
        <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-[2rem] border border-white/10 bg-surface-container p-5 shadow-[0_20px_60px_rgba(0,0,0,0.5)] sm:p-8">
          <div class="mb-6 flex items-start justify-between gap-4">
            <div>
              <h2 class="font-headline text-2xl font-bold text-white">
                Create Category
              </h2>
              <p class="mt-1 text-sm text-on-surface-variant">
                Add a new category for streams and videos.
              </p>
            </div>

            <button
              type="button"
              class="rounded-full p-2 text-on-surface-variant transition hover:bg-surface-container-high hover:text-white"
              @click="closeCreateModal"
            >
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <form class="space-y-5" @submit.prevent="createCategory">
            <div>
              <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-zinc-500">
                Category Name
              </label>
              <input
                v-model="createForm.name"
                type="text"
                placeholder="Enter category name..."
                class="w-full rounded-2xl border border-white/10 bg-surface-container-high px-4 py-4 text-white placeholder:text-zinc-500 focus:border-primary/40 focus:ring-primary/20"
              />
            </div>

            <p v-if="modalErrorMessage" class="text-sm text-error">
              {{ modalErrorMessage }}
            </p>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
              <button
                type="button"
                class="rounded-full border border-white/10 px-6 py-3 font-bold text-white transition hover:bg-white/5"
                @click="closeCreateModal"
              >
                Cancel
              </button>

              <button
                type="submit"
                :disabled="createLoading"
                class="rounded-full bg-gradient-to-r from-primary to-secondary px-6 py-3 font-bold text-on-primary-fixed shadow-[0_10px_30px_rgba(246,128,255,0.25)] transition-all hover:scale-105 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {{ createLoading ? 'Creating...' : 'Create Category' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Edit Modal -->
      <div
        v-if="showEditModal"
        class="fixed inset-0 z-[80] flex items-center justify-center bg-black/70 px-4 backdrop-blur-sm"
        @click.self="closeEditModal"
      >
        <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-[2rem] border border-white/10 bg-surface-container p-5 shadow-[0_20px_60px_rgba(0,0,0,0.5)] sm:p-8">
          <div class="mb-6 flex items-start justify-between gap-4">
            <div>
              <h2 class="font-headline text-2xl font-bold text-white">
                Edit Category
              </h2>
              <p class="mt-1 text-sm text-on-surface-variant">
                Update the selected category name.
              </p>
            </div>

            <button
              type="button"
              class="rounded-full p-2 text-on-surface-variant transition hover:bg-surface-container-high hover:text-white"
              @click="closeEditModal"
            >
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <form class="space-y-5" @submit.prevent="updateCategory">
            <div>
              <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-zinc-500">
                Category Name
              </label>
              <input
                v-model="editForm.name"
                type="text"
                placeholder="Enter category name..."
                class="w-full rounded-2xl border border-white/10 bg-surface-container-high px-4 py-4 text-white placeholder:text-zinc-500 focus:border-primary/40 focus:ring-primary/20"
              />
            </div>

            <p v-if="modalErrorMessage" class="text-sm text-error">
              {{ modalErrorMessage }}
            </p>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
              <button
                type="button"
                class="rounded-full border border-white/10 px-6 py-3 font-bold text-white transition hover:bg-white/5"
                @click="closeEditModal"
              >
                Cancel
              </button>

              <button
                type="submit"
                :disabled="updateLoading"
                class="rounded-full bg-gradient-to-r from-primary to-secondary px-6 py-3 font-bold text-on-primary-fixed shadow-[0_10px_30px_rgba(246,128,255,0.25)] transition-all hover:scale-105 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {{ updateLoading ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Delete Confirm Modal -->
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 z-[90] flex items-center justify-center bg-black/70 px-4 backdrop-blur-sm"
        @click.self="closeDeleteModal"
      >
        <div class="w-full max-w-lg rounded-[2rem] border border-white/10 bg-surface-container p-5 shadow-[0_20px_60px_rgba(0,0,0,0.5)] sm:p-8">
          <div class="mb-6 flex items-start gap-4">
            <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-full bg-error/10 text-error">
              <span
                class="material-symbols-outlined text-3xl"
                style="font-variation-settings: 'FILL' 1;"
              >
                delete
              </span>
            </div>

            <div>
              <h2 class="font-headline text-2xl font-bold text-white">
                Delete Category
              </h2>
              <p class="mt-2 text-sm leading-relaxed text-on-surface-variant">
                Are you sure you want to delete
                <span class="font-bold text-white">
                  {{ selectedCategoryToDelete?.name }}
                </span>
                ?
                This action cannot be undone.
              </p>
            </div>
          </div>

          <p v-if="modalErrorMessage" class="mb-4 text-sm text-error">
            {{ modalErrorMessage }}
          </p>

          <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
            <button
              type="button"
              class="rounded-full border border-white/10 px-6 py-3 font-bold text-white transition hover:bg-white/5"
              @click="closeDeleteModal"
            >
              Cancel
            </button>

            <button
              type="button"
              :disabled="deleteLoadingId === selectedCategoryToDelete?.id"
              class="rounded-full bg-error px-6 py-3 font-bold text-white transition-all hover:scale-105 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
              @click="confirmDeleteCategory"
            >
              {{
                deleteLoadingId === selectedCategoryToDelete?.id
                  ? 'Deleting...'
                  : 'Yes, Delete'
              }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import api from '@/services/api'
import TopNavbar from '@/components/layout/TopNavbar.vue'
import AdminSidebar from '@/components/admin/AdminSidebar.vue'

const sidebarCollapsed = ref(false)
const mobileSidebarOpen = ref(false)

const loading = ref(false)
const categories = ref([])

const search = ref('')
const errorMessage = ref('')
const successMessage = ref('')

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)

const createLoading = ref(false)
const updateLoading = ref(false)
const deleteLoadingId = ref(null)

const modalErrorMessage = ref('')

const selectedCategoryId = ref(null)
const selectedCategoryToDelete = ref(null)

const createForm = reactive({
  name: '',
})

const editForm = reactive({
  name: '',
})

const normalizeCollection = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.data)) return payload.data.data
  if (Array.isArray(payload?.data?.categories)) return payload.data.categories
  if (Array.isArray(payload?.categories)) return payload.categories
  return []
}

const filteredCategories = computed(() => {
  const keyword = search.value.trim().toLowerCase()

  if (!keyword) return categories.value

  return categories.value.filter((category) => {
    const name = category.name?.toLowerCase() || ''
    return name.includes(keyword)
  })
})

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

const resetMessages = () => {
  errorMessage.value = ''
  successMessage.value = ''
}

const resetModalError = () => {
  modalErrorMessage.value = ''
}

const loadCategories = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get('/categories')
    categories.value = normalizeCollection(response.data)
  } catch (error) {
    console.error('Failed to load categories', error)
    categories.value = []
    errorMessage.value =
      error.response?.data?.message || 'Failed to load categories.'
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  createForm.name = ''
  resetModalError()
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
  createForm.name = ''
  resetModalError()
}

const openEditModal = (category) => {
  selectedCategoryId.value = category.id
  editForm.name = category.name || ''
  resetModalError()
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  selectedCategoryId.value = null
  editForm.name = ''
  resetModalError()
}

const openDeleteModal = (category) => {
  selectedCategoryToDelete.value = category
  resetModalError()
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  selectedCategoryToDelete.value = null
  resetModalError()
}

const createCategory = async () => {
  createLoading.value = true
  resetMessages()
  resetModalError()

  try {
    const response = await api.post('/admin/categories', {
      name: createForm.name,
    })

    const newCategory = response.data?.data?.category || null

    if (newCategory) {
      categories.value = [newCategory, ...categories.value]
    }

    successMessage.value = response.data?.message || 'Category created successfully.'
    closeCreateModal()
  } catch (error) {
    console.error('Failed to create category', error)

    if (error.response?.status === 422) {
      const validationErrors = error.response.data?.errors || {}
      modalErrorMessage.value =
        Object.values(validationErrors).flat().join(' ') || 'Validation failed.'
    } else {
      modalErrorMessage.value =
        error.response?.data?.message || 'Failed to create category.'
    }
  } finally {
    createLoading.value = false
  }
}

const updateCategory = async () => {
  if (!selectedCategoryId.value) return

  updateLoading.value = true
  resetMessages()
  resetModalError()

  try {
    const response = await api.put(`/admin/categories/${selectedCategoryId.value}`, {
      name: editForm.name,
    })

    const updatedCategory = response.data?.data?.category || null

    if (updatedCategory) {
      categories.value = categories.value.map((item) =>
        Number(item.id) === Number(updatedCategory.id) ? updatedCategory : item
      )
    }

    successMessage.value = response.data?.message || 'Category updated successfully.'
    closeEditModal()
  } catch (error) {
    console.error('Failed to update category', error)

    if (error.response?.status === 422) {
      const validationErrors = error.response.data?.errors || {}
      modalErrorMessage.value =
        Object.values(validationErrors).flat().join(' ') || 'Validation failed.'
    } else {
      modalErrorMessage.value =
        error.response?.data?.message || 'Failed to update category.'
    }
  } finally {
    updateLoading.value = false
  }
}

const confirmDeleteCategory = async () => {
  if (!selectedCategoryToDelete.value) return

  deleteLoadingId.value = selectedCategoryToDelete.value.id
  resetMessages()
  resetModalError()

  try {
    const response = await api.delete(`/admin/categories/${selectedCategoryToDelete.value.id}`)

    categories.value = categories.value.filter(
      (item) => Number(item.id) !== Number(selectedCategoryToDelete.value.id)
    )

    successMessage.value = response.data?.message || 'Category deleted successfully.'
    closeDeleteModal()
  } catch (error) {
    console.error('Failed to delete category', error)
    modalErrorMessage.value =
      error.response?.data?.message || 'Failed to delete category.'
  } finally {
    deleteLoadingId.value = null
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
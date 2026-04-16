<template>
  <div class="dark">
    <div class="flex min-h-screen items-center justify-center bg-black font-body text-on-surface selection:bg-primary/30">
      <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -left-1/4 -top-1/4 h-full w-full rounded-full bg-primary/10 blur-[160px]"></div>
        <div class="absolute -bottom-1/4 -right-1/4 h-full w-full rounded-full bg-secondary/5 blur-[160px]"></div>
      </div>

      <main class="relative z-10 w-full max-w-[440px] px-6 py-10">
        <div class="mb-12 text-center">
          <h1 class="font-headline text-4xl font-black uppercase italic tracking-tighter text-fuchsia-500">
            Hisoka Noir
          </h1>
          <p class="mt-3 font-label text-xs uppercase tracking-[0.2em] text-on-surface-variant opacity-60">
            Premium Streaming Experience
          </p>
        </div>

        <div class="relative overflow-hidden rounded-lg bg-surface-container-low p-10 shadow-[0_20px_40px_rgba(0,0,0,0.6)]">
          <div class="absolute -mr-16 -mt-16 right-0 top-0 h-32 w-32 bg-primary/10 blur-3xl"></div>

          <header class="mb-8">
            <h2 class="font-headline text-2xl font-bold text-on-surface">
              Welcome Back
            </h2>
            <p class="mt-1 text-sm text-on-surface-variant">
              Enter your credentials to access the club.
            </p>
          </header>

          <form class="space-y-6" @submit.prevent="handleLogin">
            <div class="space-y-2">
              <label
                for="email"
                class="ml-1 block font-label text-[10px] font-bold uppercase tracking-widest text-on-surface-variant"
              >
                Email Address
              </label>

              <div class="group relative">
                <span
                  class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-lg text-outline transition-colors group-focus-within:text-primary"
                >
                  alternate_email
                </span>

                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  placeholder="name@exclusive.com"
                  class="h-14 w-full rounded-DEFAULT border-none bg-surface-container pl-12 pr-4 text-on-surface outline-none transition-all placeholder:text-outline-variant focus:bg-surface-container-high focus:ring-2 focus:ring-primary"
                />
              </div>

              <p v-if="errors.email" class="text-xs text-error">
                {{ errors.email[0] }}
              </p>
            </div>

            <div class="space-y-2">
              <div class="flex items-center justify-between px-1">
                <label
                  for="password"
                  class="font-label text-[10px] font-bold uppercase tracking-widest text-on-surface-variant"
                >
                  Password
                </label>

                <button
                  type="button"
                  class="text-[10px] font-bold uppercase tracking-widest text-primary transition-colors hover:text-primary-dim"
                >
                  Forgot Password?
                </button>
              </div>

              <div class="group relative">
                <span
                  class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-lg text-outline transition-colors group-focus-within:text-primary"
                >
                  lock
                </span>

                <input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  class="h-14 w-full rounded-DEFAULT border-none bg-surface-container pl-12 pr-12 text-on-surface outline-none transition-all placeholder:text-outline-variant focus:bg-surface-container-high focus:ring-2 focus:ring-primary"
                />

                <button
                  class="absolute right-4 top-1/2 -translate-y-1/2 text-outline-variant transition-colors hover:text-on-surface"
                  type="button"
                  @click="showPassword = !showPassword"
                >
                  <span class="material-symbols-outlined text-lg">
                    {{ showPassword ? 'visibility_off' : 'visibility' }}
                  </span>
                </button>
              </div>

              <p v-if="errors.password" class="text-xs text-error">
                {{ errors.password[0] }}
              </p>
            </div>

            <div class="flex items-center px-1">
              <input
                id="remember"
                v-model="remember"
                type="checkbox"
                class="h-4 w-4 cursor-pointer rounded border-none bg-surface-container-highest text-primary focus:ring-0 focus:ring-offset-0"
              />
              <label
                for="remember"
                class="ml-3 cursor-pointer select-none text-sm text-on-surface-variant"
              >
                Remember this session
              </label>
            </div>

            <p v-if="generalError" class="text-sm text-error">
              {{ generalError }}
            </p>

            <div class="pt-4">
              <button
                type="submit"
                :disabled="loading"
                class="h-14 w-full rounded-full bg-gradient-to-r from-primary to-secondary font-headline text-sm font-bold uppercase tracking-widest text-on-primary shadow-[0_8px_20px_rgba(246,128,255,0.3)] transition-all hover:scale-[1.02] hover:shadow-[0_12px_28px_rgba(246,128,255,0.45)] active:scale-95 disabled:cursor-not-allowed disabled:opacity-70"
              >
                {{ loading ? 'Signing In...' : 'Sign In' }}
              </button>
            </div>
          </form>

          <footer class="mt-8 text-center">
            <p class="text-sm text-on-surface-variant">
              New to Hisoka Noir?
              <RouterLink
                to="/register"
                class="ml-1 font-bold text-primary underline-offset-4 hover:underline"
              >
                Create Account
              </RouterLink>
            </p>
          </footer>
        </div>


        <footer class="mt-12 text-center text-[10px] font-medium uppercase tracking-widest text-outline-variant">
          <button type="button" class="transition-colors hover:text-on-surface">Terms</button>
          <span class="mx-2">•</span>
          <button type="button" class="transition-colors hover:text-on-surface">Privacy</button>
          <span class="mx-2">•</span>
          <button type="button" class="transition-colors hover:text-on-surface">Security</button>
        </footer>
      </main>

      <div class="pointer-events-none fixed bottom-0 left-0 top-0 hidden w-1/3 lg:block">
        <div
          class="h-full w-full bg-cover bg-center opacity-20"
          style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuA6v7lW81JV2amkil5-vETCiCVLLO8I2ac2nxABgkZe5xbXJJwVdIqWcdbuPpqQrA1fkESkbjR0jDnGNocBbhc7tQitcULyuKxOznBpDrq9nwpFIKANLreS2rih4WuMXa4D5qaCONr1Pp0QAasI0sdI5TvAxo27BL43Pjsw7yuCkNjH7oD1coeA4qJISVWLKwrP_G0EZmqdTkzbfOByDydf4d03cwsYITsm1psmiCXMuDjogdaY987bilW9-huTluLPT15CyUZmb-M')"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(false)
const generalError = ref('')
const errors = ref({})
const showPassword = ref(false)
const remember = ref(false)

const form = reactive({
  email: '',
  password: '',
})

const handleLogin = async () => {
  loading.value = true
  generalError.value = ''
  errors.value = {}

  try {
    const response = await api.post('/auth/login', form)

    const token = response.data?.data?.token
    const user = response.data?.data?.user

    if (token) {
      authStore.setToken(token)
      localStorage.setItem('token', token)
    }

    if (user) {
      authStore.setUser(user)
      localStorage.setItem('user', JSON.stringify(user))
    }

    router.push('/profile')
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      generalError.value =
        error.response?.data?.message || 'Something went wrong. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
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
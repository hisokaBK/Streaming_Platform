<template>
  <div class="dark">
    <div class="relative min-h-screen overflow-hidden bg-black font-body text-on-surface selection:bg-primary selection:text-on-primary">
      <header class="absolute top-0 left-0 z-30 flex h-20 w-full items-center justify-center px-8">
        <div class="font-headline text-2xl font-black uppercase italic tracking-tighter text-fuchsia-500">
          Hisoka Noir
        </div>
      </header>

      <main class="relative flex min-h-screen items-center justify-center overflow-hidden px-6 pt-28 pb-28">
        <div class="absolute left-[-10%] top-[-10%] h-[40%] w-[40%] rounded-full bg-primary/10 blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] h-[40%] w-[40%] rounded-full bg-secondary/10 blur-[120px]"></div>

        <div class="z-10 w-full max-w-md">
          <div class="neon-glow relative overflow-hidden rounded-lg border-0 bg-surface-container p-8 shadow-2xl md:p-10">
            <div class="absolute left-0 top-0 h-1 w-full bg-gradient-to-r from-primary via-secondary to-primary"></div>

            <div class="mb-10 text-center">
              <h1 class="mb-2 font-headline text-3xl font-extrabold tracking-tight text-on-surface">
                Join the Night
              </h1>
              <p class="font-body text-sm text-on-surface-variant">
                Experience premium streaming with Hisoka Noir.
              </p>
            </div>

            <form class="space-y-6" @submit.prevent="handleRegister">
              <div class="space-y-4">
                <div class="group">
                  <label
                    for="name"
                    class="mb-2 ml-1 block font-label text-[10px] font-bold uppercase tracking-widest text-on-surface-variant"
                  >
                    Full Name
                  </label>
                  <div class="relative flex items-center">
                    <span
                      class="material-symbols-outlined absolute left-4 text-lg text-outline transition-colors group-focus-within:text-primary"
                    >
                      person
                    </span>
                    <input
                      id="name"
                      v-model="form.name"
                      type="text"
                      placeholder="Hisoka Morow"
                      class="w-full rounded-full border-0 bg-surface-container-low py-4 pl-12 pr-6 font-body text-sm text-on-surface outline-none transition-all placeholder:text-outline focus:bg-surface-container-high focus:ring-1 focus:ring-primary/50"
                    />
                  </div>
                  <p v-if="errors.name" class="mt-2 text-xs text-error">
                    {{ errors.name[0] }}
                  </p>
                </div>

                <div class="group">
                  <label
                    for="email"
                    class="mb-2 ml-1 block font-label text-[10px] font-bold uppercase tracking-widest text-on-surface-variant"
                  >
                    Email Address
                  </label>
                  <div class="relative flex items-center">
                    <span
                      class="material-symbols-outlined absolute left-4 text-lg text-outline transition-colors group-focus-within:text-primary"
                    >
                      alternate_email
                    </span>
                    <input
                      id="email"
                      v-model="form.email"
                      type="email"
                      placeholder="noir@hisoka.com"
                      class="w-full rounded-full border-0 bg-surface-container-low py-4 pl-12 pr-6 font-body text-sm text-on-surface outline-none transition-all placeholder:text-outline focus:bg-surface-container-high focus:ring-1 focus:ring-primary/50"
                    />
                  </div>
                  <p v-if="errors.email" class="mt-2 text-xs text-error">
                    {{ errors.email[0] }}
                  </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div class="group">
                    <label
                      for="password"
                      class="mb-2 ml-1 block font-label text-[10px] font-bold uppercase tracking-widest text-on-surface-variant"
                    >
                      Password
                    </label>
                    <div class="relative flex items-center">
                      <span
                        class="material-symbols-outlined absolute left-4 text-lg text-outline transition-colors group-focus-within:text-primary"
                      >
                        lock
                      </span>
                      <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="••••••••"
                        class="w-full rounded-full border-0 bg-surface-container-low py-4 pl-12 pr-6 font-body text-sm text-on-surface outline-none transition-all placeholder:text-outline focus:bg-surface-container-high focus:ring-1 focus:ring-primary/50"
                      />
                    </div>
                    <p v-if="errors.password" class="mt-2 text-xs text-error">
                      {{ errors.password[0] }}
                    </p>
                  </div>

                  <div class="group">
                    <label
                      for="password_confirmation"
                      class="mb-2 ml-1 block font-label text-[10px] font-bold uppercase tracking-widest text-on-surface-variant"
                    >
                      Confirm
                    </label>
                    <div class="relative flex items-center">
                      <span
                        class="material-symbols-outlined absolute left-4 text-lg text-outline transition-colors group-focus-within:text-primary"
                      >
                        verified_user
                      </span>
                      <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        placeholder="••••••••"
                        class="w-full rounded-full border-0 bg-surface-container-low py-4 pl-12 pr-6 font-body text-sm text-on-surface outline-none transition-all placeholder:text-outline focus:bg-surface-container-high focus:ring-1 focus:ring-primary/50"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <div class="pt-4">
                <button
                  type="submit"
                  :disabled="loading"
                  class="w-full rounded-full bg-gradient-to-r from-primary to-secondary py-4 font-headline font-bold uppercase tracking-wider text-on-primary transition-all hover:shadow-[0_0_30px_rgba(246,128,255,0.4)] active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
                >
                  {{ loading ? 'Creating Account...' : 'Create Account' }}
                </button>
              </div>

              <p v-if="successMessage" class="text-center text-sm text-green-400">
                {{ successMessage }}
              </p>

              <p v-if="generalError" class="text-center text-sm text-error">
                {{ generalError }}
              </p>

              <p class="px-4 text-center text-[10px] leading-relaxed text-outline">
                By signing up, you agree to our
                <span class="cursor-pointer text-on-surface-variant transition-colors hover:text-primary">
                  Terms of Service
                </span>
                and
                <span class="cursor-pointer text-on-surface-variant transition-colors hover:text-primary">
                  Privacy Policy
                </span>.
              </p>
            </form>

            <div class="mt-8 border-t border-outline-variant/10 pt-8 text-center">
              <p class="text-sm text-on-surface-variant">
                Already have an account?
                <RouterLink
                  to="/login"
                  class="ml-1 font-bold text-primary transition-colors hover:text-secondary"
                >
                  Login
                </RouterLink>
              </p>
            </div>
          </div>

          <div class="mt-8 flex items-center justify-between px-4">
            <div class="flex items-center gap-2">
              <div class="h-2 w-2 animate-pulse rounded-full bg-tertiary"></div>
              <span class="font-label text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                Live streaming active
              </span>
            </div>
            <div class="font-label text-[10px] font-bold uppercase tracking-widest text-outline">
              v2.4.0 High-Energy
            </div>
          </div>
        </div>

        <div
          class="pointer-events-none absolute left-24 top-1/2 hidden h-96 w-64 -translate-y-1/2 -rotate-6 overflow-hidden rounded-lg opacity-40 transition-opacity duration-700 hover:opacity-100 xl:block"
        >
          <img
            src="https://lh3.googleusercontent.com/aida-public/AB6AXuAnYMW5hcaK_HqieRFcLAml_0F1CDGcjMtU28Hcv2nbjYCoVuXDB2-ozfh5Lm_D8wIiGE42wrbNi0ssq9_Qb-edhiquP4WMn0_MOWdS5-ux7glP7Y5CMW5LiDuYzi94lm0OJkA8WewGEYdIncuyinQecKXYQufn3t_4KS2HJ7TqT41G_DJhPDMhywEqKJDyL4IjHXaTbENg7tid6aMCHN1AxBwUyotNNld_mx4XtYaNybX8zYCatFclfakuu7qbYUE4wf_al-Re1S8"
            alt="cyberpunk aesthetics"
            class="h-full w-full object-cover"
          />
        </div>

        <div
          class="pointer-events-none absolute right-24 top-1/2 hidden h-[450px] w-72 -translate-y-1/2 rotate-3 overflow-hidden rounded-lg opacity-30 transition-opacity duration-700 hover:opacity-100 xl:block"
        >
          <img
            src="https://lh3.googleusercontent.com/aida-public/AB6AXuDxwniIBOaLFvaX6LD_Xbl4PNciGsohJ5i3XewPoIsJe4Heo81Fq75-3IiT1zcnt525jLqvgoZ-z4juSEqCNxxaZgLEi4PCDoZ-br9NgcqdGbG9hRNHAg0OXHliIojJuy7mTq3xBk0LLJgqBNufs-Qk5dURE8uxYIQ5bNjqV9tBbq0Xw1_5iIlS9x2ctVXbEnEKqGAJjB3-eJFi-iAVCy1n7aWGsupr0ez87opm069O7ZF1QNQWiy79KRDRYHdI1sCHfLaaqsk3h4M"
            alt="noir lighting"
            class="h-full w-full object-cover"
          />
        </div>
      </main>

      <footer class="absolute bottom-0 left-0 z-30 flex w-full justify-center px-8 py-6 font-label text-[10px] uppercase tracking-[0.2em] text-outline">
        © 2024 Hisoka Noir Entertainment. All Rights Reserved.
      </footer>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const loading = ref(false)
const successMessage = ref('')
const generalError = ref('')
const errors = ref({})

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const handleRegister = async () => {
  loading.value = true
  successMessage.value = ''
  generalError.value = ''
  errors.value = {}

  try {
    const response = await api.post('/auth/register', form)

    successMessage.value = response.data.message || 'Register successful'

    form.name = ''
    form.email = ''
    form.password = ''
    form.password_confirmation = ''

    setTimeout(() => {
      router.push('/login')
    }, 1200)
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

.neon-glow {
  box-shadow: 0 0 40px rgba(246, 128, 255, 0.1);
}
</style>
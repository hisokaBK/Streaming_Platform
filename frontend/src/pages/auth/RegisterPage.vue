<template>
  <main class="min-h-screen flex items-center justify-center bg-black px-6 py-20 relative overflow-hidden">
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-fuchsia-500/10 blur-[120px] rounded-full"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-500/10 blur-[120px] rounded-full"></div>

    <div class="w-full max-w-md relative z-10">
      <div class="bg-[#141414] rounded-2xl overflow-hidden shadow-2xl border border-white/5">
        <div class="h-1 bg-gradient-to-r from-fuchsia-500 via-purple-500 to-fuchsia-500"></div>

        <div class="p-8 md:p-10">
          <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-white">
              Create Account
            </h1>
            <p class="text-sm text-gray-400 mt-2">
              Join the platform and start your streaming journey.
            </p>
          </div>

          <form class="space-y-5" @submit.prevent="handleRegister">
            <div>
              <label for="name" class="block text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">
                Full Name
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                placeholder="Your full name"
                class="w-full rounded-full bg-[#1c1c1c] border border-white/5 px-5 py-4 text-sm text-white placeholder:text-gray-500 outline-none focus:border-fuchsia-500/50 focus:ring-1 focus:ring-fuchsia-500/30 transition"
              />
              <p v-if="errors.name" class="text-xs text-red-400 mt-2">
                {{ errors.name[0] }}
              </p>
            </div>

            <div>
              <label for="email" class="block text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">
                Email Address
              </label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="you@example.com"
                class="w-full rounded-full bg-[#1c1c1c] border border-white/5 px-5 py-4 text-sm text-white placeholder:text-gray-500 outline-none focus:border-fuchsia-500/50 focus:ring-1 focus:ring-fuchsia-500/30 transition"
              />
              <p v-if="errors.email" class="text-xs text-red-400 mt-2">
                {{ errors.email[0] }}
              </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">
                  Password
                </label>
                <input
                  id="password"
                  v-model="form.password"
                  type="password"
                  placeholder="••••••••"
                  class="w-full rounded-full bg-[#1c1c1c] border border-white/5 px-5 py-4 text-sm text-white placeholder:text-gray-500 outline-none focus:border-fuchsia-500/50 focus:ring-1 focus:ring-fuchsia-500/30 transition"
                />
                <p v-if="errors.password" class="text-xs text-red-400 mt-2">
                  {{ errors.password[0] }}
                </p>
              </div>

              <div>
                <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">
                  Confirm
                </label>
                <input
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  type="password"
                  placeholder="••••••••"
                  class="w-full rounded-full bg-[#1c1c1c] border border-white/5 px-5 py-4 text-sm text-white placeholder:text-gray-500 outline-none focus:border-fuchsia-500/50 focus:ring-1 focus:ring-fuchsia-500/30 transition"
                />
              </div>
            </div>

            <p v-if="generalError" class="text-sm text-red-400">
              {{ generalError }}
            </p>

            <button
              type="submit"
              :disabled="loading"
              class="w-full rounded-full bg-gradient-to-r from-fuchsia-500 to-purple-500 py-4 text-sm font-bold uppercase tracking-wider text-white transition hover:opacity-95 disabled:opacity-60"
            >
              {{ loading ? 'Creating account...' : 'Create Account' }}
            </button>

            <p class="text-xs text-center text-gray-500 leading-relaxed px-2">
              By signing up, you agree to our
              <span class="text-gray-300">Terms of Service</span>
              and
              <span class="text-gray-300">Privacy Policy</span>.
            </p>
          </form>

          <div class="mt-8 pt-6 border-t border-white/10 text-center">
            <p class="text-sm text-gray-400">
              Already have an account?
              <RouterLink to="/login" class="text-fuchsia-400 font-semibold hover:text-purple-400 ml-1">
                Login
              </RouterLink>
            </p>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const loading = ref(false)
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
  generalError.value = ''
  errors.value = {}

  try {
    const response = await api.post('/register', form)

    const token = response.data.token
    const user = response.data.user

    if (token) {
      localStorage.setItem('token', token)
      api.defaults.headers.common.Authorization = `Bearer ${token}`
    }

    if (user) {
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
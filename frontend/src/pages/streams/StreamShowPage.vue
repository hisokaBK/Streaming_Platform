<template>
  <div class="dark">
    <div class="min-h-screen bg-background text-on-surface">
      <TopNavbar @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed" />

      <div class="flex h-[calc(100vh-80px)] pt-0">
        <AppSidebar :collapsed="sidebarCollapsed" />

        <main
          :class="[
            'flex h-full flex-1 transition-all duration-300',
            sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'
          ]"
        >
          <!-- Left: Conversations -->
          <section class="flex w-full flex-col border-r border-outline-variant/10 bg-surface-container-lowest md:w-96">
            <div class="p-6">
              <div class="mb-4 flex items-center justify-between gap-3">
                <h1 class="font-headline text-2xl font-bold">Messages</h1>

                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    class="rounded-full bg-surface-container-high px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-on-surface transition hover:bg-surface-bright"
                    @click="openFollowingPopup"
                  >
                    Following
                  </button>

                  <button
                    type="button"
                    class="rounded-full bg-primary px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-on-primary shadow-[0_0_12px_rgba(246,128,255,0.25)] transition hover:scale-105 active:scale-95"
                    @click="openUsersPopup"
                  >
                    New Chat
                  </button>
                </div>
              </div>

              <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-sm text-on-surface-variant">
                  search
                </span>
                <input
                  v-model="search"
                  type="text"
                  placeholder="Search conversations..."
                  class="w-full rounded-xl border-none bg-surface-container-low py-3 pl-10 text-sm text-white placeholder:text-on-surface-variant/50 focus:ring-1 focus:ring-primary/50"
                />
              </div>
            </div>

            <div class="flex-1 overflow-y-auto px-2 pb-4 space-y-1">
              <div
                v-if="conversationsLoading"
                v-for="n in 4"
                :key="`conv-skeleton-${n}`"
                class="flex items-center gap-4 p-4 opacity-40 animate-pulse"
              >
                <div class="h-12 w-12 rounded-full bg-surface-container-high"></div>
                <div class="flex-1">
                  <div class="mb-2 flex items-center justify-between">
                    <div class="h-3 w-24 rounded bg-surface-container-high"></div>
                    <div class="h-2 w-8 rounded bg-surface-container-high"></div>
                  </div>
                  <div class="h-2 w-40 rounded bg-surface-container-high"></div>
                </div>
              </div>

              <div
                v-else-if="filteredConversations.length === 0"
                class="px-4 py-8 text-center text-sm text-on-surface-variant"
              >
                No conversations found.
              </div>

              <button
                v-else
                v-for="conversation in filteredConversations"
                :key="conversation.participant?.id"
                type="button"
                class="group relative w-full rounded-2xl p-4 text-left transition-all"
                :class="conversationClasses(conversation)"
                @click="openConversation(conversation.participant)"
              >
                <div
                  v-if="activeParticipantId === conversation.participant?.id"
                  class="absolute bottom-0 left-0 top-0 w-1 bg-primary"
                ></div>

                <div
                  v-else-if="conversation.has_unread"
                  class="absolute bottom-3 left-3 top-3 w-[3px] rounded-full bg-primary/80"
                ></div>

                <div class="flex items-center gap-4">
                  <div class="relative">
                    <template v-if="getAvatarUrl(conversation.participant?.avatar)">
                      <img
                        :src="getAvatarUrl(conversation.participant?.avatar)"
                        :alt="conversation.participant?.name || 'User avatar'"
                        class="h-12 w-12 rounded-full object-cover"
                        :class="activeParticipantId === conversation.participant?.id
                          ? 'ring-2 ring-primary/20'
                          : conversation.has_unread
                            ? 'ring-2 ring-primary/30'
                            : 'grayscale group-hover:grayscale-0'"
                      />
                    </template>

                    <template v-else>
                      <div
                        class="flex h-12 w-12 items-center justify-center rounded-full border-2 text-sm font-bold uppercase"
                        :class="activeParticipantId === conversation.participant?.id
                          ? 'border-primary/30 bg-zinc-900 text-white'
                          : conversation.has_unread
                            ? 'border-primary/30 bg-zinc-900 text-white'
                            : 'border-white/10 bg-zinc-900 text-white'"
                      >
                        {{ getInitials(conversation.participant?.name) }}
                      </div>
                    </template>

                    <span
                      v-if="conversation.has_unread"
                      class="absolute -right-1 -top-1 h-3.5 w-3.5 rounded-full bg-primary ring-2 ring-surface-container-lowest"
                    ></span>
                  </div>

                  <div class="min-w-0 flex-1">
                    <div class="mb-0.5 flex items-center justify-between gap-3">
                      <span
                        class="truncate font-headline text-sm font-bold transition-colors"
                        :class="activeParticipantId === conversation.participant?.id
                          ? 'text-white'
                          : conversation.has_unread
                            ? 'text-white'
                            : 'text-on-surface-variant group-hover:text-white'"
                      >
                        {{ conversation.participant?.name || 'Unknown user' }}
                      </span>

                      <span
                        class="text-[10px]"
                        :class="conversation.has_unread ? 'text-primary' : 'text-on-surface-variant'"
                      >
                        {{ formatConversationTime(conversation.last_message?.created_at) }}
                      </span>
                    </div>

                    <div class="mb-1 truncate text-[11px] text-on-surface-variant/70">
                      {{ conversation.participant?.email || 'No email' }}
                    </div>

                    <div class="flex items-center justify-between gap-3">
                      <div
                        class="truncate text-xs"
                        :class="conversation.has_unread
                          ? 'font-semibold text-white'
                          : 'text-on-surface-variant'"
                      >
                        {{ conversation.last_message?.content || 'No messages yet' }}
                      </div>

                      <div
                        v-if="conversation.unread_count > 0"
                        class="flex min-w-5 items-center justify-center rounded-full bg-primary px-1.5 py-0.5 text-[10px] font-bold text-on-primary-fixed shadow-[0_0_8px_rgba(246,128,255,0.8)]"
                      >
                        {{ conversation.unread_count }}
                      </div>
                    </div>
                  </div>
                </div>
              </button>
            </div>
          </section>

          <!-- Right: Chat -->
          <section class="hidden flex-1 flex-col bg-surface md:flex">
            <template v-if="activeParticipant">
              <div class="flex h-20 items-center justify-between bg-surface-container/30 px-8 backdrop-blur-md">
                <div class="flex items-center gap-4">
                  <div class="relative">
                    <template v-if="getAvatarUrl(activeParticipant.avatar)">
                      <img
                        :src="getAvatarUrl(activeParticipant.avatar)"
                        :alt="activeParticipant.name || 'Participant avatar'"
                        class="h-10 w-10 rounded-full object-cover ring-2 ring-primary/40"
                      />
                    </template>

                    <template v-else>
                      <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-primary/30 bg-zinc-900 text-sm font-bold uppercase text-white">
                        {{ getInitials(activeParticipant.name) }}
                      </div>
                    </template>
                  </div>

                  <div>
                    <h2 class="font-headline text-lg font-bold text-white">
                      {{ activeParticipant.name }}
                    </h2>
                    <p class="text-xs text-on-surface-variant">
                      {{ activeParticipant.email }}
                    </p>
                  </div>
                </div>
              </div>

              <div
                ref="messagesContainer"
                class="flex-1 overflow-y-auto p-8 space-y-6"
              >
                <div v-if="messagesLoading" class="space-y-5">
                  <div
                    v-for="n in 4"
                    :key="`msg-skeleton-${n}`"
                    class="flex items-end gap-3 max-w-[50%] animate-pulse opacity-20"
                  >
                    <div class="h-8 w-8 rounded-full bg-surface-container-high"></div>
                    <div class="h-12 w-full rounded-2xl rounded-bl-none bg-surface-container-high"></div>
                  </div>
                </div>

                <div
                  v-else-if="messages.length === 0"
                  class="flex h-full items-center justify-center text-sm text-on-surface-variant"
                >
                  No messages yet. Start the conversation.
                </div>

                <template v-else>
                  <div class="self-center">
                    <span class="rounded-full bg-surface-container-high px-3 py-1 text-[10px] font-medium uppercase tracking-widest text-on-surface-variant">
                      Conversation
                    </span>
                  </div>

                  <div
                    v-for="message in messages"
                    :key="message.id"
                    class="flex max-w-[70%] items-end gap-3"
                    :class="isMyMessage(message) ? 'self-end flex-row-reverse' : ''"
                  >
                    <template v-if="!isMyMessage(message)">
                      <template v-if="getAvatarUrl(message.sender?.avatar)">
                        <img
                          :src="getAvatarUrl(message.sender?.avatar)"
                          :alt="message.sender?.name || 'Sender avatar'"
                          class="h-8 w-8 rounded-full object-cover"
                        />
                      </template>

                      <template v-else>
                        <div class="flex h-8 w-8 items-center justify-center rounded-full border border-white/10 bg-zinc-900 text-[10px] font-bold uppercase text-white">
                          {{ getInitials(message.sender?.name) }}
                        </div>
                      </template>
                    </template>

                    <div
                      class="whitespace-pre-wrap break-words p-4 text-sm shadow-lg"
                      :class="isMyMessage(message)
                        ? 'rounded-2xl rounded-br-none bg-gradient-to-br from-fuchsia-600 to-purple-600 text-white shadow-[0_5px_20px_rgba(246,128,255,0.2)]'
                        : 'rounded-2xl rounded-bl-none bg-surface-container-high text-white'"
                    >
                      {{ message.content }}

                      <div
                        class="mt-2 flex justify-end text-[9px]"
                        :class="isMyMessage(message) ? 'text-fuchsia-200' : 'text-on-surface-variant'"
                      >
                        {{ formatMessageTime(message.created_at) }}
                      </div>
                    </div>
                  </div>
                </template>
              </div>

              <div class="bg-surface-container/30 p-6 backdrop-blur-md">
                <p v-if="sendError" class="mb-3 text-sm text-error">
                  {{ sendError }}
                </p>

                <div class="relative flex items-center gap-3 rounded-2xl bg-surface-container-low p-2 ring-1 ring-white/5">
                  <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center text-on-surface-variant transition-colors hover:text-primary"
                  >
                    <span class="material-symbols-outlined">add_circle</span>
                  </button>

                  <input
                    v-model="messageForm.content"
                    type="text"
                    placeholder="Type your message..."
                    class="flex-1 border-none bg-transparent py-2 text-sm text-white placeholder:text-on-surface-variant/40 focus:ring-0"
                    @keydown.enter.prevent="sendMessage"
                  />

                  <div class="relative">
                    <button
                      type="button"
                      class="flex h-10 w-10 items-center justify-center text-on-surface-variant transition-colors hover:text-primary"
                      @click="toggleEmojiPicker"
                    >
                      <span class="material-symbols-outlined">mood</span>
                    </button>

                    <div
                      v-if="showEmojiPicker"
                      class="absolute bottom-14 right-0 z-30 w-64 rounded-2xl border border-white/10 bg-surface-container p-3 shadow-[0_20px_50px_rgba(0,0,0,0.45)]"
                    >
                      <div class="grid grid-cols-6 gap-2">
                        <button
                          v-for="emoji in emojis"
                          :key="emoji"
                          type="button"
                          class="rounded-xl p-2 text-xl transition hover:bg-surface-container-high"
                          @click="appendEmoji(emoji)"
                        >
                          {{ emoji }}
                        </button>
                      </div>
                    </div>
                  </div>

                  <button
                    type="button"
                    :disabled="sendLoading || !messageForm.content.trim() || !activeParticipant"
                    class="flex h-10 w-12 items-center justify-center rounded-xl bg-primary text-on-primary shadow-[0_0_15px_rgba(246,128,255,0.4)] transition-all hover:scale-105 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                    @click="sendMessage"
                  >
                    <span
                      class="material-symbols-outlined font-bold"
                      style="font-variation-settings: 'FILL' 1;"
                    >
                      send
                    </span>
                  </button>
                </div>
              </div>
            </template>

            <template v-else>
              <div class="flex flex-1 flex-col items-center justify-center bg-surface p-8 text-center">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-primary/5 ring-1 ring-primary/20 shadow-[0_0_50px_rgba(246,128,255,0.1)]">
                  <span
                    class="material-symbols-outlined text-5xl text-primary"
                    style="font-variation-settings: 'FILL' 1;"
                  >
                    forum
                  </span>
                </div>

                <h3 class="mb-2 font-headline text-2xl font-bold text-white">
                  No conversation selected
                </h3>

                <p class="max-w-xs text-sm leading-relaxed text-on-surface-variant">
                  Choose a conversation from the sidebar or start a new one from the popups.
                </p>
              </div>
            </template>
          </section>
        </main>
      </div>

      <!-- All Users Popup -->
      <div
        v-if="showUsersPopup"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/70 px-4 backdrop-blur-sm"
        @click.self="closeUsersPopup"
      >
        <div class="w-full max-w-3xl rounded-3xl border border-white/10 bg-surface-container p-6 shadow-[0_20px_60px_rgba(0,0,0,0.5)]">
          <div class="mb-5 flex items-center justify-between gap-4">
            <div>
              <h2 class="font-headline text-2xl font-bold text-white">All Users</h2>
              <p class="text-sm text-on-surface-variant">Choose anyone on the platform to start a chat.</p>
            </div>

            <button
              type="button"
              class="rounded-full p-2 text-on-surface-variant transition hover:bg-surface-container-high hover:text-white"
              @click="closeUsersPopup"
            >
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <div class="mb-5 relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-sm text-on-surface-variant">
              search
            </span>
            <input
              v-model="usersPopupSearch"
              type="text"
              placeholder="Search users..."
              class="w-full rounded-xl border-none bg-surface-container-low py-3 pl-10 text-sm text-white placeholder:text-on-surface-variant/50 focus:ring-1 focus:ring-primary/50"
            />
          </div>

          <div class="max-h-[420px] overflow-y-auto space-y-3">
            <div
              v-if="usersLoading"
              v-for="n in 5"
              :key="`popup-user-skeleton-${n}`"
              class="flex animate-pulse items-center gap-4 rounded-2xl bg-surface-container-low p-4"
            >
              <div class="h-12 w-12 rounded-full bg-surface-container-high"></div>
              <div class="flex-1">
                <div class="mb-2 h-3 w-32 rounded bg-surface-container-high"></div>
                <div class="h-2 w-40 rounded bg-surface-container-high"></div>
              </div>
            </div>

            <div
              v-else-if="filteredUsersForPopup.length === 0"
              class="rounded-2xl bg-surface-container-low p-8 text-center text-sm text-on-surface-variant"
            >
              No users found.
            </div>

            <article
              v-else
              v-for="user in filteredUsersForPopup"
              :key="`popup-user-${user.id}`"
              class="flex items-center gap-4 rounded-2xl bg-surface-container-low p-4 transition hover:bg-surface-container-high"
            >
              <template v-if="getAvatarUrl(user.profile?.avatar)">
                <img
                  :src="getAvatarUrl(user.profile?.avatar)"
                  :alt="user.name || 'User avatar'"
                  class="h-12 w-12 rounded-full object-cover ring-2 ring-primary/20"
                />
              </template>

              <template v-else>
                <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-primary/20 bg-zinc-900 text-sm font-bold uppercase text-white">
                  {{ getInitials(user.name) }}
                </div>
              </template>

              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                  <h3 class="truncate font-headline text-sm font-bold text-white">
                    {{ user.name }}
                  </h3>

                  <span
                    v-if="isFollowingUser(user.id)"
                    class="rounded-full bg-primary/10 px-2 py-1 text-[10px] font-bold uppercase tracking-widest text-primary"
                  >
                    Following
                  </span>
                </div>

                <p class="truncate text-xs text-on-surface-variant">
                  {{ user.email }}
                </p>
              </div>

              <button
                type="button"
                class="rounded-full bg-primary px-4 py-2 text-xs font-bold uppercase tracking-widest text-on-primary shadow-[0_0_15px_rgba(246,128,255,0.25)] transition hover:scale-105 active:scale-95"
                @click="startChatFromPopup(user, 'users')"
              >
                Message
              </button>
            </article>
          </div>
        </div>
      </div>

      <!-- Following Popup -->
      <div
        v-if="showFollowingPopup"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/70 px-4 backdrop-blur-sm"
        @click.self="closeFollowingPopup"
      >
        <div class="w-full max-w-3xl rounded-3xl border border-white/10 bg-surface-container p-6 shadow-[0_20px_60px_rgba(0,0,0,0.5)]">
          <div class="mb-5 flex items-center justify-between gap-4">
            <div>
              <h2 class="font-headline text-2xl font-bold text-white">Following</h2>
              <p class="text-sm text-on-surface-variant">Quick access to people you already follow.</p>
            </div>

            <button
              type="button"
              class="rounded-full p-2 text-on-surface-variant transition hover:bg-surface-container-high hover:text-white"
              @click="closeFollowingPopup"
            >
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <div class="mb-5 relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-sm text-on-surface-variant">
              search
            </span>
            <input
              v-model="followingPopupSearch"
              type="text"
              placeholder="Search following..."
              class="w-full rounded-xl border-none bg-surface-container-low py-3 pl-10 text-sm text-white placeholder:text-on-surface-variant/50 focus:ring-1 focus:ring-primary/50"
            />
          </div>

          <div class="max-h-[420px] overflow-y-auto space-y-3">
            <div
              v-if="followingLoading"
              v-for="n in 5"
              :key="`popup-following-skeleton-${n}`"
              class="flex animate-pulse items-center gap-4 rounded-2xl bg-surface-container-low p-4"
            >
              <div class="h-12 w-12 rounded-full bg-surface-container-high"></div>
              <div class="flex-1">
                <div class="mb-2 h-3 w-32 rounded bg-surface-container-high"></div>
                <div class="h-2 w-40 rounded bg-surface-container-high"></div>
              </div>
            </div>

            <div
              v-else-if="filteredFollowingForPopup.length === 0"
              class="rounded-2xl bg-surface-container-low p-8 text-center text-sm text-on-surface-variant"
            >
              No following users found.
            </div>

            <article
              v-else
              v-for="user in filteredFollowingForPopup"
              :key="`popup-following-${user.id}`"
              class="flex items-center gap-4 rounded-2xl bg-surface-container-low p-4 transition hover:bg-surface-container-high"
            >
              <template v-if="getAvatarUrl(user.avatar)">
                <img
                  :src="getAvatarUrl(user.avatar)"
                  :alt="user.name || 'User avatar'"
                  class="h-12 w-12 rounded-full object-cover ring-2 ring-primary/20"
                />
              </template>

              <template v-else>
                <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-primary/20 bg-zinc-900 text-sm font-bold uppercase text-white">
                  {{ getInitials(user.name) }}
                </div>
              </template>

              <div class="min-w-0 flex-1">
                <h3 class="truncate font-headline text-sm font-bold text-white">
                  {{ user.name }}
                </h3>
                <p class="truncate text-xs text-on-surface-variant">
                  {{ user.email }}
                </p>
              </div>

              <button
                type="button"
                class="rounded-full bg-primary px-4 py-2 text-xs font-bold uppercase tracking-widest text-on-primary shadow-[0_0_15px_rgba(246,128,255,0.25)] transition hover:scale-105 active:scale-95"
                @click="startChatFromPopup(user, 'following')"
              >
                Message
              </button>
            </article>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from 'vue'
import api from '@/services/api'
import TopNavbar from '@/components/layout/TopNavbar.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'

const sidebarCollapsed = ref(false)

const conversations = ref([])
const messages = ref([])
const users = ref([])
const following = ref([])

const activeParticipant = ref(null)
const activeParticipantId = ref(null)

const conversationsLoading = ref(false)
const messagesLoading = ref(false)
const usersLoading = ref(false)
const followingLoading = ref(false)
const sendLoading = ref(false)

const search = ref('')
const sendError = ref('')
const showEmojiPicker = ref(false)

const showUsersPopup = ref(false)
const showFollowingPopup = ref(false)
const usersPopupSearch = ref('')
const followingPopupSearch = ref('')

const messagesContainer = ref(null)

const messageForm = ref({
  content: '',
})

const emojis = ['😀', '😂', '😍', '🔥', '👍', '❤️', '😎', '😭', '🥳', '👏', '🤍', '🤝', '😮', '✨', '💜', '🎉', '🫶', '😅']

const getStoredUser = () => {
  try {
    return JSON.parse(localStorage.getItem('user') || 'null')
  } catch {
    return null
  }
}

const authUser = ref(getStoredUser())

const normalizeCollection = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  return []
}

const buildStorageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http')) return path
  return `http://localhost:8000/storage/${path}`
}

const getAvatarUrl = (avatar) => {
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

const followingIds = computed(() => {
  return following.value.map((item) => Number(item.id))
})

const filteredConversations = computed(() => {
  const keyword = search.value.trim().toLowerCase()

  if (!keyword) return conversations.value

  return conversations.value.filter((conversation) => {
    const name = conversation.participant?.name?.toLowerCase() || ''
    const email = conversation.participant?.email?.toLowerCase() || ''
    const lastMessage = conversation.last_message?.content?.toLowerCase() || ''

    return (
      name.includes(keyword) ||
      email.includes(keyword) ||
      lastMessage.includes(keyword)
    )
  })
})

const filteredUsersForPopup = computed(() => {
  const keyword = usersPopupSearch.value.trim().toLowerCase()
  const list = users.value.filter((user) => Number(user.id) !== Number(authUser.value?.id))

  if (!keyword) return list

  return list.filter((user) => {
    const name = user.name?.toLowerCase() || ''
    const email = user.email?.toLowerCase() || ''
    return name.includes(keyword) || email.includes(keyword)
  })
})

const filteredFollowingForPopup = computed(() => {
  const keyword = followingPopupSearch.value.trim().toLowerCase()

  if (!keyword) return following.value

  return following.value.filter((user) => {
    const name = user.name?.toLowerCase() || ''
    const email = user.email?.toLowerCase() || ''
    return name.includes(keyword) || email.includes(keyword)
  })
})

const isFollowingUser = (userId) => {
  return followingIds.value.includes(Number(userId))
}

const conversationClasses = (conversation) => {
  if (activeParticipantId.value === conversation.participant?.id) {
    return 'bg-surface-container-high'
  }

  if (conversation.has_unread) {
    return 'bg-surface-container-low ring-1 ring-primary/20 shadow-[0_0_20px_rgba(246,128,255,0.08)] hover:bg-surface-container'
  }

  return 'hover:bg-surface-container'
}

const formatConversationTime = (value) => {
  if (!value) return '--'

  const date = new Date(value)
  return date.toLocaleTimeString([], {
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatMessageTime = (value) => {
  if (!value) return '--:--'

  const date = new Date(value)
  return date.toLocaleTimeString([], {
    hour: '2-digit',
    minute: '2-digit',
  })
}

const isMyMessage = (message) => {
  return Number(message.sender?.id) === Number(authUser.value?.id)
}

const scrollToBottom = async () => {
  await nextTick()

  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const loadConversations = async () => {
  conversationsLoading.value = true

  try {
    const response = await api.get('/messages/conversations')
    conversations.value = normalizeCollection(response.data?.data)
  } catch (error) {
    console.error('Failed to load conversations', error)
    conversations.value = []
  } finally {
    conversationsLoading.value = false
  }
}

const loadUsers = async () => {
  usersLoading.value = true

  try {
    const response = await api.get('/users')
    users.value = normalizeCollection(response.data?.data || response.data)
  } catch (error) {
    console.error('Failed to load users', error)
    users.value = []
  } finally {
    usersLoading.value = false
  }
}

const loadFollowing = async () => {
  if (!authUser.value?.id) return

  followingLoading.value = true

  try {
    const response = await api.get(`/subscrip/users/${authUser.value.id}/following`)
    following.value = normalizeCollection(response.data)
  } catch (error) {
    console.error('Failed to load following users', error)
    following.value = []
  } finally {
    followingLoading.value = false
  }
}

const loadMessages = async (participant) => {
  if (!participant?.id) return

  messagesLoading.value = true
  sendError.value = ''

  try {
    const response = await api.get(`/messages/messages/${participant.id}`)

    activeParticipant.value = response.data?.participant || participant
    activeParticipantId.value = participant.id
    messages.value = normalizeCollection(response.data?.data)

    conversations.value = conversations.value.map((conversation) => {
      if (Number(conversation.participant?.id) === Number(participant.id)) {
        return {
          ...conversation,
          unread_count: 0,
          has_unread: false,
          last_message: conversation.last_message
            ? {
                ...conversation.last_message,
                is_read: true,
              }
            : conversation.last_message,
        }
      }

      return conversation
    })

    await scrollToBottom()
  } catch (error) {
    console.error('Failed to load messages', error)
    messages.value = []
  } finally {
    messagesLoading.value = false
  }
}

const openConversation = async (participant) => {
  showEmojiPicker.value = false
  await loadMessages(participant)
}

const ensureConversationExists = (participant, newMessage = null) => {
  const exists = conversations.value.some(
    (conversation) => Number(conversation.participant?.id) === Number(participant.id)
  )

  if (!exists) {
    conversations.value = [
      {
        participant,
        last_message: newMessage,
        unread_count: 0,
        has_unread: false,
      },
      ...conversations.value,
    ]
  }
}

const sendMessage = async () => {
  const content = messageForm.value.content.trim()

  if (!content || !activeParticipant.value?.id || sendLoading.value) return

  sendLoading.value = true
  sendError.value = ''

  try {
    const response = await api.post('/messages/messages', {
      receiver_id: activeParticipant.value.id,
      content,
    })

    const newMessage = response.data?.data

    if (newMessage) {
      messages.value = [...messages.value, newMessage]

      ensureConversationExists(activeParticipant.value, newMessage)

      conversations.value = conversations.value.map((conversation) => {
        if (Number(conversation.participant?.id) === Number(activeParticipant.value.id)) {
          return {
            ...conversation,
            last_message: newMessage,
            unread_count: 0,
            has_unread: false,
          }
        }

        return conversation
      })

      conversations.value = [...conversations.value].sort((a, b) => {
        const first = new Date(b.last_message?.created_at || 0).getTime()
        const second = new Date(a.last_message?.created_at || 0).getTime()
        return first - second
      })
    }

    messageForm.value.content = ''
    showEmojiPicker.value = false
    await scrollToBottom()
  } catch (error) {
    sendError.value =
      error.response?.data?.message || 'Failed to send message.'
  } finally {
    sendLoading.value = false
  }
}

const toggleEmojiPicker = () => {
  showEmojiPicker.value = !showEmojiPicker.value
}

const appendEmoji = (emoji) => {
  messageForm.value.content += emoji
  showEmojiPicker.value = false
}

const openUsersPopup = async () => {
  showFollowingPopup.value = false
  showUsersPopup.value = true
  usersPopupSearch.value = ''

  if (users.value.length === 0) {
    await loadUsers()
  }
}

const closeUsersPopup = () => {
  showUsersPopup.value = false
}

const openFollowingPopup = async () => {
  showUsersPopup.value = false
  showFollowingPopup.value = true
  followingPopupSearch.value = ''

  if (following.value.length === 0) {
    await loadFollowing()
  }
}

const closeFollowingPopup = () => {
  showFollowingPopup.value = false
}

const startChatFromPopup = async (user, source = 'users') => {
  const participant =
    source === 'following'
      ? {
          id: user.id,
          name: user.name,
          email: user.email,
          avatar: user.avatar,
        }
      : {
          id: user.id,
          name: user.name,
          email: user.email,
          avatar: user.profile?.avatar,
        }

  ensureConversationExists(participant)

  if (source === 'users') {
    closeUsersPopup()
  } else {
    closeFollowingPopup()
  }

  await openConversation(participant)
}

onMounted(async () => {
  await Promise.all([loadConversations(), loadUsers(), loadFollowing()])

  if (conversations.value.length > 0) {
    await openConversation(conversations.value[0].participant)
  }
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
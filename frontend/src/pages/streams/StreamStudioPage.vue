<script setup>
import { onMounted, onBeforeUnmount, ref, nextTick } from 'vue'
import { Room, Track } from 'livekit-client'
import { useRoute } from 'vue-router'
import api from '@/services/api'

const route = useRoute()

const room = ref(null)
const connected = ref(false)
const loading = ref(true)
const errorMessage = ref('')
const debugMessage = ref('')
const localVideoEl = ref(null)

const connectStudio = async () => {
  try {
    debugMessage.value = 'Requesting studio token...'

    const response = await api.post(`/stream/streams/${route.params.id}/studio-token`)
    const { token, url, room_name } = response.data.data || {}

    if (!token || !url || !room_name) {
      throw new Error('Token response is incomplete.')
    }

    debugMessage.value = `Connecting to ${room_name}...`

    const liveRoom = new Room()
    await liveRoom.connect(url, token)

    debugMessage.value = 'Connected. Enabling camera...'
    await liveRoom.localParticipant.setCameraEnabled(true)

    debugMessage.value = 'Enabling microphone...'
    await liveRoom.localParticipant.setMicrophoneEnabled(true)

    room.value = liveRoom
    connected.value = true

    await nextTick()

    const cameraPublication = Array.from(
      liveRoom.localParticipant.videoTrackPublications.values()
    ).find((pub) => pub.source === Track.Source.Camera)

    const localTrack = cameraPublication?.videoTrack

    if (localTrack && localVideoEl.value) {
      localTrack.attach(localVideoEl.value)
      localVideoEl.value.muted = true
      localVideoEl.value.autoplay = true
      localVideoEl.value.playsInline = true
    }

    debugMessage.value = 'Live started successfully.'
  } catch (error) {
    console.error('Live studio error:', error)
    console.error('Live studio response:', error?.response?.data)
    console.error('Live studio message:', error?.message)

    errorMessage.value =
      error?.response?.data?.message ||
      error?.message ||
      'Failed to start live studio.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  connectStudio()
})

onBeforeUnmount(() => {
  if (room.value) {
    room.value.disconnect()
  }
})
</script>

<template>
  <div class="min-h-screen bg-black p-6 text-white">
    <h1 class="mb-4 text-3xl font-bold">Stream Studio</h1>

    <div class="mb-6 aspect-video overflow-hidden rounded-xl bg-zinc-900">
      <video
        ref="localVideoEl"
        class="h-full w-full object-cover"
      ></video>
    </div>

    <p v-if="loading" class="text-zinc-400">Loading studio...</p>
    <p v-if="debugMessage" class="mb-3 text-sm text-zinc-300">{{ debugMessage }}</p>
    <p v-if="connected" class="text-green-400">Live started successfully.</p>
    <p v-if="errorMessage" class="text-red-400">{{ errorMessage }}</p>
  </div>
</template>
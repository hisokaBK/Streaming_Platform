<script setup>
import { onMounted, ref } from 'vue'
import { Room } from 'livekit-client'
import { useRoute } from 'vue-router'
import api from '@/services/api'

const route = useRoute()
const room = ref(null)
const connected = ref(false)
const errorMessage = ref('')

const connectStudio = async () => {
  try {
    const tokenResponse = await api.post(`/stream/streams/${route.params.id}/studio-token`)
    const { token, url } = tokenResponse.data.data

    const liveRoom = new Room()
    await liveRoom.connect(url, token)

    await liveRoom.localParticipant.setCameraEnabled(true)
    await liveRoom.localParticipant.setMicrophoneEnabled(true)

    room.value = liveRoom
    connected.value = true
  } catch (error) {
    console.error('Live studio error:', error)
    console.error('Live studio response:', error?.response?.data)
    console.error('Live studio message:', error?.message)
  
    errorMessage.value =
      error?.response?.data?.message ||
      error?.message ||
      'Failed to start live studio.'
  }
}

onMounted(() => {
  connectStudio()
})
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold">Stream Studio</h1>
    <p v-if="connected" class="text-green-500">Live started successfully.</p>
    <p v-if="errorMessage" class="text-red-500">{{ errorMessage }}</p>
  </div>
</template>
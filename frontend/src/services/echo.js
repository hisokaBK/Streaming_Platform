import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import api from '@/services/api'

window.Pusher = Pusher

const echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
  wsPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
  wssPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
  forceTLS: false,
  enabledTransports: ['ws', 'wss'],
  authorizer: (channel) => {
    return {
      authorize: (socketId, callback) => {
        api.post('/broadcasting/auth', {
          socket_id: socketId,
          channel_name: channel.name,
        })
          .then((response) => callback(false, response.data))
          .catch((error) => callback(true, error))
      },
    }
  },
})

window.Echo = echo

export default echo
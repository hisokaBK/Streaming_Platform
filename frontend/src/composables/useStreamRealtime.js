import echo from '@/services/echo'

export function useStreamRealtime(stream) {
  let subscribedStreamId = null

  const subscribeToStreamChannel = (streamId) => {
    if (!streamId) return

    unsubscribeFromStreamChannel()

    subscribedStreamId = String(streamId)

    echo.private(`stream.${subscribedStreamId}`)
      .listen('.comment.created', (event) => {
        if (!stream.value || !event?.comment) return

        const currentComments = Array.isArray(stream.value.comments)
          ? stream.value.comments
          : []

        const exists = currentComments.some(
          (comment) => Number(comment.id) === Number(event.comment.id)
        )

        if (exists) return

        stream.value = {
          ...stream.value,
          comments: [event.comment, ...currentComments],
          comments_count: Number(
            event.comments_count ?? (stream.value.comments_count || 0) + 1
          ),
        }
      })
      .listen('.reaction.updated', (event) => {
        if (!stream.value) return

        stream.value = {
          ...stream.value,
          reactions_summary: event.reactions_summary || {},
          reactions_count: Number(event.reactions_count || 0),
        }
      })
  }

  const unsubscribeFromStreamChannel = () => {
    if (!subscribedStreamId) return

    echo.leave(`stream.${subscribedStreamId}`)
    subscribedStreamId = null
  }

  return {
    subscribeToStreamChannel,
    unsubscribeFromStreamChannel,
  }
}
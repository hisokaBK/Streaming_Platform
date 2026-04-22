import echo from '@/services/echo'

export function useNotificationsRealtime(notifications, getAuthUserId, meta = null) {
  let subscribedUserId = null

  const subscribeToNotificationChannel = () => {
    const userId =
      typeof getAuthUserId === 'function'
        ? getAuthUserId()
        : getAuthUserId

    if (!userId) return

    unsubscribeFromNotificationChannel()

    subscribedUserId = String(userId)

    echo.private(`user.${subscribedUserId}.notifications`)
      .listen('.notification.created', (event) => {
        if (!event?.notification) return

        const currentNotifications = Array.isArray(notifications.value)
          ? notifications.value
          : []

        const exists = currentNotifications.some(
          (item) => Number(item.id) === Number(event.notification.id)
        )

        if (exists) return

        notifications.value = [event.notification, ...currentNotifications]

        if (meta?.value) {
          meta.value = {
            ...meta.value,
            total: Number(meta.value.total || 0) + 1,
          }
        }
      })
  }

  const unsubscribeFromNotificationChannel = () => {
    if (!subscribedUserId) return

    echo.leave(`user.${subscribedUserId}.notifications`)
    subscribedUserId = null
  }

  return {
    subscribeToNotificationChannel,
    unsubscribeFromNotificationChannel,
  }
}
/* global PWA */
self.addEventListener('push', event => {
  if (!(self.Notification && self.Notification.permission === 'granted')) {
    return
  }

  const sendNotification = (data) => {
    /**
     * @param {Object} jsonData
     * @param {string} jsonData.body
     */
    let jsonData = JSON.parse(data),
        title = jsonData.title

    delete jsonData.title

    return self.registration.showNotification(title, jsonData)
  };

  if (event.data) {
    const message = event.data.text()
    event.waitUntil(sendNotification(message))
  }
})

self.addEventListener('notificationclick', event => {
  event.notification.close()

  const clients = self.clients

  event.waitUntil(clients.matchAll({
    type: "window"
  }).then(function(clientList) {
    for (let i = 0; i < clientList.length; i++) {
      let client = clientList[i];

      if (client.url === '/' && 'focus' in client)
        return client.focus()
    }
    if (clients.openWindow)
      return clients.openWindow(event.notification.data.url ?? PWA.app.url)
  }))
})
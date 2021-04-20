self.addEventListener('push', function (event) {
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
  }

  if (event.data) {
    const message = event.data.text()
    event.waitUntil(sendNotification(message))
  }
})
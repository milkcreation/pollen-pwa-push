self.addEventListener('push', function (event) {
  if (!(self.Notification && self.Notification.permission === 'granted')) {
    return
  }

  const sendNotification = data => {
    /**
     * @param {Object} json
     */
    let json = JSON.parse(data),
        title = json.title

    delete json.title

    return self.registration.showNotification(title, json)
  };

  if (event.data) {
    const message = event.data.text()
    event.waitUntil(sendNotification(message))
  }
})
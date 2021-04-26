'use strict'

document.addEventListener('DOMContentLoaded', () => {
  const $notifierSend = document.getElementById('PwaPushNotifier-form'),
        serializeForm = (form) => {
          let obj = {},
              formData = new FormData(form)

          for (let key of formData.keys()) {
            obj[key] = formData.get(key)
          }
          return obj
        }

  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('sw.js')
        .then(() => {
          console.info('[PwaPush Notifier] Service worker has been registered')
        })
        .catch(e => {
          console.error('[PwaPush Notifier] Service worker registration failed', e)
        })
  } else {
    console.warn('[PwaPush Notifier] Service workers are not supported by this browser')
  }

  $notifierSend.addEventListener('submit', e => {
    e.preventDefault()

    const data = serializeForm(e.target)

    fetch('notifier.send', {
      method: 'POST',
      headers: {
        'Content-type': 'application/json; charset=UTF-8',
        'X-Requested-with': 'XMLHttpRequest'
      },
      body: JSON.stringify(data),
    })
        .then(response => {
          if (response.ok) {
            return response.json()
          }
        })
        .then(json => {
          console.info(json)
        })
        .catch(e => {
          console.error(e)
        })
  })
})
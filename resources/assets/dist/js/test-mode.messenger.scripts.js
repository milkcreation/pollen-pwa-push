/* global PwaPushMessenger */
'use strict'

document.addEventListener('DOMContentLoaded', () => {
  const $messengerForm = document.getElementById('PwaPushMessenger-form'),
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
          console.info('[PwaPush Messenger] Service worker has been registered')
        })
        .catch(e => {
          console.error('[PwaPush Messenger] Service worker registration failed', e)
        })
  } else {
    console.warn('[PwaPush Messenger] Service workers are not supported by this browser')
  }

  $messengerForm.addEventListener('submit', e => {
    e.preventDefault()

    const data = serializeForm(e.target)

    if(data['message_id'] === undefined) {
        alert(PwaPushMessenger.l10n.error.missing)
        return
    }

    fetch('messenger.send', {
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
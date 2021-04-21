/* global PwaPushTester */
'use strict'

/**
 * @see https://github.com/Minishlink/web-push-php-example/blob/master/src/app.js
 * @see https://developers.google.com/web/updates/2015/03/push-notifications-on-the-open-web
 */

/**
 * @type {boolean}
 */
let isPushEnabled = false

const applicationServerKey = PwaPushTester.public_key,
    $subscribeButton = document.querySelector('#PwaPushTester-button--subscribe'),
    $sendButton = document.querySelector('#PwaPushTester-button--send'),
    intialiseState = () => {
      // Are Notifications supported in the service worker?
      if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
        console.warn('[PwaPush Tester] Notifications are not supported by this browser')
        changePushButtonState('incompatible')
        return
      }

      // Check the current Notification permission.
      // If its denied, the button should appears as such, until the user changes the permission manually
      if (Notification.permission === 'denied') {
        console.warn('[PwaPush Tester] Notifications are denied by the user')
        changePushButtonState('incompatible')
        return
      }

      // Check if push messaging is supported
      if (!('PushManager' in window)) {
        console.warn('[PwaPush Tester] Push notifications are not supported by this browser')
        changePushButtonState('incompatible')
        return
      }

      pushSubscriptionUpdate()
    },
    changePushButtonState = state => {
      switch (state) {
        case 'enabled':
          $subscribeButton.disabled = false
          $subscribeButton.textContent = PwaPushTester.l10n.enabled
          isPushEnabled = true
          $sendButton.disabled = false
          break
        case 'disabled':
          $subscribeButton.disabled = false
          $subscribeButton.textContent = PwaPushTester.l10n.disabled
          isPushEnabled = false
          $sendButton.disabled = true
          break
        case 'computing':
          $subscribeButton.disabled = true
          $subscribeButton.textContent = PwaPushTester.l10n.computing
          $sendButton.disabled = true
          break
        case 'incompatible':
          $subscribeButton.disabled = true
          $subscribeButton.textContent = PwaPushTester.l10n.incompatible
          $sendButton.disabled = true
          break
        default:
          console.error('[PwaPush Tester] Unhandled push button state', state)
          break
      }
    },
    urlBase64ToUint8Array = base64String => {
      const padding = '='.repeat((4 - (base64String.length % 4)) % 4),
          base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/'),
          rawData = window.atob(base64),
          outputArray = new Uint8Array(rawData.length)

      for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i)
      }

      return outputArray
    },
    checkNotificationPermission = () => {
      return new Promise((resolve, reject) => {
        if (Notification.permission === 'denied') {
          return reject(new Error('[PwaPush Tester] Push messages are blocked.'))
        }
        if (Notification.permission === 'granted') {
          return resolve()
        }
        if (Notification.permission === 'default') {
          return Notification.requestPermission().then(result => {
            if (result !== 'granted') {
              reject(new Error('[PwaPush Tester] Bad permission result'))
            } else {
              resolve()
            }
          })
        }
        return reject(new Error('[PwaPush Tester] Unknown permission'))
      })
    },
    pushSubscribe = () => {
      changePushButtonState('computing')

      checkNotificationPermission()
          .then(() => navigator.serviceWorker.ready)
          .then(serviceWorkerRegistration =>
              serviceWorkerRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(applicationServerKey),
              })
          )
          .then(subscription => {
            // Subscription was successful
            // create subscription on your server
            return pushSubscriptionXhr(subscription, 'POST')
          })
          .then(subscription => subscription && changePushButtonState('enabled')) // update your UI
          .catch(e => {
            if (Notification.permission === 'denied') {
              // The user denied the notification permission which
              // means we failed to subscribe and the user will need
              // to manually change the notification permission to
              // subscribe to push messages
              console.warn('[PwaPush Tester] Notifications are denied by the user.')
              changePushButtonState('incompatible')
            } else {
              // A problem occurred with the subscription; common reasons
              // include network errors or the user skipped the permission
              console.error('[PwaPush Tester] Impossible to subscribe to push notifications', e)
              changePushButtonState('disabled')
            }
          })
    },
    pushUnsubscribe = () => {
      changePushButtonState('computing')

      // To unsubscribe from push messaging, you need to get the subscription object
      navigator.serviceWorker.ready
          .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
          .then(subscription => {
            // Check that we have a subscription to unsubscribe
            if (!subscription) {
              // No subscription object, so set the state
              // to allow the user to subscribe to push
              changePushButtonState('disabled')
              return
            }

            // We have a subscription, unsubscribe
            // Remove push subscription from server
            return pushSubscriptionXhr(subscription, 'DELETE')
          })
          .then(subscription => subscription.unsubscribe())
          .then(() => changePushButtonState('disabled'))
          .catch(e => {
            // We failed to unsubscribe, this can lead to
            // an unusual state, so  it may be best to remove
            // the users data from your data store and
            // inform the user that you have done so
            console.error('[PwaPush Tester] Error when unsubscribing the user', e)
            changePushButtonState('disabled')
          })
    },
    pushSubscriptionUpdate = () => {
      navigator.serviceWorker.ready
          .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
          .then(subscription => {
            changePushButtonState('disabled')

            if (!subscription) {
              // We aren't subscribed to push, so set UI to allow the user to enable push
              return
            }
            // Keep your server in sync with the latest endpoint
            return pushSubscriptionXhr(subscription, 'PUT')
          })
          .then(subscription => subscription && changePushButtonState('enabled')) // Set your UI to show they have subscribed for push messages
          .catch(e => {
            console.error('[PwaPush Tester] Error when updating the subscription', e)
          })
    },
    pushSubscriptionXhr = (subscription, method) => {
      const key = subscription.getKey('p256dh'),
          token = subscription.getKey('auth'),
          contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0]

      return fetch('tester.subscription', {
        method: method,
        headers: {
          'Content-type': 'application/json; charset=UTF-8',
          'X-Requested-with': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          endpoint: subscription.endpoint,
          publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
          authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
          contentEncoding: contentEncoding
        })
      })
          .then(response => {
            if (response.ok) {
              return response.json()
            } else {
              console.warn(response.status)
              return Promise.reject(response)
            }
          })
          .then(json => {
            console.info(json)
            return subscription
          })
          .catch(e => {
            console.error(e)
          })
    },
    pushSendXhr = subscription => {
      const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0],
          jsonSubscription = subscription.toJSON()

      return fetch('tester.send', {
        method: 'POST',
        headers: {
          'Content-type': 'application/json; charset=UTF-8',
          'X-Requested-with': 'XMLHttpRequest'
        },
        body: JSON.stringify(Object.assign(jsonSubscription, {contentEncoding}))
      })
          .then(response => {
            if (response.ok) {
              return response.json()

            } else {
              console.warn(response.status)
              return Promise.reject(response)
            }
          })
          .then(json => {
            console.info(json)
            return subscription
          })
          .catch(e => {
            console.error(e)
          })
    }
/**
 * @param {Object} PushTest
 * @param {Object} PushTest.l10n
 * @param {string} PushTest.l10n.button_default
 * @param {string} PushTest.l10n.sending
 * @param {string} PushTest.l10n.enabled
 * @param {string} PushTest.l10n.disabled
 * @param {string} PushTest.l10n.computing
 * @param {string} PushTest.l10n.incompatible
 * @param {string} PushTest.l10n.please_enabling
 * @param {string} PushTest.public_key
 */
document.addEventListener('DOMContentLoaded', () => {
  if (!$subscribeButton || !$sendButton) {
    return
  }

  $subscribeButton.addEventListener('click', function () {
    if (isPushEnabled) {
      pushUnsubscribe()
    } else {
      pushSubscribe()
    }
  })

  $sendButton.addEventListener('click', () =>
      navigator.serviceWorker.ready
          .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
          .then(subscription => {
            if (!subscription) {
              alert(PwaPushTester.l10n.please_enabling)
              return
            }
            return pushSendXhr(subscription)
          })
  )

  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('sw.js')
        .then(() => {
          console.info('[PwaPush Tester] Service worker has been registered')
          intialiseState()
        })
        .catch(e => {
          console.error('[PwaPush Tester] Service worker registration failed', e)
          changePushButtonState('incompatible')
        })
  } else {
    console.warn('[PwaPush Tester] Service workers are not supported by this browser')
    changePushButtonState('incompatible')
  }
})

'use strict'

import Observer from '@pollen-solutions/support/resources/assets/src/js/mutation-observer'

class PwaPush {
  constructor(el, options = {}) {
    this.debug = false

    this.initialized = false

    this.options = {
      classes: {
        title: 'PwaPush-title',
        content: 'PwaPush-content',
        close: 'PwaPush-close',
        switch: 'PwaPush-switch',
        handler: 'PwaPush-handler',
      }
    }

    this.control = {
      title: 'title',
      content: 'content',
      close: 'close',
      switch: 'switch',
      handler: 'handler'
    }

    this.el = el
    this.elClose = null
    this.elHandler = null
    this.elSwitch = null
    this.isPushEnabled = false

    this._initOptions(options)
    this._init()
  }

  // PLUGINS
  // -------------------------------------------------------------------------------------------------------------------
  // Initialisation des options
  _initOptions(options) {
    let tagOptions = this.el.dataset.options || null

    if (tagOptions) {
      try {
        tagOptions = decodeURIComponent(tagOptions)
      } catch (e) {
        if (this.debug) {
          console.debug(e)
        }
      }
    }

    try {
      tagOptions = JSON.parse(tagOptions)
    } catch (e) {
      if (this.debug) {
        console.debug(e)
      }
    }

    if (typeof tagOptions === 'object' && tagOptions !== null) {
      Object.assign(this.options, tagOptions)
    }

    Object.assign(this.options, options)
  }

  // Resolution d'objet depuis une clé à point
  _objResolver(dotKey, obj) {
    return dotKey.split('.').reduce(function (prev, curr) {
      return prev ? prev[curr] : null
    }, obj || self)
  }

  // Initialisation
  _init() {
    if (!('serviceWorker' in navigator)) {
      console.warn('[PwaPush] Service workers are not supported by this browser')
      return
    }

    if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
      console.warn('[PwaPush] Notifications are not supported by this browser')
      return
    }

    if (Notification.permission === 'denied') {
      console.warn('[PwaPush] Notifications are denied by the user')
      return
    }

    if (!('PushManager' in window)) {
      console.warn('[PwaPush] Push notifications are not supported by this browser')
      return
    }

    this.endpoint = this.option('endpoint')
    if (this.endpoint === null) {
      console.error('[PwaPush] Subscribe endpoint is required.')
      return
    }

    this.publicKey = this.option('public_key')
    if (this.publicKey === null) {
      console.error('[PwaPush] Public key is required.')
      return
    }

    this._initControls()
    this._initEvents()

    navigator.serviceWorker.ready
        .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
        .then(subscription => {
          this._changeState('disabled')

          if (!subscription) {
            return
          }

          return this._xhrSubscription(subscription, 'PUT')
        })
        .then(subscription => subscription && this._changeState('enabled'))
        .catch(e => {
          console.error('[PwaPush] Error when updating the subscription', e)
        })

    this.initialized = true

    if (this.debug) {
      console.debug('[PwaPush] fully initialized')
    }
  }

  // Initialisation
  _destroy() {
    this.initialized = true
  }

  // INITIALISATIONS
  // -------------------------------------------------------------------------------------------------------------------
  // Initialisation des éléments de contrôle.
  _initControls() {
    let $title = document.querySelector('[data-pwa-push="title"]')
    if ($title) {
      $title.classList.add(this.option('classes.title'))
    }

    let $content = document.querySelector('[data-pwa-push="content"]')
    if ($content) {
      $content.classList.add(this.option('classes.content'))
    }

    this.elClose = document.querySelector('[data-pwa-push="close"]')
    if (this.elClose) {
      this.elClose.classList.add(this.option('classes.close'))
    }

    this.elSwitch = document.querySelector('[data-pwa-push="switch"]')
    if (this.elSwitch) {
      this.elSwitch.classList.add(this.option('classes.switch'))
      this.elSwitch.disabled = true
    }

    this.elHandler = document.querySelector('[data-pwa-push="handler"]')
    if (this.elHandler) {
      this.elHandler.classList.add(this.option('classes.handler'))
      this.elHandler.style.display = 'block'
    }

    if (this.debug) {
      console.debug('[PwaPush] controls initialized')
    }
  }

  // Initialisation des événements déclenchement.
  _initEvents() {
    if (this.elHandler) {
      this.elHandler.addEventListener('click', e => {
        e.preventDefault()

        if (this.el.classList.contains('show')) {
          this.el.classList.toggle('show', false)
        } else {
          this.el.classList.toggle('show', true)
        }
      })
    }

    if (this.elClose) {
      this.elClose.addEventListener('click', e => {
        e.preventDefault()
        this._setDismissed()
        this.el.classList.toggle('show', false)
      })
    }

    if (this.elSwitch) {
      this.elSwitch.addEventListener('change', () => {
        if (this.isPushEnabled) {
          this._unsubscribe()
        } else {
          this._subscribe()
        }
      })
    }

    document.addEventListener('click', e => {
      let outside = true

      for (let node = e.target; node !== document.body; node = node.parentNode) {
        if (node.classList.contains('PwaPush')) {
          outside = false
        }
      }

      if (outside) {
        this.el.classList.toggle('show', false)
      }
    })

    if (this.debug) {
      console.debug('[PwaPush] events initialized')
    }
  }

  // EVENEMENTS
  // -----------------------------------------------------------------------------------------------------------------

  // ACTIONS
  // -----------------------------------------------------------------------------------------------------------------
  _clearDismissed() {
    localStorage.setItem('pwa-push-dismiss', 'false')
  }

  _getDismissed() {
    return localStorage.getItem('pwa-push-dismiss') === 'true'
  }

  _setDismissed() {
    localStorage.setItem('pwa-push-dismiss', 'true')
  }

  _changeState(state) {
    switch (state) {
      case 'enabled':
        this.isPushEnabled = true
        this.elSwitch.disabled = false
        this.elSwitch.checked = true
        break
      case 'disabled':
        this.isPushEnabled = false
        this.elSwitch.disabled = false
        this.elSwitch.checked = false
        break
      case 'computing':
        this.elSwitch.disabled = true
        break
      case 'incompatible':
        this.elSwitch.disabled = true
        break
      default:
        console.error('[PwaPush] Unhandled push button state', state)
        break
    }
  }

  _checkNotificationPermission() {
    return new Promise((resolve, reject) => {
      if (Notification.permission === 'denied') {
        return reject(new Error('[PwaPush] Push messages are blocked.'))
      }
      if (Notification.permission === 'granted') {
        return resolve()
      }
      if (Notification.permission === 'default') {
        return Notification.requestPermission().then(result => {
          if (result !== 'granted') {
            reject(new Error('[PwaPush] Bad permission result'))
          } else {
            resolve()
          }
        })
      }
      return reject(new Error('[PwaPush] Unknown permission'))
    })
  }

  _subscribe() {
    this._changeState('computing')

    this._checkNotificationPermission()
        .then(() => navigator.serviceWorker.ready)
        .then(serviceWorkerRegistration =>
            serviceWorkerRegistration.pushManager.subscribe({
              userVisibleOnly: true,
              applicationServerKey: this._urlBase64ToUint8Array(this.publicKey),
            })
        )
        .then(subscription => {
          return this._xhrSubscription(subscription, 'POST')
        })
        .then(subscription => subscription && this._changeState('enabled'))
        .catch(e => {
          if (Notification.permission === 'denied') {
            console.warn('[PwaPush] Notifications are denied by the user.')
            this._changeState('incompatible')
          } else {
            console.error('[PwaPush] Impossible to subscribe to push notifications', e)
            this._changeState('disabled')
          }
        })
  }

  _unsubscribe() {
    this._changeState('computing')

    navigator.serviceWorker.ready
        .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
        .then(subscription => {
          if (!subscription) {
            this._changeState('disabled')
            return
          }

          return this._xhrSubscription(subscription, 'DELETE')
        })
        .then(subscription => subscription.unsubscribe())
        .then(() => this._changeState('disabled'))
        .catch(e => {
          console.error('[PwaPush] Error when unsubscribing the user', e)
          this._changeState('disabled')
        })
  }

  _urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4)
    const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/')

    const rawData = window.atob(base64)
    const outputArray = new Uint8Array(rawData.length)

    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i)
    }
    return outputArray
  }

  _xhrSubscription(subscription, method) {
    const key = subscription.getKey('p256dh'),
        token = subscription.getKey('auth'),
        contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0]

    return fetch(this.endpoint, {
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
          }

          return Promise.reject(response)
        })
        .then(json => {
          if (this.debug) {
            console.debug(json)
          }

          return subscription
        })
        .catch(e => {
          throw e
        })
  }

  // ACCESSEURS
  // -------------------------------------------------------------------------------------------------------------------
  // Récupération d'options (syntaxe à point permise)
  option(key = null, defaults = null) {
    if (key === null) {
      return this.options
    }

    return this._objResolver(key, this.options) ?? defaults
  }
}

window.addEventListener('load', () => {
  const $elements = document.querySelectorAll('[data-observe="pwa-push"]')

  if ($elements) {
    for (const $el of $elements) {
      new PwaPush($el)
    }
  }

  Observer('[data-observe="pwa-push"]', function ($el) {
    new PwaPush($el)
  })
})

export default PwaPush
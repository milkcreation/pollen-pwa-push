<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
    <meta charset="utf-8"/>
    <meta content="IE=edge, chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Notificateur</title>
</head>

<body>
<form id="notifierForm">
    <button type="submit">Envoyer</button>
</form>

<script>
  navigator.serviceWorker.register('sw.js').then(
      () => {
        console.log('Pwa Push Notifier >> Service worker has been registered');
      },
      e => {
        console.error('Pwa Push Notifier >> Service worker registration failed', e);
      }
  )
  const $notifierSend = document.getElementById('notifierForm')

  $notifierSend.addEventListener('submit', e => {
    e.preventDefault()

    fetch('notifier-send', {
      method: 'POST',
      headers: {
        'Content-type': 'application/json; charset=UTF-8',
        'X-Requested-with': 'XMLHttpRequest'
      }
    })
        .then(response => {
          if (response.ok) {
            return response.json()
          }
        })
        .then(json => {
          console.log(json)
        })
        .catch(e => {
          console.log(e)
        })
  })
</script>
</body>
</html>
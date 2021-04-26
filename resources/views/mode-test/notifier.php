<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="IE=edge, chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title><?php echo $this->get('PwaPushNotifier.l10n.title'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="stylesheet" href="notifier.styles.css" type="text/css" media="all">
</head>

<body class="PwaPushTestMode">
<div class="PwaPushTestMode-container">
    <div class="PwaPushTestMode-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
            <circle cx="32" cy="28.5" r="2.5"/>
            <path d="M14,55a3,3,0,0,0-3-3H5a3,3,0,0,0-3,3v7H14Z"/>
            <circle cx="8" cy="48.5" r="3.5"/>
            <rect x="7" y="40" width="2" height="2"/>
            <rect x="7" y="36" width="2" height="2"/>
            <rect x="7" y="32" width="2" height="2"/>
            <rect x="7" y="28" width="2" height="2"/>
            <rect x="11" y="28" width="2" height="2"/>
            <rect x="15" y="28" width="2" height="2"/>
            <rect x="19" y="28" width="2" height="2"/>
            <rect x="55" y="40" width="2" height="2"/>
            <rect x="55" y="36" width="2" height="2"/>
            <rect x="55" y="32" width="2" height="2"/>
            <rect x="55" y="28" width="2" height="2"/>
            <rect x="51" y="28" width="2" height="2"/>
            <rect x="47" y="28" width="2" height="2"/>
            <rect x="43" y="28" width="2" height="2"/>
            <circle cx="32" cy="7" r="1"/>
            <path d="M27.763,4.348a4.99,4.99,0,0,0,0,5.3l1.694-1.064a2.988,2.988,0,0,1,0-3.176Z"/>
            <path d="M36.237,9.652a4.99,4.99,0,0,0,0-5.3L34.543,5.412a2.988,2.988,0,0,1,0,3.176Z"/>
            <path d="M24.373,2.219a9.011,9.011,0,0,0,0,9.562l1.7-1.062a7.009,7.009,0,0,1,0-7.438Z"/>
            <path d="M39.627,11.781a9.011,9.011,0,0,0,0-9.562l-1.7,1.062a7.009,7.009,0,0,1,0,7.438Z"/>
            <path d="M23,39a3,3,0,0,0,3,3H38a3,3,0,0,0,3-3V17a3,3,0,0,0-3-3H33V10H31v4H26a3,3,0,0,0-3,3Zm2-21H39V38H36V33a2,2,0,0,0-2-2H30a2,2,0,0,0-2,2v5H25Z"/>
            <path d="M18,55v7H30V55a3,3,0,0,0-3-3H21A3,3,0,0,0,18,55Z"/>
            <circle cx="24" cy="48.5" r="3.5"/>
            <path d="M34,55v7H46V55a3,3,0,0,0-3-3H37A3,3,0,0,0,34,55Z"/>
            <circle cx="40" cy="48.5" r="3.5"/>
            <path d="M59,52H53a3,3,0,0,0-3,3v7H62V55A3,3,0,0,0,59,52Z"/>
            <circle cx="56" cy="48.5" r="3.5"/>
        </svg>
    </div>

    <h1 class="PwaPushTestMode-title">
        <?php echo $this->get('PwaPushNotifier.l10n.title'); ?>
    </h1>

    <p class="PwaPushTestMode-text">
        <?php echo $this->get('PwaPushNotifier.l10n.text'); ?>
    </p>

    <p class="PwaPushTestMode-infos">
        <?php echo $this->get('PwaPushNotifier.l10n.infos'); ?>
    </p>

    <form id="PwaPushNotifier-form" method="post" action="">
        <h2>
            <?php echo $this->get('PwaPushNotifier.l10n.form.title'); ?>
        </h2>
        <div class="PwaPushTestMode-formRow">
            <input
                    type="text"
                    class="PwaPushTestMode-formControl PwaPushTestMode-formControl--text"
                    name="title"
                    placeholder="<?php echo $this->get('PwaPushNotifier.l10n.fields.title.placeholder'); ?>"
                    value="<?php echo $this->get('PwaPushNotifier.l10n.fields.title.value'); ?>"
            />
        </div>
        <div class="PwaPushTestMode-formRow">
            <textarea
                    class="PwaPushTestMode-formControl PwaPushTestMode-formControl--textarea"
                    name="body"
                    placeholder="<?php echo $this->get('PwaPushNotifier.l10n.fields.body.placeholder'); ?>"
            ><?php echo $this->get('PwaPushNotifier.l10n.fields.body.value'); ?></textarea>
        </div>
        <div class="PwaPushTestMode-formRow">
            <button class="PwaPushTestMode-button" type="submit">
                <?php echo $this->get('PwaPushNotifier.l10n.submit.text'); ?>
            </button>
        </div>
    </form>

<script type="text/javascript" src="notifier.scripts.js"></script>
</body>
</html>
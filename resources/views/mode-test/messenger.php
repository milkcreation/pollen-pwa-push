<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="IE=edge, chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title><?php echo $this->get('PwaPushMessenger.l10n.title'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="stylesheet" href="messenger.styles.css" type="text/css" media="all">
</head>

<body class="PwaPushTestMode">
<div class="PwaPushTestMode-container">
    <div class="PwaPushTestMode-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="15 15 70 70">
            <path d="M44.61,49.54a1.15,1.15,0,0,1-.81-.33l-6.57-6.58H18.29a3,3,0,0,1-3-3V27.9a3,3,0,0,1,3-3H49.55a3,3,0,0,1,3,3V39.61a3,3,0,0,1-3,3h-3.5a.29.29,0,0,0-.29.29v5.47a1.16,1.16,0,0,1-.71,1.07A1.32,1.32,0,0,1,44.61,49.54ZM18.29,27.18a.72.72,0,0,0-.72.72V39.61a.73.73,0,0,0,.72.73H37.34a2.05,2.05,0,0,1,1.44.59l4.69,4.69v-2.7a2.59,2.59,0,0,1,2.58-2.58h3.5a.73.73,0,0,0,.72-.73V27.9a.72.72,0,0,0-.72-.72ZM37.34,42.63h0Z"/>
            <path d="M65.06,72.79h0l-31.41,0a1.15,1.15,0,0,1-1.14-1.15l0-30.12a1.14,1.14,0,0,1,1.15-1.14h3.64a2.05,2.05,0,0,1,1.44.59l4.69,4.69v-2.7a2.59,2.59,0,0,1,2.58-2.58h3.5a.73.73,0,0,0,.72-.73V27.9a.72.72,0,0,0-.72-.72H33.72a1.13,1.13,0,0,1-.81-.34,1.14,1.14,0,0,1-.34-.81V22.79a1.18,1.18,0,0,1,.33-.82,1,1,0,0,1,.82-.33l31.4,0a1.14,1.14,0,0,1,1.15,1.15l0,22.79a1.15,1.15,0,0,1-1.15,1.14H50.45a.73.73,0,0,0-.72.73V59.2a.72.72,0,0,0,.72.72H54a2.59,2.59,0,0,1,2.58,2.59V65.2l4.69-4.68a2,2,0,0,1,1.44-.6h2.41a1.14,1.14,0,0,1,1.15,1.15V71.65A1.15,1.15,0,0,1,65.06,72.79ZM34.81,70.45l29.1,0V62.22H62.66L56.2,68.79a1.15,1.15,0,0,1-2-.81V62.51a.29.29,0,0,0-.29-.29h-3.5a3,3,0,0,1-3-3V47.49a3,3,0,0,1,3-3H64L64,24l-29.11,0v.94H49.55a3,3,0,0,1,3,3V39.61a3,3,0,0,1-3,3h-3.5a.29.29,0,0,0-.29.29v5.47a1.15,1.15,0,0,1-2,.82l-6.65-6.65-2.3,0Z"/>
            <path d="M70.31,46.76H65.1A1.13,1.13,0,0,1,64,45.61L64,24l-29.11,0V26a1.15,1.15,0,0,1-1.15,1.15h-5.2a1.11,1.11,0,0,1-.81-.34,1.14,1.14,0,0,1-.34-.81V22.29a6.89,6.89,0,0,1,6.87-6.87h0l30.36.05a6.88,6.88,0,0,1,6.87,6.88l0,23.27A1.14,1.14,0,0,1,70.31,46.76Zm-4.06-2.3h2.91l0-22.11a4.6,4.6,0,0,0-4.58-4.59l-30.35,0h0a4.6,4.6,0,0,0-4.58,4.58v2.58h2.9V22.79a1.18,1.18,0,0,1,.33-.82,1,1,0,0,1,.82-.33l31.4,0a1.14,1.14,0,0,1,1.15,1.15Z"/>
            <path d="M64.53,84.58h0l-30.35,0a6.89,6.89,0,0,1-6.87-6.89l.05-36.17a1.15,1.15,0,0,1,1.15-1.14h5.2a1.14,1.14,0,0,1,1.15,1.15l0,29,29.1,0V61.07a1.16,1.16,0,0,1,1.15-1.15h5.21a1.11,1.11,0,0,1,.81.34,1.13,1.13,0,0,1,.34.81l0,16.65a6.88,6.88,0,0,1-6.88,6.86ZM29.65,42.63l0,35a4.59,4.59,0,0,0,4.57,4.59l30.35,0h0a4.58,4.58,0,0,0,4.58-4.57l0-15.49H66.22v9.43a1.15,1.15,0,0,1-1.15,1.14h0l-31.41,0a1.15,1.15,0,0,1-1.14-1.15l0-29Z"/>
            <path d="M55.39,69.13A1.12,1.12,0,0,1,55,69,1.15,1.15,0,0,1,54.24,68V62.51a.29.29,0,0,0-.29-.29h-3.5a3,3,0,0,1-3-3V47.49a3,3,0,0,1,3-3H81.71a3,3,0,0,1,3,3V59.2a3,3,0,0,1-3,3h-19L56.2,68.79A1.16,1.16,0,0,1,55.39,69.13ZM50.45,46.76a.73.73,0,0,0-.72.73V59.2a.72.72,0,0,0,.72.72H54a2.59,2.59,0,0,1,2.58,2.59V65.2l4.69-4.68a2,2,0,0,1,1.44-.6h19a.72.72,0,0,0,.72-.72V47.49a.73.73,0,0,0-.72-.73Z"/>
            <path d="M54.07,78.45H44.72a1.15,1.15,0,1,1,0-2.3h9.35a1.15,1.15,0,1,1,0,2.3Z"/>
            <polygon points="76.53 55.35 76.53 51.37 72.54 51.37 72.54 55.35 76.53 55.35 76.53 55.35"/>
            <polygon points="68.3 55.35 68.3 51.37 64.31 51.37 64.31 55.35 68.3 55.35 68.3 55.35"/>
            <polygon points="60.09 55.35 60.09 51.37 56.1 51.37 56.1 55.35 60.09 55.35 60.09 55.35"/>
            <polygon points="43.63 35.77 43.63 31.78 39.64 31.78 39.64 35.77 43.63 35.77 43.63 35.77"/>
            <polygon points="35.39 35.77 35.39 31.78 31.41 31.78 31.41 35.77 35.39 35.77 35.39 35.77"/>
            <polygon points="27.18 35.77 27.18 31.78 23.2 31.78 23.2 35.77 27.18 35.77 27.18 35.77"/>
        </svg>
    </div>

    <h1 class="PwaPushTestMode-title">
        <?php echo $this->get('PwaPushMessenger.l10n.title'); ?>
    </h1>

    <p class="PwaPushTestMode-text">
        <?php echo $this->get('PwaPushMessenger.l10n.text'); ?>
    </p>

    <p class="PwaPushTestMode-infos">
        <?php echo $this->get('PwaPushMessenger.l10n.infos'); ?>
    </p>

    <h2 class="mb-4">
        <?php echo $this->get('PwaPushMessenger.l10n.table.title'); ?>
    </h2>
    <?php if ($messages = $this->get('PwaPushMessenger.messages.datas')) : ?>
    <form id="PwaPushMessenger-form" method="post" action="">
        <table class="PwaPushTestMode-table table mb-4">
            <thead>
            <th scope="col"></th>
            <th scope="col"><?php echo $this->get('PwaPushMessenger.l10n.table.head.message'); ?></th>
            <th scope="col"><?php echo $this->get('PwaPushMessenger.l10n.table.head.created_at'); ?></th>
            </thead>
            <tbody>
            <?php foreach ($messages as $mess) : ?>
                <tr>
                    <td><input type="radio" name="message_id" value="<?php echo $mess->id; ?>"></td>
                    <td>
                        <div>
                            <div><b><?php echo $mess->payload['title'] ?? ''; ?></b></div>
                        </div>
                        <div>
                            <div><?php echo nl2br($mess->payload['body']) ?? ''; ?></div>
                        </div>
                    </td>
                    <td><?php echo $mess->created_at; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <button class="PwaPushTestMode-button" type="submit">
            <?php echo $this->get('PwaPushMessenger.l10n.button.text'); ?>
        </button>
    </form>
    <?php endif; ?>

    <script type="text/javascript">
        <?php echo 'let PwaPushMessenger = ' . json_encode($this->get('PwaPushMessenger', [])) . ';'; ?>
    </script>

    <script type="text/javascript" src="messenger.scripts.js"></script>
</body>
</html>
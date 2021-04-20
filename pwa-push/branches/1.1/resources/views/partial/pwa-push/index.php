<?php
/**
 * @var Pollen\Partial\PartialViewLoaderInterface $this
 */
?>
<div <?php echo $this->htmlAttrs(); ?>>
    <?php if ($title = $this->get('title')) : ?>
        <h3 class="<?php echo $this->get('classes.title'); ?>" data-pwa-push="title"><?php echo $title; ?></h3>
    <?php endif; ?>

    <?php if ($content = $this->get('content')) : ?>
        <div class="<?php echo $this->get('classes.content'); ?>" data-pwa-push="content"><?php echo $content; ?></div>
    <?php endif; ?>

    <?php if ($handler = $this->get('handler')) : ?>
    <a href="#" class="<?php echo $this->get('classes.handler'); ?>" data-pwa-push="handler">
        <?php echo $handler; ?>
    </a>
    <?php endif; ?>

    <label class="<?php echo $this->get('classes.switch'); ?>">
        <input type="checkbox" data-pwa-push="switch" disabled>
        <span></span>
    </label>

    <a href="#" class="<?php echo $this->get('classes.close'); ?>" data-pwa-push="close">
        <?php echo $this->get('close'); ?>
    </a>
</div>

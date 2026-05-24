<?php

if (! defined('ABSPATH')) {
    exit;
}
?>
<div id="ez-penalty-skeleton" class="htmx-indicator absolute inset-0 z-20 flex flex-col gap-2 p-6 bg-base-100/85 rounded-box border border-base-200">
    <?php for ($i = 0; $i < 6; $i++) : ?>
        <div class="skeleton h-10 w-full"></div>
    <?php endfor; ?>
</div>

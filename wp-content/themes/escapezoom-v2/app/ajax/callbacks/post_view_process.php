<?php
$dispatcher = new \EscapeZoom\Core\Ajax\CoreAjaxDispatcher();
$dispatcher->handle('post_view_process', $_POST);
die;

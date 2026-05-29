<?php
$dispatcher = new \EscapeZoom\Core\Ajax\CoreAjaxDispatcher();
echo json_encode($dispatcher->handle('sort_products_get', $_POST));
die;

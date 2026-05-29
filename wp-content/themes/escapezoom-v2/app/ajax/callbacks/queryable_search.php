<?php
$dispatcher = new \EscapeZoom\Core\Ajax\CoreAjaxDispatcher();
echo json_encode($dispatcher->handle('queryable_search', $_POST));
die;

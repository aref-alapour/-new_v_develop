<?php
$dispatcher = new \EscapeZoom\Core\Ajax\CoreAjaxDispatcher();
echo json_encode(['status' => $dispatcher->handle('baz_kon', $_POST)]);
die;

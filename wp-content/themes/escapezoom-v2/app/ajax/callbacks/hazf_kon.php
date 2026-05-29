<?php
$dispatcher = new \EscapeZoom\Core\Ajax\CoreAjaxDispatcher();
echo json_encode(['status' => $dispatcher->handle('hazf_kon', $_POST)]);
die;

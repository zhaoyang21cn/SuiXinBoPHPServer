<?php
/**
 * 入口程序
 * Date: 2016/4/20
 */
require_once 'Path.php';
require_once  SERVICE_PATH . '/Server.php';
ini_set('date.timezone','Asia/Shanghai');
header("Access-Control-Allow-Origin: *");
$server = new Server();
$server->handle();







<?php
/**
 * User: alderzhang
 * Date: 2017/3/21
 * Time: 10:36
 */
require_once dirname(__FILE__) . '/Path.php';

// 开发人员调整1400019352为自己的appid,秘钥和公钥，并且设置录像SECRET_ID和SECRET_KEY
define('SDK_APP_ID', '1400019352');
define('PRIVATE_KEY', DEPS_PATH . '/sig/private_key');
define('PUBLIC_KEY', DEPS_PATH . '/sig/public_key');
define('VIDEO_RECORD_SECRET_ID', 'Your_Video_Secret_ID');
define('VIDEO_RECORD_SECRET_KEY', 'Your_Video_Secret_Key');
?>
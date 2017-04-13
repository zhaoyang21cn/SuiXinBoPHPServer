<?php
/**
 * User: alderzhang
 * Date: 2017/3/21
 * Time: 10:36
 */
require_once dirname(__FILE__) . '/Path.php';

// 开发人员调整以下参数
define('SDK_APP_ID', 'Your_SDK_APP_ID'); //APPID
define('PRIVATE_KEY', DEPS_PATH . '/sig/private_key'); //私钥文件
define('PUBLIC_KEY', DEPS_PATH . '/sig/public_key'); //公钥文件
define('VIDEO_RECORD_SECRET_ID', 'Your_Video_Secret_ID'); //录像Secret ID
define('VIDEO_RECORD_SECRET_KEY', 'Your_Video_Secret_Key'); //录像Secret Key
define('AUTHORIZATION_KEY', 'Your_Authrization_Key'); //权限密钥
?>
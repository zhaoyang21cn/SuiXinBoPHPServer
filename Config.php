<?php
/**
 * User: alderzhang
 * Date: 2017/3/21
 * Time: 10:36
 */
require_once dirname(__FILE__) . '/Path.php';

// 开发人员调整以下参数
define('DEFAULT_SDK_APP_ID', '{{.Your_SDK_APP_ID}}'); //默认APPID
define('CLOUDAPI_SECRET_ID', '{{.Your_CloudAPI_Secret_ID}}'); //云API Secret ID
define('CLOUDAPI_SECRET_KEY', '{{.Your_CloudAPI_Secret_Key}}'); //云API Secret Key
define('AUTHORIZATION_KEY', serialize([
    '{{.Your_SDK_APP_ID}}' => '{{.Your_Authrization_Key}}'
])); //权限密钥表

?>
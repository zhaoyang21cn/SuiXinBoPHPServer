<?php
/**
 * Date: 2016/4/19
 */

define('ERR_SUCCESS', 0);
define('ERR_INVALID_REQ', 10001);
define('ERR_REQ_JSON', 10002);
define('ERR_REQ_DATA', 10003);

//独立账号相关
define('ERR_REGISTER_USER_EXIST', 10004); //用户名已注册
define('ERR_USER_NOT_EXIST', 10005); //用户不存在
define('ERR_PASSWORD', 10006); //密码有误
define('ERR_REPEATE_LOGIN', 10007); //重复登录
define('ERR_REPEATE_LOGOUT', 10008); //重复退出
define('ERR_TOKEN_EXPIRE', 10009); //token过期
define('ERR_AV_ROOM_NOT_EXIST', 10010); //直播房间不存在

// 直播相关
define('ERR_LIVE_NO_AV_ROOM_ID', 20001);  // 用户没有av房间ID
define('ERR_USER_NO_LIVE', 20002);  // 用户没有在直播

define('ERR_SERVER', 90000);  // 服务器内部错误

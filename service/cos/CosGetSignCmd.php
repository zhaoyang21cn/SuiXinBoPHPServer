<?php
require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/Cmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once LIB_PATH . '/db/DB.php';
require_once DEPS_PATH . '/cos-php-sdk/include.php';
require_once DEPS_PATH . '/cos-php-sdk/Qcloud_cos/Auth.php';

use Qcloud_cos\Auth;

/**
 * Date: 2016/5/3
 */
class CosGetSignCmd extends Cmd
{

    public function parseInput()
    {
        // 没有输入参数
        return new CmdResp(ERR_SUCCESS, '');
    }
    
    public function handle()
    {
        $sign = Auth::appSign(time() + 30 * 24 * 60 * 60, 'sxbbucket');
        return new CmdResp(ERR_SUCCESS, '', array('sign' => $sign));
    }
}
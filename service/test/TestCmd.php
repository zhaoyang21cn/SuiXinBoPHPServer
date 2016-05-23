<?php
require_once SERVICE_PATH . 'Cmd.php';
require_once SERVICE_PATH . 'CmdResp.php';
require_once LIB_PATH . 'ErrorNo.php';
require_once DB_PATH . 'DB.php';
require_once SERVICE_PATH . 'ReqChecker.php';
require_once DEPS_PATH . 'cos-php-sdk/include.php';
require_once DEPS_PATH . 'cos-php-sdk/Qcloud_cos/Cosapi.php';

/**
 * Date: 2016/4/19
 */

use Qcloud_cos\Cosapi;

class TestCmd extends  Cmd
{
    public function parseInput()
    {
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
//        $sign = "pYuULhlsmAbX5L/CqCnsTOQxQ3dhPTEwMDIyODUzJms9QUtJRElXZTdBdEkxMFBRa204UkVEbDRVTzdJNm15bjZOREY3JmU9MjU5MjAwMCZ0PTE0NjIyNTY3MDEmcj0xODY0NDgwOTU5JmY9JmI9c3hiYnVja2V0";
//        $data = base64_decode($sign);
        $sign = "t7ga2qxHAudvI94NNLgNt6bTSUxhPTEwMDIyODUzJms9QUtJRElXZTdBdEkxMFBRa204UkVEbDRVTzdJNm15bjZOREY3JmU9MTQ2NDk1NjM5MyZ0PTE0NjIzNjQzOTMmcj0xNzczOTQyMTM2JmY9JmI9c3hiYnVja2V0";
        $data = base64_decode($sign);
        echo ($data);die;
        Cosapi::setTimeout(10);
        // 上传文件
        $uploadRet = Cosapi::upload( __DIR__ . '/test2.txt', 'sxbbucket', '/test123.txt');
        return new CmdResp(ERR_SUCCESS, '' , $uploadRet);
    }


}
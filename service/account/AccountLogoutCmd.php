<?php

/**
 * 用户注册接口
 * Date: 2016/11/15
 */

require_once dirname(__FILE__) . '/../../Path.php';
require_once SERVICE_PATH . '/Cmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/Account.php';

class AccountLogoutCmd extends Cmd
{
    private $account;
    
    public function __construct()
    {
        $this->account = new Account();
    }

    public function parseInput()
    {
        if (empty($this->req['token']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of token');
        }
        if (!is_string($this->req['token']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Invalid token');
        }
        $this->account->setToken($this->req['token']);
        $this->account->setLogoutTime(date('U'));
       
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        $errorMsg = '';
        $ret = $this->account->logout($errorMsg);        
        return new CmdResp($ret, $errorMsg);
    }
}

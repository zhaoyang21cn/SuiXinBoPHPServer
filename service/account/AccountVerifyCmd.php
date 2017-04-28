<?php

/**
 * 用户校验接口
 * Date: 2016/11/15
 */

require_once dirname(__FILE__) . '/../../Config.php';
require_once SERVICE_PATH . '/Cmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/Account.php';

class AccountVerifyCmd extends Cmd
{
    // 用户账号对象
    private $account;
    private $appid;
    private $privatekey;
    private $publickey;
    
    public function __construct()
    {
        $this->account = new Account();
    }

    public function parseInput()
    {
        if (empty($this->req['id']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of id');
        }
        if (!is_string($this->req['id']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Invalid id');
        }
        $this->account->setUser($this->req['id']);

        if (empty($this->req['sig']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of sig');
        }
        if (!is_string($this->req['sig']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Invalid sig');
        }
        $this->account->setUserSig($this->req['sig']);

        if (isset($this->req['appid']) && is_int($this->req['appid']))
        {
            $this->appid = strval($this->req['appid']);
        }
        else
        {
            $this->appid = DEFAULT_SDK_APP_ID;
        }

        $this->privatekey = KEYS_PATH . '/' . $this->appid . '/private_key';
        $this->publickey = KEYS_PATH . '/' . $this->appid . '/public_key';
        if(!file_exists($this->privatekey) || !file_exists($this->publickey)){
            return new CmdResp(ERR_REQ_DATA, 'Invalid appid');
        }
        
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        $account = $this->account;
        
        // 获取sig
        $ret = $account->verifyUserSig($this->appid, $this->publickey);

//        $data['publickey'] = $this->publickey;
//        $data['privatekey'] = $this->privatekey;
        if($ret != 0)
        {
            return new CmdResp(ERR_SERVER, 'Sig verify failed!', $data);
        }
        else
        {
            return new CmdResp(ERR_SUCCESS, 'Sig verify success!', $data);
        }
    }
}

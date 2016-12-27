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

class AccountLoginCmd extends Cmd
{
    // 用户账号对象
    private $account;
    
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
        
        if (empty($this->req['pwd']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of pwd');
        }
        if (!is_string($this->req['pwd']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Invalid pwd');
        }
        
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        $result = array();
        $account = $this->account;
        $errorMsg = '';
        
        // 获取用户账号信息
        $ret = $account->getAccountRecordByUserID($errorMsg);
        if($ret != ERR_SUCCESS)
        {
            return new CmdResp($ret, $errorMsg);
        }
        
        // 密码验证
        $ret = $account->authentication($this->req['pwd'], $errorMsg);
        if($ret != ERR_SUCCESS)
        {
            return new CmdResp($ret, $errorMsg);
        }
        
        // 获取sig
        // 开发人员调整1400019352为自己的appid,秘钥和公钥
        $private_key = DEPS_PATH . '/sig/private_key';
        $public_key = DEPS_PATH . '/sig/public_key';
        $userSig = $account->getUserSig();
        if(empty($userSig))
        {
            $userSig = $account->genUserSig('1400019352', $private_key);
            // 更新对象account的成员userSig
            $account->setUserSig($userSig);
        } 
        else 
        {
            $ret = $account->verifyUserSig('1400019352', $public_key);
            if($ret == 1) //过期重新生成
            {
                $userSig = $account->genUserSig('1400019352', $private_key);
                // 更新对象account的成员userSig
                $account->setUserSig($userSig);
            }
            else if($ret == -1) 
            {
                return new CmdResp(ERR_SERVER, 'Server error');
            }
        }
        if(empty($userSig))
            return new CmdResp(ERR_SERVER, 'Server error');

        $ret = $account->getState();
        if($ret == 1) //已登录
        {
            $data = array();
            $data['userSig'] = $account->getUserSig();
            $data['token'] = $account->getToken();
            return new CmdResp(ERR_SUCCESS, '', $data);
        }
        
        //获取token
        $token = $account->genToken();
        if(empty($token))
        {
            return new CmdResp(ERR_SERVER, 'Server error');
        }
        $account->setToken($token);
        
        $account->setLoginTime(date('U'));

        //登录，更新DB    
        $ret = $account->login($errorMsg);
        
        if($ret != ERR_SUCCESS)
        {
            return new CmdResp($ret, $errorMsg);
        }
        else
        {
            $data['userSig'] = $userSig;
            $data['token'] = $token;
            return new CmdResp(ERR_SUCCESS, '', $data);
        }
    }
}

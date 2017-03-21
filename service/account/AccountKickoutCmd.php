<?php

/**
 * 用户踢出接口
 * User: alderzhang
 * Date: 2017/3/21
 * Time: 10:10
 */

require_once dirname(__FILE__) . '/../../Config.php';
require_once SERVICE_PATH . '/Cmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/Account.php';

class AccountKickoutCmd extends Cmd
{

    public function __construct()
    {
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

        if (empty($this->req['pwd']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of pwd');
        }
        if (!is_string($this->req['pwd']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Invalid pwd');
        }

        if (empty($this->req['kickid']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of kickid');
        }
        if (!is_string($this->req['kickid']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Invalid kickid');
        }

        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        $account = new Account();
        $account->setUser($this->req['id']);
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
        $userSig = $account->getUserSig();
        if(empty($userSig))
        {
            $userSig = $account->genUserSig(SDK_APP_ID, PRIVATE_KEY);
            // 更新对象account的成员userSig
            $account->setUserSig($userSig);
        }
        else
        {
            $ret = $account->verifyUserSig(SDK_APP_ID, PUBLIC_KEY);
            if($ret == 1) //过期重新生成
            {
                $userSig = $account->genUserSig(SDK_APP_ID, PRIVATE_KEY);
                // 更新对象account的成员userSig
                $account->setUserSig($userSig);
            }
            else if($ret == -1)
            {
                return new CmdResp(ERR_SERVER, 'Server error:gen sig fail');
            }
        }
        if(empty($userSig))
            return new CmdResp(ERR_SERVER, 'Server error: gen sig fail');

        $kickAccount = new Account();
        $kickAccount->setUser($this->req['kickid']);

        // 获取用户账号信息
        $ret = $kickAccount->getAccountRecordByUserID($errorMsg);
        if($ret != ERR_SUCCESS)
        {
            return new CmdResp($ret, $errorMsg);
        }

        //踢出用户
        $holdSig = !empty($this->req['holdsig']) && $this->req['holdsig'];
        $ret = $kickAccount->kickout($errorMsg, SDK_APP_ID, $account->getUser(), $account->getUserSig(), $holdSig);

//        $data['admin'] = $account->getUser();
//        $data['userSig'] = $userSig;
//        $data['sdkappid'] = SDK_APP_ID;
        $data['kickID'] = $kickAccount->getUser();
        $data['holdSig'] = $holdSig;

        if($ret != ERR_SUCCESS)
        {
            return new CmdResp($ret, $errorMsg, $data);
        }
        else
        {
            return new CmdResp(ERR_SUCCESS, '', $data);
        }
    }
}
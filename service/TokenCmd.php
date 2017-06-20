<?php

/**
 * 新的命令基类
 * Date: 2016/11/18
 * Tips：相比Cmd类，主要增加Token过期验证，并将用户token转换成用户名
 *       新的接口都继承于此类，除了心跳
 */

require_once 'CmdResp.php';
require_once MODEL_PATH . '/Account.php';

abstract class TokenCmd
{

    protected $req;
    protected $user;

    private function loadJsonReq()
    {
        $data = file_get_contents('php://input');
        if (empty($data)) {
            $this->req = array();
            return true;
        }
        // 最大递归层数为12
        $this->req = json_decode($data, true, 12);
        //var_dump($this->req);
        //var_dump($data);
        //exit(0);
        return is_null($this->req) ? false : true;
    }

    abstract public function parseInput();

    abstract public function handle();

    public static function makeResp($errorCode, $errorInfo, $data = null)
    {
        $reply = array();
        if (is_array($data)) {
            $reply = $data;
        }
        $reply['errorCode'] = $errorCode;
        $reply['errorInfo'] = $errorInfo;
        return $reply;
    }

    public final function execute()
    {
        if (!$this->loadJsonReq()) {
            return new CmdResp(ERR_REQ_JSON, 'HTTP Request Json Parse Error');
        }

        if (empty($this->req['token'][0])) {
            return new CmdResp(ERR_REQ_DATA, 'Lack of token');
        }
        if (!is_string($this->req['token'][0])) {
            return new CmdResp(ERR_REQ_DATA, ' Invalid token');
        }

        $account = new Account();
        $account->setToken($this->req['token']);
        $errorMsg = '';
        $ret = $account->getAccountRecordByToken($errorMsg);
        if ($ret != ERR_SUCCESS) {
            return new CmdResp($ret, $errorMsg);
        }

        $lastRequestTime = $account->getLastRequestTime();

        $curr = date('U');
        if ($curr - $lastRequestTime > 7 * 24 * 60 * 60) {
            $ret = $account->logout($errorMsg);
            if ($ret != ERR_SUCCESS) {
                return new CmdResp($ret, $errorMsg);
            }

            return new CmdResp(ERR_TOKEN_EXPIRE, 'User token expired');
        }

        $account->setLastRequestTime($lastRequestTime);
        $ret = $account->updateLastRequestTime($errorMsg);
        if ($ret != ERR_SUCCESS) {
            return new CmdResp($ret, $errorMsg);
        }

        $this->user = $account->getUser();

        $resp = $this->parseInput();

        if (!$resp->isSuccess()) {
            return $resp;
        }
        $resp = $this->handle();
        return $resp;
    }
}

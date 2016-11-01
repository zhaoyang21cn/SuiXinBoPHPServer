<?php

require_once 'CmdResp.php';

/**
 * Date: 2016/4/19
 */
abstract class Cmd
{

    protected $req;

    private function loadJsonReq()
    {
        $data = file_get_contents('php://input');
        if (empty($data))
        {
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

    /**
     * @return CmdResp
     */
    abstract public function parseInput();

    abstract public function handle();
    
    public static function makeResp($errorCode, $errorInfo, $data = null)
    {
        $reply = array();
        if (is_array($data))
        {
            $reply = $data;
        }
        $reply['errorCode'] = $errorCode;
        $reply['errorInfo'] = $errorInfo;
        return $reply;
    }

    public final function execute()
    {
        if (!$this->loadJsonReq())
        {
            return new CmdResp(ERR_REQ_JSON, 'HTTP Request Json Parse Error');
        }
        $resp = $this->parseInput();
        if (!$resp->isSuccess())
        {
            return $resp;
        }
        $resp = $this->handle();
        return $resp;
    }
}

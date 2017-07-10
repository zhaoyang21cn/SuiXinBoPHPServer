<?php

/**
 * Created by PhpStorm.
 * User: alderzhang
 * Date: 2017/4/13
 * Time: 09:27
 */
require_once dirname(__FILE__) . '/../../Config.php';

require_once SERVICE_PATH . '/TokenCmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/AvRoom.php';

require_once LIB_PATH . '/log/FileLogHandler.php';
require_once LIB_PATH . '/log/Log.php';

class GetLinkSigCmd extends TokenCmd
{
    private $avRoom;
    private $appid;
    private $authorizationKey;

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

        if (!isset($this->req['roomnum']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of roomnum');
        }
        if (!is_int($this->req['roomnum']))
        {
            return new CmdResp(ERR_REQ_DATA, ' Invalid roomnum');
        }

        if (!isset($this->req['current_roomnum']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of current_roomnum');
        }
        if (!is_int($this->req['current_roomnum']))
        {
            return new CmdResp(ERR_REQ_DATA, ' Invalid current_roomnum');
        }

        $this->avRoom = new AvRoom($this->user);
        //Log::info('get link sig, set current roomnum:'.$this->req['current_roomnum']);
        $this->avRoom->setId($this->req['current_roomnum']);

        if (isset($this->req['appid']) && is_int($this->req['appid']))
        {
            $this->appid = strval($this->req['appid']);
        }
        else
        {
            $this->appid = DEFAULT_SDK_APP_ID;
        }

        $keys = unserialize(AUTHORIZATION_KEY);

        if(empty($keys[$this->appid]) || !is_string($keys[$this->appid])){
            return new CmdResp(ERR_REQ_DATA, 'Invalid appid');
        }
        else{
            $this->authorizationKey = $keys[$this->appid];
        }

        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        $linkSig = '';
        $errorMsg = '';

        $ret = AvRoom::getRoomById($this->avRoom->getId());
        if (empty($ret))// 加载房间出错
        {
            //Log::error('get link sig get room by id '.$this->avRoom->getId().' failed');
            return new CmdResp(ERR_SERVER, 'User av room not exists');
        }

        $ret = $this->avRoom->getLinkSig($this->authorizationKey, $this->req['id'], $this->req['roomnum'], $linkSig, $errorMsg);
        if($ret != ERR_SUCCESS)
        {
            return new CmdResp($ret, $errorMsg);
        }

        $data = array(
            'linksig' => $linkSig,
//            'appid' => $this->appid,
//            'key' => $this->authorizationKey,
        );
        return new CmdResp(ERR_SUCCESS, '', $data);
    }
}
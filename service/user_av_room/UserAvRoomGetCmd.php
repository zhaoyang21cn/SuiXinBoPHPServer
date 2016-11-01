<?php

require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/Cmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/UserAvRoom.php';

/**
 * Date: 2016/4/22
 */
class UserAvRoomGetCmd extends Cmd
{

    private $userAvRoom;

    public function parseInput()
    {
        if (empty($this->req['uid']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of uid');
        }
        $this->userAvRoom = new UserAvRoom($this->req['uid']);
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        $ret = $this->userAvRoom->load();
        // 出错
        if ($ret < 0)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error'); 
        }
        // 不存在
        if ($ret == 0)
        {
            if (!$this->userAvRoom->create())
            {
                return new CmdResp(ERR_SERVER, 'Server internal error'); 
            }
        }
        $id = $this->userAvRoom->getId();
        return new CmdResp(ERR_SUCCESS, '', array('avRoomId' => (int)$id));
    }

}

<?php
/**
 * 房间成员上报接口
 * Date: 2016/11/17
 */
require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/TokenCmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/InteractAvRoom.php';


class ReportRoomMemberCmd extends TokenCmd
{
    private $interactAvRoom;
    private $operate;

    public function parseInput()
    {
        if (!isset($this->req['roomnum']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of roomnum');
        }

        if (!is_int($this->req['roomnum'])) {
            if (is_string($this->req['roomnum'])) {
                $this->req['roomnum'] = intval($this->req['roomnum']);
            } else {
                return new CmdResp(ERR_REQ_DATA, ' Invalid roomnum');
            }
        }
        
        if (!isset($this->req['role']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of role');
        }
        
        if (!is_int($this->req['role']))
        {
             return new CmdResp(ERR_REQ_DATA, ' Invalid role');
        }
        
        if (!isset($this->req['operate']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of operate');
        }
        
        if (!is_int($this->req['operate']))
        {
             return new CmdResp(ERR_REQ_DATA, ' Invalid operate');
        }
        $this->operate = $this->req['operate'];
        $this->interactAvRoom = new InteractAvRoom($this->user, $this->req['roomnum'], 'off',  $this->req['role']);
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        //检查直播房间是否存在
        if($this->interactAvRoom->getAvRoomId() == 0)
        {
            return new CmdResp(ERR_AV_ROOM_NOT_EXIST, 'av room is not exist'); 
        }
        $ret = false;
        if($this->operate == 0) //成员进入房间
        {
            $ret = $this->interactAvRoom->enterRoom();
        }
        if($this->operate == 1) //成员退出房间
        {
            $ret = $this->interactAvRoom->exitRoom();
        }

        if (!$ret)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error'); 
        }
 
        return new CmdResp(ERR_SUCCESS, '');
    }    
}

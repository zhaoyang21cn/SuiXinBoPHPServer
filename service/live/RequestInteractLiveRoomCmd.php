<?php
/**
 * 房间上麦请求接口（直播互动）
 * Date: 2016/11/17
 */
require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/TokenCmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/InteractAvRoom.php';

class RequestInteractLiveRoomCmd extends TokenCmd
{
    private $interactAvRoom;

    public function parseInput()
    {
        if (!isset($this->req['roomnum'])) {
            return new CmdResp(ERR_REQ_DATA, 'Lack of roomnum');
        }
        if (!is_int($this->req['roomnum'])) {
            if (is_string($this->req['roomnum'])) {
                $this->req['roomnum'] = intval($this->req['roomnum']);
            } else {
                return new CmdResp(ERR_REQ_DATA, 'Invalid roomnum');
            }
        }

        $this->interactAvRoom = new InteractAvRoom($this->user, $this->req['roomnum']);
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        //检查直播房间是否存在
        if ($this->interactAvRoom->getAvRoomId() == 0) {
            return new CmdResp(ERR_AV_ROOM_NOT_EXIST, 'av room is not exist');
        }

        //上麦
        $ret = $this->interactAvRoom->enterRoom();
        if (!$ret) {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }

        return new CmdResp(ERR_SUCCESS, '');
    }
}

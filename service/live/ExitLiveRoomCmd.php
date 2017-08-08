<?php
/**
 * 退出房间接口
 * Date: 2016/11/17
 */
require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/TokenCmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/AvRoom.php';
require_once MODEL_PATH . '/InteractAvRoom.php';
require_once MODEL_PATH . '/NewLiveRecord.php';

class ExitLiveRoomCmd extends TokenCmd
{

    private $avRoom;

    public function parseInput()
    {
        if (empty($this->req['roomnum'])) {
            return new CmdResp(ERR_REQ_DATA, 'Lack of roomnum');
        }
        if (!is_int($this->req['roomnum'])) {
            if (is_string($this->req['roomnum'])) {
                $this->req['roomnum'] = intval($this->req['roomnum']);
            } else {
                return new CmdResp(ERR_REQ_DATA, 'Invalid roomnum');
            }
        }

        if (empty($this->req['type'])) {
            return new CmdResp(ERR_REQ_DATA, 'Lack of type');
        }
        if (!is_string($this->req['type'])) {
            return new CmdResp(ERR_REQ_DATA, ' Invalid type');
        }

        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        //删除直播记录
        $ret = NewLiveRecord::delete($this->user);
        if (!$ret) {
            return new CmdResp(ERR_SERVER, 'Server internal error: Delete live record fail');
        }

        //清空房间成员
        $ret = InteractAvRoom::ClearRoomByRoomNum($this->req['roomnum']);
        if (!$ret) {
            return new CmdResp(ERR_SERVER, 'Server internal error: Delete member list fail');
        }

        //更新 以该uid为主播的room num房间结束时间
        AvRoom::finishRoomByUidAndRoomNum($this->user, $this->req['roomnum']);

        return new CmdResp(ERR_SUCCESS, '');
    }
}

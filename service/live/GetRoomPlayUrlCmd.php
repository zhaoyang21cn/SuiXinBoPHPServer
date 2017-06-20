<?php
/**
 * 拉取本房间的推流地址接口
 * Date: 2016/12/29
 */
require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/TokenCmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/NewLiveRecord.php';

class GetRoomPlayUrlCmd extends TokenCmd
{
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

        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        //获取url
        $data = NewLiveRecord::getLiveStreamByRoomID(0, $this->req['roomnum']);
        if (is_null($data)) {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }

        return new CmdResp(ERR_SUCCESS, '', $data);
    }
}

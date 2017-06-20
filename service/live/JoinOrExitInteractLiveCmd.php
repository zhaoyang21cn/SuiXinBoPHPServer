<?php
/**
 * 房间成员加入和退出互动（上下麦）上报接口
 * Date: 2016/11/17
 */
require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/TokenCmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/InteractAvRoom.php';


class JoinOrExitInteractLiveCmd extends TokenCmd
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

        if (!isset($this->req['status'])) {
            return new CmdResp(ERR_REQ_DATA, 'Lack of status');
        }

        if (!is_string($this->req['status'])) {
            return new CmdResp(ERR_REQ_DATA, ' Invalid status');
        }

        $this->interactAvRoom = new InteractAvRoom($this->user, $this->req['roomnum'], $this->req['status']);
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        //更新互动状态
        $ret = $this->interactAvRoom->updateStatus();
        if (!$ret) {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }

        return new CmdResp(ERR_SUCCESS, '');
    }
}

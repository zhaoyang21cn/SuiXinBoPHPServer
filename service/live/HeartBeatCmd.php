<?php
/**
 * 心跳接口
 * Date: 2016/11/18
 */
require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/Cmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/Account.php';
require_once MODEL_PATH . '/InteractAvRoom.php';
require_once MODEL_PATH . '/NewLiveRecord.php';
require_once MODEL_PATH . '/AvRoom.php';
require_once LIB_PATH . '/db/DB.php';

class HeartBeatCmd extends Cmd
{
    private $token;
    private $roomnum;
    private $role;
    private $thumbup = 0;
    private $modifyTime;
    private $video_type = 0;

    public function parseInput()
    {
        if (!isset($this->req['token'])) {
            return new CmdResp(ERR_REQ_DATA, 'Lack of token');
        }
        if (!is_string($this->req['token'])) {
            return new CmdResp(ERR_REQ_DATA, ' Invalid token');
        }

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

        if (!isset($this->req['role'])) {
            return new CmdResp(ERR_REQ_DATA, 'Lack of role');
        }
        if (!is_int($this->req['role'])) {
            return new CmdResp(ERR_REQ_DATA, 'Invalid role');
        }

        if (isset($this->req['thumbup']) && is_int($this->req['thumbup'])) {
            $this->thumbup = $this->req['thumbup'];
        }

        $this->token = $this->req['token'];
        $this->roomnum = $this->req['roomnum'];
        $this->role = $this->req['role'];
        $this->modifyTime = date('U');

        if (isset($this->req['video_type'])) {
            $this->video_type = $this->req['video_type'];
        }

        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        $errorMsg = '';
        $account = new Account();
        $account->setToken($this->token);

        //获取用户名
        $ret = $account->getAccountUidByToken($errorMsg);
        if ($ret != ERR_SUCCESS) {
            return new CmdResp($ret, $errorMsg);
        }

        //更新房间成员心跳
        $uid = $account->getUser();
        $ret = InteractAvRoom::updateLastUpdateTimeByUid($uid, $this->role, $this->modifyTime, $this->video_type);
        if (!$ret) {
            return new CmdResp(ERR_SERVER, 'Server error: update time fail');
        }

        //更新直播信息
        $data = array();
        $data['admire_count'] = $this->thumbup;
        $data['modify_time'] = $this->modifyTime;
        $ret = NewLiveRecord::updateByHostUid($uid, $data);
        if ($ret == -1) {
            return new CmdResp(ERR_SERVER, 'Server error: update live record time fail');
        }

        AvRoom::updateLastUpdateTimeByUidAndRoomNum($uid, $this->roomnum, $this->modifyTime);

        //更新用户最新请求时间
        $account->setLastRequestTime($this->modifyTime);
        $ret = $account->updateLastRequestTime($errorMsg);
        if ($ret != ERR_SUCCESS) {
            return new CmdResp($ret, $errorMsg);
        }

        return new CmdResp(ERR_SUCCESS, '');
    }
}

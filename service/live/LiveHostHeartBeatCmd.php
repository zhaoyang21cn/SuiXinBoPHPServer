<?php
/**
 * 直播心跳接口
 * Date: 2016/4/22
 */
require_once dirname(__FILE__) . '/../../Path.php';

require_once 'LiveModifyCmd.php';
require_once SERVICE_PATH . '/Cmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/LiveRecord.php';
require_once CLIENT_DATA_PATH . '/CliLiveData.php';
require_once LIB_PATH . '/db/DB.php';

/**
 * Date: 2016/4/22
 */
class LiveHostHeartBeatCmd extends LiveModifyCmd
{

    public function parseInput()
    {
        if (empty($this->req['uid']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of uid');
        }
        if (!is_string($this->req['uid']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Invalid uid');
        }
        $this->uid = $this->req['uid'];
        $fields = array(
            'admireCount',
            'watchCount',
            'timeSpan',
        );
        $data = array();
        foreach ($fields as $field)
        {
            if (!isset($this->req[$field]))
            {
                return new CmdResp(ERR_REQ_DATA, 'Lack of ' . $field);
            }
            $val = $this->req[$field];
            if ($val !== (int)$val || $val < 0)
            {
                return new CmdResp(ERR_REQ_DATA, $field . ' should be a non-negative number');
            }
            $data[$field] = $val;
        }
        $this->watchCount = $data['watchCount'];
        $this->timeSpan = $data['timeSpan'];
        $this->admireCount = $data['admireCount'];
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        $record = new LiveRecord();
        $cnt = $record->loadByHostUid($this->uid);
        if ($cnt < 0)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }
        if ($cnt === 0)
        {
            return new CmdResp(ERR_USER_NO_LIVE, 'The user is not in live');
        }
        $fields = array(
            LiveRecord::FIELD_WATCH_COUNT => $this->watchCount,
            LiveRecord::FIELD_TIME_SPAN => $this->timeSpan,
            LiveRecord::FIELD_ADMIRE_COUNT => $this->admireCount,
        );
        $cnt = LiveRecord::updateByHostUid($this->uid, $fields);
        if ($cnt < 0)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }
        return new CmdResp(ERR_SUCCESS, '');
    }

}